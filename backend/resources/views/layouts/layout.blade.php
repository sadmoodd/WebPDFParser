<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield("title")</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .drop-zone {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }
        .drop-zone:hover {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        .drop-zone.dragover {
            border-color: #0d6efd;
            background: #d4edda;
        }
        .file-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .file-item {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .processing-status {
            font-size: 0.9rem;
        }
        .progress {
            height: 8px;
        }
        .result-card {
            border-left: 4px solid #0d6efd;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-house-check"></i>
                ЕГРН Обработчик
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route("index") }}"><i class="bi bi-house"></i> Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route("about") }}"><i class="bi bi-info-circle"></i> О системе</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route("help") }}"><i class="bi bi-question-circle"></i> Помощь</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield("content")


</body>
</html>