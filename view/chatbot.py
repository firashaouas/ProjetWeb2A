from flask import Flask, request, jsonify
from flask_cors import CORS
import requests

app = Flask(__name__)
CORS(app, resources={r"/chat": {"origins": "*"}})

@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        user_message = data.get('message', '').strip()

        if not user_message:
            return jsonify({'response': 'Veuillez entrer un message !'}), 400

        # Envoie la question à l'API Ollama
        ollama_url = "http://localhost:11434/api/chat"
        payload = {
            "model": "llama3.2",  # Utilise llama3.2 comme modèle
            "messages": [{"role": "user", "content": user_message}]
        }
        response = requests.post(ollama_url, json=payload)

        if response.status_code == 200:
            result = response.json()
            answer = result['message']['content'] if result.get('message') else "Désolé, je n'ai pas compris."
            return jsonify({'response': answer})
        else:
            return jsonify({'response': f"Erreur avec Ollama : {response.status_code}"}), 500

    except Exception as e:
        return jsonify({'response': f'Erreur côté serveur : {str(e)}'}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001)