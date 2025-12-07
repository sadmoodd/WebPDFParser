# Парсер ПДФ ЕГРН выпиоск

## Фнкциональное предназначение 

Сервис предназначен для автоматизации офисных процессов, связанных с сведением выписок из ЕГРН с таблицой Excel. Функциональные требования и особенности указаны в соответсвующем разделе О Системе на интерактивных формах приложения.

## Скриншоты

### Главная форма
<img width="1920" height="963" alt="image" src="https://github.com/user-attachments/assets/91dd9061-d606-4527-b0d1-4e99010d8b41" />

### Форма О системе
<img width="1920" height="963" alt="image" src="https://github.com/user-attachments/assets/e7836885-a44b-4861-a034-25b49be2059b" />

### Форма Помощь
<img width="1920" height="963" alt="image" src="https://github.com/user-attachments/assets/c8ab66a1-b2e3-4632-826d-f3a2b5a900d4" />



## Структура проекта и архитектурные особенности

```
│ main.py # CLI / точка входа
│ settings.py # работа с .env и путями
│ pdf_parser.py # извлечение текста/изображений из PDF
│ ai_extractor.py # DeepSeek OCR + LLM JSON‑экстракция
│ table_writer.py # сборка DataFrame и сохранение в Excel
│ logger_cfg.py # настройка логгера
│ requirements.txt
│ .env.example # образец настроек (см. ниже)
│
├─backend/ # web‑API / интеграция с PHP
│ ├─... (Laravel 12 + blade-шабллнизатор)
│
├─data/
│ ├─input/ # входящие PDF (пользователь кладёт сюда)​
│ └─output/ # результаты Excel​
│
├─logs/ # файлы логов приложения​
├─tmp/ # временные файлы / кэш​
├─result/ # экспорт для внешних систем
├─shared_uploads/ # общая папка обмена с web‑частью
└─src/ # вспомогательные модули, если потребуется расширять
```

Все папки проекта *обязательны* и должны присутствовать в проекте для правильной его работы.

## Настройки

Папки `data/input`, `data/output`, `logs`, `tmp` создаются автоматически, если их нет.

### Настройки `settings.py`

Все, что находится ниже - переменные окружения (`.env / .env.example`), могут быть изменены или же оставлены по умолчанию. Они обрабатываются в `settings.py`

```
Пути *(рекомендуется тщательно отнестись к их замене на хосте, чтобы избежать ошибок)*

PROJECT_PATH=/home/username/projects/WebPDFParser

INPUT_DIR=/home/username/projects/WebPDFParser/data/input
OUTPUT_DIR=/home/username/projects/WebPDFParser/data/output
LOG_DIR=/home/username/projects/WebPDFParser/logs
TEMP_DIR=/home/username/projects/WebPDFParser/tmp

Логирование

LOG_LEVEL=INFO
LOG_FILE=egrn_parser.log

Ключи API

HF_API_KEY=hf_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

Модели LLM

LLM_MODEL= # текстовая модель (OpenRouter/HF router)
​
LLM_TEMPERATURE=0.0
LLM_MAX_TOKENS=10000
LLM_TIMEOUT=60
Vision‑модель для OCR через HF router

HF_MODEL=DeepSeek-ai/DeepSeek-VL-7B-chat

Параметры обработки PDF

MAX_FILES_BATCH=50
MIN_TEXT_LENGTH=50
MAX_TEXT_FOR_LLM=90000

Параметры Excel

EXCEL_FONT=Calibri
EXCEL_FONT_SIZE=11
EXCEL_HEADER_HEIGHT=25
```

## Настройка окружения (.env)

Файл `.env` должен лежать в корне проекта рядом с `settings.py` и `main.py`.
`python-dotenv` подхватывает значения при импорте `settings`.


## Установка и запуск

```
# Установка исходников и зависимостей
git clone https://github.com/sadmoodd/WebPDFParser.git
cd WebPDFParser

python -m venv .venv
source ./.venv/bin/activate # For Linux
.venv\Scripts\activate      # For Windows

pip install requirements.txt

# Запуск Python-API
python src/python_api.py

# Запуск Laravel бекенда
cd backend
php artisan serve

```







