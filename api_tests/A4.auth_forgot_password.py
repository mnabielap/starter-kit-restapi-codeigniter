import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

payload = {
    "email": "admin@example.com" 
}

# Request
utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/forgot-password",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)