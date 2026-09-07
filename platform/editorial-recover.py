#!/usr/bin/env python3
from __future__ import annotations

import html
import json
import re
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ARTICLES = ROOT / "content" / "articles"

BLOCK_RE = re.compile(r"<(h2|p)>(.*?)</\1>", re.I | re.S)
TAG_RE = re.compile(r"<[^>]+>")
WORD_RE = re.compile(r"[A-Za-zÀ-ÿ0-9']+")


def plain(s: str) -> str:
    return re.sub(r"\s+", " ", html.unescape(TAG_RE.sub(" ", s or ""))).strip()


def wc(s: str) -> int:
    return len(WORD_RE.findall(plain(s)))


def merge_paragraphs(paragraphs: list[str]) -> list[str]:
    if not paragraphs:
        return []
    out: list[str] = []
    i = 0
    while i < len(paragraphs):
        cur = paragraphs[i]
        cur_w = wc(cur)
        # Keep thesis/emphasis paragraphs as intentional beats.
        if "<strong>" in cur.lower():
            out.append(cur)
            i += 1
            continue
        # Merge short rhetorical fragments with the next compatible paragraph.
        if cur_w <= 14 and i + 1 < len(paragraphs):
            nxt = paragraphs[i + 1]
            if "<strong>" not in nxt.lower() and wc(nxt) <= 42 and cur_w + wc(nxt) <= 58:
                out.append(cur.rstrip() + " " + nxt.lstrip())
                i += 2
                continue
        # Merge two short explanatory paragraphs where the result remains readable.
        if cur_w <= 22 and i + 1 < len(paragraphs):
            nxt = paragraphs[i + 1]
            if "<strong>" not in nxt.lower() and wc(nxt) <= 26 and cur_w + wc(nxt) <= 52:
                out.append(cur.rstrip() + " " + nxt.lstrip())
                i += 2
                continue
        out.append(cur)
        i += 1
    return out


def parse_sections(body: str):
    blocks = [(m.group(1).lower(), m.group(2)) for m in BLOCK_RE.finditer(body)]
    intro: list[str] = []
    sections: list[dict] = []
    current = None
    for kind, inner in blocks:
        if kind == "h2":
            current = {"heading": inner, "paras": []}
            sections.append(current)
        elif current is None:
            intro.append(inner)
        else:
            current["paras"].append(inner)
    return intro, sections


def desired_h2_count(total_words: int, current: int) -> int:
    if current <= 6:
        return current
    if total_words < 700:
        return min(current, 5)
    if total_words < 1000:
        return min(current, 6)
    if total_words < 1400:
        return min(current, 7)
    return min(current, 8)


def consolidate_sections(sections: list[dict], total_words: int) -> list[dict]:
    if len(sections) <= 1:
        return sections
    target = desired_h2_count(total_words, len(sections))
    keep = {0, len(sections) - 1}

    # Prefer headings that carry the most developed argument, rather than preserving
    # every micro-section. Questions get a small bonus because they often represent
    # a genuine reader turn in the article.
    scored = []
    for idx, sec in enumerate(sections[1:-1], 1):
        words = sum(wc(p) for p in sec["paras"])
        bonus = 20 if "?" in plain(sec["heading"]) or "¿" in plain(sec["heading"]) else 0
        scored.append((words + bonus, idx))
    for _, idx in sorted(scored, reverse=True)[: max(0, target - len(keep))]:
        keep.add(idx)

    result: list[dict] = []
    for idx, sec in enumerate(sections):
        paras = merge_paragraphs(sec["paras"])
        if idx in keep or not result:
            result.append({"heading": sec["heading"], "paras": paras})
        else:
            # Removing a weak heading preserves all its prose while letting the
            # previous idea develop for longer, which restores narrative continuity.
            result[-1]["paras"].extend(paras)
            result[-1]["paras"] = merge_paragraphs(result[-1]["paras"])
    return result


def rebuild(body: str) -> str:
    intro, sections = parse_sections(body)
    if not sections:
        return body
    total_words = wc(body)
    intro = merge_paragraphs(intro)
    sections = consolidate_sections(sections, total_words)
    chunks = [f"<p>{p}</p>" for p in intro]
    for sec in sections:
        chunks.append(f"<h2>{sec['heading']}</h2>")
        chunks.extend(f"<p>{p}</p>" for p in sec["paras"])
    return "".join(chunks)


def align_translation_groups(records: list[tuple[Path, dict]]) -> None:
    by_number: dict[int, list[tuple[Path, dict]]] = defaultdict(list)
    for path, data in records:
        num = data.get("article_number")
        if isinstance(num, int):
            by_number[num].append((path, data))
    for num, items in by_number.items():
        if len(items) < 2:
            continue
        es = next((d for p, d in items if d.get("language") == "es"), None)
        canonical = (es or items[0][1]).get("translation_group")
        if not canonical:
            continue
        for _, data in items:
            data["translation_group"] = canonical


def main() -> int:
    records: list[tuple[Path, dict]] = []
    for path in sorted(ARTICLES.glob("*/*.json")):
        data = json.loads(path.read_text(encoding="utf-8"))
        records.append((path, data))

    align_translation_groups(records)
    changed = 0
    for path, data in records:
        before = json.dumps(data, ensure_ascii=False, separators=(",", ":"))
        data["content_html"] = rebuild(data.get("content_html", ""))
        data["status"] = "publish"
        after = json.dumps(data, ensure_ascii=False, separators=(",", ":"))
        if after != before:
            path.write_text(after + "\n", encoding="utf-8")
            changed += 1
    print(f"Editorial recovery changed {changed} article JSON files")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
