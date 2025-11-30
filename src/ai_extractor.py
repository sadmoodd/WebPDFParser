"""
ai_extractor.py
🔥 ИСПРАВЛЕННАЯ ВЕРСИЯ С ДИАГНОСТИКОЙ API!
"""

import json
import re
from typing import Dict, Any, Union
from openai import OpenAI
import traceback

from logger_cfg import setup_logger
from settings import (
    PERPLEXITY_API_KEY,
    LLM_MODEL,
    LLM_TEMPERATURE,
    LLM_MAX_TOKENS,
    LLM_TIMEOUT,
    MAX_TEXT_FOR_LLM,
)

logger = setup_logger(__name__)

# ✅ ДИАГНОСТИКА API КЛЮЧА
logger.info(f"🔑 API_KEY: {'OK' if PERPLEXITY_API_KEY else '❌ ПУСТО!' if PERPLEXITY_API_KEY == '' else '❌ НЕВЕРНЫЙ'}")
logger.info(f"🤖 MODEL: {LLM_MODEL}")

client = OpenAI(
    api_key=PERPLEXITY_API_KEY,
    base_url="https://api.perplexity.ai"
)


def extract_content_from_response(response: Any) -> str:
    """🔥 СУПЕР-БЕЗОПАСНОЕ извлечение с диагностикой!"""
    logger.debug(f"🔍 Response type: {type(response)}")
    
    if not response:
        logger.error("❌ Response is None/empty")
        return ""
    
    try:
        # 1. Полный дамп для отладки
        logger.debug(f"🔍 Full response: {response}")
        
        # 2. Стандартный путь
        if hasattr(response, 'choices') and response.choices:
            first_choice = response.choices[0]
            logger.debug(f"🔍 First choice: {first_choice}")
            
            if hasattr(first_choice, 'message') and first_choice.message:
                content = getattr(first_choice.message, 'content', '')
                logger.debug(f"🔍 Content found: {content[:100]}...")
                return content or ""
        
        # 3. Fallback пути
        if isinstance(response, dict):
            choices = response.get('choices', [])
            if choices:
                message = choices[0].get('message', {})
                content = message.get('content', '')
                logger.debug(f"🔍 Dict content: {content[:100]}...")
                return content
        
        logger.error("❌ Нет content в response!")
        return ""
        
    except Exception as e:
        logger.error(f"❌ Extract error: {e}")
        logger.error(f"   Trace: {traceback.format_exc()}")
        return ""


def call_perplexity_api(pdf_text: str) -> str:
    """🔥 API вызов с ПОЛНОЙ диагностикой!"""
    logger.info(f"🌐 Тестируем API: model={LLM_MODEL}")
    
    try:
        # ✅ ТЕСТОВЫЙ запрос
        response = client.chat.completions.create(
            model=LLM_MODEL,
            messages=[
                {"role": "system", "content": "Ты парсер ЕГРН."},
                {"role": "user", "content": "Привет! Верни JSON: {\"test\": \"OK\"}"}
            ],
            temperature=0.1,
            max_tokens=100,
            timeout=30,
        )
        
        api_response = extract_content_from_response(response)
        logger.info(f"✅ ТЕСТ API: {len(api_response)} симв. -> {api_response[:100]}...")
        
        if not api_response or len(api_response) < 5:
            return '{"error": "API returns empty response"}'
        
        # ✅ РЕАЛЬНЫЙ запрос
        if len(pdf_text) > MAX_TEXT_FOR_LLM:
            pdf_text = pdf_text[:MAX_TEXT_FOR_LLM]
        
        prompt = f"""
ТЫ — СУПЕР-ПАРСЕР ЕГРН №1! ТВОЯ ЗАДАЧА: ИЗВЛЕЧЬ АБСОЛЮТНО ВСЮ ИНФОРМАЦИЮ!

🚨 КРИТИЧЕСКИ ВАЖНЫЕ ПРАВИЛА (СТРОГО ВЫПОЛНЯЙ):
1. ЧИТАЙ ТЕКСТ СЛОВО В СЛОВО 3 РАЗА
2. ИЩИ ВСЮ ПОСЛЕДОВАТЕЛЬНОСТЬ: 74:36:, 36:, 01:, 02:
3. АДРЕС: улица+дом, город, район, квартал, индекс
4. ОТВЕЧАЙ ТОЛЬКО JSON! БЕЗ ```
5. ЕСЛИ НЕ НАШЁЛ — null (НЕ ПРОПУСКАЙ ПОЛЯ!)
6. ДАТЫ: ДД.ММ.ГГГГ или ГГГГ-ММ-ДД

🔍 ЧТО ИСКАТЬ В ЕГРН (конкретные маркеры):
    Ищи именно это
    COLUMNS_MAPPING = {
    'Адрес, комплекс': 'address',
    'Наименование здания': 'building_name',
    'Литера / Строение': 'litera',
    'Кадастр. номер ЗУ': 'cadastral_land',
    'Кадастр. номер здания': 'cadastral_building',
    '№ помещения': 'room_number',
    'Этаж': 'floor',
    'Площадь (м²)': 'area',
    'Предполагаемое назначение': 'purpose',
    'Статус': 'status',
    'Арендатор': 'tenant',
    'Подтверждение из PDF': 'confirmation',
    'Примечания и расхождения': 'notes',
    'Собственник': 'owner',
    'Обременение (аренда)': 'encumbrance',
    'PDF-источник': 'pdf_source',
}
📊 ОБЯЗАТЕЛЬНАЯ СТРУКТУРА JSON (ВСЕ поля заполни!):

{{
  "cadastral_number": "74:36:0100100:123 или null",
  "cadastral_quarter": "36:01:0202001 или null", 
  "address": "г.Челябинск, ул.Ленина,10 или null",
  "literal": "литер А или null",
  "area": 1500.00 или null,
  "permitted_use": "жилое/склад или null",
  "status": "учт/зарегистрирован или null",
  "owner": {{
    "full_name": "Иванов И.И. или null",
    "right_type": "собственность/пользование или null",
    "share": "1/2 или null"
  }},
  "rental_data": {{
    "tenant": "ООО Ромашка или null",
    "rent_type": "аренда/субаренда или null",
    "period_start": "01.01.2025 или null",
    "period_end": "31.12.2030 или null",
    "registration_date": "15.03.2024 или null"
  }},
  "objects_on_land": [
    {{
      "cadastral_number": "74:36:0100100:456 или null",
      "description": "жилое здание 5 эт. или null",
      "area": 1200.5 или null
    }}
  ],
  "notes": "все важное: обременения, ограничения, примечания"
}}

🎯 ПРИМЕРЫ ИЗВЛЕЧЕНИЯ:
Текст: "Кадастровый номер объекта недвижимости 74:36:0100100:123"
→ "cadastral_number": "74:36:0100100:123"

Текст: "Адрес (местоположение): г. Челябинск, Центральный район, ул. Кирова, 25"
→ "address": "г. Челябинск, Центральный район, ул. Кирова, 25"

Текст: "Правообладатель: Гражданка Иванова Ирина Ивановна, доля 1/2"
→ "owner": {{"full_name": "Иванова Ирина Ивановна", "right_type": "собственность", "share": "1/2"}}

Текст: "Договор аренды №123 от 01.01.2025, Арендатор: ООО Ромашка, до 31.12.2030"
→ "rental_data": {{"tenant": "ООО Ромашка", "period_start": "01.01.2025", "period_end": "31.12.2030"}}

📄 ТЕКСТ ЕГРН ДЛЯ АНАЛИЗА (читай внимательно):
{pdf_text[:8000]}

🚀 ВЕРНИ ТОЛЬКО JSON! НИ СЛОВА БОЛЬШЕ!
"""

        
        response = client.chat.completions.create(
            model=LLM_MODEL,
            messages=[
                {"role": "user", "content": prompt}
            ],
            temperature=0.1,
            max_tokens=LLM_MAX_TOKENS,
            timeout=LLM_TIMEOUT,
        )
        
        api_response = extract_content_from_response(response)
        logger.info(f"✅ РЕАЛЬНЫЙ ответ: {len(api_response)} симв.")
        logger.debug(f"   Preview: {api_response}")
        
        return api_response or '{"error": "Empty API response"}'
        
    except Exception as e:
        logger.error(f"❌ API КРИТИЧЕСКАЯ ОШИБКА: {e}")
        logger.error(f"   Trace: {traceback.format_exc()}")
        return f'{{"error": "{str(e)}"}}'


def parse_json_response(json_str: str) -> Dict[str, Any]:
    """🔥 ОТЛАДОЧНЫЙ ПАРСЕР - покажет ВСЁ!"""
    logger.info(f"🔍 RAW JSON ({len(json_str)} симв.): {json_str}")
    
    if not json_str:
        return {"error": "Пустой ответ"}
    
    # ✅ ШАГ 1: Убираем Markdown БЕЗ regex ошибок
    cleaned = json_str.replace('``````', '').strip()
    cleaned = re.sub(r'\n\s*', ' ', cleaned)  # Только пробелы
    
    logger.info(f"🔍 CLEANED: {cleaned[:300]}...")
    
    # ✅ ШАГ 2: Ищем JSON блок
    start = cleaned.find('{')
    end = cleaned.rfind('}') + 1
    
    if start != -1 and end > start:
        json_candidate = cleaned[start:end]
        logger.info(f"🔍 JSON BLOCK: {json_candidate[:200]}...")
        
        try:
            parsed = json.loads(json_candidate)
            
            # ✅ ЛОГИРУЕМ РЕАЛЬНЫЕ ДАННЫЕ!
            logger.info(f"✅ РЕАЛЬНЫЕ ДАННЫЕ:")
            logger.info(f"   Кадастр: {parsed.get('cadastral_number')}")
            logger.info(f"   Адрес: {parsed.get('address')}")
            logger.info(f"   Площадь: {parsed.get('area')}")
            logger.info(f"   Ключи: {list(parsed.keys())}")
            
            return parsed
            
        except json.JSONDecodeError as e:
            logger.error(f"❌ JSON ERROR: {e}")
    
    # ✅ EMERGENCY: ручной парсинг ключей
    logger.warning("🔥 EMERGENCY PARSING...")
    cadastral_match = re.search(r'cadastral[_-]?number["\s:]*([^\s,"}]*)', cleaned, re.IGNORECASE)
    address_match = re.search(r'address["\s:]*([^\s,"}]*)', cleaned, re.IGNORECASE)
    
    emergency_data = {
        "cadastral_number": cadastral_match.group(1) if cadastral_match else None,
        "address": address_match.group(1) if address_match else None,
        "area": None,
        "owner": {"full_name": None},
        "emergency_parsed": True
    }
    
    logger.info(f"✅ EMERGENCY DATA: {emergency_data}")
    return emergency_data

def extract_egrn_data(pdf_text: str) -> Dict[str, Any]:
    """Главная функция с защитой."""
    logger.info(f"📄 extract_egrn_data: {len(pdf_text)} симв.")
    
    if not pdf_text or len(pdf_text.strip()) < 10:
        return {"error": "Текст слишком короткий"}
    
    try:
        json_str = call_perplexity_api(pdf_text)
        data = parse_json_response(json_str)
        
        logger.info(f"📊 РЕЗУЛЬТАТ: {json.dumps(data, ensure_ascii=False)[:200]}...")
        return {"data": data}
        
    except Exception as e:
        logger.error(f"❌ extract_egrn_data: {e}\n{traceback.format_exc()}")
        return {"error": str(e)}


if __name__ == "__main__":
    test_text = "КАДАСТРОВЫЙ НОМЕР 74:36:0100100:123 АДРЕС: г.Челябинск ул.Ленина 10"
    result = extract_egrn_data(test_text)
    print(json.dumps(result, ensure_ascii=False, indent=2))
