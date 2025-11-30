# src/python_api.py

from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
from pathlib import Path

# Импортируем из нашего проекта
from main import process_files_batch
from settings import INPUT_DIR, OUTPUT_DIR, MAX_FILES_BATCH, DEFAULT_COLUMNS
from logger_cfg import setup_logger

logger = setup_logger(__name__)

app = Flask(__name__)
CORS(app)

@app.route("/api/parse-egrn", methods=["POST"])
def parse_egrn():
    """Основной endpoint для Laravel."""
    logger.info("➡️ /api/parse-egrn called")
    logger.info(f"Content-Type: {request.content_type}")
    
    # ✅ ОДИН раз парсим JSON
    try:
        data = request.get_json(silent=True)
        if data is None:
            return jsonify({"success": False, "error": "No JSON"}), 400
        logger.info(f"JSON data: {data}")
    except Exception as e:
        logger.error(f"JSON parse error: {e}")
        return jsonify({"success": False, "error": "Invalid JSON"}), 400

    pdf_paths = data.get("pdf_paths") or []
    columns = data.get("columns") or []

    if not pdf_paths:
        return jsonify({"success": False, "error": "pdf_paths is empty"}), 400

    if len(pdf_paths) > MAX_FILES_BATCH:
        return jsonify({"success": False, "error": f"Too many files, max {MAX_FILES_BATCH}"}), 400

    pdf_files = [Path(p) for p in pdf_paths]

    # ✅ НЕ проверяем файлы - пробуем обработать!
    missing = [str(p) for p in pdf_files if not p.exists()]
    if missing:
        logger.warning(f"⚠️ Файлы не найдены, но продолжаем: {missing}")
    
    logger.info(f"🚀 Обработка {len(pdf_files)} файлов: {[f.name for f in pdf_files]}")

    # Стандартные колонки
    use_columns = columns if columns else DEFAULT_COLUMNS

    try:
        result = process_files_batch(pdf_files, use_columns)
        
        if not result.get("success"):
            logger.error(f"Обработка не удалась: {result}")
            return jsonify({"success": False, "error": result.get("error", "Processing failed")}), 500

        excel_path = Path(result["excel_path"])
        stats = result.get("stats", {})

        logger.info(f"✅ Excel: {excel_path} ({excel_path.stat().st_size/1024/1024:.1f}MB)")

        return jsonify({
            "success": True,
            "excel_path": str(excel_path),
            "excel_filename": excel_path.name,
            "file_size": f"{excel_path.stat().st_size / (1024*1024):.2f} MB",
            "rows": stats.get("success", 0) + stats.get("failed", 0),
            "errors": stats.get("failed", 0),
            "message": f"{stats.get('success', 0)} успехов, {stats.get('failed', 0)} ошибок"
        }), 200
        
    except Exception as e:
        logger.error(f"❌ Критическая ошибка: {e}", exc_info=True)
        return jsonify({"success": False, "error": str(e)}), 500

@app.route("/api/download/<filename>", methods=["GET"])
def download_result(filename):
    """Скачивание Excel файла."""
    excel_path = OUTPUT_DIR / filename
    
    logger.info(f"📥 Скачивание: {filename}")
    
    if not excel_path.exists():
        logger.error(f"❌ Не найден: {excel_path}")
        return jsonify({"error": "File not found"}), 404
    
    return send_file(
        excel_path,
        as_attachment=True,
        download_name=filename,
        mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    )

@app.route("/api/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "ready": True})

if __name__ == "__main__":
    logger.info("🚀 API запущен: http://0.0.0.0:5000")
    app.run(host="0.0.0.0", port=5000, debug=False)
