#!/usr/bin/env python3
"""Deploy aws-gateway fixes to the payment-gateway EC2 instance."""
from __future__ import annotations

import json
import subprocess
import sys
from pathlib import Path

INSTANCE_ID = "i-0aa75bfc70f13c259"
REGION = "us-east-1"
AZ = "us-east-1b"
SSH_USER = "ubuntu"
HOST = "98.94.149.174"
REMOTE_ROOT = "/var/www/laravel"
LOCAL_ROOT = Path(__file__).resolve().parent

FILES = [
    "app/Http/Controllers/SubscriptionController.php",
    "app/Services/HeroPortal/HeroPortalWebhookService.php",
    "app/Services/HeroPortal/HeroPortalPlanMapper.php",
    "config/heroportal.php",
]


def run(cmd: list[str], *, check: bool = True) -> subprocess.CompletedProcess:
    print(">", " ".join(cmd))
    return subprocess.run(cmd, check=check, text=True, capture_output=True)


def main() -> int:
    key = Path.home() / ".ssh" / "pgw-temp-key"
    pub = Path(str(key) + ".pub")
    if not key.exists():
        run(["ssh-keygen", "-t", "rsa", "-b", "2048", "-f", str(key), "-N", ""], check=True)

    pub_text = pub.read_text(encoding="utf-8")
    send = run(
        [
            "aws",
            "ec2-instance-connect",
            "send-ssh-public-key",
            "--instance-id",
            INSTANCE_ID,
            "--instance-os-user",
            SSH_USER,
            "--ssh-public-key",
            pub_text,
            "--availability-zone",
            AZ,
            "--region",
            REGION,
        ]
    )
    if send.returncode != 0:
        print(send.stderr or send.stdout)
        print("AWS credentials required. Run: aws configure")
        return 1

    for rel in FILES:
        local = LOCAL_ROOT / rel
        remote = f"{REMOTE_ROOT}/{rel.replace(chr(92), '/')}"
        remote_dir = "/".join(remote.split("/")[:-1])
        run(
            [
                "ssh",
                "-i",
                str(key),
                "-o",
                "StrictHostKeyChecking=no",
                f"{SSH_USER}@{HOST}",
                f"mkdir -p {remote_dir}",
            ]
        )
        scp = run(
            [
                "scp",
                "-i",
                str(key),
                "-o",
                "StrictHostKeyChecking=no",
                str(local),
                f"{SSH_USER}@{HOST}:{remote}",
            ]
        )
        if scp.returncode != 0:
            print(scp.stderr or scp.stdout)
            return 1
        print(f"uploaded {rel}")

    post = run(
        [
            "ssh",
            "-i",
            str(key),
            "-o",
            "StrictHostKeyChecking=no",
            f"{SSH_USER}@{HOST}",
            f"cd {REMOTE_ROOT} && php artisan optimize:clear && php artisan config:cache",
        ]
    )
    print(post.stdout)
    if post.returncode != 0:
        print(post.stderr)
        return 1

    verify = run(
        [
            "ssh",
            "-i",
            str(key),
            "-o",
            "StrictHostKeyChecking=no",
            f"{SSH_USER}@{HOST}",
            f"grep HeroPortalWebhookService {REMOTE_ROOT}/app/Http/Controllers/SubscriptionController.php | head -1",
        ]
    )
    print(verify.stdout.strip())
    print("Deploy complete.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
