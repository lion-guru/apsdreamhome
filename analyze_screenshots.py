import base64
import requests
import json

# Ollama endpoint
OLLAMA_URL = "http://localhost:11434/api/generate"

def analyze_with_moondream(image_path, prompt):
    """Analyze an image with moondream via Ollama"""
    with open(image_path, "rb") as f:
        image_data = base64.b64encode(f.read()).decode('utf-8')
    
    payload = {
        "model": "moondream",
        "prompt": prompt,
        "images": [image_data],
        "stream": False,
        "options": {"temperature": 0.1, "num_predict": 1000}
    }
    
    response = requests.post(OLLAMA_URL, json=payload)
    if response.status_code == 200:
        return response.json().get("response", "")
    else:
        return f"Error: {response.status_code} - {response.text}"

# Analyze each screenshot
screenshots = [
    ("homepage.png", "Analyze this APS Dream Home homepage. Identify: 1) Visual design quality and branding consistency 2) Key sections and their clarity 3) Navigation and search functionality visibility 4) Call-to-action placement 5) Any UI issues or missing elements 6) Overall professional appearance for a real estate website"),
    ("login_page.png", "Analyze this APS Dream Home login page. Identify: 1) Visual design and branding 2) Form field clarity and accessibility 2) Login options (email/password, Google, Phone, Air Login) 3) Registration links visibility 3) Role-based login options (Associate, Agent, Admin) 4) Any UI issues or missing elements 5) Mobile responsiveness indicators"),
    ("admin_erp_overview.png", "Analyze this APS Dream Home Admin ERP Overview dashboard. Identify: 1) Dashboard layout and information hierarchy 2) Key metrics visibility and readability 3) Navigation sidebar organization 3) Quick actions and their relevance 4) Charts/data visualization quality 5) Data density and readability 6) Any UI issues or missing elements 6) Professional admin dashboard appearance"),
    ("login_page.png", "Analyze this login page specifically for role-based access. Check: 1) Are all role-based login links visible (Associate, Agent, Admin)? 2) Is Air Login (OTP) option clearly visible? 3) Is the form accessible and well-labeled? 3) Are there clear registration pathways? 4) Visual consistency with brand?"),
    ("mlm_dashboard.png", "Analyze this MLM Commission Dashboard. Identify: 1) Data visualization quality (tables, charts) 2) Key metrics visibility (commission streams, rank distribution) 3) Data density and readability 4) Quick actions relevance 5) Data freshness indicators 6) Professional financial dashboard appearance 6) Any missing metrics or UI issues"),
]

for image_path, prompt in screenshots:
    print(f"\n{'='*60}")
    print(f"ANALYZING: {image_path}")
    print(f"{'='*60}")
    result = analyze_with_moondream(image_path, prompt)
    print(result)
    print()