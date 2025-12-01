@extends("layouts.layout")
@section("title", "О системе")

@section('content')
<div class="container mt-4">
    <!-- Заголовок -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <i class="bi bi-cpu display-1 text-primary mb-3"></i>
                <h1 class="display-4 fw-bold text-primary mb-2">Система обработки ЕГРН</h1>
                <p class="lead text-muted">Автоматический парсер выписок из Единого государственного реестра недвижимости</p>
            </div>
        </div>
    </div>

    <!-- Особенности работы -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-lightning-charge me-2"></i>
                        Особенности работы системы
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h5><i class="bi bi-check-circle-fill text-success me-2"></i>Что поддерживается</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-success me-2"></i>
                                    <strong>Цифровые PDF ЕГРН</strong> (официальные выписки от Росреестра)
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-success me-2"></i>
                                    Стандартные формы выписок об объектах недвижимости
                                </li>
                                <li class="bi bi-circle-fill text-success me-2"></i>
                                    Объекты: земельные участки, здания, помещения, квартиры
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Технические ограничения</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-warning me-2"></i>
                                    <strong>Макс. 50 файлов</strong> за раз (для стабильности)
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-circle-fill text-warning me-2"></i>
                                    <strong>Макс. 10 МБ</strong> на файл
                                </li>
                                <li class="bi bi-circle-fill text-warning me-2"></i>
                                    Только форматe *.pdf</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Критические требования к PDF -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-alert-triangle me-2"></i>
                        🚨 Критические требования к PDF файлам
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>⚠️ Неправильный формат = 100% ошибка обработки!</strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-danger mb-3">
                                <i class="bi bi-file-earmark-text me-2"></i>✅ Идеальные PDF
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item">
                                    <div class="d-flex">
                                        <i class="bi bi-check-lg text-success fs-5 me-3"></i>
                                        <div>
                                            <strong>Цифровые выписки Росреестра</strong><br>
                                            <small class="text-muted">Официальные PDF с текстом (не картинки)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex">
                                        <i class="bi bi-check-lg text-success fs-5 me-3"></i>
                                        <div>
                                            <strong>Четкий машинописный текст</strong><br>
                                            <small class="text-muted">Без рукописных пометок</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-danger mb-3">
                                <i class="bi bi-x-circle me-2"></i>❌ НЕ поддерживаются
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item bg-danger bg-opacity-10">
                                    <div class="d-flex">
                                        <i class="bi bi-x-lg text-danger fs-5 me-3"></i>
                                        <div>
                                            <strong>Сканы фотографий, кривые сканы</strong><br>
                                            <small class="text-muted">📱 Сфотографированные документы</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item bg-danger bg-opacity-10">
                                    <div class="d-flex">
                                        <i class="bi bi-x-lg text-danger fs-5 me-3"></i>
                                        <div>
                                            <strong>Портретная ориентация</strong><br>
                                            <small class="text-muted">📐 Только альбомная (landscape)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item bg-danger bg-opacity-10">
                                    <div class="d-flex">
                                        <i class="bi bi-x-lg text-danger fs-5 me-3"></i>
                                        <div>
                                            <strong>Многостраничные сканы</strong><br>
                                            <small class="text-muted">>5 страниц или нестандартный формат</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица требований -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-table me-2"></i>Таблица требований к файлам
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Параметр</th>
                                    <th>✅ Поддерживается</th>
                                    <th>❌ Не поддерживается</th>
                                    <th>Причина</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><strong>Тип документа</strong></td>
                                    <td>Цифровая выписка ЕГРН</td>
                                    <td>Скан, фото, бумажный документ</td>
                                    <td>OCR не распознает рукописный текст</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>Ориентация</strong></td>
                                    <td>Альбомная (landscape)</td>
                                    <td>Портретная (portrait)</td>
                                    <td>Стандартная верстка ЕГРН</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Размер файла</strong></td>
                                    <td>&le; 10 МБ</td>
                                    <td>> 10 МБ</td>
                                    <td>Ограничение сервера</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>Количество файлов</strong></td>
                                    <td>&le; 50 файлов</td>
                                    <td>> 50 файлов</td>
                                    <td>Ограничение памяти</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Качество текста</strong></td>
                                    <td>Четкий печатный текст</td>
                                    <td>Размытость, пометки ручкой</td>
                                    <td>Алгоритмы распознавания</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Рекомендации -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>💡 Рекомендации по подготовке
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-download text-primary fs-1 mb-2"></i>
                                <h6>Скачайте официальные PDF</h6>
                                <small class="text-muted">Прямо с портала Росреестра</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-rulers text-primary fs-1 mb-2"></i>
                                <h6>Проверьте ориентацию</h6>
                                <small class="text-muted">Должна быть альбомная</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="bi bi-check-all text-primary fs-1 mb-2"></i>
                                <h6>Тестируйте по 1-5 файлам</h6>
                                <small class="text-muted">Сначала проверьте качество</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Техническая информация -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Техническая информация</h6>
                </div>
                <div class="card-body">
                    <div class="row text-muted">
                        <div class="col-md-3">
                            <strong>Backend:</strong> Laravel + Python (Flask)<br>
                            <strong>OCR:</strong> Tesseract + ML модели
                        </div>
                        <div class="col-md-3">
                            
                            <strong>Хранилище:</strong> Локальные диски
                        </div>
                        <div class="col-md-3">
                            <strong>Формат вывода:</strong> Excel (.xlsx)<br>
                            <strong>Кодировка:</strong> UTF-8
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="/" class="btn btn-primary">
                                <i class="bi bi-gear me-2"></i>Начать обработку
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
}
.table-success { background-color: #d1edff !important; }
.table-warning { background-color: #fff3cd !important; }
.table-danger { background-color: #f8d7da !important; }
.border-danger { border-color: #dc3545 !important; }
.fs-1 { font-size: 2.5rem !important; }
</style>
@endsection
