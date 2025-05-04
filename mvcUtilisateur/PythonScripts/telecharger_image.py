import os
import requests

url = "https://images.unsplash.com/photo-1602524812085-6fa0b0b1ecdc"  # 🔁 Mets ici l'URL d'une vraie image Unsplash
output_folder = "C:/MesImages"  # 🔁 Change le dossier si tu veux
filename = "photo_unsplash.jpg"  # 🔁 Le nom du fichier final

os.makedirs(output_folder, exist_ok=True)
response = requests.get(url)

if response.status_code == 200:
    full_path = os.path.join(output_folder, filename)
    with open(full_path, "wb") as f:
        f.write(response.content)
    print(f"✅ Image enregistrée ici : {full_path}")
else:
    print("❌ Erreur de téléchargement")
