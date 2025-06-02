import face_recognition
import cv2
import os
import pickle
import sys

# Charger les visages enregistrés
KNOWN_FACES_DIR = "C:/xampp/htdocs/face/database"
TOLERANCE = 0.6  # Seuil de similarité

def load_known_faces():
    known_face_encodings = []
    known_face_ids = []
    
    if not os.path.exists(KNOWN_FACES_DIR):
        os.makedirs(KNOWN_FACES_DIR)
        return known_face_encodings, known_face_ids
    
    for filename in os.listdir(KNOWN_FACES_DIR):
        if filename.endswith(".pkl"):
            user_id = os.path.splitext(filename)[0]
            with open(os.path.join(KNOWN_FACES_DIR, filename), "rb") as f:
                data = pickle.load(f)
                known_face_encodings.append(data['encoding'])
                known_face_ids.append(user_id)
    
    return known_face_encodings, known_face_ids

def recognize_face():
    known_face_encodings, known_face_ids = load_known_faces()
    
    if not known_face_encodings:
        print("Aucun visage enregistré dans la base de données", file=sys.stderr)
        return None
    
    video_capture = cv2.VideoCapture(0)
    recognized_id = None
    
    for _ in range(30):  # Essayer pendant 30 frames
        ret, frame = video_capture.read()
        if not ret:
            break
        
        # Trouver les visages dans l'image
        face_locations = face_recognition.face_locations(frame)
        face_encodings = face_recognition.face_encodings(frame, face_locations)
        
        for face_encoding in face_encodings:
            # Comparer avec les visages connus
            matches = face_recognition.compare_faces(
                known_face_encodings, face_encoding, tolerance=TOLERANCE)
            
            if True in matches:
                first_match_index = matches.index(True)
                recognized_id = known_face_ids[first_match_index]
                break
        
        if recognized_id is not None:
            break
        
        # Afficher un message à l'utilisateur
        cv2.putText(frame, "Recherche de visage...", (30, 30), 
                    cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2)
        cv2.imshow('Reconnaissance faciale', frame)
        
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
    
    video_capture.release()
    cv2.destroyAllWindows()
    
    return recognized_id

if __name__ == "__main__":
    user_id = recognize_face()
    if user_id:
        print(user_id)  # Juste l'ID, sans autre texte
    else:
        print("Aucun visage reconnu", file=sys.stderr)
        sys.exit(1)