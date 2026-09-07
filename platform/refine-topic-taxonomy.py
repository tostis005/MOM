#!/usr/bin/env python3
"""Refine MOM's broad topic taxonomy without touching editorial content.

The generic normalizer guarantees valid canonical taxonomy IDs. This pass makes
sure each article has exactly one broad navigation topic, inferred from the
approved inventory intent rather than incidental audience/stage words. ES is
canonical for each translation group and the same topic assignment is copied to
EN.
"""
from __future__ import annotations

import argparse
import copy
import json
import unicodedata
from pathlib import Path

TOP_LEVEL = {
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


def load(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def save(path: Path, data: dict) -> None:
    path.write_text(json.dumps(data, ensure_ascii=False, separators=(",", ":")) + "\n", encoding="utf-8")


def fold(value: str) -> str:
    value = unicodedata.normalize("NFKD", value or "")
    return "".join(ch for ch in value if not unicodedata.combining(ch)).lower()


def text(data: dict) -> str:
    seo = data.get("seo") if isinstance(data.get("seo"), dict) else {}
    return fold(" ".join([
        str(data.get("title", "")),
        str(data.get("slug", "")),
        str(data.get("excerpt", "")),
        str(seo.get("search_intent", "")),
    ]))


def infer(data: dict) -> str:
    number = int(data.get("article_number", 0) or 0)
    t = text(data)

    # Canonical second-batch cluster 1001-1100: guilt, matrescence, identity,
    # nostalgia, sensitivity, pressure and maternal self-trust.
    if 1001 <= number <= 1100:
        return "motherhood-identity"

    checks = [
        ("sleep", ("sueno", "sleep", "nap", "siesta", "bedtime", "night waking", "despertar nocturno")),
        ("child-feeding", ("comida", "feeding", "eating", "picky", "alimento", "snack", "meal", "food")),
        ("potty-hygiene-autonomy", ("potty", "panal", "toilet", "orinal", "bath", "bano", "vestir", "dressing")),
        ("play-learning-autonomy", ("juego", "play", "montessori", "toy", "juguete", "actividad", "activity")),
        ("childcare-school-social", ("daycare", "guarderia", "preschool", "colegio", "escuela infantil", "school transition")),
        ("pregnancy-preparation", ("embarazo", "pregnan", "hospital bag", "bolsa del hospital", "baby registry", "registry")),
        ("postpartum-newborn", ("postpartum", "posparto", "newborn", "recien nacido", "primeras semanas")),
        ("breastfeeding-baby-feeding", ("lactancia", "breastfeed", "weaning", "destete")),
        ("work-balance-life", ("vuelta al trabajo", "return to work", "career", "carrera profesional", "working mom", "stay-at-home")),
        ("travel-outings-celebrations", ("viaj", "travel", "flight", "vuelo", "road trip", "restaurant", "birthday", "cumple", "vacation", "vacaciones")),
        ("family-siblings-boundaries", ("herman", "sibling", "grandparent", "abuelo", "familiares", "extended family")),
        ("couple-coparenting", ("pareja", "partner relationship", "couple", "coparent", "co-parent", "paternidad", "fatherhood", "involved dad")),
        ("routines-family-life", ("carga mental", "mental load", "rutina familiar", "family routine", "organizacion familiar", "household")),
        ("parenting-behavior", ("crianza", "parenting", "rabieta", "tantrum", "limite", "boundar", "gritar", "yell", "pega", "hitting", "muerde", "biting", "frustr", "cooper", "disciplina", "discipline", "perdon", "apolog", "consecuencia")),
        ("motherhood-identity", ("maternidad", "motherhood", "maternal", "matresc", "mom guilt", "culpa materna")),
    ]
    for topic, needles in checks:
        if any(n in t for n in needles):
            return topic
    return "parenting-behavior"


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("root", nargs="?", default=".")
    args = parser.parse_args()
    root = Path(args.root).resolve()

    groups: dict[str, dict[str, tuple[Path, dict]]] = {}
    for path in sorted((root / "content/articles").glob("*/*.json")):
        data = load(path)
        group = str(data.get("translation_group", ""))
        lang = str(data.get("language", ""))
        if group and lang:
            groups.setdefault(group, {})[lang] = (path, data)

    changed = 0
    for group, by_lang in groups.items():
        source_lang = "es" if "es" in by_lang else next(iter(by_lang))
        _, source = by_lang[source_lang]
        broad = infer(source)

        source_topic = source.get("taxonomy", {}).get("topic", {})
        source_terms = source_topic.get("terms", []) if isinstance(source_topic, dict) else []
        subterms = [str(v) for v in source_terms if str(v) not in TOP_LEVEL]
        canonical_terms = [broad] + [v for v in subterms if v != broad]
        canonical = {"primary": broad, "terms": canonical_terms}

        for lang, (path, data) in by_lang.items():
            before = copy.deepcopy(data)
            taxonomy = data.setdefault("taxonomy", {})
            taxonomy["topic"] = copy.deepcopy(canonical)
            if data != before:
                save(path, data)
                changed += 1

    print(f"Refined broad topic taxonomy for {changed} article JSON file(s).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
