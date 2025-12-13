import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

refresh_token = utils.load_config("refresh_token")

payload = {
    "refreshToken": refresh_token
}

# Request
utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/logout",
    method="POST",
    body=payload,
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)