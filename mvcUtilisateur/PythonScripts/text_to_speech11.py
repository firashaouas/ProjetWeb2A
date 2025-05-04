import pyttsx3
import sys
import os

def text_to_speech(text):
    engine = pyttsx3.init()

    # Configuration : voix + vitesse + volume
    engine.setProperty('rate', 150)    # vitesse
    engine.setProperty('volume', 1.0)  # volume max

    # Lister les voix disponibles
    voices = engine.getProperty('voices')
    for voice in voices:
        if "french" in voice.name.lower():
            engine.setProperty('voice', voice.id)
            break

    engine.say(text)
    engine.runAndWait()

if __name__ == '__main__':
    if len(sys.argv) > 1:
        input_text = " ".join(sys.argv[1:])  # Combiner tous les arguments
        text_to_speech(input_text)
    else:
        print("Pas de texte donné.")
