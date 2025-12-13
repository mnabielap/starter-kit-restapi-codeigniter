import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Data - Using the Admin credentials from the Seeder
payload = {
    "email": "admin@example.com",
    "password": "password123"
}

# Request
response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/login",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# Logic: Save tokens if successful
if response.status_code == 200:
    data = response.json().get('data', {})
    tokens = data.get('tokens', {})
    user = data.get('user', {})
    
    utils.save_config("access_token", tokens.get('access', {}).get('token'))
    utils.save_config("refresh_token", tokens.get('refresh', {}).get('token'))
    utils.save_config("user_id", user.get('id'))
    print("\n[INFO] Login successful. Admin Tokens saved to secrets.json")