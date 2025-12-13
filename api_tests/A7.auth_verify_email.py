import sys
import os
sys.path.append(os.path.abspath(os.path.dirname(__file__)))
import utils

token = "dummy_verify_token"

# Request
utils.send_and_print(
    url=f"{utils.BASE_URL}/auth/verify-email?token={token}",
    method="POST",
    headers={"Content-Type": "application/json"},
    output_file=f"{os.path.splitext(os.path.basename(__file__))[0]}.json"
)