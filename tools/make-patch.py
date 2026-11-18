#!/usr/bin/env python3
"""
ابزار رسمی ساخت پچ افزایشی (Delta Patch Builder) قالب وردپرس/ووکامرس رنگ صادقی
تولید بسته .zip و .json مجهز به checksum از نوع SHA-256 (بدون امضای دیجیتال) برای نصب از پنل تنظیمات قالب.

مثال استفاده:
  1) ساخت پچ از تغییرات بین دو کامیت یا تگ گیت:
     python3 tools/make-patch.py --from HEAD~1 --to HEAD --version 2.1.1 --notes "بهبود سرعت و رفع باگ"

  2) ساخت پچ از فایل‌های مشخص:
     python3 tools/make-patch.py --version 2.1.1 --files styles.css resources/js/theme.js
"""

from __future__ import annotations

import argparse
import base64
import hashlib
import json
import re
import subprocess
import sys
import zipfile
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
EXCLUDED_PREFIXES = (
    ".git/",
    "tools/",
    "tests/",
    ".github/",
    "screenshots/",
    "uploads/",
    "node_modules/",
)
ALLOWED_EXTS = {
    ".php", ".css", ".js", ".json", ".webp", ".png", ".jpg", ".jpeg",
    ".svg", ".woff2", ".woff", ".md", ".txt", ".pot", ".po", ".mo",
}


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def is_patchable(rel_path: str) -> bool:
    rel_path = rel_path.replace("\\", "/").lstrip("/")
    if not rel_path or ".." in rel_path:
        return False
    if any(rel_path.startswith(p) for p in EXCLUDED_PREFIXES):
        return False
    return Path(rel_path).suffix.lower() in ALLOWED_EXTS


def git_diff_files(from_ref: str, to_ref: str) -> tuple[list[str], list[str]]:
    cmd = ["git", "diff", "--no-renames", "--name-status", from_ref, to_ref]
    out = subprocess.check_output(cmd, cwd=ROOT, text=True).strip()
    upserts: list[str] = []
    deletes: list[str] = []
    for line in out.splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        status = parts[0][0]
        path = parts[-1]
        if not is_patchable(path):
            continue
        if status == "D":
            deletes.append(path)
        else:
            upserts.append(path)
    return upserts, deletes


def build_patch(
    target_version: str,
    upserts: list[str],
    deletes: list[str],
    min_version: str,
    title: str,
    notes: str,
    out_dir: Path,
    git_sources: dict[str, str] | None = None,
) -> tuple[Path, Path]:
    out_dir.mkdir(parents=True, exist_ok=True)
    if not re.fullmatch(r"[0-9A-Za-z][0-9A-Za-z._+-]*", target_version) or ".." in target_version:
        raise ValueError("Invalid version")
    slug_ver = target_version
    zip_path = out_dir / f"rangesadeghi-patch-{slug_ver}.zip"
    json_path = out_dir / f"rangesadeghi-patch-{slug_ver}.json"

    files_meta_zip: dict[str, dict[str, str]] = {}
    files_meta_json: dict[str, dict[str, str]] = {}
    payloads: dict[str, bytes] = {}

    for rel in sorted(set(upserts)):
        if not is_patchable(rel) or Path(rel).is_absolute():
            raise ValueError(f"Invalid path: {rel}")
        if git_sources and rel in git_sources:
            raw = subprocess.check_output(["git", "show", f"{git_sources[rel]}:{rel}"], cwd=ROOT)
        else:
            abs_file = ROOT / rel
            if not abs_file.is_file() or not abs_file.resolve().is_relative_to(ROOT.resolve()):
                raise FileNotFoundError(f"File not found or outside theme: {rel}")
            raw = abs_file.read_bytes()
        payloads[rel] = raw
        digest = sha256_bytes(raw)
        files_meta_zip[rel] = {"action": "upsert", "sha256": digest}
        files_meta_json[rel] = {
            "action": "upsert",
            "sha256": digest,
            "content_base64": base64.b64encode(raw).decode("ascii"),
        }

    for rel in sorted(set(deletes)):
        if not is_patchable(rel) or Path(rel).is_absolute() or rel in payloads:
            raise ValueError(f"Invalid or conflicting deletion: {rel}")
        files_meta_zip[rel] = {"action": "delete"}
        files_meta_json[rel] = {"action": "delete"}

    common_manifest = {
        "theme": "rangesadeghi",
        "target_version": target_version,
        "min_version": min_version,
        "title": title or f"بروزرسانی قالب رنگ صادقی به نسخه {target_version}",
        "notes": notes,
        "created_at": datetime.now(timezone.utc).isoformat(),
    }

    zip_manifest = {**common_manifest, "files": files_meta_zip}
    json_bundle = {**common_manifest, "files": files_meta_json}

    with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        zf.writestr("patch.json", json.dumps(zip_manifest, ensure_ascii=False, indent=2))
        for rel in sorted(set(upserts)):
            zf.writestr(f"files/{rel}", payloads[rel])

    json_path.write_text(json.dumps(json_bundle, ensure_ascii=False, indent=2), encoding="utf-8")
    return zip_path, json_path


def main() -> int:
    parser = argparse.ArgumentParser(description="Build a verified delta patch for Rangesadeghi WP theme.")
    parser.add_argument("--version", required=True, help="Target theme version (e.g. 2.1.1)")
    parser.add_argument("--min-version", default="2.1.0", help="Minimum required installed version")
    parser.add_argument("--from", dest="from_ref", help="Git start ref (e.g. v2.1.0 or HEAD~1)")
    parser.add_argument("--to", dest="to_ref", default="HEAD", help="Git end ref (default: HEAD)")
    parser.add_argument("--files", nargs="*", default=[], help="Explicit relative file paths to include")
    parser.add_argument("--delete", nargs="*", default=[], help="Explicit relative file paths to delete")
    parser.add_argument("--title", default="", help="Patch title")
    parser.add_argument("--notes", default="", help="Changelog notes")
    parser.add_argument("--out-dir", default="dist-patches", help="Output directory")
    args = parser.parse_args()

    for f in args.files + args.delete:
        if not is_patchable(f) or Path(f).is_absolute():
            parser.error(f"Invalid or excluded path: {f}")
    upserts = list(args.files)
    deletes = list(args.delete)
    git_sources = {}

    if args.from_ref:
        g_up, g_del = git_diff_files(args.from_ref, args.to_ref)
        git_sources = {path: args.to_ref for path in g_up if path not in args.files}
        upserts.extend(g_up)
        deletes.extend(g_del)

    if not upserts and not deletes:
        print("هیچ فایل تغییرداده‌شده‌ای برای ساخت پچ مشخص نشد.", file=sys.stderr)
        return 1

    out_dir = Path(args.out_dir)
    if not out_dir.is_absolute():
        out_dir = ROOT / out_dir

    zip_file, json_file = build_patch(
        target_version=args.version,
        upserts=upserts,
        deletes=deletes,
        min_version=args.min_version,
        title=args.title,
        notes=args.notes,
        out_dir=out_dir,
        git_sources=git_sources,
    )
    zip_hash = sha256_bytes(zip_file.read_bytes())
    print(f"ZIP Patch : {zip_file} (SHA256: {zip_hash})")
    print(f"JSON Patch: {json_file}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
