import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Placeholder token - in real scenario, this comes from the email link
token = "dummy_token_from_email_link"

payload = {
    "password": "newpassword123"
}

# Request
utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/reset-password?token={token}",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)