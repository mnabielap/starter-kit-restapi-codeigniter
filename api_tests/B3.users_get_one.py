import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

access_token = utils.load_config("access_token")
# Try to use the user created in B1, otherwise use the logged-in user
target_id = utils.load_config("target_user_id") or utils.load_config("user_id")

utils.send_and_print(
    url=f"{utils.BASE_URL}/users/{target_id}",
    method="GET",
    headers={
        "Authorization": f"Bearer {access_token}"
    },
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)