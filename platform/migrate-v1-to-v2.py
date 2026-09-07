#!/usr/bin/env python3
"""
Migrate MOM article JSON from the inherited FOOD-shaped schema v1 to Content Contract v2.

The migration is intentionally narrow:
- preserves every top-level field and value except `schema_version` and `taxonomy`;
- replaces legacy taxonomy names with the generic dimensions used by MOM;
- uses the same deterministic classification for ES/EN versions, primarily via
  `translation_group`, legacy taxonomy hints and article title/search intent;
- is idempotent.

It can safely be run across the full repository whenever a legacy v1 article is
added by an older content-generation process.
"""
from __future__ import annotations

import argparse
import copy
import json
import re
import sys
from pathlib import Path

TOPIC_FAMILY_MAP = {
    "parenting": "parenting-behavior",
    "respectful-parenting": "parenting-behavior",
    "parenting-behavior": "parenting-behavior",
    "baby-sleep": "sleep",
    "toddler-sleep": "sleep",
    "child-sleep": "sleep",
    "sleep": "sleep",
    "family-feeding": "child-feeding",
    "child-feeding": "child-feeding",
    "feeding": "child-feeding",
    "potty-training": "potty-hygiene-autonomy",
    "potty-hygiene": "potty-hygiene-autonomy",
    "child-autonomy": "potty-hygiene-autonomy",
    "family-routines": "routines-family-life",
    "home-routines": "routines-family-life",
    "family-life": "routines-family-life",
    "play-learning": "play-learning-autonomy",
    "play-autonomy": "play-learning-autonomy",
    "montessori-play": "play-learning-autonomy",
    "childcare-school": "childcare-school-social",
    "school-childcare": "childcare-school-social",
    "pregnancy-preparation": "pregnancy-preparation",
    "baby-preparation": "pregnancy-preparation",
    "pregnancy": "pregnancy-preparation",
    "postpartum-newborn": "postpartum-newborn",
    "newborn-life": "postpartum-newborn",
    "postpartum": "postpartum-newborn",
    "breastfeeding": "breastfeeding-baby-feeding",
    "baby-feeding": "breastfeeding-baby-feeding",
    "couple-coparenting": "couple-coparenting",
    "coparenting": "couple-coparenting",
    "maternal-identity": "motherhood-identity",
    "motherhood-identity": "motherhood-identity",
    "maternal-wellbeing": "motherhood-identity",
    "family-siblings": "family-siblings-boundaries",
    "siblings-family": "family-siblings-boundaries",
    "work-balance": "work-balance-life",
    "work-parenthood": "work-balance-life",
    "travel-family": "travel-outings-celebrations",
    "family-travel": "travel-outings-celebrations",
}

SUBTOPIC_MAP = {
    "bedtime-routines": "bedtime-routines", "night-wakings": "night-wakings", "naps": "naps",
    "nap-routines": "naps", "sleep-regressions": "sleep-transitions", "sleep-transitions": "sleep-transitions",
    "wake-windows": "sleep-transitions", "sleep-environment": "sleep-environment", "travel-sleep": "sleep-travel",
    "respectful-parenting": "respectful-parenting", "positive-discipline": "respectful-parenting",
    "boundaries": "boundaries", "tantrums": "tantrums", "cooperation": "cooperation",
    "frustration": "frustration-emotions", "emotions": "frustration-emotions", "repair": "repair",
    "picky-eating": "picky-eating", "food-refusal": "picky-eating", "baby-led-feeding": "baby-led-feeding",
    "table-autonomy": "baby-led-feeding", "family-meals": "mealtime-routines", "meal-routines": "mealtime-routines",
    "new-foods": "new-foods", "snacks": "snacks-sweets", "sweets": "snacks-sweets", "eating-out": "eating-out",
    "potty-training": "potty-training", "toilet-training": "potty-training", "bath": "bath-hygiene",
    "hygiene": "bath-hygiene", "dressing": "dressing", "responsibilities": "responsibilities",
    "morning-routines": "morning-routines", "evening-routines": "evening-routines",
    "home-organization": "home-organization", "family-routines": "family-routines", "mental-load": "mental-load",
    "independent-play": "independent-play", "activities": "activities", "toddler-activities": "activities",
    "montessori": "montessori", "toys": "toys-books", "books": "toys-books", "daycare": "daycare",
    "preschool": "preschool-school", "school": "preschool-school", "school-transition": "school-transitions",
    "daycare-transition": "school-transitions", "extracurriculars": "extracurriculars", "playdates": "playdates",
    "baby-registry": "baby-registry", "registry": "baby-registry", "hospital-bag": "hospital-bag",
    "home-preparation": "home-preparation", "prepare-home": "home-preparation", "pregnancy-emotions": "pregnancy-emotions",
    "pregnancy-boundaries": "pregnancy-boundaries", "first-weeks": "first-weeks", "newborn-routines": "newborn-routines",
    "postpartum-home": "postpartum-home", "visits": "visits-help", "help": "visits-help",
    "breastfeeding": "breastfeeding", "weaning": "weaning", "feeding-logistics": "feeding-logistics",
    "coparenting": "coparenting", "relationship": "relationship-after-kids", "division-of-labor": "division-of-labor",
    "couple-conflict": "conflict-repair", "mom-guilt": "mom-guilt", "maternal-guilt": "mom-guilt",
    "matrescence": "matrescence", "identity-transition": "identity", "identity": "identity",
    "perfectionism": "perfectionism", "comparison": "comparison", "alone-time": "alone-time",
    "grandparents": "grandparents-family", "family-boundaries": "family-boundaries", "new-sibling": "new-sibling",
    "sibling-rivalry": "sibling-rivalry", "family-traditions": "family-traditions", "return-to-work": "return-to-work",
    "childcare-work": "childcare-work", "career-identity": "career-identity", "personal-time": "personal-time",
    "flying": "flying", "air-travel": "flying", "road-trips": "road-trips", "packing": "packing",
    "restaurants": "restaurants-outings", "outings": "restaurants-outings", "vacations": "vacations",
    "celebrations": "celebrations",
}

ARTICLE_TYPE_MAP = {
    "how-to": "how-to", "parenting-routines": "routine-organization", "routines": "routine-organization",
    "routine": "routine-organization", "problem-solving": "everyday-problem", "everyday-problem": "everyday-problem",
    "comparisons": "decision-comparison", "comparison": "decision-comparison", "decision-making": "decision-comparison",
    "checklist": "checklist-preparation", "preparation": "checklist-preparation", "ideas": "ideas-activities",
    "activities": "ideas-activities", "communication": "communication-boundaries", "boundaries": "communication-boundaries",
    "transitions": "transition-change", "transition": "transition-change", "explainer": "understanding",
    "explainers": "understanding", "parenting-approaches": "understanding", "emotional-experience": "emotions-identity",
    "emotions": "emotions-identity", "identity": "emotions-identity", "relationships": "relationships",
    "relationship": "relationships",
}

TOP_LEVEL_TOPICS = {
    "sleep", "parenting-behavior", "child-feeding", "potty-hygiene-autonomy", "routines-family-life",
    "play-learning-autonomy", "childcare-school-social", "pregnancy-preparation", "postpartum-newborn",
    "breastfeeding-baby-feeding", "couple-coparenting", "motherhood-identity", "family-siblings-boundaries",
    "work-balance-life", "travel-outings-celebrations",
}
STAGES = {"pregnancy", "preparing-for-baby", "postpartum", "newborn", "baby", "toddler", "preschool", "school-age", "second-child-siblings", "parenthood-general"}
AUDIENCES = {"parents", "mothers", "fathers", "couples", "family-caregivers"}
ARTICLE_TYPES = {"how-to", "routine-organization", "everyday-problem", "decision-comparison", "checklist-preparation", "ideas-activities", "communication-boundaries", "transition-change", "understanding", "emotions-identity", "relationships"}

SUBTOPIC_DEFS = {
    "bedtime-routines": ("sleep", "Rutinas de sueño", "Bedtime routines", "rutinas-sueno", "bedtime-routines"),
    "night-wakings": ("sleep", "Despertares nocturnos", "Night wakings", "despertares-nocturnos", "night-wakings"),
    "naps": ("sleep", "Siestas", "Naps", "siestas", "naps"),
    "sleep-transitions": ("sleep", "Transiciones de sueño", "Sleep transitions", "transiciones-sueno", "sleep-transitions"),
    "sleep-environment": ("sleep", "Entorno de sueño", "Sleep environment", "entorno-sueno", "sleep-environment"),
    "sleep-travel": ("sleep", "Sueño en viajes", "Sleep while traveling", "sueno-viajes", "sleep-travel"),
    "respectful-parenting": ("parenting-behavior", "Crianza respetuosa", "Respectful parenting", "crianza-respetuosa", "respectful-parenting"),
    "boundaries": ("parenting-behavior", "Límites", "Boundaries", "limites", "boundaries"),
    "tantrums": ("parenting-behavior", "Rabietas", "Tantrums", "rabietas", "tantrums"),
    "cooperation": ("parenting-behavior", "Cooperación", "Cooperation", "cooperacion", "cooperation"),
    "frustration-emotions": ("parenting-behavior", "Frustración y emociones", "Frustration & emotions", "frustracion-emociones", "frustration-emotions"),
    "repair": ("parenting-behavior", "Reparación", "Repair", "reparacion", "repair"),
    "picky-eating": ("child-feeding", "Picky eating y rechazo", "Picky eating & refusal", "picky-eating-rechazo", "picky-eating"),
    "baby-led-feeding": ("child-feeding", "Baby-led feeding", "Baby-led feeding", "baby-led-feeding", "baby-led-feeding"),
    "mealtime-routines": ("child-feeding", "Rutinas de comida", "Mealtime routines", "rutinas-comida", "mealtime-routines"),
    "new-foods": ("child-feeding", "Alimentos nuevos", "New foods", "alimentos-nuevos", "new-foods"),
    "snacks-sweets": ("child-feeding", "Snacks y dulces", "Snacks & sweets", "snacks-dulces", "snacks-sweets"),
    "eating-out": ("child-feeding", "Comer fuera", "Eating out", "comer-fuera", "eating-out"),
    "potty-training": ("potty-hygiene-autonomy", "Dejar el pañal", "Potty training", "dejar-panal", "potty-training"),
    "bath-hygiene": ("potty-hygiene-autonomy", "Baño e higiene", "Bath & hygiene", "bano-higiene", "bath-hygiene"),
    "dressing": ("potty-hygiene-autonomy", "Vestirse", "Getting dressed", "vestirse", "dressing"),
    "responsibilities": ("potty-hygiene-autonomy", "Responsabilidades", "Responsibilities", "responsabilidades", "responsibilities"),
    "morning-routines": ("routines-family-life", "Rutinas de mañana", "Morning routines", "rutinas-manana", "morning-routines"),
    "evening-routines": ("routines-family-life", "Rutinas de tarde", "Evening routines", "rutinas-tarde", "evening-routines"),
    "home-organization": ("routines-family-life", "Organización del hogar", "Home organization", "organizacion-hogar", "home-organization"),
    "family-routines": ("routines-family-life", "Rutinas familiares", "Family routines", "rutinas-familiares", "family-routines"),
    "mental-load": ("routines-family-life", "Carga mental", "Mental load", "carga-mental", "mental-load"),
    "independent-play": ("play-learning-autonomy", "Juego independiente", "Independent play", "juego-independiente", "independent-play"),
    "activities": ("play-learning-autonomy", "Actividades", "Activities", "actividades", "activities"),
    "montessori": ("play-learning-autonomy", "Montessori", "Montessori", "montessori", "montessori"),
    "toys-books": ("play-learning-autonomy", "Juguetes y libros", "Toys & books", "juguetes-libros", "toys-books"),
    "daycare": ("childcare-school-social", "Daycare y guardería", "Daycare", "daycare-guarderia", "daycare"),
    "preschool-school": ("childcare-school-social", "Preschool y colegio", "Preschool & school", "preschool-colegio", "preschool-school"),
    "school-transitions": ("childcare-school-social", "Adaptación y transiciones", "School transitions", "adaptacion-transiciones", "school-transitions"),
    "extracurriculars": ("childcare-school-social", "Extraescolares", "Extracurriculars", "extraescolares", "extracurriculars"),
    "playdates": ("childcare-school-social", "Playdates y vida social", "Playdates & social life", "playdates-vida-social", "playdates"),
    "baby-registry": ("pregnancy-preparation", "Baby registry y compras", "Baby registry & shopping", "baby-registry-compras", "baby-registry"),
    "hospital-bag": ("pregnancy-preparation", "Bolsa del hospital", "Hospital bag", "bolsa-hospital", "hospital-bag"),
    "home-preparation": ("pregnancy-preparation", "Preparar la casa", "Preparing the home", "preparar-casa", "home-preparation"),
    "pregnancy-emotions": ("pregnancy-preparation", "Emociones del embarazo", "Pregnancy emotions", "emociones-embarazo", "pregnancy-emotions"),
    "pregnancy-boundaries": ("pregnancy-preparation", "Límites durante el embarazo", "Pregnancy boundaries", "limites-embarazo", "pregnancy-boundaries"),
    "first-weeks": ("postpartum-newborn", "Primeras semanas", "First weeks", "primeras-semanas", "first-weeks"),
    "newborn-routines": ("postpartum-newborn", "Rutinas con recién nacido", "Newborn routines", "rutinas-recien-nacido", "newborn-routines"),
    "postpartum-home": ("postpartum-newborn", "Casa y posparto", "Home & postpartum", "casa-posparto", "postpartum-home"),
    "visits-help": ("postpartum-newborn", "Visitas y ayuda", "Visits & help", "visitas-ayuda", "visits-help"),
    "breastfeeding": ("breastfeeding-baby-feeding", "Lactancia", "Breastfeeding", "lactancia", "breastfeeding"),
    "weaning": ("breastfeeding-baby-feeding", "Destete", "Weaning", "destete", "weaning"),
    "feeding-logistics": ("breastfeeding-baby-feeding", "Logística de alimentación", "Feeding logistics", "logistica-alimentacion", "feeding-logistics"),
    "coparenting": ("couple-coparenting", "Coparentalidad", "Coparenting", "coparentalidad", "coparenting"),
    "relationship-after-kids": ("couple-coparenting", "Pareja después de los hijos", "Relationship after kids", "pareja-despues-hijos", "relationship-after-kids"),
    "division-of-labor": ("couple-coparenting", "Reparto de tareas", "Division of labor", "reparto-tareas", "division-of-labor"),
    "conflict-repair": ("couple-coparenting", "Conflicto y reparación", "Conflict & repair", "conflicto-reparacion", "conflict-repair"),
    "mom-guilt": ("motherhood-identity", "Culpa materna", "Mom guilt", "culpa-materna", "mom-guilt"),
    "matrescence": ("motherhood-identity", "Matrescencia", "Matrescence", "matrescencia", "matrescence"),
    "identity": ("motherhood-identity", "Identidad", "Identity", "identidad", "identity"),
    "perfectionism": ("motherhood-identity", "Perfeccionismo", "Perfectionism", "perfeccionismo", "perfectionism"),
    "comparison": ("motherhood-identity", "Comparación", "Comparison", "comparacion", "comparison"),
    "alone-time": ("motherhood-identity", "Tiempo propio", "Time for yourself", "tiempo-propio", "alone-time"),
    "grandparents-family": ("family-siblings-boundaries", "Abuelos y familia", "Grandparents & family", "abuelos-familia", "grandparents-family"),
    "new-sibling": ("family-siblings-boundaries", "Nuevo hermano", "New sibling", "nuevo-hermano", "new-sibling"),
    "sibling-rivalry": ("family-siblings-boundaries", "Rivalidad entre hermanos", "Sibling rivalry", "rivalidad-hermanos", "sibling-rivalry"),
    "family-boundaries": ("family-siblings-boundaries", "Límites familiares", "Family boundaries", "limites-familiares", "family-boundaries"),
    "family-traditions": ("family-siblings-boundaries", "Tradiciones familiares", "Family traditions", "tradiciones-familiares", "family-traditions"),
    "return-to-work": ("work-balance-life", "Vuelta al trabajo", "Return to work", "vuelta-trabajo", "return-to-work"),
    "childcare-work": ("work-balance-life", "Cuidado y trabajo", "Childcare & work", "cuidado-trabajo", "childcare-work"),
    "career-identity": ("work-balance-life", "Carrera e identidad", "Career & identity", "carrera-identidad", "career-identity"),
    "personal-time": ("work-balance-life", "Tiempo personal", "Personal time", "tiempo-personal", "personal-time"),
    "flying": ("travel-outings-celebrations", "Viajar en avión", "Flying", "viajar-avion", "flying"),
    "road-trips": ("travel-outings-celebrations", "Road trips", "Road trips", "road-trips", "road-trips"),
    "packing": ("travel-outings-celebrations", "Equipaje", "Packing", "equipaje", "packing"),
    "restaurants-outings": ("travel-outings-celebrations", "Restaurantes y salidas", "Restaurants & outings", "restaurantes-salidas", "restaurants-outings"),
    "vacations": ("travel-outings-celebrations", "Vacaciones", "Vacations", "vacaciones", "vacations"),
    "celebrations": ("travel-outings-celebrations", "Celebraciones", "Celebrations", "celebraciones", "celebrations"),
}

SPANISH_MARKERS = {"bebé": "baby", "bebe": "baby", "recién nacido": "newborn", "recien nacido": "newborn", "toddler": "toddler", "niño pequeño": "toddler", "guardería": "preschool", "guarderia": "preschool", "daycare": "preschool", "preschool": "preschool", "colegio": "school-age", "escuela": "school-age", "embarazo": "pregnancy", "embarazada": "pregnancy", "posparto": "postpartum", "postparto": "postpartum"}
ENGLISH_MARKERS = {"newborn": "newborn", "baby": "baby", "toddler": "toddler", "daycare": "preschool", "preschool": "preschool", "school": "school-age", "pregnancy": "pregnancy", "pregnant": "pregnancy", "postpartum": "postpartum"}

def norm(value: object) -> str:
    return str(value or "").strip().lower()

def text_blob(data: dict) -> str:
    pieces = [data.get("title", ""), data.get("slug", ""), data.get("translation_group", ""), data.get("seo", {}).get("search_intent", "") if isinstance(data.get("seo"), dict) else ""]
    return " ".join(norm(x) for x in pieces)

def legacy_values(taxonomy: dict, field: str) -> list[str]:
    value = taxonomy.get(field, [])
    if isinstance(value, list): return [norm(v) for v in value if norm(v)]
    return [norm(value)] if norm(value) else []

def choose_topic(data: dict, legacy: dict) -> tuple[str, list[str]]:
    blob = text_blob(data)
    family = norm(legacy.get("food_family"))
    primary = TOPIC_FAMILY_MAP.get(family, "")
    rules = [
        ("motherhood-identity", ("mom guilt", "culpa materna", "matresc", "identity", "identidad", "sentirte tú", "sentirte tu", "feel like yourself", "motherhood")),
        ("sleep", ("sleep", "sueño", "sueno", "siesta", "nap", "bedtime", "despertar", "wake window")),
        ("child-feeding", ("picky eating", "alimentación", "alimentacion", "comida", "food refusal", "baby-led feeding", "snack")),
        ("potty-hygiene-autonomy", ("potty", "pañal", "panal", "orinal", "toilet training", "baño", "bano")),
        ("childcare-school-social", ("daycare", "guardería", "guarderia", "preschool", "school", "colegio")),
        ("pregnancy-preparation", ("baby registry", "hospital bag", "bolsa del hospital", "preparar la casa", "prepare home", "before baby", "antes del bebé", "antes del bebe")),
        ("couple-coparenting", ("coparent", "pareja", "partner", "relationship after", "repartir la carga", "division of labor")),
        ("family-siblings-boundaries", ("hermano", "sibling", "abuelos", "grandparent", "family boundary", "límites a familiares", "limites a familiares")),
        ("play-learning-autonomy", ("montessori", "juego", "play", "activity", "actividad", "toy", "juguete")),
        ("travel-outings-celebrations", ("viaj", "travel", "flight", "vuelo", "road trip", "vacacion", "restaurant", "birthday", "cumpleaños", "cumpleanos")),
        ("work-balance-life", ("return to work", "vuelta al trabajo", "career", "carrera", "conciliación", "conciliacion")),
        ("postpartum-newborn", ("posparto", "postpartum", "newborn", "recién nacido", "recien nacido", "primeras semanas")),
        ("breastfeeding-baby-feeding", ("lactancia", "breastfeeding", "weaning", "destete")),
    ]
    if not primary:
        for candidate, needles in rules:
            if any(n in blob for n in needles): primary = candidate; break
    if not primary: primary = "parenting-behavior"
    subtopics = []
    for raw in legacy_values(legacy, "food_subcategories"):
        mapped = SUBTOPIC_MAP.get(raw)
        if mapped and mapped not in subtopics: subtopics.append(mapped)
    keyword_subtopics = [
        ("bedtime-routines", ("bedtime", "hora de dormir", "rutina de sueño", "rutina de sueno")), ("night-wakings", ("night waking", "despertar nocturno")),
        ("naps", ("nap", "siesta")), ("sleep-transitions", ("regression", "regresión", "regresion", "wake window", "ventana de sueño", "ventana de sueno")),
        ("respectful-parenting", ("respectful parenting", "crianza respetuosa", "positive discipline", "disciplina positiva")), ("boundaries", ("boundar", "límite", "limite")),
        ("tantrums", ("tantrum", "rabieta")), ("cooperation", ("cooperat", "cooperación", "cooperacion", "repetir")),
        ("picky-eating", ("picky eating", "comen poco", "rechazan alimentos", "food refusal")), ("baby-led-feeding", ("baby-led", "table autonomy", "autonomía en la mesa", "autonomia en la mesa")),
        ("potty-training", ("potty", "pañal", "panal", "orinal", "toilet")), ("activities", ("activit", "actividad")), ("daycare", ("daycare", "guardería", "guarderia")),
        ("baby-registry", ("baby registry",)), ("hospital-bag", ("hospital bag", "bolsa del hospital")), ("home-preparation", ("prepare home", "preparar la casa")),
        ("mom-guilt", ("mom guilt", "culpa")), ("matrescence", ("matresc",)), ("identity", ("identity", "identidad", "recognize yourself", "reconocerte", "sentirte tú", "sentirte tu")),
        ("alone-time", ("time alone", "tiempo sola", "descanso", "break from your kids", "break from kids")),
    ]
    for child, needles in keyword_subtopics:
        if any(n in blob for n in needles) and child not in subtopics: subtopics.append(child)
    parent_by_child = {term_id: spec[0] for term_id, spec in SUBTOPIC_DEFS.items()}
    subtopics = [s for s in subtopics if parent_by_child.get(s) == primary]
    return primary, subtopics[:3]

def choose_stages(data: dict, topic: str) -> list[str]:
    blob = text_blob(data); stages = []
    for marker, stage in {**SPANISH_MARKERS, **ENGLISH_MARKERS}.items():
        if marker in blob and stage not in stages: stages.append(stage)
    if topic == "pregnancy-preparation":
        if any(x in blob for x in ("registry", "hospital bag", "bolsa del hospital", "prepare home", "preparar la casa", "before baby", "antes del bebé", "antes del bebe")): stages = ["preparing-for-baby"]
        elif "pregnan" in blob or "embaraz" in blob: stages = ["pregnancy"]
    elif topic == "postpartum-newborn":
        stages = []
        if "postpartum" in blob or "posparto" in blob or "postparto" in blob: stages.append("postpartum")
        if "newborn" in blob or "recién nacido" in blob or "recien nacido" in blob: stages.append("newborn")
    elif topic == "motherhood-identity": stages = ["parenthood-general"]
    elif topic in {"couple-coparenting", "work-balance-life", "family-siblings-boundaries", "routines-family-life"} and not stages: stages = ["parenthood-general"]
    if not stages: stages = ["parenthood-general"]
    return [s for s in stages if s in STAGES][:3]

def choose_audience(data: dict, topic: str) -> list[str]:
    blob = text_blob(data)
    if topic == "motherhood-identity" or any(x in blob for x in ("mom guilt", "madre", "motherhood", "maternidad", "matresc")): return ["mothers"]
    if any(x in blob for x in ("dad guilt", "fatherhood", "paternidad", "como padre", "as a dad", "as a father")): return ["fathers"]
    if topic == "couple-coparenting" or any(x in blob for x in ("pareja", "partner", "coparent")): return ["couples"]
    if any(x in blob for x in ("abuelos", "grandparent", "caregiver", "cuidadores")): return ["family-caregivers"]
    return ["parents"]

def choose_article_types(data: dict, legacy: dict) -> tuple[str, list[str]]:
    blob = text_blob(data); mapped = []
    primary = ARTICLE_TYPE_MAP.get(norm(legacy.get("primary_article_type")), "")
    for raw in legacy_values(legacy, "article_types"):
        target = ARTICLE_TYPE_MAP.get(raw)
        if target and target not in mapped: mapped.append(target)
    rules = [("checklist-preparation", ("checklist", "lista", "qué llevar", "que llevar", "registry", "qué comprar", "que comprar")), ("decision-comparison", (" vs ", "versus", "compar", "elegir", "choose daycare", "cómo elegir", "como elegir")), ("ideas-activities", ("ideas", "actividades", "activities")), ("emotions-identity", ("culpa", "guilt", "identity", "identidad", "matresc", "sentir", "feeling")), ("communication-boundaries", ("límite", "limite", "boundar", "qué decir", "que decir", "how to talk")), ("transition-change", ("transition", "transición", "transicion", "cambio", "change")), ("routine-organization", ("rutina", "routine", "organizar", "organization")), ("understanding", ("qué significa", "que significa", "what does", "why ", "por qué", "por que")), ("how-to", ("cómo ", "como ", "how to "))]
    for target, needles in rules:
        if any(n in blob for n in needles) and target not in mapped: mapped.append(target)
    if not primary: primary = mapped[0] if mapped else "understanding"
    if primary not in mapped: mapped.insert(0, primary)
    mapped = [x for x in mapped if x in ARTICLE_TYPES]
    if not mapped: mapped = ["understanding"]; primary = "understanding"
    return primary, mapped[:3]

def migrate_article(data: dict) -> tuple[dict, bool]:
    version = int(data.get("schema_version", 1) or 1); taxonomy = data.get("taxonomy", {})
    if version >= 2: return data, False
    if not isinstance(taxonomy, dict): raise ValueError("legacy taxonomy must be an object")
    before = copy.deepcopy(data)
    topic, children = choose_topic(data, taxonomy); stages = choose_stages(data, topic); audiences = choose_audience(data, topic); primary_type, types = choose_article_types(data, taxonomy)
    data["schema_version"] = 2
    data["taxonomy"] = {"topic": {"primary": topic, "terms": [topic] + [x for x in children if x != topic]}, "stage": {"primary": stages[0], "terms": stages}, "audience": {"primary": audiences[0], "terms": audiences}, "article_type": {"primary": primary_type, "terms": types}}
    for key in set(before) | set(data):
        if key in {"schema_version", "taxonomy"}: continue
        if before.get(key) != data.get(key): raise AssertionError(f"migration changed protected field {key!r}")
    return data, True

def ensure_topic_registry(root: Path) -> bool:
    path = root / "content/taxonomies/topic.json"; definition = json.loads(path.read_text(encoding="utf-8")); terms = definition.setdefault("terms", [])
    known = {str(t.get("id")) for t in terms if isinstance(t, dict) and t.get("id")}; changed = False
    for term_id, (parent, es, en, slug_es, slug_en) in SUBTOPIC_DEFS.items():
        if term_id in known: continue
        terms.append({"id": term_id, "parent": parent, "home_visible": False, "label": {"es": es, "en": en}, "slug": {"es": slug_es, "en": slug_en}}); changed = True
    if changed: path.write_text(json.dumps(definition, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    return changed

def disable_legacy_v1(root: Path) -> bool:
    path = root / "content/site.json"; site = json.loads(path.read_text(encoding="utf-8")); legacy = site.setdefault("legacy_v1", {})
    if legacy.get("enabled") is False: return False
    legacy["enabled"] = False; path.write_text(json.dumps(site, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"); return True

def main() -> int:
    parser = argparse.ArgumentParser(); parser.add_argument("root", nargs="?", default="."); parser.add_argument("--check", action="store_true", help="fail if a v1 file still exists; do not rewrite"); args = parser.parse_args(); root = Path(args.root)
    registry_changed = ensure_topic_registry(root) if not args.check else False
    files = sorted((root / "content/articles").glob("*/*.json"))
    if not files: print("No article JSON files found", file=sys.stderr); return 1
    changed = 0; legacy_left = []; groups = {}
    for path in files:
        data = json.loads(path.read_text(encoding="utf-8"))
        if int(data.get("schema_version", 1) or 1) < 2:
            if args.check: legacy_left.append(str(path)); continue
            data, did_change = migrate_article(data)
            if did_change: path.write_text(json.dumps(data, ensure_ascii=False, separators=(",", ":")) + "\n", encoding="utf-8"); changed += 1
        group = str(data.get("translation_group", "")); lang = str(data.get("language", ""))
        if group and lang: groups.setdefault(group, {})[lang] = data
    if legacy_left: print("Legacy v1 article JSON remains:\n" + "\n".join(legacy_left), file=sys.stderr); return 1
    parity_errors = []
    for group, by_lang in groups.items():
        if "es" in by_lang and "en" in by_lang and by_lang["es"].get("taxonomy") != by_lang["en"].get("taxonomy"): parity_errors.append(group)
    if parity_errors: print("Taxonomy mismatch between ES/EN for translation groups: " + ", ".join(parity_errors), file=sys.stderr); return 1
    site_changed = disable_legacy_v1(root) if not args.check else False
    print(f"Migrated {changed} legacy article JSON files to schema v2; checked {len(files)} total; topic_registry_changed={registry_changed}; legacy_disabled={site_changed}.")
    return 0

if __name__ == "__main__": raise SystemExit(main())
