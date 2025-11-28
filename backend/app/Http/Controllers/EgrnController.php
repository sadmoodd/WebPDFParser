<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EgrnController extends Controller
{
    private $flaskApiUrl = 'http://localhost:5000'; // Измените на адрес вашего Flask сервера

    public function index()
    {
        return view('index');
    }

    public function processEgrn(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:50',
            'files.*' => 'required|file|mimes:pdf|max:10240', // 10MB на файл
            'columns' => 'nullable|array',
            'columns.*' => 'string'
        ]);

        // Сохраняем PDF файлы временно
        $pdfPaths = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('uploads/egrn', 'local');
            $pdfPaths[] = storage_path('app/' . $path);
        }

        // Формируем запрос к Flask API
        $response = Http::timeout(300)->post($this->flaskApiUrl . '/api/parse-egrn', [
            'pdf_paths' => $pdfPaths,
            'columns' => $request->columns ?? []
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'error' => $response->json('error', 'Ошибка API')
            ], 400);
        }

        $data = $response->json();
        if (!$data['success']) {
            return response()->json($data, 400);
        }

        return response()->json($data);
    }

    public function downloadResult(Request $request, $filename)
    {
        $flaskUrl = $this->flaskApiUrl . '/download/' . $filename;
        
        $response = Http::timeout(60)->get($flaskUrl);
        
        if (!$response->successful()) {
            abort(404, 'Файл не найден');
        }

        return response($response->body())
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
