@extends("layouts.layout")
@section("title", "Обработка ЕГРН выписок")

@section("content")
<!-- CSRF токен (ОБЯЗАТЕЛЬНО сверху!) -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Основной контент -->
<div class="container mt-4">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-primary">
                <i class="bi bi-file-text"></i> Обработка ЕГРН выписок
            </h1>
            <p class="lead">Загрузите PDF файлы выписок ЕГРН для автоматической обработки</p>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center shadow">
                <div class="card-body">
                    <h3 class="card-title" id="totalFiles">0</h3>
                    <p class="card-text">Всего файлов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center shadow">
                <div class="card-body">
                    <h3 class="card-title" id="processedFiles">0</h3>
                    <p class="card-text">Обработано</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center shadow">
                <div class="card-body">
                    <h3 class="card-title" id="successFiles">0</h3>
                    <p class="card-text">Успешно</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card text-center shadow">
                <div class="card-body">
                    <h3 class="card-title" id="errorFiles">0</h3>
                    <p class="card-text">С ошибками</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Область загрузки -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud-upload"></i> Загрузка файлов
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Drop Zone -->
                    <div class="drop-zone mb-3 p-4 border rounded-3 text-center" id="dropZone">
                        <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3"></i>
                        <h5>Перетащите файлы сюда</h5>
                        <p class="text-muted">или нажмите для выбора файлов</p>
                        <p class="small text-muted">Поддерживаются PDF файлы ЕГРН выписок (макс. 50 файлов)</p>
                        <input type="file" id="fileInput" multiple accept=".pdf" style="display: none;">
                        <button class="btn btn-primary mt-2" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-folder2-open"></i> Выбрать файлы
                        </button>
                    </div>

                    <!-- Список файлов -->
                    <div class="file-list card" id="fileList">
                        <div class="card-header">
                            <h6 class="mb-0">Загруженные файлы <span class="badge bg-primary fs-6" id="filesCount">0</span></h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="text-center p-3 text-muted" id="emptyFileList">
                                Файлы не загружены
                            </div>
                            <div id="fileItems"></div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <button class="btn btn-outline-secondary" id="clearAllBtn" disabled>
                            <i class="bi bi-trash"></i> Очистить все
                        </button>
                        <button class="btn btn-success" id="processBtn" disabled>
                            <i class="bi bi-gear"></i> Начать обработку
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Панель статуса -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity"></i> Статус обработки
                    </h5>
                </div>
                <div class="card-body">
                    <div id="processingStatus">
                        <p class="text-muted text-center">Ожидание загрузки файлов...</p>
                    </div>
                    <div class="progress mt-3" style="display: none;" id="progressBar">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progressBarInner">0%</div>
                    </div>
                    
                    <!-- Кнопка отмены во время обработки -->
                    <div class="mt-3" id="cancelBtnContainer" style="display: none;">
                        <button class="btn btn-danger w-100" id="cancelBtn">
                            <i class="bi bi-stop-circle"></i> Отмена операции
                        </button>
                    </div>
                </div>
            </div>

            <!-- Результаты -->
            <div class="card shadow-sm mt-4 result-card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-download"></i> Результаты
                    </h5>
                </div>
                <div class="card-body">
                    <div id="resultsContent">
                        <p class="text-muted text-center">Результаты появятся здесь после обработки</p>
                    </div>
                    <div class="d-grid gap-2 mt-3" id="resultsButtons" style="display: none;">
                        <!-- Кнопки результатов будут добавлены динамически -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let files = [];
let processing = false;
let eventSource = null; // Для отмены SSE

// Элементы DOM
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileItems = document.getElementById('fileItems');
const emptyFileList = document.getElementById('emptyFileList');
const processBtn = document.getElementById('processBtn');
const clearAllBtn = document.getElementById('clearAllBtn');
const processingStatus = document.getElementById('processingStatus');
const progressBar = document.getElementById('progressBar');
const progressBarInner = document.getElementById('progressBarInner');
const resultsContent = document.getElementById('resultsContent');
const resultsButtons = document.getElementById('resultsButtons');
const filesCount = document.getElementById('filesCount');
const cancelBtnContainer = document.getElementById('cancelBtnContainer');
const cancelBtn = document.getElementById('cancelBtn');

// Статистические элементы
const totalFilesEl = document.getElementById('totalFiles');
const processedFilesEl = document.getElementById('processedFiles');
const successFilesEl = document.getElementById('successFiles');
const errorFilesEl = document.getElementById('errorFiles');

// Drag & Drop события
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
});
['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
});

dropZone.addEventListener('drop', handleDrop, false);
fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

function handleDrop(e) {
    handleFiles(e.dataTransfer.files);
}

function handleFiles(fileList) {
    Array.from(fileList).forEach(file => {
        if (file.type === 'application/pdf' && file.size <= 10 * 1024 * 1024) {
            files.push({
                file: file,
                name: file.name,
                size: file.size,
                status: 'pending'
            });
        } else {
            alert(`Файл ${file.name} слишком большой (${(file.size/1024/1024).toFixed(1)}MB). Максимум 10MB!`);
        }
    });
    updateUI();
}

// ✅ НОВАЯ ОБРАБОТКА - полный сброс
function resetAll() {
    files = [];
    processing = false;
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
    progressBar.style.display = 'none';
    cancelBtnContainer.style.display = 'none';
    resultsButtons.style.display = 'none';
    resultsContent.innerHTML = '<p class="text-muted text-center">Результаты появятся здесь после обработки</p>';
    processingStatus.innerHTML = '<p class="text-muted text-center">Ожидание загрузки файлов...</p>';
    updateUI();
}

// ✅ ОТМЕНА ОПЕРАЦИИ - переход на главную
function cancelOperation() {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
    window.location.href = "{{ route('index') }}";
}

// Обновление статистики
function updateStats() {
    const total = files.length;
    const processed = files.filter(f => f.status !== 'pending').length;
    const success = files.filter(f => f.status === 'success').length;
    const error = files.filter(f => f.status === 'error').length;
    
    totalFilesEl.textContent = total;
    processedFilesEl.textContent = processed;
    successFilesEl.textContent = success;
    errorFilesEl.textContent = error;
    
    // Обновление прогресс-бара
    if (total > 0) {
        const progress = (processed / total * 100).toFixed(1);
        progressBarInner.style.width = `${progress}%`;
        progressBarInner.textContent = `${progress}%`;
    }
}

// Обновление интерфейса
function updateUI() {
    filesCount.textContent = files.length;
    updateStats();
    
    if (files.length === 0) {
        emptyFileList.style.display = 'block';
        fileItems.innerHTML = '';
        processBtn.disabled = true;
        clearAllBtn.disabled = true;
    } else {
        emptyFileList.style.display = 'none';
        fileItems.innerHTML = files.map((file, index) => `
            <div class="d-flex justify-content-between align-items-center p-2 border-bottom file-item">
                <div>
                    <i class="bi bi-file-pdf-fill text-danger me-2"></i>
                    <strong>${file.name}</strong>
                    <br><small class="text-muted">${(file.size/1024/1024).toFixed(1)} MB</small>
                </div>
                <div>
                    <span class="badge ${getStatusBadge(file.status)}">${getStatusText(file.status)}</span>
                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile(${index})" ${processing ? 'disabled' : ''}>
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        `).join('');
        processBtn.disabled = processing || files.length === 0;
        clearAllBtn.disabled = processing || files.length === 0;
    }
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'bg-secondary',
        'processing': 'bg-warning',
        'success': 'bg-success',
        'error': 'bg-danger'
    };
    return badges[status] || 'bg-secondary';
}

function getStatusText(status) {
    const texts = {
        'pending': 'Ожидание',
        'processing': 'Обработка...',
        'success': 'Готово ✓',
        'error': 'Ошибка ✗'
    };
    return texts[status] || 'Ожидание';
}

function removeFile(index) {
    if (processing) return;
    files.splice(index, 1);
    updateUI();
}

clearAllBtn.addEventListener('click', () => {
    if (processing) return;
    files = [];
    updateUI();
});

// ✅ КНОПКА ОТМЕНЫ
cancelBtn.addEventListener('click', cancelOperation);

// ✅ ГЛАВНАЯ КНОПКА ОБРАБОТКИ
processBtn.addEventListener('click', async function() {
    if (processing || files.length === 0) return;
    
    processing = true;
    processBtn.disabled = true;
    clearAllBtn.disabled = true;
    progressBar.style.display = 'block';
    cancelBtnContainer.style.display = 'block';
    
    // Обновляем статусы всех файлов на "processing"
    files.forEach(f => f.status = 'processing');
    updateUI();
    
    processingStatus.innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-arrow-repeat"></i> Отправка файлов в Python API...
        </div>
    `;
    
    const formData = new FormData();
    files.forEach((f, index) => {
        console.log(`📁 Добавляем файл ${index + 1}: ${f.name}`);
        formData.append('files[]', f.file, f.name);
    });
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    try {
        const response = await fetch('/api/parse-egrn', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Python API ответ:', data);
        
        if (!response.ok || !data.success) {
            throw new Error(data.error || data.message || 'Ошибка сервера');
        }
        
        // ✅ УСПЕХ!
        files.forEach(f => f.status = 'success');
        updateUI();
        
        processingStatus.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> ✅ ${data.message}
                <hr class="my-2">
                <small>Файл: <strong>${data.excel_filename}</strong> (${data.file_size})</small>
            </div>
        `;
        
        resultsContent.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle"></i> Обработка завершена!</h6>
                <p>${data.message}</p>
                <small>Excel: ${data.excel_filename} (${data.file_size})</small>
            </div>
        `;
        
        // ✅ КНОПКИ РЕЗУЛЬТАТОВ с НОВОЙ ОБРАБОТКОЙ
        resultsButtons.innerHTML = `
            <a href="/api/download/${data.excel_filename}" class="btn btn-success w-100 mb-2" download>
                <i class="bi bi-file-earmark-excel"></i> Скачать Excel (${data.file_size})
            </a>
            <button class="btn btn-primary w-100 mb-2" onclick="resetAll()">
                <i class="bi bi-arrow-clockwise"></i> Новая обработка
            </button>
            <a href="{{ route('index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-house"></i> На главную
            </a>
        `;
        resultsButtons.style.display = 'block';
        cancelBtnContainer.style.display = 'none';
        
    } catch (error) {
        console.error('Ошибка:', error);
        files.forEach(f => f.status = 'error');
        updateUI();
        
        processingStatus.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> ❌ ${error.message}
            </div>
        `;
        cancelBtnContainer.style.display = 'none';
    }
    
    processing = false;
    progressBar.style.display = 'none';
    updateUI();
});

// ✅ Функция очистки результатов (устаревшая, теперь используется resetAll())
function clearResults() {
    resetAll();
}

// Инициализация
updateUI();
</script>

<style>
.drop-zone {
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
    cursor: pointer;
}
.drop-zone.dragover {
    border-color: #0d6efd;
    background-color: #f8f9ff;
}
.drop-zone:hover {
    border-color: #0d6efd;
}
.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}
.file-item {
    border-bottom: 1px solid #eee;
}
.file-item:last-child {
    border-bottom: none;
}
#progressBarInner {
    transition: width 0.3s ease;
}
#cancelBtn {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>
@endsection
