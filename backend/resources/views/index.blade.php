@extends("layouts.layout")
@section("title", "Обработка ЕГРН выписок")

@section("content")

    <!-- Основной контент -->
    <div class="container mt-4">
        <!-- Заголовок и описание -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-primary">
                    <i class="bi bi-file-text"></i>
                    Обработка ЕГРН выписок
                </h1>
                <p class="lead">Загрузите PDF файлы выписок ЕГРН для автоматической обработки и извлечения данных</p>
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
                            <i class="bi bi-cloud-upload"></i>
                            Загрузка файлов
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Drop Zone -->
                        <div class="drop-zone mb-3" id="dropZone">
                            <i class="bi bi-cloud-arrow-up display-4 text-muted mb-3"></i>
                            <h5>Перетащите файлы сюда</h5>
                            <p class="text-muted">или нажмите для выбора файлов</p>
                            <p class="small text-muted">Поддерживаются PDF файлы ЕГРН выписок</p>
                            <input type="file" id="fileInput" multiple accept=".pdf" style="display: none;">
                            <button class="btn btn-primary mt-2" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-folder2-open"></i>
                                Выбрать файлы
                            </button>
                        </div>

                        <!-- Список файлов -->
                        <div class="file-list card" id="fileList">
                            <div class="card-header">
                                <h6 class="mb-0">Загруженные файлы</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="text-center p-3 text-muted" id="emptyFileList">
                                    Файлы не загружены
                                </div>
                                <div id="fileItems"></div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <button class="btn btn-outline-secondary" id="clearAllBtn" disabled>
                                <i class="bi bi-trash"></i>
                                Очистить все
                            </button>
                            <button class="btn btn-success" id="processBtn" disabled>
                                <i class="bi bi-gear"></i>
                                Начать обработку
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
                            <i class="bi bi-activity"></i>
                            Статус обработки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="processingStatus">
                            <p class="text-muted text-center">Ожидание загрузки файлов...</p>
                        </div>
                        <div class="progress mt-3" style="display: none;" id="progressBar">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Результаты -->
                <div class="card shadow-sm mt-4 result-card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-download"></i>
                            Результаты
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContent">
                            <p class="text-muted text-center">Результаты появятся здесь после обработки</p>
                        </div>
                        <div class="d-grid gap-2 mt-3" id="resultsButtons" style="display: none;">
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-file-excel"></i>
                                Скачать Excel
                            </button>
                            <button class="btn btn-outline-success">
                                <i class="bi bi-file-text"></i>
                                Скачать отчет
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Переменные для хранения состояния
        let files = [];
        let processing = false;

        // Элементы DOM
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const fileItems = document.getElementById('fileItems');
        const emptyFileList = document.getElementById('emptyFileList');
        const processBtn = document.getElementById('processBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');
        const processingStatus = document.getElementById('processingStatus');
        const progressBar = document.getElementById('progressBar');
        const resultsButtons = document.getElementById('resultsButtons');

        // Обработчики событий для Drop Zone
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // Обработчик выбора файлов через input
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        // Функция обработки файлов
        function handleFiles(newFiles) {
            for (let file of newFiles) {
                if (file.type === 'application/pdf') {
                    files.push({
                        file: file,
                        id: Date.now() + Math.random(),
                        status: 'pending'
                    });
                }
            }
            updateFileList();
            updateStatistics();
        }

        // Обновление списка файлов
        function updateFileList() {
            if (files.length === 0) {
                emptyFileList.style.display = 'block';
                fileItems.innerHTML = '';
                processBtn.disabled = true;
                clearAllBtn.disabled = true;
            } else {
                emptyFileList.style.display = 'none';
                fileItems.innerHTML = files.map(file => `
                    <div class="file-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-pdf text-danger"></i>
                            <span class="ms-2">${file.file.name}</span>
                            <small class="text-muted ms-2">(${(file.file.size / 1024 / 1024).toFixed(2)} MB)</small>
                        </div>
                        <div>
                            <span class="badge bg-secondary">Ожидание</span>
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile('${file.id}')">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
                processBtn.disabled = false;
                clearAllBtn.disabled = false;
            }
        }

        // Удаление файла
        function removeFile(fileId) {
            files = files.filter(f => f.id !== fileId);
            updateFileList();
            updateStatistics();
        }

        // Очистка всех файлов
        clearAllBtn.addEventListener('click', () => {
            files = [];
            updateFileList();
            updateStatistics();
        });

        // Обновление статистики
        function updateStatistics() {
            document.getElementById('totalFiles').textContent = files.length;
            const processed = files.filter(f => f.status === 'processed').length;
            const success = files.filter(f => f.status === 'success').length;
            const errors = files.filter(f => f.status === 'error').length;
            
            document.getElementById('processedFiles').textContent = processed;
            document.getElementById('successFiles').textContent = success;
            document.getElementById('errorFiles').textContent = errors;
        }

        // Обработка файлов
        processBtn.addEventListener('click', async () => {
            if (processing || files.length === 0) return;
            
            processing = true;
            processBtn.disabled = true;
            progressBar.style.display = 'block';
            
            let processedCount = 0;
            let successCount = 0;
            let errorCount = 0;

            for (let fileObj of files) {
                if (fileObj.status === 'pending') {
                    // Обновляем статус файла
                    fileObj.status = 'processing';
                    updateFileList();
                    
                    // Имитация обработки (замените на реальный API вызов)
                    processingStatus.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-hourglass-split"></i>
                            Обрабатывается: ${fileObj.file.name}
                        </div>
                    `;

                    try {
                        // Здесь будет реальный вызов API
                        await simulateProcessing(fileObj);
                        fileObj.status = 'success';
                        successCount++;
                    } catch (error) {
                        fileObj.status = 'error';
                        errorCount++;
                    }

                    processedCount++;
                    updateFileList();
                    updateStatistics();

                    // Обновляем прогресс бар
                    const progress = (processedCount / files.length) * 100;
                    progressBar.querySelector('.progress-bar').style.width = `${progress}%`;
                }
            }

            // Завершение обработки
            processing = false;
            progressBar.style.display = 'none';
            resultsButtons.style.display = 'block';

            processingStatus.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    Обработка завершена!<br>
                    Успешно: ${successCount}, Ошибок: ${errorCount}
                </div>
            `;
        });

        // Имитация обработки (замените на реальный API)
        async function simulateProcessing(fileObj) {
            const formData = new FormData();
            files.forEach(f => formData.append('files[]', f.file));
            
            // CSRF токен в FormData (ДЛЯ CURL тоже работает)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            formData.append('_token', csrfToken);

            try {
                // ✅ ПРАВИЛЬНЫЙ fetch с await
                const response = await fetch('/api/process-egrn', {
                    method: 'POST',
                    body: formData  // НЕ headers с FormData!
                });

                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.error || data.message || 'Серверная ошибка');
                }

                // ✅ Показываем результат ГЛОБАЛЬНО (не внутри цикла!)
                resultsContent.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message}
                    </div>
                `;
                resultsButtons.innerHTML = `
                    <a href="${data.excel_url}" class="btn btn-success" target="_blank">
                        <i class="bi bi-file-excel"></i> Скачать Excel
                    </a>
                    <a href="/api/download/${new URL(data.excel_url).pathname.split('/').pop()}" class="btn btn-primary" download>
                        <i class="bi bi-download"></i> Скачать через Laravel
                    </a>
                `;
                resultsButtons.style.display = 'block';

                return data;  // ✅ Возвращаем успех
                
            } catch (error) {
                console.error('API Error:', error);
                throw error;  // ❌ Перебрасываем ошибку наверх
            }
        }



        // Инициализация
        updateFileList();
        updateStatistics();
    </script>
@endsection("content")