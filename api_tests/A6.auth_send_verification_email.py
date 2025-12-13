import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

access_token = utils.load_config("access_token")

# Request
utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/send-verification-email",
    method="POST",
    headers={
        "Content-Type": "application/json",
        "Authorization": f"Bearer {access_token}"
    },
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)