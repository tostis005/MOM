#!/usr/bin/env python3
"""MOM-specific normalization wrapper around the generic v1->v2 migration.

In addition to migrating legacy v1 JSON, this normalizer repairs Content Contract
v2 taxonomy assignments so every article uses only canonical IDs declared in
content/taxonomies/*.json. Spanish is treated as the canonical taxonomy for a
translation group and then copied to English, without touching editorial fields.
"""
from __future__ import annotations

import argparse
import copy
import json
import re
import subprocess
import sys
import unicodedata
from pathlib import Path


TOP_LEVEL_TOPICS = {
    "sleep",
    "parenting-behavior",
    "child-feeding",
    "potty-hygiene-autonomy",
    "routines-family-life",
    "play-learning-autonomy",
    "childcare-school-social",
    "pregnancy-preparation",
    "postpartum-newborn",
    "breastfeeding-baby-feeding",
    "couple-coparenting",
    "motherhood-identity",
    "family-siblings-boundaries",
    "work-balance-life",
    "travel-outings-celebrations",
}

STAGE_ALIASES = {
    "early-childhood": "toddler",
    "family-life": "parenthood-general",
    "parenting-years": "parenthood-general",
    "motherhood": "parenthood-general",
    "parenthood": "parenthood-general",
    "parents": "parenthood-general",
    "infancy": "baby",
    "infant": "baby",
    "new-parenthood": "newborn",
    "second-child": "second-child-siblings",
    "siblings": "second-child-siblings",
}

AUDIENCE_ALIASES = {
    "parent": "parents",
    "moms": "mothers",
    "mom": "mothers",
    "mothers-and-fathers": "parents",
    "dads": "fathers",
    "dad": "fathers",
    "partners": "couples",
    "caregivers": "family-caregivers",
    "families": "family-caregivers",
}

ARTICLE_TYPE_ALIASES = {
    "parenting-routines": "routine-organization",
    "routines": "routine-organization",
    "routine": "routine-organization",
    "problem-solving": "everyday-problem",
    "comparisons": "decision-comparison",
    "comparison": "decision-comparison",
    "decision-making": "decision-comparison",
    "checklist": "checklist-preparation",
    "preparation": "checklist-preparation",
    "ideas": "ideas-activities",
    "activities": "ideas-activities",
    "play-activities": "ideas-activities",
    "communication": "communication-boundaries",
    "boundaries": "communication-boundaries",
    "transitions": "transition-change",
    "transition": "transition-change",
    "explainer": "understanding",
    "explainers": "understanding",
    "parenting-approaches": "understanding",
    "emotional-experience": "emotions-identity",
    "emotions": "emotions-identity",
    "identity": "emotions-identity",
    "parenting-reflection": "emotions-identity",
    "reflection": "emotions-identity",
    "relationships": "relationships",
    "relationship": "relationships",
    "family-relationships": "relationships",
}

TOPIC_ALIASES = {
    "parenting": "parenting-behavior",
    "respectful-parenting": "parenting-behavior",
    "positive-discipline": "parenting-behavior",
    "parent-child-relationship": "parenting-behavior",
    "child-behavior": "parenting-behavior",
    "emotional-regulation": "parenting-behavior",
    "baby-sleep": "sleep",
    "toddler-sleep": "sleep",
    "child-sleep": "sleep",
    "family-feeding": "child-feeding",
    "feeding": "child-feeding",
    "food": "child-feeding",
    "potty-training": "potty-hygiene-autonomy",
    "potty-hygiene": "potty-hygiene-autonomy",
    "child-autonomy": "potty-hygiene-autonomy",
    "family-routines": "routines-family-life",
    "home-routines": "routines-family-life",
    "family-life": "routines-family-life",
    "home-organization": "routines-family-life",
    "mental-load": "routines-family-life",
    "play-development": "play-learning-autonomy",
    "play-learning": "play-learning-autonomy",
    "play-autonomy": "play-learning-autonomy",
    "montessori-play": "play-learning-autonomy",
    "childcare-school": "childcare-school-social",
    "school-childcare": "childcare-school-social",
    "pregnancy": "pregnancy-preparation",
    "baby-preparation": "pregnancy-preparation",
    "pregnancy-emotions": "pregnancy-preparation",
    "postpartum": "postpartum-newborn",
    "newborn-life": "postpartum-newborn",
    "newborn": "postpartum-newborn",
    "breastfeeding": "breastfeeding-baby-feeding",
    "baby-feeding": "breastfeeding-baby-feeding",
    "couple": "couple-coparenting",
    "coparenting": "couple-coparenting",
    "couple-relationship": "couple-coparenting",
    "partner-relationship": "couple-coparenting",
    "fatherhood": "couple-coparenting",
    "active-fatherhood": "couple-coparenting",
    "maternal-identity": "motherhood-identity",
    "motherhood": "motherhood-identity",
    "motherhood-identity": "motherhood-identity",
    "maternal-wellbeing": "motherhood-identity",
    "maternal-pressure": "motherhood-identity",
    "mom-guilt": "motherhood-identity",
    "maternal-guilt": "motherhood-identity",
    "matrescence": "motherhood-identity",
    "identity": "motherhood-identity",
    "identity-transition": "motherhood-identity",
    "family-siblings": "family-siblings-boundaries",
    "siblings-family": "family-siblings-boundaries",
    "siblings": "family-siblings-boundaries",
    "grandparents": "family-siblings-boundaries",
    "extended-family": "family-siblings-boundaries",
    "family-boundaries": "family-siblings-boundaries",
    "work-balance": "work-balance-life",
    "work-parenthood": "work-balance-life",
    "career-parenthood": "work-balance-life",
    "travel-family": "travel-outings-celebrations",
    "family-travel": "travel-outings-celebrations",
    "outings": "travel-outings-celebrations",
    "celebrations": "travel-outings-celebrations",
}


def load(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def save(path: Path, data: dict) -> None:
    path.write_text(
        json.dumps(data, ensure_ascii=False, separators=(",", ":")) + "\n",
        encoding="utf-8",
    )


def fold(text: str) -> str:
    text = unicodedata.normalize("NFKD", text or "")
    text = "".join(ch for ch in text if not unicodedata.combining(ch))
    return text.lower()


def article_text(data: dict) -> str:
    seo = data.get("seo") if isinstance(data.get("seo"), dict) else {}
    bits = [
        str(data.get("title", "")),
        str(data.get("slug", "")),
        str(data.get("excerpt", "")),
        str(seo.get("search_intent", "")),
    ]
    return fold(" ".join(bits))


def taxonomy_definitions(root: Path) -> dict[str, set[str]]:
    site = load(root / "content/site.json")
    defs: dict[str, set[str]] = {}
    for rel in site.get("taxonomies", []):
        definition = load(root / rel)
        dimension = str(definition.get("dimension", ""))
        defs[dimension] = {
            str(term["id"])
            for term in definition.get("terms", [])
            if isinstance(term, dict) and term.get("id")
        }
    return defs


def infer_topic(data: dict) -> str:
    text = article_text(data)
    checks = [
        ("sleep", ("sueno", "sleep", "nap", "siesta", "bedtime", "night waking", "despertar nocturno")),
        ("child-feeding", ("comida", "feeding", "eating", "picky", "alimento", "snack", "meal", "food")),
        ("potty-hygiene-autonomy", ("potty", "panal", "toilet", "orinal", "bath", "bano", "vestir", "dressing")),
        ("play-learning-autonomy", ("juego", "play", "montessori", "toy", "juguete", "actividad", "activity")),
        ("childcare-school-social", ("daycare", "guarderia", "preschool", "school", "colegio", "escuela", "educador")),
        ("pregnancy-preparation", ("embarazo", "pregnan", "hospital bag", "bolsa del hospital", "baby registry", "registry")),
        ("postpartum-newborn", ("postpartum", "posparto", "newborn", "recien nacido", "primeras semanas")),
        ("breastfeeding-baby-feeding", ("lactancia", "breastfeed", "weaning", "destete")),
        ("work-balance-life", ("vuelta al trabajo", "return to work", "career", "carrera", "working mom", "stay-at-home")),
        ("travel-outings-celebrations", ("viaj", "travel", "flight", "vuelo", "road trip", "restaurant", "birthday", "cumple", "vacation", "vacaciones")),
        ("family-siblings-boundaries", ("herman", "sibling", "grandparent", "abuelo", "familiares", "extended family")),
        ("couple-coparenting", ("pareja", "partner", "coparent", "co-parent", "father", "padre", "dad", "paternidad")),
        ("motherhood-identity", ("madre", "maternidad", "motherhood", "mom ", "mom-", "maternal", "matresc", "culpa", "guilt")),
        ("routines-family-life", ("rutina", "routine", "carga mental", "mental load", "organiza", "household", "chores")),
        ("parenting-behavior", ("crianza", "parenting", "rabieta", "tantrum", "limite", "boundar", "gritar", "yell", "pega", "hitting", "muerde", "biting", "frustr", "cooper", "disciplina", "discipline", "perdon", "apolog")),
    ]
    for topic, needles in checks:
        if any(n in text for n in needles):
            return topic
    number = int(data.get("article_number", 0) or 0)
    if 1001 <= number <= 1100:
        return "motherhood-identity"
    return "parenting-behavior"


def infer_stage(data: dict) -> str:
    text = article_text(data)
    if any(n in text for n in ("embarazo", "pregnan")):
        return "pregnancy"
    if any(n in text for n in ("hospital bag", "bolsa del hospital", "baby registry", "registry", "preparar la casa", "prepare the home")):
        return "preparing-for-baby"
    if any(n in text for n in ("postpartum", "posparto")):
        return "postpartum"
    if any(n in text for n in ("newborn", "recien nacido")):
        return "newborn"
    if any(n in text for n in ("baby", "bebe")):
        return "baby"
    if "toddler" in text:
        return "toddler"
    if any(n in text for n in ("preschool", "infantil")):
        return "preschool"
    if any(n in text for n in ("school-age", "school age", "edad escolar", "colegio", "school")):
        return "school-age"
    if any(n in text for n in ("sibling", "herman", "second child", "segundo hijo")):
        return "second-child-siblings"
    return "parenthood-general"


def infer_audience(data: dict) -> str:
    text = article_text(data)
    if any(n in text for n in ("madre", "maternidad", "motherhood", "mom ", "maternal", "matresc")):
        return "mothers"
    if any(n in text for n in ("padre", "paternidad", "father", "dad ")):
        return "fathers"
    if any(n in text for n in ("pareja", "partner", "couple", "coparent", "co-parent")):
        return "couples"
    return "parents"


def infer_article_type(data: dict) -> str:
    text = article_text(data)
    title = fold(str(data.get("title", "")))
    if title.startswith("como ") or title.startswith("how to "):
        return "how-to"
    if any(n in text for n in ("checklist", "lista practica", "what to pack", "que llevar")):
        return "checklist-preparation"
    if any(n in text for n in ("vs ", "versus", "diferencias", "differences", "compar")):
        return "decision-comparison"
    if any(n in text for n in ("rutina", "routine", "organiza", "mental load", "carga mental")):
        return "routine-organization"
    if any(n in text for n in ("actividad", "activities", "ideas", "juego", "play")):
        return "ideas-activities"
    if any(n in text for n in ("limite", "boundar", "hablar", "talk", "communication")):
        return "communication-boundaries"
    if any(n in text for n in ("transicion", "transition", "llegada", "arrival", "new baby", "nuevo bebe")):
        return "transition-change"
    if any(n in text for n in ("culpa", "guilt", "identidad", "identity", "maternidad", "motherhood", "presion", "pressure", "sensitive", "sensible", "vulnerab")):
        return "emotions-identity"
    if any(n in text for n in ("pareja", "partner", "relationship", "relacion", "herman", "sibling", "grandparent", "abuelo")):
        return "relationships"
    return "understanding"


def normalize_assignment(dimension: str, assignment: object, valid: set[str], inferred: str) -> dict:
    if not isinstance(assignment, dict):
        assignment = {}
    raw_terms = assignment.get("terms")
    if not isinstance(raw_terms, list):
        raw_terms = []
    raw_primary = assignment.get("primary")
    aliases = {}
    if dimension == "topic":
        aliases = TOPIC_ALIASES
    elif dimension == "stage":
        aliases = STAGE_ALIASES
    elif dimension == "audience":
        aliases = AUDIENCE_ALIASES
    elif dimension == "article_type":
        aliases = ARTICLE_TYPE_ALIASES

    def canonical(value: object) -> str | None:
        if value is None:
            return None
        value = str(value)
        if value in valid:
            return value
        mapped = aliases.get(value)
        if mapped in valid:
            return mapped
        return None

    terms: list[str] = []
    for value in raw_terms:
        mapped = canonical(value)
        if mapped and mapped not in terms:
            terms.append(mapped)
    primary = canonical(raw_primary)
    if primary and primary not in terms:
        terms.insert(0, primary)
    if not terms:
        fallback = inferred if inferred in valid else sorted(valid)[0]
        terms = [fallback]
        primary = fallback
    elif not primary:
        primary = inferred if inferred in terms else terms[0]
    if dimension == "topic":
        terms = [t for t in terms if t not in TOP_LEVEL_TOPICS or t == inferred]
        if inferred in valid and inferred not in terms:
            terms.insert(0, inferred)
        primary = inferred if inferred in valid else terms[0]
    return {"primary": primary, "terms": terms}


def normalize_v2_taxonomies(root: Path) -> int:
    valid_by_dimension = taxonomy_definitions(root)
    changed = 0
    for path in sorted((root / "content/articles").glob("*/*.json")):
        data = load(path)
        if int(data.get("schema_version", 1) or 1) < 2:
            continue
        before = copy.deepcopy(data)
        current = data.get("taxonomy")
        if not isinstance(current, dict):
            current = {}
        inferred = {
            "topic": infer_topic(data),
            "stage": infer_stage(data),
            "audience": infer_audience(data),
            "article_type": infer_article_type(data),
        }
        normalized = {}
        for dimension in ("topic", "stage", "audience", "article_type"):
            valid = valid_by_dimension.get(dimension, set())
            if not valid:
                raise RuntimeError(f"No taxonomy terms declared for {dimension}")
            normalized[dimension] = normalize_assignment(dimension, current.get(dimension), valid, inferred[dimension])
        data["taxonomy"] = normalized
        if data != before:
            save(path, data)
            changed += 1
    return changed


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
        _, es_data = by_lang["es"]
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
        if "es" in by_lang and "en" in by_lang and by_lang["es"][1].get("taxonomy") != by_lang["en"][1].get("taxonomy"):
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
    proc = subprocess.run([sys.executable, str(root / "platform/migrate-v1-to-v2.py"), str(root)], text=True, capture_output=True)
    if proc.stdout:
        print(proc.stdout, end="")
    if proc.returncode and "Taxonomy mismatch between ES/EN" not in (proc.stderr or ""):
        if proc.stderr:
            print(proc.stderr, file=sys.stderr, end="")
        return proc.returncode
    if proc.stderr and proc.returncode:
        print("Core migration produced locale differences; aligning by translation_group.")
    normalized = normalize_v2_taxonomies(root)
    synced = sync_translation_taxonomy(root)
    legacy_disabled = disable_legacy(root)
    check_contract(root)
    subprocess.run([sys.executable, str(root / "platform/validate-content.py"), str(root)], check=True)
    print(f"Normalized {normalized} v2 article(s); aligned {synced} bilingual taxonomy pair(s); legacy_disabled={legacy_disabled}.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
