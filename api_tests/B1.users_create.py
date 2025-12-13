import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

access_token = utils.load_config("access_token")

payload = {
    "name": "Created by Admin",
    "email": "created@example.com",
    "password": "password123",
    "role": "user"
}

# Request
response = utils.send_and_print(
    url=f"{utils.BASE_URL}/users",
    method="POST",
    body=payload,
    headers={
        "Content-Type": "application/json",
        "Authorization": f"Bearer {access_token}"
    },
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)

# Save this new user ID to test update/delete later
if response.status_code == 201:
    new_user_id = response.json().get('data', {}).get('id')
    utils.save_config("target_user_id", new_user_id)
    print(f"\n[INFO] Target User ID {new_user_id} saved for B3/B4/B5 tests.")