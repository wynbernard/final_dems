from PIL import Image
import pytesseract
# import requests

# Set tesseract path if needed (Windows only)
# pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

# Load image
image = Image.open("./src/images/id_card.jpg")  # Adjust the path to your image

# Crop image to area where the name is (adjust these values to your layout)
# Example: (left, top, right, bottom)
name_region = image.crop((100, 200, 600, 260))  # Adjust based on your image layout

# OCR to extract name
extracted_name = pytesseract.image_to_string(name_region).strip()

print(f"Extracted Name: {extracted_name}")

# Send to PHP server
# response = requests.post("http://localhost/compare.php", data={'extracted_name': extracted_name})

# print("Server Response:", response.text)
