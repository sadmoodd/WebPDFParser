<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EgrnController extends Controller
{
    private $flaskApiUrl = 'http://localhost:5000';
    private $sharedUploadsDir = '/home/frelomm/nethammer/FINAL_BTW/shared_uploads';

    public function index()
    {
        return view('index');
    }

    private function getDefaultColumns(): array
    {
        return [
            '№ п/п',
            'Адрес, комплекс', 
            'Наименование здания',
            'Литера / Строение',
            'Кадастр. номер ЗУ',
            'Кадастр. номер здания',
            '№ помещения',
            'Этаж',
            'Площадь (м²)',
            'Предполагаемое назначение',
            'Статус',
            'Арендатор',
            'Подтверждение из PDF',
            'Примечания и расхождения',
            'Собственник',
            'Обременение (аренда)',
            'PDF-источник'
        ];
    }

    public function processEgrn(Request $request)
    {
        $request->merge(['_ignore_size_limit' => true]);

    // 🔥 СУПЕР-ДИАГНОСТИКА!
        $files = $request->file('files');
        Log::info('🚀 EGRN PROCESS START', [
            'files_keys' => array_keys($request->file() ?: []),
            'files_count' => $request->hasFile('files') ? 
                (is_array($request->file('files')) ? count($request->file('files')) : 1) : 0
        ]);


        try {
            // ✅ ИСПРАВЛЕНО: правильно получаем файлы
            $files = $request->file('files');
            Log::info('🔥 FILES DEBUG', [
                'files_type' => gettype($files),
                'is_array' => is_array($files),
                'files_count_real' => is_array($files) ? count($files) : 1,
                'files_class' => $files instanceof \Illuminate\Http\UploadedFile ? 'SingleFile' : 'Array',
                'request_files_all' => count($request->allFiles())
            ]);
            // 🔥 Логика для 1 файла ИЛИ массива файлов
            if ($files instanceof \Illuminate\Http\UploadedFile) {
                // ОДИН файл
                $allFiles = [$files];
                Log::info('🔸 ОДИН файл');
            } elseif (is_array($files)) {
                // МНОГО файлов
                $allFiles = $files;
                Log::info('🔸 МНОГО файлов: ' . count($allFiles));
            } else {
                return response()->json(['success' => false, 'error' => 'Нет файлов'], 400);
            }

            // ✅ Фильтруем только PDF
            $validFiles = [];
            foreach ($allFiles as $file) {
                if ($file && $file->isValid() && 
                    $file->getMimeType() === 'application/pdf' && 
                    $file->getSize() <= 10*1024*1024) {
                    $validFiles[] = $file;
                }
            }

            if (empty($validFiles)) {
                return response()->json(['success' => false, 'error' => 'Нет валидных PDF'], 400);
            }

            Log::info('✅ VALID FILES COUNT', ['count' => count($validFiles)]);

            // ✅ Сохраняем с безопасными именами
            $pdfPaths = [];
            foreach ($validFiles as $file) {
                $safeName = 'egrn_' . time() . '_' . Str::random(8) . '.pdf';
                $fullPath = $this->sharedUploadsDir . '/' . $safeName;
                
                if (!is_dir($this->sharedUploadsDir)) {
                    mkdir($this->sharedUploadsDir, 0755, true);
                }
                
                $file->move($this->sharedUploadsDir, $safeName);
                $pdfPaths[] = $fullPath;
                Log::info('✅ SAVED FILE', ['filename' => $safeName]);
            }

            // ✅ Отправляем в Python
            Log::info('📤 TO PYTHON', ['count' => count($pdfPaths)]);
            $pythonResponse = Http::timeout(600)->post($this->flaskApiUrl . '/api/parse-egrn', [
                'pdf_paths' => $pdfPaths,
                'columns' => $this->getDefaultColumns()
            ]);

            Log::info('📥 PYTHON RESPONSE', [
                'status' => $pythonResponse->status(),
                'body_preview' => substr($pythonResponse->body(), 0, 200)
            ]);

            if (!$pythonResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Python API недоступен',
                    'details' => $pythonResponse->body()
                ], 502);
            }

            $data = $pythonResponse->json();
            if (!$data['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error'] ?? 'Python обработка не удалась'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $data['message'],
                'excel_filename' => $data['excel_filename'],
                'file_size' => $data['file_size'],
                'rows' => $data['rows']
            ]);

        } catch (\Exception $e) {
            Log::error('💥 CRASH', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }

    public function downloadResult($filename)
    {
        $flaskUrl = $this->flaskApiUrl . '/api/download/' . $filename;
        $response = Http::timeout(60)->get($flaskUrl);
        
        if (!$response->successful()) {
            abort(404, 'Файл не найден');
        }

        return response($response->body())
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
