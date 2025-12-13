import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

access_token = utils.load_config("access_token")
target_id = utils.load_config("target_user_id") or utils.load_config("user_id")

payload = {
    "name": "Admin 123 Updated Name via Python"
}

utils.send_and_print(
    url=f"{utils.BASE_URL}/users/{target_id}",
    method="PATCH",
    body=payload,
    headers={
        "Content-Type": "application/json",
        "Authorization": f"Bearer {access_token}"
    },
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)