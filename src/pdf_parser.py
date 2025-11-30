"""
pdf_parser.py
═══════════════════════════════════════════════════════════════════════════════
Извлечение текста из PDF (обычные + отсканированные).
pdfplumber → PyPDF2 → Tesseract OCR → гарантированный результат!
"""

import pdfplumber
import PyPDF2
import pytesseract
import pdf2image
import re
from pathlib import Path
from PIL import Image
from io import BytesIO
from logger_cfg import setup_logger

logger = setup_logger(__name__)


def clean_egrn_text(text: str) -> str:
    """Очистка текста от мусора."""
    if not text:
        return ""
    
    # Удаляем КУВИ и служебные строки
    text = re.sub(r'\d{2}\.\d{2}\.\d{4}г\. № КУВИ-[\d/-]+', '', text)
    text = re.sub(r'Лист №? ?\d+', '', text)
    text = re.sub(r'Раздел \d(\.\d)?', '', text)
    text = re.sub(r'Всего листов.*?\n', '', text)
    text = re.sub(r'ДОКУМЕНТ ПОДПИСАН.*', '', text, flags=re.DOTALL | re.IGNORECASE)
    
    # Удаляем пустые строки
    lines = [line.strip() for line in text.split('\n') if line.strip()]
    return '\n'.join(lines)


def extract_text_pdfplumber(pdf_path: Path) -> str:
    """Попытка 1: pdfplumber (для текстовых PDF)"""
    text_content = []
    try:
        with pdfplumber.open(pdf_path) as pdf:
            for page_num, page in enumerate(pdf.pages):
                text = page.extract_text()
                if text:
                    text_content.append(text)
        result = "\n".join(text_content)
        if result and len(result.strip()) > 50:
            logger.info(f"✅ pdfplumber: {len(result)} символов")
            return result
        return ""
    except Exception as e:
        logger.warning(f"⚠️ pdfplumber ошибка: {e}")
        return ""


def extract_text_pypdf2(pdf_path: Path) -> str:
    """Попытка 2: PyPDF2 (альтернативный текстовый парсер)"""
    text_content = []
    try:
        with open(pdf_path, 'rb') as f:
            reader = PyPDF2.PdfReader(f)
            for page_num, page in enumerate(reader.pages):
                text = page.extract_text()
                if text:
                    text_content.append(text)
        result = "\n".join(text_content)
        if result and len(result.strip()) > 50:
            logger.info(f"✅ PyPDF2: {len(result)} символов")
            return result
        return ""
    except Exception as e:
        logger.error(f"⚠️ PyPDF2 ошибка: {e}")
        return ""


def extract_text_ocr(pdf_path: Path, max_pages: int = 5) -> str:
    """Попытка 3: OCR через Tesseract (для сканов)"""
    text_content = []
    try:
        # Конвертируем PDF в изображения
        images = pdf2image.convert_from_path(pdf_path, dpi=150, first_page=1, last_page=max_pages)
        
        logger.info(f"📸 OCR обработка {len(images)} страниц...")
        
        for page_num, image in enumerate(images):
            try:
                # Оптимизируем изображение для OCR
                # Преобразуем в оттенки серого
                image_bw = image.convert('L')
                
                # Увеличиваем контраст
                from PIL import ImageEnhance
                enhancer = ImageEnhance.Contrast(image_bw)
                image_bw = enhancer.enhance(2)
                
                # Запускаем OCR с русским языком
                text = pytesseract.image_to_string(image_bw, lang='rus')
                
                if text and len(text.strip()) > 20:
                    text_content.append(text)
                    logger.info(f"  Страница {page_num + 1}: {len(text)} символов")
            except Exception as e:
                logger.warning(f"  ⚠️ OCR ошибка на странице {page_num + 1}: {e}")
                continue
        
        result = "\n".join(text_content)
        if result and len(result.strip()) > 50:
            logger.info(f"✅ OCR: {len(result)} символов")
            return result
        return ""
        
    except Exception as e:
        logger.error(f"❌ OCR критическая ошибка: {e}")
        return ""


def extract_text(pdf_path: Path) -> str:
    """
    УМНАЯ последовательность:
    1. pdfplumber (быстро для текстовых)
    2. PyPDF2 (альтернатива)
    3. OCR Tesseract (для сканов)
    
    ✅ Гарантированно что-то вернёт!
    """
    pdf_path = Path(pdf_path)
    if not pdf_path.exists():
        logger.error(f"❌ Файл не существует: {pdf_path}")
        raise FileNotFoundError(f"Нет файла: {pdf_path}")

    logger.info(f"📄 ОБРАБОТКА: {pdf_path.name} ({pdf_path.stat().st_size / 1024 / 1024:.2f}MB)")
    
    # ✅ ЭТАП 1: pdfplumber
    logger.info("  1️⃣  Попытка 1: pdfplumber...")
    full_text = extract_text_pdfplumber(pdf_path)
    
    if full_text and len(full_text.strip()) > 100:
        cleaned = clean_egrn_text(full_text)
        logger.info(f"✅ УСПЕХ с pdfplumber: {len(cleaned)} символов")
        return cleaned
    
    # ✅ ЭТАП 2: PyPDF2
    logger.info("  2️⃣  Попытка 2: PyPDF2...")
    pypdf2_text = extract_text_pypdf2(pdf_path)
    
    if pypdf2_text and len(pypdf2_text.strip()) > 100:
        cleaned = clean_egrn_text(pypdf2_text)
        logger.info(f"✅ УСПЕХ с PyPDF2: {len(cleaned)} символов")
        return cleaned
    
    # ✅ ЭТАП 3: OCR Tesseract (для сканов)
    logger.info("  3️⃣  Попытка 3: OCR Tesseract (для сканов)...")
    ocr_text = extract_text_ocr(pdf_path, max_pages=5)
    
    if ocr_text and len(ocr_text.strip()) > 100:
        cleaned = clean_egrn_text(ocr_text)
        logger.info(f"✅ УСПЕХ с OCR: {len(cleaned)} символов")
        return cleaned
    
    # ❌ Финальная проверка
    logger.error(f"❌ НЕ УДАЛОСЬ извлечь текст из {pdf_path.name}")
    logger.error("   Возможные причины: защита, шифрование, очень плохое качество сканирования")
    return ""


if __name__ == "__main__":
    # Тест на проблемном файле
    test_file = Path("/home/frelomm/nethammer/FINAL_BTW/data/input/ЗУ 71 аренда 6345.pdf")
    if test_file.exists():
        text = extract_text(test_file)
        print("\n" + "="*80)
        print("РЕЗУЛЬТАТ:")
        print("="*80)
        print(text[:1000])
        if len(text) > 1000:
            print(f"\n... (всего {len(text)} символов)")
    else:
        print(f"❌ Тестовый файл не найден: {test_file}")
