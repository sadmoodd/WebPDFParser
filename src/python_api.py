from flask import Flask, request, jsonify
from pathlib import Path
import json
import pandas as pd
from datetime import datetime
import os

app = Flask(__name__)

from flask_cors import CORS

CORS(app)

# Максимальный размер загруженного файла
app.config['MAX_CONTENT_LENGTH'] = 500 * 1024 * 1024

@app.route("/api/parse-egrn", methods=['POST'])
def parse_egrn():
    """
    Request JSON:
    {
        "pdf_paths": ["/tmp/uploads/file1.pdf", "/tmp/uploads/file2.pdf"],
        "columns": ["кадастровый_номер", "адрес", "площадь", ...],
        "output_format": "xlsx"  # опционально
    }
    
    Response JSON:
    {
        "success": true,
        "excel_path": "/tmp/results/result_12345.xlsx",
        "excel_url": "http://localhost:8000/downloads/result_12345.xlsx",
        "file_size": "2.5MB",
        "rows_processed": 45,
        "errors": 2,
        "message": "Обработка завершена: 45 успехов, 2 ошибки"
    }
    """

    try:
        data = request.get_json()
        
        pdf_paths = data.get('pdf_paths', [])
        columns = data.get('columns', [])

        if not pdf_paths:
            return jsonify({
                "success" : False,
                "error": "Нет путей к файлам"
            }), 400
    
        if len(pdf_paths) > 50:
            return jsonify({
                "success" : False,
                "error": "Максимум 50 загружаемых файлов"
            }), 400


        # Обработка
        # TODO!!!
        # result = process_egrn_batch(
        #     pdf_files=[Path(p) for p in pdf_paths],
        #     custom_columns=columns if columns else None
        # )
        results_dir = Path('result')
        results_dir.mkdir(exist_ok=True)
        excel_path = results_dir / f"result_{int(datetime.now().timestamp())}.xlsx"

        df = pd.DataFrame({"кадастровый_номер": ["77:01:0001:1"], "адрес": ["Москва"]})
        df.to_excel(excel_path, index=False)

        result = {
            "excel_path": excel_path,
            "total_rows": 1, 
            "successful_count": 1,
            "failed_count": 0
        }
        
        excel_path = result.get('excel_path')
        print("OK")
        return jsonify({
            "success": True,
            "excel_path": str(excel_path),
            "excel_url": f"http://localhost:8000/downloads/{Path(excel_path).name}",
            "file_size": f"{os.path.getsize(excel_path) / (1024*1024):.2f}MB",
            "rows_processed": result.get('total_rows'),
            "errors": result.get('failed_count'),
            "message": f"Обработано: {result.get('successful_count')} успехов, {result.get('failed_count')} ошибок"
        }), 200

    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


app.route("/download/<filename>", methods=['GET'])
def download_file(filename):
    """
    Endpoint для загрузки таблицы Excel
    """
    from flask import send_file

    results_dir = Path('result')
    file_path = results_dir / filename

    # проверяем, что путь точно в results/
    if not file_path.resolve().parent == results_dir.resolve():
        return jsonify({"error": "Invalid file path"}), 403
    
    if not file_path.exists():
        return jsonify({"error": "File not found"}), 404

    return send_file(
        file_path,
        as_attachment=True,
        download_name=filename,
        mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    )


@app.route('/api/health', methods=['GET'])
def health():
    """Проверка, жив ли API"""
    return jsonify({"status": "ok", "message": "Python API is running"}), 200


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)