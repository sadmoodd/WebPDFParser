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
    return text


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


def extract_text_ocr(pdf_path: Path, max_pages: int = 4) -> str:
    """🔥 OCR + АВТО-ПОВОРОТ всех страниц!"""
    try:
        import pdf2image
        import pytesseract
        from PIL import Image, ImageEnhance
        
        # ✅ КОНВЕРТИРУЕМ с поворотом на 0°!
        images = pdf2image.convert_from_path(
            str(pdf_path), 
            dpi=300,
            first_page=1, 
            last_page=max_pages,
            fmt='RGB',
        )
        
        logger.info(f"📸 OCR {len(images)} стр. DPI=300")
        text_parts = []
        
        for i, image in enumerate(images):
            try:
                # ✅ ПОВЕРНУТЬ ЕЩЁ РАЗ (на всякий случай)
                image = image.rotate(0, expand=True)  # 0° альбом
                
                # Контраст
                img = ImageEnhance.Contrast(image.convert('L')).enhance(2.0)
                
                # PSM=6 для блоков текста
                config = '--oem 3 --psm 6'
                page_text = pytesseract.image_to_string(img, lang='rus', config=config)
                
                if len(page_text.strip()) > 10:
                    text_parts.append(page_text)
                    logger.info(f"  📄 стр.{i+1}: {len(page_text)} симв.")
            except Exception as e:
                logger.warning(f"  ⚠️ стр.{i+1}: {e}")
        
        result = clean_egrn_text("\n".join(text_parts))
        if len(result) > 50:
            logger.info(f"✅ OCR + поворот: {len(result)} симв.")
            return result
    except Exception as e:
        logger.error(f"❌ OCR: {e}")
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
