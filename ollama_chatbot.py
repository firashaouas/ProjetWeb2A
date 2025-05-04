from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import json
import time
import re

app = Flask(__name__)
CORS(app, resources={
    r"/chat": {
        "origins": ["http://localhost", "http://127.0.0.1", "http://0.0.0.0"],
        "methods": ["POST"],
        "allow_headers": ["Content-Type"]
    }
})

def detect_language(message):
    message = message.lower()
    english_keywords = ['hello', 'hi', 'how', 'what', 'where', 'when', 'why', 'is', 'are', 'can', 'do', 'movie', 'film', 'activity', 'event']
    french_keywords = ['bonjour', 'salut', 'comment', 'quoi', 'où', 'quand', 'pourquoi', 'est', 'sont', 'peux', 'film', 'activité', 'événement']
    
    if any(keyword in message for keyword in english_keywords):
        return "en"
    elif any(keyword in message for keyword in french_keywords):
        return "fr"
    return "fr"  # Par défaut, français si ambigu

def get_ollama_response(payload):
    max_retries = 5
    for attempt in range(max_retries):
        try:
            response = requests.post("http://localhost:11434/api/chat", json=payload, timeout=240)
            if response.status_code == 200:
                result = response.json()
                content = result.get('message', {}).get('content', '')
                if content:
                    return {"message": {"content": content}}
                return {"message": {"content": "Désolé, je n'ai pas assez d'infos pour répondre précisément, mais pose-moi une autre question !"}}
            time.sleep(2 ** attempt)
        except requests.exceptions.RequestException as e:
            if attempt == max_retries - 1:
                return {"message": {"content": "Désolé, je n'ai pas assez d'infos pour répondre précisément, mais pose-moi une autre question !"}}
            time.sleep(2 ** attempt)
    return {"message": {"content": "Désolé, je n'ai pas assez d'infos pour répondre précisément, mais pose-moi une autre question !"}}

@app.route('/chat', methods=['POST'])
def chat():
    try:
        data = request.get_json()
        if not data or 'message' not in data:
            return jsonify({'response': ''})

        user_message = data['message'].strip().lower()
        if not user_message:
            return jsonify({'response': ''})

        language = detect_language(user_message)

        tunisia_loisirs_keywords = [
            'loisirs', 'divertissements', 'activités', 'événements', 'tunis', 'prix', 'réservation',
            'covoiturage', 'transport', 'produits', 'sponsors', 'parc', 'cinéma', 'paintball', 'trampoline',
            'festival', 'tunisie', 'monastir', 'sousse', 'hammamet', 'carthage', 'djerba', 'cinéma', 'concerts',
            'théâtre', 'expositions', 'sports', 'plongée', 'randonnée', 'parc aquatique', 'musée', 'festival de musique',
            'festival de danse', 'festival de théâtre', 'festival de cinéma', 'festival gastronomique', 'festival culturel',
            'festival de la mer', 'festival de la culture', 'festival de la jeunesse', 'festival de la mode',
            'billets', 'réservation', 'activité', 'sortie', 'excursion', 'tourisme', 'visite', 'guide touristique',
            'circuit', 'aventure', 'nature', 'plage', 'montagne', 'camping', 'randonnée', 'kayak', 'plongée sous-marine',
            'parapente', 'accrobranche', 'paintball', 'karting', 'bowling', 'laser game', 'trampoline park',
            'escape game', 'salle de jeux', 'parc d’attractions', 'parc animalier', 'zoo', 'aquarium', 'musée des sciences',
            'musée d’histoire', 'musée d’art', 'musée archéologique', 'musée ethnographique', 'musée maritime', 'la marsa',
            'la goulette', 'la medina', 'la kasbah', 'la medina de tunis', 'la medina de sousse', 'la medina de hammamet',
            'padel', 'tennis', 'golf', 'surf', 'kite surf', 'windsurf', 'voile', 'yachting', 'croisière', 'bateau',
            'plongée libre', 'snorkeling', 'Gammarth',
            'leisure', 'entertainment', 'activities', 'events', 'price', 'booking', 'carpool', 'transport',
            'products', 'sponsors', 'park', 'cinema', 'concert', 'theater', 'exhibition', 'sport', 'diving', 'hiking',
            'water park', 'museum', 'music festival', 'dance festival', 'theater festival', 'film festival', 'food festival',
            'cultural festival', 'sea festival', 'youth festival', 'fashion festival', 'ticket', 'activity', 'outing',
            'excursion', 'tourism', 'visit', 'tour guide', 'circuit', 'adventure', 'nature', 'beach', 'mountain',
            'camping', 'kayaking', 'scuba diving', 'paragliding', 'tree climbing', 'go-karting', 'bowling', 'laser tag',
            'trampoline park', 'escape room', 'game room', 'amusement park', 'animal park', 'aquarium', 'science museum',
            'history museum', 'art museum', 'archaeological museum', 'ethnographic museum', 'maritime museum'
        ]

        if not any(keyword in user_message for keyword in tunisia_loisirs_keywords):
            return jsonify({'response': ''})

        system_message = {
            "fr": (
                "Tu es un expert en loisirs et divertissements en Tunisie. Réponds uniquement à des questions sur ce thème, incluant : "
                "- Activités (ex. : paintball, cinéma, trampoline) avec lieux et prix (ex. : paintball à Hammamet : 30-60 TND). "
                "- Événements à Tunis (dates, lieux, ex. : Festival de Carthage en mai 2025). "
                "- Produits à vendre (ex. : équipements de paintball, billets). "
                "- Covoiturage/transport pour les activités/événements (ex. : 10 TND par trajet). "
                "- Sponsors (ex. : TunisAir, Orange Tunisie). "
                "Utilise des exemples réalistes et donne des détails. Réponds en français."
            ),
            "en": (
                "You are an expert in leisure and entertainment in Tunisia. Only answer questions related to this theme, including: "
                "- Activities (e.g., paintball, cinema, trampoline) with locations and prices (e.g., paintball in Hammamet: 30-60 TND). "
                "- Events in Tunis (dates, locations, e.g., Carthage Festival in May 2025). "
                "- Products for sale (e.g., paintball gear, tickets). "
                "- Carpooling/transport for activities/events (e.g., 10 TND per trip). "
                "- Sponsors (e.g., TunisAir, Orange Tunisia). "
                "Use realistic examples and provide details. Answer in English."
            )
        }

        payload = {
            "model": "llama3.2",
            "messages": [
                {"role": "system", "content": system_message[language]},
                {"role": "user", "content": user_message}
            ],
            "stream": False,
            "options": {"temperature": 0.7}  # Ajout pour ajuster la créativité
        }

        result = get_ollama_response(payload)
        final_response = result['message']['content']
        
        return jsonify({'response': final_response})

    except Exception:
        return jsonify({'response': "Désolé, je n'ai pas assez d'infos pour répondre précisément, mais pose-moi une autre question !"})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001)