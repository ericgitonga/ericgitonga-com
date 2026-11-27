#!/usr/bin/env python3
"""Direct Media Uploader for ericgitonga.com.

Uploads photographs directly to Angry Hosting via hardened FTPS and triggers
an instant Vercel Blob media-manifest rebuild — bypassing Git commits, PRs,
and redeployments entirely.

Usage:
    python scripts/upload_plate_photo.py <path_to_photo> [--plate Drops] [--album Drops] [--filename custom.jpg] [--no-sync]

Examples:
    python scripts/upload_plate_photo.py ~/Pictures/d_19.jpg
    python scripts/upload_plate_photo.py ~/Pictures/flower.jpg --plate Daguerreotypes --album Macro
"""

from __future__ import annotations

import argparse
import getpass
import json
import os
import pathlib
import ssl
import sys
import urllib.error
import urllib.request
from ftplib import FTP_TLS

REPO_ROOT = pathlib.Path(__file__).resolve().parent.parent
DEFAULT_PEM = REPO_ROOT / ".github" / "scripts" / "letsencrypt_geny_intermediates.pem"
CONFIG_FILE = pathlib.Path.home() / ".config" / "ericgitonga" / "ftp.env"

VALID_PLATES = {
    "drops": "Drops",
    "daguerreotypes": "Daguerreotypes",
    "daubs": "Daubs",
    "dudus": "Dudus",
}


def load_env_file(filepath: pathlib.Path) -> dict[str, str]:
    env = {}
    if not filepath.is_file():
        return env
    try:
        with open(filepath, "r", encoding="utf-8") as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    k, v = line.split("=", 1)
                    v = v.strip()
                    if (v.startswith('"') and v.endswith('"')) or (v.startswith("'") and v.endswith("'")):
                        v = v[1:-1]
                    env[k.strip()] = v
    except Exception:
        pass
    return env


def resolve_credentials() -> dict[str, str]:
    """Resolve FTPS credentials and CRON_SECRET from env or config files."""
    creds = {
        "FTP_HOST": os.environ.get("ANGRYHOSTING_FTP_HOST") or os.environ.get("FTP_SERVER", ""),
        "FTP_USER": os.environ.get("ANGRYHOSTING_FTP_USER") or os.environ.get("FTP_USERNAME", ""),
        "FTP_PASS": os.environ.get("ANGRYHOSTING_FTP_PASSWORD") or os.environ.get("FTP_PASSWORD", ""),
        "FTP_BASE_PATH": os.environ.get("ANGRYHOSTING_FTP_BASE_PATH", ""),
        "CRON_SECRET": os.environ.get("CRON_SECRET", ""),
    }

    # Search known env files
    candidate_paths = [
        CONFIG_FILE,
        REPO_ROOT / ".env.production.local",
        REPO_ROOT / ".env.local",
        pathlib.Path.home() / ".env.local",
        pathlib.Path.home() / ".env",
    ]

    for p in candidate_paths:
        file_env = load_env_file(p)
        if not creds["FTP_HOST"]:
            creds["FTP_HOST"] = file_env.get("ANGRYHOSTING_FTP_HOST") or file_env.get("FTP_SERVER", "")
        if not creds["FTP_USER"]:
            creds["FTP_USER"] = file_env.get("ANGRYHOSTING_FTP_USER") or file_env.get("FTP_USERNAME", "")
        if not creds["FTP_PASS"]:
            creds["FTP_PASS"] = file_env.get("ANGRYHOSTING_FTP_PASSWORD") or file_env.get("FTP_PASSWORD", "")
        if not creds["FTP_BASE_PATH"]:
            creds["FTP_BASE_PATH"] = file_env.get("ANGRYHOSTING_FTP_BASE_PATH", "")
        if not creds["CRON_SECRET"]:
            creds["CRON_SECRET"] = file_env.get("CRON_SECRET", "")

    # Filter out literal sensitive placeholders
    for k in ("FTP_HOST", "FTP_USER", "FTP_PASS"):
        if creds[k] in ("[SENSITIVE]", '"[SENSITIVE]"', ""):
            creds[k] = ""

    return creds


def prompt_and_save_credentials(creds: dict[str, str]) -> dict[str, str]:
    changed = False
    if not creds["FTP_HOST"]:
        val = input("Enter Angry Hosting FTPS Host [default: angryhosting.com]: ").strip()
        creds["FTP_HOST"] = val or "angryhosting.com"
        changed = True
    if not creds["FTP_USER"]:
        creds["FTP_USER"] = input("Enter Angry Hosting FTPS Username: ").strip()
        changed = True
    if not creds["FTP_PASS"]:
        creds["FTP_PASS"] = getpass.getpass("Enter Angry Hosting FTPS Password: ").strip()
        changed = True

    if changed:
        try:
            CONFIG_FILE.parent.mkdir(parents=True, exist_ok=True)
            with open(CONFIG_FILE, "w", encoding="utf-8") as f:
                f.write(f"ANGRYHOSTING_FTP_HOST={creds['FTP_HOST']}\n")
                f.write(f"ANGRYHOSTING_FTP_USER={creds['FTP_USER']}\n")
                f.write(f"ANGRYHOSTING_FTP_PASSWORD={creds['FTP_PASS']}\n")
                if creds.get("FTP_BASE_PATH"):
                    f.write(f"ANGRYHOSTING_FTP_BASE_PATH={creds['FTP_BASE_PATH']}\n")
                if creds.get("CRON_SECRET"):
                    f.write(f"CRON_SECRET={creds['CRON_SECRET']}\n")
            os.chmod(CONFIG_FILE, 0o600)
            print(f"Saved credentials securely to {CONFIG_FILE} (mode 0600)")
        except Exception as e:
            print(f"Warning: Could not save credentials to {CONFIG_FILE}: {e}")

    return creds


def upload_via_ftps(
    local_path: pathlib.Path,
    plate_title: str,
    album_title: str,
    target_filename: str,
    creds: dict[str, str],
) -> str:
    ctx = ssl.create_default_context(ssl.Purpose.SERVER_AUTH)
    if DEFAULT_PEM.is_file():
        ctx.load_verify_locations(cafile=str(DEFAULT_PEM))
    ctx.minimum_version = ssl.TLSVersion.TLSv1_2
    ctx.check_hostname = True

    host = creds["FTP_HOST"]
    user = creds["FTP_USER"]
    passwd = creds["FTP_PASS"]
    base_path = (creds.get("FTP_BASE_PATH") or "").strip("/")

    print(f"\n[1/3] Connecting to {host} via strict TLS 1.2+ FTPS...")
    ftps = FTP_TLS(context=ctx)
    ftps.connect(host, 21, timeout=30)
    ftps.auth()
    ftps.prot_p()
    ftps.login(user, passwd)
    print("  Authenticated successfully.")

    # Navigate or create plate and album directories
    dest_parts = [p for p in [base_path, plate_title, album_title] if p]
    for part in dest_parts:
        try:
            ftps.cwd(part)
        except Exception:
            print(f"  Creating directory: {part}")
            ftps.mkd(part)
            ftps.cwd(part)

    current_dir = ftps.pwd()
    print(f"  Remote target directory: {current_dir}")

    # Upload binary file
    file_size = local_path.stat().st_size
    print(f"[2/3] Uploading {target_filename} ({file_size:,} bytes)...")
    with open(local_path, "rb") as fp:
        ftps.storbinary(f"STOR {target_filename}", fp)

    # Verify upload byte size
    try:
        remote_size = ftps.size(target_filename)
        if remote_size == file_size:
            print(f"  Verification passed: {remote_size:,} bytes verified on remote storage.")
        else:
            print(f"  Warning: Local size ({file_size}) != remote size ({remote_size})")
    except Exception:
        print("  Uploaded file successfully (SIZE command unsupported by server daemon).")

    ftps.quit()
    remote_url = f"https://media.ericgitonga.com/{plate_title}/{album_title}/{target_filename}"
    return remote_url


def trigger_sync(cron_secret: str) -> None:
    if not cron_secret:
        print("\n[3/3] CRON_SECRET not provided; skipping instant manifest refresh.")
        print("  The nightly cron job will automatically index the new photo.")
        return

    print("\n[3/3] Triggering instant media manifest rebuild on Vercel...")
    sync_url = "https://ericgitonga.com/api/sync-media"
    req = urllib.request.Request(
        sync_url,
        data=b"",
        headers={
            "Authorization": f"Bearer {cron_secret}",
            "User-Agent": "DirectPhotoIntake/1.0",
        },
        method="POST",
    )

    try:
        with urllib.request.urlopen(req, timeout=45) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            if data.get("ok"):
                counts = data.get("albumCounts", {})
                print("  Manifest rebuilt and synced to Vercel Blob successfully!")
                print(f"  Live Album Counts: {json.dumps(counts, indent=4)}")
            else:
                print(f"  Sync response: {data}")
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8", errors="replace")
        print(f"  Sync webhook returned HTTP {e.code}: {err_body}")
    except Exception as e:
        print(f"  Sync webhook trigger error: {e}")


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Upload photos directly to ericgitonga.com galleries without Git/PRs."
    )
    parser.add_argument("photo", help="Path to local image file")
    parser.add_argument(
        "--plate",
        default="drops",
        help="Target plate: Drops, Daguerreotypes, Daubs, Dudus (default: Drops)",
    )
    parser.add_argument(
        "--album",
        default=None,
        help="Target album name (defaults to Plate name, e.g. Drops)",
    )
    parser.add_argument(
        "--filename",
        default=None,
        help="Remote filename (defaults to local basename, e.g. d_19.jpg)",
    )
    parser.add_argument(
        "--no-sync",
        action="store_true",
        help="Skip triggering /api/sync-media rebuild",
    )

    args = parser.parse_args()
    photo_path = pathlib.Path(args.photo).expanduser().resolve()
    if not photo_path.is_file():
        print(f"Error: Photo file not found: {photo_path}", file=sys.stderr)
        return 1

    plate_key = args.plate.lower()
    if plate_key not in VALID_PLATES:
        print(f"Error: Invalid plate '{args.plate}'. Must be one of: {', '.join(VALID_PLATES.values())}", file=sys.stderr)
        return 1

    plate_title = VALID_PLATES[plate_key]
    album_title = args.album or plate_title
    target_filename = args.filename or photo_path.name

    creds = resolve_credentials()
    if not creds["FTP_HOST"] or not creds["FTP_USER"] or not creds["FTP_PASS"]:
        creds = prompt_and_save_credentials(creds)

    try:
        remote_url = upload_via_ftps(
            photo_path, plate_title, album_title, target_filename, creds
        )
        print(f"\nPhoto Live At: {remote_url}")

        if not args.no_sync:
            trigger_sync(creds.get("CRON_SECRET", ""))

        print("\nDone! Photo uploaded and indexed without opening a PR or touching Git.")
        return 0
    except Exception as err:
        print(f"\nUpload failed: {err}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
