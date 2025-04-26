from flask import Flask, request, jsonify
import os
import uuid
from pydub import AudioSegment
import speech_recognition as sr

app = Flask(__name__)
UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

@app.route('/convert', methods=['POST'])
def convert_voice():
    if 'file' not in request.files:
        return jsonify({'error': 'No file provided'}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'Empty filename'}), 400

    # ذخیره فایل OGG/OPUS
    unique_id = str(uuid.uuid4())
    ogg_path = os.path.join(UPLOAD_FOLDER, f"{unique_id}.ogg")
    wav_path = os.path.join(UPLOAD_FOLDER, f"{unique_id}.wav")
    file.save(ogg_path)

    try:
        # تبدیل به WAV
        AudioSegment.from_file(ogg_path).export(wav_path, format='wav')

        # تبدیل صدا به متن
        recognizer = sr.Recognizer()
        with sr.AudioFile(wav_path) as source:
            audio_data = recognizer.record(source)
            text = recognizer.recognize_google(audio_data, language='fa-IR')  # زبان فارسی

        # پاکسازی فایل‌های موقت
        os.remove(ogg_path)
        os.remove(wav_path)

        return jsonify({'text': text})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)
