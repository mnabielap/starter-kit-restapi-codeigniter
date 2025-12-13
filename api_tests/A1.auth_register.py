import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Data
payload = {
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123", # Valid strength
    "role": "user"
}

# Request
response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/register",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# Logic: Save credentials if successful
if response.status_code == 201:
    data = response.json().get('data', {})
    tokens = data.get('tokens', {})
    user = data.get('user', {})
    
    utils.save_config("access_token", tokens.get('access', {}).get('token'))
    utils.save_config("refresh_token", tokens.get('refresh', {}).get('token'))
    utils.save_config("user_id", user.get('id'))
    print("\n[INFO] Registration successful. Tokens and User ID saved to secrets.json")