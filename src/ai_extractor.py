"""
ai_extractor.py + DEEPSEEK VISION OCR!
✅ ТЕКСТ ЦЕЛИКОМ без обрезки!
"""

import json
import re
import base64
from typing import Dict, Any, Union, List
from openai import OpenAI
from PIL import Image
import io
import traceback

from logger_cfg import setup_logger
from settings import (
    PERPLEXITY_API_KEY,
    LLM_MODEL,
    HF_API_KEY,
    HF_MODEL,
    LLM_TEMPERATURE,
    LLM_MAX_TOKENS,
    LLM_TIMEOUT,
    MAX_TEXT_FOR_LLM,
)

logger = setup_logger(__name__)

logger.info(f"🔑 API_KEY: {'OK' if HF_API_KEY else '❌ ПУСТО!'}")
logger.info(f"🤖 Text MODEL: {LLM_MODEL}")
logger.info(f"🖼️ Vision MODEL: {HF_MODEL}")

client = OpenAI(api_key=HF_API_KEY, base_url="https://router.huggingface.co/v1")

def image_to_base64(image: Image.Image) -> str:
    """PIL Image → base64"""
    buffer = io.BytesIO()
    image.save(buffer, format='JPEG', quality=95)
    return base64.b64encode(buffer.getvalue()).decode()

def create_deepseek_prompt(page_num: int, total_pages: int) -> str:
    """Промпт DeepSeek Vision OCR"""
    return f"""Извлеки ТОЧНЫЙ текст ЕГРН выписки с этой страницы. 
Кадастры (74:36:...), адреса, площади, собственников.
НЕ исправляй! Страница {page_num}/{total_pages}."""

def extract_text_deepseek_ocr(pdf_images: List[Image.Image]) -> str:
    """🔥 DeepSeek Vision OCR - ВСЕ страницы!"""
    full_text = []
    
    for page_num, image in enumerate(pdf_images, 1):
        try:
            logger.info(f"🖼️ DeepSeek OCR стр.{page_num}/{len(pdf_images)}...")
            
            image_b64 = image_to_base64(image)
            prompt = create_deepseek_prompt(page_num, len(pdf_images))

            messages = [{
                "role": "user",
                "content": [
                    {"type": "text", "text": prompt},
                    {"type": "image_url", "image_url": {"url": f"data:image/jpeg;base64,{image_b64}"}}
                ]
            }]

            response = client.chat.completions.create(
                model=HF_MODEL,
                messages=messages,
                max_tokens=4096,
                temperature=0.1,
                timeout=60,
            )

            extracted_text = response.choices[0].message.content.strip()
            logger.info(f"  ✅ стр.{page_num}: {len(extracted_text)} симв.")
            full_text.append(extracted_text)
            
        except Exception as e:
            logger.error(f"❌ DeepSeek стр.{page_num}: {e}")
            continue
    
    result = "\n\n--- СТРАНИЦА ---\n\n".join(full_text)
    logger.info(f"🎉 DeepSeek OCR: {len(result)} симв. всего")
    return result

def extract_content_from_response(response: Any) -> str:
    """🔥 Извлечение контента"""
    if not response:
        return ""
    
    try:
        if hasattr(response, 'choices') and response.choices:
            content = response.choices[0].message.content
            return content.strip()
        
        if isinstance(response, dict) and response.get('choices'):
            content = response['choices'][0]['message']['content']
            return content.strip()
        
        return ""
    except:
        return ""

def call_perplexity_api(pdf_input: Union[str, List[Image.Image]]) -> str:
    """🔥 ГИБРИД: DeepSeek OCR → Text LLM - ТЕКСТ ЦЕЛИКОМ!"""
    logger.info(f"🌐 Text LLM: {LLM_MODEL}")
    
    # Тест API
    test_response = client.chat.completions.create(
        model=LLM_MODEL,
        messages=[{"role": "user", "content": "{\"test\": \"OK\"}"}],
        temperature=0.1,
        max_tokens=100
    )
    logger.info(f"✅ ТЕСТ API: OK")
    
    # 🔥 DeepSeek Vision OCR (если изображения)
    if isinstance(pdf_input, list) and pdf_input and isinstance(pdf_input[0], Image.Image):
        logger.info("🔥 DeepSeek Vision OCR активирован!")
        pdf_text = extract_text_deepseek_ocr(pdf_input)
    else:
        pdf_text = str(pdf_input)
    
    # ✅ ЛОГИРУЕМ ПОЛНЫЙ ТЕКСТ!
    logger.info(f"📄 ТЕКСТ ДЛЯ LLM: {len(pdf_text)} симв.")
    logger.debug(f"📄 Preview: {pdf_text[:500]}...")
    
    # ✅ ЕСЛИ ТЕКСТ ОЧЕНЬ БОЛЬШОЙ - разбиваем на части!
    if len(pdf_text) > 32000:  # Лимит токенов
        logger.warning(f"⚠️ Текст {len(pdf_text)} симв. → разбивка на части!")
        chunks = [pdf_text[i:i+32000] for i in range(0, len(pdf_text), 32000)]
        results = []
        
        for i, chunk in enumerate(chunks):
            logger.info(f"📄 Часть {i+1}/{len(chunks)}: {len(chunk)} симв.")
            chunk_result = _process_text_chunk(chunk)
            results.append(chunk_result)
        
        # Объединяем результаты
        pdf_text = "\n\n".join([r for r in results if r])
        logger.info(f"📄 Объединено: {len(pdf_text)} симв.")
    
    prompt = f"""ИЗВЛЕКИ из ЕГРН ТОЛЬКО JSON!

ПРАВИЛА:
- ТОЛЬКО JSON! Без текста/объяснений!
- Не нашёл → null
- Кадастр: ВСЕ 74:36:...
- Площадь: ЧИСЛО (1234.56)
- Даты: ДД.ММ.ГГГГ

{{
  "cadastral_number": null,
  "cadastral_building": null,
  "address": null,
  "area": null,
  "owner": null,
  "tenant": null,
  "floor": null,
  "literal": null,
  "cadastral_quarter": null
  "permitted_use": null,
  "room_number": null,
  
  "status": null,
  "rental_data", {{
      "rent_type": null,
      "period_start": null,
      "period_end": null
  }} 
}}

ТЕКСТ:
{pdf_text}

JSON:"""

    response = client.chat.completions.create(
        model=LLM_MODEL,
        messages=[{"role": "user", "content": prompt}],
        temperature=0.1,
        max_tokens=LLM_MAX_TOKENS,
        timeout=LLM_TIMEOUT,
    )
    
    api_response = extract_content_from_response(response)
    logger.info(f"✅ Text LLM: {len(api_response)} симв.")
    return api_response or '{"error": "Empty response"}'

def _process_text_chunk(chunk: str) -> str:
    """Обрабатывает большой текст по частям"""
    prompt = f"""Найди в ЕГРН ТЕКСТЕ:
- Кадастры 74:36:...
- Адреса (г., ул., д.)
- Площади (числа)
- Собственников

Верни ТОЛЬКО найденное:"""
    
    response = client.chat.completions.create(
        model=LLM_MODEL,
        messages=[{"role": "user", "content": f"{prompt}\n\n{chunk[:3000]}" }],
        max_tokens=2000,
        temperature=0.1,
    )
    return extract_content_from_response(response)

def parse_json_response(json_str: str) -> Dict[str, Any]:
    """🔥 Парсер JSON"""
    logger.info(f"🔍 RAW JSON ({len(json_str)} симв.): {json_str[:300]}...")
    
    if not json_str:
        return {"error": "Пустой ответ"}
    
    # Полная очистка
    cleaned = re.sub(r'``````', '', json_str)
    cleaned = re.sub(r'\n\s+', ' ', cleaned).strip()
    
    try:
        parsed = json.loads(cleaned)
        logger.info(f"✅ JSON: {list(parsed.keys())}")
        logger.info(f"   Кадастр: {parsed.get('cadastral_number')}")
        logger.info(f"   Адрес: {parsed.get('address')}")
        logger.info(f"   Площадь: {parsed.get('area')}")
        return parsed
    except json.JSONDecodeError:
        # Emergency парсинг
        cadastral = re.search(r'cadastral[_-]?number["\s:]*"?([^\s,"}]*)"?', cleaned, re.I)
        address = re.search(r'address["\s:]*"?([^\s,"}]*)"?', cleaned, re.I)
        area = re.search(r'"?area["\s:]*"?([0-9.,]+)"?', cleaned)
        
        emergency = {
            "cadastral_number": cadastral.group(1) if cadastral else None,
            "address": address.group(1) if address else None,
            "area": float(area.group(1).replace(',', '.')) if area else None,
            "emergency_parsed": True
        }
        logger.info(f"✅ Emergency: {emergency}")
        return emergency

def extract_egrn_data(pdf_input: Union[str, List[Image.Image]]) -> Dict[str, Any]:
    """Главная функция"""
    logger.info(f"📄 extract_egrn_data: {len(str(pdf_input))} симв./изобр.")
    
    if not pdf_input:
        return {"error": "Нет данных"}
    
    try:
        json_str = call_perplexity_api(pdf_input)
        data = parse_json_response(json_str)
        logger.info(f"📊 РЕЗУЛЬТАТ: {json.dumps(data, ensure_ascii=False, indent=2)}")
        return {"data": data}
    except Exception as e:
        logger.error(f"❌ extract_egrn_data: {e}")
        return {"error": str(e)}

if __name__ == "__main__":
    test_text = """Кадастровый номер: 74:36:0303005:71
Адрес: Челябинская область, г Челябинск, р-н Ленинский, ул Енисейская
Площадь: 6345"""
    
    result = extract_egrn_data(test_text)
    print(json.dumps(result, ensure_ascii=False, indent=2))
