@extends("layouts.layout")
@section("title", "Помощь")

@section('content')
<div class="container mt-4">
    <!-- Заголовок -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <i class="bi bi-life-preserver display-1 text-warning mb-3"></i>
                <h1 class="display-4 fw-bold text-warning mb-2">Помощь и решения проблем</h1>
                <p class="lead text-muted">Часто встречающиеся проблемы и их решения</p>
            </div>
        </div>
    </div>

    <!-- Быстрый поиск -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body text-center p-4">
                    <h4 class="mb-3">Не нашли решение?</h4>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" 
                               placeholder="Опишите вашу проблему..." id="searchHelp">
                        <button class="btn btn-warning btn-lg" type="button">
                            <i class="bi bi-search"></i> Найти
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Основные проблемы -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow border-warning">
                <div class="card-header bg-warning text-dark">
                    <h3 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Типовые проблемы обработки
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Проблема 1 -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-start border-warning border-5 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">
                                        <i class="bi bi-file-earmark-x me-2"></i>
                                        ❌ Неполные данные в Excel
                                    </h5>
                                    <p class="card-text">
                                        В таблице пропущены кадастровые номера, адреса, площади или другие поля.
                                    </p>
                                    <div class="alert alert-danger">
                                        <strong>Причины:</strong>
                                    </div>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>PDF-скан вместо цифрового документа</li>
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Низкое качество печати/скана</li>
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Пометки ручкой поверх текста</li>
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Не та форма выписки ЕГРН</li>
                                    </ul>
                                    <div class="alert alert-success">
                                        <strong>✅ Решение:</strong>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Используйте <strong>цифровую выписку</strong> с Росреестра</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Проверьте <strong>альбомную ориентацию</strong></li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Тестируйте по 1 файлу</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Проблема 2 -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-start border-danger border-5 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-danger">
                                        <i class="bi bi-stopwatch me-2"></i>
                                        ❌ Файл не скачивается (404)
                                    </h5>
                                    <p class="card-text">
                                        Получена ошибка 404 при попытке скачать Excel результат.
                                    </p>
                                    <div class="alert alert-danger">
                                        <strong>Причины:</strong>
                                    </div>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Файл еще обрабатывается</li>
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Python сервис недоступен</li>
                                        <li><i class="bi bi-x-circle text-danger me-1"></i>Проблема с правами доступа</li>
                                    </ul>
                                    <div class="alert alert-success">
                                        <strong>✅ Решение:</strong>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Подождите 10-30 сек и повторите</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Обновите страницу</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Попробуйте другой браузер</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Проблема 3 -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-start border-info border-5 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-info">
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        ❌ Файлы не загружаются
                                    </h5>
                                    <p class="card-text">
                                        Drag&Drop не работает или файлы не отображаются в списке.
                                    </p>
                                    <div class="alert alert-info">
                                        <strong>Причины:</strong>
                                    </div>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="bi bi-x-circle text-info me-1"></i>Файл > 10 МБ</li>
                                        <li><i class="bi bi-x-circle text-info me-1"></i>Не PDF формат</li>
                                        <li><i class="bi bi-x-circle text-info me-1"></i>Блокировка браузером</li>
                                    </ul>
                                    <div class="alert alert-success">
                                        <strong>✅ Решение:</strong>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Проверьте размер файла</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Выберите только PDF</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Отключите блокировщики рекламы</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Проблема 4 -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-start border-secondary border-5 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary">
                                        <i class="bi bi-clock-history me-2"></i>
                                        ❌ Долгая обработка
                                    </h5>
                                    <p class="card-text">
                                        Обработка зависает или занимает >5 минут.
                                    </p>
                                    <div class="alert alert-secondary">
                                        <strong>Причины:</strong>
                                    </div>
                                    <ul class="list-unstyled mb-3">
                                        <li><i class="bi bi-x-circle text-secondary me-1"></i>>20 файлов сразу</li>
                                        <li><i class="bi bi-x-circle text-secondary me-1"></i>Сложные PDF (>10 страниц)</li>
                                        <li><i class="bi bi-x-circle text-secondary me-1"></i>Нагрузка на сервер</li>
                                    </ul>
                                    <div class="alert alert-success">
                                        <strong>✅ Решение:</strong>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Обрабатывайте по 5-10 файлов</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Ждите полного завершения</li>
                                        <li><i class="bi bi-check-circle text-success me-1"></i>Пробуйте в рабочее время</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица кодов ошибок -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bug me-2"></i>Коды ошибок и их значения
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Код</th>
                                    <th>Описание</th>
                                    <th>Причина</th>
                                    <th>Решение</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-danger">
                                    <td>de>404</code></td>
                                    <td>Файл не найден</td>
                                    <td>Excel еще создается</td>
                                    <td>Подождите 30 сек и повторите</td>
                                </tr>
                                <tr class="table-warning">
                                    <td>de>413</code></td>
                                    <td>Файл слишком большой</td>
                                    <td>>10 МБ или >50 файлов</td>
                                    <td>Уменьшите размер/количество</td>
                                </tr>
                                <tr class="table-danger">
                                    <td>de>500</code></td>
                                    <td>Ошибка сервера</td>
                                    <td>Python сервис упал</td>
                                    <td>Обновите страницу через 1 мин</td>
                                </tr>
                                <tr class="table-warning">
                                    <td>de>400</code></td>
                                    <td>Неверный PDF</td>
                                    <td>Скан/не ЕГРН/поврежден</td>
                                    <td>Проверьте формат документа</td>
                                </tr>
                                <tr class="table-info">
                                    <td>de>429</code></td>
                                    <td>Слишком много запросов</td>
                                    <td>Превышен лимит нагрузки</td>
                                    <td>Подождите 5 минут</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Чек-лист перед обработкой -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>✅ Чек-лист перед загрузкой
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-pdf-fill text-primary fs-1 mb-2"></i>
                                    <h6>Только PDF</h6>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="pdf">
                                        <label class="form-check-label small" for="pdf">✓ PDF формат</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <i class="bi bi-rulers text-primary fs-1 mb-2"></i>
                                    <h6>Альбомная</h6>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="landscape">
                                        <label class="form-check-label small" for="landscape">✓ Landscape</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <i class="bi bi-hdd-network text-primary fs-1 mb-2"></i>
                                    <h6>&le; 10 МБ</h6>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="size">
                                        <label class="form-check-label small" for="size">✓ Размер OK</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 h-100">
                                <div class="card-body">
                                    <i class="bi bi-download text-primary fs-1 mb-2"></i>
                                    <h6>Цифровой</h6>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="digital">
                                        <label class="form-check-label small" for="digital">✓ Росреестр PDF</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('index') }}" class="btn btn-success btn-lg">
                            <i class="bi bi-gear-fill me-2"></i>
                            Готово! Начать обработку
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>

<script>
document.getElementById('searchHelp').addEventListener('keyup', function(e) {
    const query = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.card-body');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.parentElement.style.display = text.includes(query) ? 'block' : 'none';
    });
});
</script>

<style>
.border-5 { border-width: 5px !important; }
.fs-1 { font-size: 2.5rem !important; }
.table-danger, .table-warning, .table-info { font-weight: 500; }
</style>
@endsection
