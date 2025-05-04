from flask import Flask, request, jsonify
from flask_cors import CORS
import os, requests

app = Flask(__name__)
CORS(app)  # ✅ Autorise les appels depuis le navigateur (port différent)

# 📁 Tes dossiers de destination
PRIMARY_DIR = "C:/MesImages"
FALLBACK_DIR = "C:/DefaultImages"

@app.route("/telecharger", methods=["POST"])
def telecharger():
    data = request.json
    image_url = data.get("image_url")

    if not image_url:
        return jsonify({"error": "URL manquante"}), 400

    # 📁 Utiliser PRIMARY_DIR si existe, sinon fallback
    directory = PRIMARY_DIR if os.path.exists(PRIMARY_DIR) else FALLBACK_DIR
    os.makedirs(directory, exist_ok=True)

    # 📸 Nom du fichier à partir de l'URL
    filename = image_url.split("/")[-1].split("?")[0] + ".jpg"
    path = os.path.join(directory, filename)

    try:
        image = requests.get(image_url)
        with open(path, "wb") as f:
            f.write(image.content)
        return jsonify({"message": f"✅ Image enregistrée dans : {path}"})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(port=5000)
