#!/usr/bin/env python3
from __future__ import annotations

import collections
import html
import json
import re
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ARTICLES = ROOT / "content" / "articles"
OUT = ROOT / "editorial-audit-report.json"

TAG_RE = re.compile(r"<[^>]+>")
P_RE = re.compile(r"<p>(.*?)</p>", re.I | re.S)
H2_RE = re.compile(r"<h2>(.*?)</h2>", re.I | re.S)
LI_RE = re.compile(r"<li>(.*?)</li>", re.I | re.S)
STRONG_RE = re.compile(r"<strong>(.*?)</strong>", re.I | re.S)
SENT_SPLIT_RE = re.compile(r"(?<=[.!?])\s+")
WORD_RE = re.compile(r"[A-Za-zÀ-ÿ0-9']+")


def fold(s: str) -> str:
    s = unicodedata.normalize("NFKD", s or "")
    s = "".join(c for c in s if not unicodedata.combining(c))
    s = html.unescape(TAG_RE.sub(" ", s)).lower()
    return re.sub(r"\s+", " ", s).strip()


def plain(s: str) -> str:
    s = html.unescape(TAG_RE.sub(" ", s or ""))
    return re.sub(r"\s+", " ", s).strip()


def words(s: str) -> list[str]:
    return WORD_RE.findall(plain(s))


def norm_para(s: str) -> str:
    return fold(s)


def ngrams(tokens: list[str], n: int = 6):
    for i in range(len(tokens) - n + 1):
        yield " ".join(tokens[i:i+n])


def article_record(path: Path) -> dict:
    d = json.loads(path.read_text(encoding="utf-8"))
    body = d.get("content_html", "")
    paras = [plain(x) for x in P_RE.findall(body)]
    h2s = [plain(x) for x in H2_RE.findall(body)]
    lis = [plain(x) for x in LI_RE.findall(body)]
    bolds = [plain(x) for x in STRONG_RE.findall(body)]
    body_plain = plain(body)
    toks = words(body_plain)
    para_wc = [len(words(p)) for p in paras]
    sentences = [s.strip() for s in SENT_SPLIT_RE.split(body_plain) if s.strip()]
    question_paras = sum(1 for p in paras if "?" in p or "¿" in p)
    short_paras = sum(1 for n in para_wc if 0 < n <= 12)
    very_short_paras = sum(1 for n in para_wc if 0 < n <= 6)
    long_paras = sum(1 for n in para_wc if n >= 70)
    consecutive_short_max = 0
    run = 0
    for n in para_wc:
        if 0 < n <= 12:
            run += 1
            consecutive_short_max = max(consecutive_short_max, run)
        else:
            run = 0
    title = d.get("title", "")
    intro = " ".join(paras[:3])
    ending = " ".join(paras[-3:])
    first_words = " ".join(words(paras[0])[:14]) if paras else ""
    last_h2 = h2s[-1] if h2s else ""
    low = fold(body_plain)
    phrase_counts = {
        "no_necesitas": len(re.findall(r"\bno necesitas\b|\byou do not need\b|\byou don't need\b", low)),
        "no_significa": len(re.findall(r"\bno significa\b|\bdoes not mean\b|\bdoesn't mean\b", low)),
        "a_veces": len(re.findall(r"\ba veces\b|\bsometimes\b", low)),
        "puede": len(re.findall(r"\bpuede\b|\bpuedes\b|\bcan\b", low)),
        "importante": len(re.findall(r"\bimportante\b|\bimportant\b", low)),
        "clave": len(re.findall(r"\bla clave\b|\bthe key\b", low)),
    }
    wc = len(toks)
    h2_density = (len(h2s) * 1000 / wc) if wc else 0
    short_ratio = (short_paras / len(paras)) if paras else 0
    question_ratio = (question_paras / len(paras)) if paras else 0

    reasons = []
    score = 0
    if wc < 800:
        score += 3; reasons.append("body_under_800_words")
    elif wc < 1050:
        score += 1; reasons.append("body_under_1050_words")
    if h2_density > 7.0:
        score += 2; reasons.append("high_h2_density")
    elif h2_density > 5.8:
        score += 1; reasons.append("elevated_h2_density")
    if short_ratio > 0.38:
        score += 2; reasons.append("many_short_paragraphs")
    elif short_ratio > 0.28:
        score += 1; reasons.append("elevated_short_paragraphs")
    if consecutive_short_max >= 5:
        score += 2; reasons.append("long_run_of_short_paragraphs")
    elif consecutive_short_max >= 3:
        score += 1; reasons.append("run_of_short_paragraphs")
    if question_ratio > 0.22:
        score += 1; reasons.append("many_question_paragraphs")
    if phrase_counts["no_significa"] >= 5:
        score += 1; reasons.append("repeated_no_significa_pattern")
    if phrase_counts["no_necesitas"] >= 5:
        score += 1; reasons.append("repeated_no_necesitas_pattern")
    if phrase_counts["a_veces"] >= 8:
        score += 1; reasons.append("repeated_a_veces_pattern")
    if len(h2s) >= 10:
        score += 1; reasons.append("many_sections")
    if long_paras >= 4:
        score += 1; reasons.append("many_long_paragraphs")

    return {
        "path": str(path.relative_to(ROOT)),
        "article_number": d.get("article_number"),
        "translation_group": d.get("translation_group"),
        "language": d.get("language"),
        "title": title,
        "words": wc,
        "paragraphs": len(paras),
        "h2_count": len(h2s),
        "h2_density_per_1000_words": round(h2_density, 2),
        "h2s": h2s,
        "list_items": len(lis),
        "bold_spans": len(bolds),
        "avg_paragraph_words": round(sum(para_wc) / len(para_wc), 1) if para_wc else 0,
        "short_paragraph_ratio": round(short_ratio, 3),
        "very_short_paragraphs": very_short_paras,
        "consecutive_short_max": consecutive_short_max,
        "question_paragraph_ratio": round(question_ratio, 3),
        "long_paragraphs": long_paras,
        "phrase_counts": phrase_counts,
        "opening": paras[0] if paras else "",
        "opening_14_words": first_words,
        "intro_3_paragraphs": intro,
        "last_h2": last_h2,
        "ending_3_paragraphs": ending,
        "risk_score": score,
        "risk_reasons": reasons,
        "paragraphs_normalized": [norm_para(p) for p in paras if len(words(p)) >= 8],
        "ngrams6": list(set(ngrams([w.lower() for w in toks], 6))),
    }


def main() -> int:
    records = [article_record(p) for p in sorted(ARTICLES.glob("*/*.json"))]

    para_docs: dict[str, set[str]] = collections.defaultdict(set)
    ngram_docs: dict[str, set[str]] = collections.defaultdict(set)
    h2_docs: dict[str, set[str]] = collections.defaultdict(set)
    opening_docs: dict[str, set[str]] = collections.defaultdict(set)
    for r in records:
        key = r["path"]
        for p in set(r.pop("paragraphs_normalized")):
            para_docs[p].add(key)
        for ng in r.pop("ngrams6"):
            ngram_docs[ng].add(key)
        for h in set(fold(x) for x in r["h2s"]):
            if h:
                h2_docs[h].add(key)
        opening_docs[fold(r["opening_14_words"])].add(key)

    repeated_paras = [
        {"paragraph": p, "count": len(files), "files": sorted(files)}
        for p, files in para_docs.items() if len(files) >= 2
    ]
    repeated_paras.sort(key=lambda x: (-x["count"], x["paragraph"]))

    common_ngrams = [
        {"ngram": ng, "count": len(files), "files": sorted(files)}
        for ng, files in ngram_docs.items() if len(files) >= 6
    ]
    common_ngrams.sort(key=lambda x: (-x["count"], x["ngram"]))

    repeated_h2s = [
        {"h2": h, "count": len(files), "files": sorted(files)}
        for h, files in h2_docs.items() if len(files) >= 3
    ]
    repeated_h2s.sort(key=lambda x: (-x["count"], x["h2"]))

    repeated_openings = [
        {"opening_14_words": op, "count": len(files), "files": sorted(files)}
        for op, files in opening_docs.items() if op and len(files) >= 2
    ]
    repeated_openings.sort(key=lambda x: (-x["count"], x["opening_14_words"]))

    by_lang = {}
    for lang in ("es", "en"):
        subset = [r for r in records if r["language"] == lang]
        by_lang[lang] = {
            "count": len(subset),
            "avg_words": round(sum(r["words"] for r in subset) / len(subset), 1) if subset else 0,
            "median_words": sorted(r["words"] for r in subset)[len(subset)//2] if subset else 0,
            "avg_h2": round(sum(r["h2_count"] for r in subset) / len(subset), 2) if subset else 0,
            "avg_short_para_ratio": round(sum(r["short_paragraph_ratio"] for r in subset) / len(subset), 3) if subset else 0,
            "risk_7_plus": sum(1 for r in subset if r["risk_score"] >= 7),
            "risk_5_6": sum(1 for r in subset if 5 <= r["risk_score"] <= 6),
            "risk_3_4": sum(1 for r in subset if 3 <= r["risk_score"] <= 4),
            "risk_0_2": sum(1 for r in subset if r["risk_score"] <= 2),
        }

    ranked = sorted(records, key=lambda r: (-r["risk_score"], r["language"], r["article_number"]))
    report = {
        "summary": {
            "total": len(records),
            "by_language": by_lang,
            "exact_repeated_paragraphs": len(repeated_paras),
            "repeated_h2_patterns": len(repeated_h2s),
            "repeated_opening_patterns": len(repeated_openings),
            "common_6grams_6plus_articles": len(common_ngrams),
        },
        "highest_risk": ranked[:60],
        "all_articles": sorted(records, key=lambda r: (r["language"], r["article_number"])),
        "repeated_paragraphs": repeated_paras[:100],
        "repeated_h2s": repeated_h2s[:100],
        "repeated_openings": repeated_openings[:100],
        "common_ngrams": common_ngrams[:100],
    }
    OUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote {OUT} for {len(records)} articles")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
