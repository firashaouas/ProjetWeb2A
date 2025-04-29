import pyttsx3
import sys

def text_to_speech(text):
    engine = pyttsx3.init()
    engine.say(text)
    engine.runAndWait()

if __name__ == '__main__':
    text = sys.argv[1] if len(sys.argv) > 1 else "Hello, how can I assist you?"
    text_to_speech(text)
