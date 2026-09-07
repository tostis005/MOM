#!/usr/bin/env python3
"""MOM-specific normalization wrapper around the generic v1->v2 migration.

The core migrator classifies each locale independently, then this wrapper makes
`translation_group` the canonical unit: when ES and EN both exist, the Spanish
classification is copied to the English twin. This keeps navigation identical
across locales without touching any editorial field.
"""
from __future__ import annotations

import argparse
import copy
import json
import subprocess
import sys
from pathlib import Path


def load(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def save(path: Path, data: dict) -> None:
    path.write_text(json.dumps(data, ensure_ascii=False, separators=(",", ":")) + "\n", encoding="utf-8")


def collect(root: Path):
    groups = {}
    legacy = []
    for path in sorted((root / "content/articles").glob("*/*.json")):
        data = load(path)
        if int(data.get("schema_version", 1) or 1) < 2:
            legacy.append(path)
        group = str(data.get("translation_group", ""))
        lang = str(data.get("language", ""))
        if group and lang:
            groups.setdefault(group, {})[lang] = (path, data)
    return groups, legacy


def sync_translation_taxonomy(root: Path) -> int:
    groups, legacy = collect(root)
    if legacy:
        raise RuntimeError("Legacy JSON remains after core migration: " + ", ".join(str(p) for p in legacy))
    changed = 0
    for group, by_lang in groups.items():
        if "es" not in by_lang or "en" not in by_lang:
            continue
        es_path, es_data = by_lang["es"]
        en_path, en_data = by_lang["en"]
        canonical = copy.deepcopy(es_data.get("taxonomy", {}))
        if en_data.get("taxonomy") == canonical:
            continue
        before = copy.deepcopy(en_data)
        en_data["taxonomy"] = canonical
        for key in set(before) | set(en_data):
            if key == "taxonomy":
                continue
            if before.get(key) != en_data.get(key):
                raise AssertionError(f"{group}: parity sync changed protected field {key!r}")
        save(en_path, en_data)
        changed += 1
    return changed


def disable_legacy(root: Path) -> bool:
    path = root / "content/site.json"
    site = load(path)
    legacy = site.setdefault("legacy_v1", {})
    if legacy.get("enabled") is False:
        return False
    legacy["enabled"] = False
    path.write_text(json.dumps(site, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    return True


def check_contract(root: Path) -> None:
    groups, legacy = collect(root)
    if legacy:
        raise RuntimeError("Legacy v1 JSON found: " + ", ".join(str(p) for p in legacy))
    mismatches = []
    for group, by_lang in groups.items():
        if "es" in by_lang and "en" in by_lang:
            if by_lang["es"][1].get("taxonomy") != by_lang["en"][1].get("taxonomy"):
                mismatches.append(group)
    if mismatches:
        raise RuntimeError("ES/EN taxonomy mismatch: " + ", ".join(mismatches))
    site = load(root / "content/site.json")
    if site.get("legacy_v1", {}).get("enabled") is not False:
        raise RuntimeError("content/site.json must have legacy_v1.enabled=false")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("root", nargs="?", default=".")
    parser.add_argument("--check", action="store_true")
    args = parser.parse_args()
    root = Path(args.root).resolve()

    if args.check:
        check_contract(root)
        subprocess.run([sys.executable, str(root / "platform/validate-content.py"), str(root)], check=True)
        print("MOM Content Contract v2 is clean and translation taxonomy is aligned.")
        return 0

    proc = subprocess.run(
        [sys.executable, str(root / "platform/migrate-v1-to-v2.py"), str(root)],
        text=True,
        capture_output=True,
    )
    if proc.stdout:
        print(proc.stdout, end="")
    if proc.returncode and "Taxonomy mismatch between ES/EN" not in (proc.stderr or ""):
        if proc.stderr:
            print(proc.stderr, file=sys.stderr, end="")
        return proc.returncode
    if proc.stderr and proc.returncode:
        print("Core migration produced locale differences; aligning by translation_group.")

    synced = sync_translation_taxonomy(root)
    legacy_disabled = disable_legacy(root)
    check_contract(root)
    subprocess.run([sys.executable, str(root / "platform/validate-content.py"), str(root)], check=True)
    print(f"Aligned {synced} bilingual taxonomy pair(s); legacy_disabled={legacy_disabled}.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
