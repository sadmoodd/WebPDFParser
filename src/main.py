"""
main.py
═══════════════════════════════════════════════════════════════════════════════
Точка входа в приложение.
Оркестрирует процесс: Поиск файлов -> Парсинг PDF -> LLM -> Excel.

Поддерживает:
- Пакетную обработку
- Обработку ошибок (не падает, если один файл битый)
- Логирование прогресса
"""

import sys
import time
import pandas as pd
from pathlib import Path
from typing import List, Dict, Optional

# Импорты наших модулей
from settings import INPUT_DIR, OUTPUT_DIR, DEFAULT_COLUMNS, MAX_FILES_BATCH
from logger_cfg import setup_logger
from pdf_parser import extract_text
from ai_extractor import extract_egrn_data
from table_writer import create_empty_dataframe, create_rows_from_llm_data, save_to_excel

logger = setup_logger(__name__)

# ════════════════════════════════════════════════════════════════════════════
# ОСНОВНАЯ ЛОГИКА ОБРАБОТКИ (API-AGNOSTIC)
# ════════════════════════════════════════════════════════════════════════════

def process_single_file(pdf_path: Path) -> Dict:
    """
    Обрабатывает один файл: PDF -> Text -> LLM -> Data Dict.
    """
    result = {"file": pdf_path.name, "success": False, "data": {}}
    
    try:
        # 1. Извлекаем текст
        text = extract_text(pdf_path)
        if not text:
            result["error"] = "Empty text (scan?)"
            return result

        # 2. Отправляем в LLM
        data = extract_egrn_data(text)
        print(f"LEN OF TEXT {len(text)}")
        if "error" in data:
            result["error"] = data["error"]
            return result

        result["success"] = True
        result["data"] = data
        return result

    except Exception as e:
        logger.exception(f"Ошибка при обработке {pdf_path.name}")
        result["error"] = str(e)
        return result

def process_files_batch(
    pdf_files: List[Path], 
    columns: List[str] = DEFAULT_COLUMNS
) -> Dict:
    """
    Обрабатывает пакет файлов и возвращает путь к Excel.
    Используется и в CLI, и в Web API.
    """
    if not pdf_files:
        return {"error": "Нет файлов для обработки"}

    logger.info(f"🚀 Начинаем обработку {len(pdf_files)} файлов...")
    start_time = time.time()
    
    # Создаем пустой DataFrame
    df = create_empty_dataframe(columns)
    
    stats = {"success": 0, "failed": 0, "total": len(pdf_files)}
    processed_rows = []

    # Цикл по файлам
    for idx, pdf_file in enumerate(pdf_files, 1):
        logger.info(f"[{idx}/{len(pdf_files)}] Обработка: {pdf_file.name}")
        
        # Обработка файла
        res = process_single_file(pdf_file)
        
        if res["success"]:
            stats["success"] += 1
            # Конвертируем JSON в строки для Excel
            rows = create_rows_from_llm_data(res["data"], pdf_file.name, columns)
            processed_rows.extend(rows)
        else:
            stats["failed"] += 1
            logger.error(f"❌ Ошибка: {res.get('error')}")
            # Добавляем строку с ошибкой в Excel
            error_row = {col: "" for col in columns}
            error_row['Адрес, комплекс'] = f"ОШИБКА: {res.get('error')}"
            error_row['PDF-источник'] = pdf_file.name
            error_row['Статус'] = 'Error'
            processed_rows.append(error_row)

    # Собираем DataFrame
    if processed_rows:
        new_df = pd.DataFrame(processed_rows)
        df = pd.concat([df, new_df], ignore_index=True)

    # Генерируем имя файла
    timestamp = time.strftime("%Y%m%d_%H%M%S")
    output_filename = f"Таблица_Сводная_{timestamp}.xlsx"
    output_path = OUTPUT_DIR / output_filename
    
    # Сохраняем
    save_to_excel(df, output_path)
    
    duration = time.time() - start_time
    logger.info(f"🏁 Готово за {duration:.1f} сек. Успех: {stats['success']}, Ошибки: {stats['failed']}")

    return {
        "success": True,
        "excel_path": str(output_path),
        "stats": stats,
        "duration": duration
    }

# ════════════════════════════════════════════════════════════════════════════
# CLI ИНТЕРФЕЙС (МЕНЮ)
# ════════════════════════════════════════════════════════════════════════════

def get_pdf_files_from_dir(directory: Path) -> List[Path]:
    """Ищет PDF файлы в папке."""
    return sorted(list(directory.glob("*.pdf"))) + sorted(list(directory.glob("*.PDF")))

def main_menu():
    print("\n" + "═"*60)
    print("🏗️  EGRN PARSER AI (CLI Mode)")
    print("═"*60)
    print(f"📂 Папка ввода:  {INPUT_DIR}")
    print(f"📂 Папка вывода: {OUTPUT_DIR}")
    print("-" * 60)
    print("1. 🚀 Обработать все PDF в папке ввода")
    print("2. ⚙️  Настройки (показать)")
    print("3. 🚪 Выход")
    
    choice = input("\nВаш выбор (1-3): ").strip()
    
    if choice == '1':
        files = get_pdf_files_from_dir(INPUT_DIR)
        if not files:
            print("❌ В папке input нет PDF файлов!")
            return
        
        print(f"Найдено {len(files)} файлов.")
        if len(files) > MAX_FILES_BATCH:
            print(f"⚠️ Внимание: Будет обработано только первые {MAX_FILES_BATCH} файлов.")
            files = files[:MAX_FILES_BATCH]
            
        confirm = input("Начать обработку? (y/n): ").lower()
        if confirm == 'y':
            result = process_files_batch(files)
            print("\n✅ Результат сохранен:", result['excel_path'])
            
    elif choice == '2':
        print("\n--- Настройки (.env) ---")
        from settings import PERPLEXITY_API_KEY
        print(f"API Key: {'*' * 10}{PERPLEXITY_API_KEY[-4:] if PERPLEXITY_API_KEY else 'NOT SET'}")
        print(f"Columns: {len(DEFAULT_COLUMNS)} шт.")
        input("\nНажмите Enter...")
        
    elif choice == '3':
        sys.exit(0)

if __name__ == "__main__":
    try:
        while True:
            main_menu()
    except KeyboardInterrupt:
        print("\n👋 Выход...")
