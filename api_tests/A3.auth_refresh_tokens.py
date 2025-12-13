import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

# Load saved refresh token
refresh_token = utils.load_config("refresh_token")

if not refresh_token:
    print("No refresh token found in secrets.json. Run login first.")
    sys.exit(1)

payload = {
    "refreshToken": refresh_token
}

# Request
response = utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/refresh-tokens",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# Logic: Update tokens
if response.status_code == 200:
    data = response.json().get('data', {})
    utils.save_config("access_token", data.get('access', {}).get('token'))
    utils.save_config("refresh_token", data.get('refresh', {}).get('token'))
    print("\n[INFO] Tokens refreshed and saved.")