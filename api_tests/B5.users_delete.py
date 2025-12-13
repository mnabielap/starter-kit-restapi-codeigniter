import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

access_token = utils.load_config("access_token")
target_id = utils.load_config("target_user_id")

if not target_id:
    print("No target_user_id found (run B1 first). Skipping delete to avoid deleting Admin.")
    sys.exit(0)

utils.send_and_print(
    url=f"{utils.BASE_URL}/users/{target_id}",
    method="DELETE",
    headers={
        "Authorization": f"Bearer {access_token}"
    },
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)