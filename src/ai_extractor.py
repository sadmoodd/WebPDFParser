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
    HF_API_KEY,
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
    api_key=HF_API_KEY,
    base_url="https://router.huggingface.co/v1"
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
        
        prompt = f"""ИЗВЛЕКИ из ЕГРН ТОЛЬКО JSON!

ПРАВИЛА:
- ТОЛЬКО JSON без текста!
- Не нашёл → null
- Кадастр: полная последовательность 74:36:...
- Площадь: число (1234.56)
- Даты: ДД.ММ.ГГГГ

Нужны поля:
{{
  "cadastral_land": null,
  "cadastral_building": null,
  "address": null,
  "area": null,
  "owner": null,
  "tenant": null,
  "floor": null,
  "litera": null,
  "status": null
}}

ПРИМЕРЫ:
"74:36:0100100:123" → "cadastral_land": "74:36:0100100:123"
"г.Челябинск ул.Ленина 10" → "address": "г.Челябинск ул.Ленина 10" 
"Площадь 1234,56" → "area": 1234.56

ТЕКСТ:
{pdf_text[:4000]}

JSON:"""


        
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
    test_text = """Филиал публично-правовой компании "Роскадастр" по Челябинской области
полное наименование органа регистрации прав
Выписка из Единого государственного реестра недвижимости об объекте недвижимости
Сведения о характеристиках объекта недвижимости
На основании запроса от 18.09.2025, поступившего на рассмотрение 18.09.2025, сообщаем, что согласно записям Единого государственного реестра недвижимости:
Земельный участок
вид объекта недвижимости
раздела 1
Кадастровый номер: 74:36:0303005:71
Номер кадастрового квартала: 74:36:0303005
Дата присвоения кадастрового номера: 04.12.2015
Ранее присвоенный государственный учетный номер: данные отсутствуют
Местоположение: Челябинская область, г Челябинск, р-н Ленинский, ул Енисейская
Площадь: 6345 +/- 28
Кадастровая стоимость, руб.: 6083158
Кадастровые номера расположенных в пределах земельного 74:36:0303005:425, 74:36:0303005:462
участка объектов недвижимости:
Кадастровые номера объектов недвижимости, из которых данные отсутствуют
образован объект недви"""
    result = extract_egrn_data(test_text)
    print(json.dumps(result, ensure_ascii=False, indent=2))
