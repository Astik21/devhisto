import os
import requests
from flask import Flask, request, jsonify

# Configuration
API_TOKEN = "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"
API_URL = "https://api-inference.huggingface.co/models/microsoft/layoutlmv3-base"

# Flask app
app = Flask(__name__)

# Hugging Face API function
def query_huggingface_api(pdf_bytes):
    headers = {"Authorization": f"Bearer {API_TOKEN}"}
    response = requests.post(API_URL, headers=headers, data=pdf_bytes)
    return response.json()

@app.route("/process-pdf", methods=["POST"])
def process_pdf():
    if 'file' not in request.files:
        return jsonify({"error": "No file provided"}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({"error": "Empty filename"}), 400

    try:
        pdf_bytes = file.read()
        result = query_huggingface_api(pdf_bytes)
        return jsonify(result)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Run the app
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
