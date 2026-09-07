#!/usr/bin/env python3
from __future__ import annotations

import html
import json
import re
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ARTICLES = ROOT / "content" / "articles"

H2_RE = re.compile(r"<h2>(.*?)</h2>", re.I | re.S)
P_TAG_RE = re.compile(r"<p>(.*?)</p>", re.I | re.S)
TAG_RE = re.compile(r"<[^>]+>")
WORD_RE = re.compile(r"[A-Za-zÀ-ÿ0-9']+")


def plain(s: str) -> str:
    return re.sub(r"\s+", " ", html.unescape(TAG_RE.sub(" ", s or ""))).strip()


def wc(s: str) -> int:
    return len(WORD_RE.findall(plain(s)))


def can_merge(a: str, b: str) -> bool:
    if "<strong>" in a.lower() or "<strong>" in b.lower():
        return False
    aw, bw = wc(a), wc(b)
    if aw <= 14 and bw <= 42 and aw + bw <= 58:
        return True
    if aw <= 22 and bw <= 26 and aw + bw <= 52:
        return True
    return False


def smooth_segment(segment: str) -> str:
    """Merge only adjacent <p> blocks; preserve every non-paragraph HTML byte."""
    pieces = re.split(r"(<p>.*?</p>)", segment, flags=re.I | re.S)
    out: list[str] = []
    i = 0
    while i < len(pieces):
        piece = pieces[i]
        if not re.fullmatch(r"<p>.*?</p>", piece or "", flags=re.I | re.S):
            out.append(piece)
            i += 1
            continue

        inner = P_TAG_RE.fullmatch(piece).group(1)
        j = i + 1
        between = ""
        if j < len(pieces) and not re.fullmatch(r"<p>.*?</p>", pieces[j] or "", flags=re.I | re.S):
            between = pieces[j]
            j += 1
        if (
            j < len(pieces)
            and not between.strip()
            and re.fullmatch(r"<p>.*?</p>", pieces[j] or "", flags=re.I | re.S)
        ):
            next_inner = P_TAG_RE.fullmatch(pieces[j]).group(1)
            if can_merge(inner, next_inner):
                out.append(f"<p>{inner.rstrip()} {next_inner.lstrip()}</p>")
                i = j + 1
                continue

        out.append(piece)
        if between:
            out.append(between)
            i = j
        else:
            i += 1
    merged = "".join(out)
    # A second pass catches short paragraphs created after one merge, but still only
    # touches adjacent paragraph tags and leaves lists/tables/H3/other HTML intact.
    if merged != segment:
        pieces2 = re.split(r"(<p>.*?</p>)", merged, flags=re.I | re.S)
        out2: list[str] = []
        i = 0
        while i < len(pieces2):
            piece = pieces2[i]
            if not re.fullmatch(r"<p>.*?</p>", piece or "", flags=re.I | re.S):
                out2.append(piece)
                i += 1
                continue
            inner = P_TAG_RE.fullmatch(piece).group(1)
            if i + 2 < len(pieces2) and not pieces2[i + 1].strip() and re.fullmatch(r"<p>.*?</p>", pieces2[i + 2] or "", flags=re.I | re.S):
                nxt = P_TAG_RE.fullmatch(pieces2[i + 2]).group(1)
                if can_merge(inner, nxt):
                    out2.append(f"<p>{inner.rstrip()} {nxt.lstrip()}</p>")
                    i += 3
                    continue
            out2.append(piece)
            i += 1
        merged = "".join(out2)
    return merged


def split_sections(body: str):
    matches = list(H2_RE.finditer(body))
    if not matches:
        return body, []
    intro = body[: matches[0].start()]
    sections = []
    for idx, m in enumerate(matches):
        start = m.end()
        end = matches[idx + 1].start() if idx + 1 < len(matches) else len(body)
        sections.append({"heading": m.group(1), "content": body[start:end]})
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
    scored = []
    for idx, sec in enumerate(sections[1:-1], 1):
        section_words = wc(sec["content"])
        bonus = 20 if "?" in plain(sec["heading"]) or "¿" in plain(sec["heading"]) else 0
        # Lists/tables are meaningful standalone reader tasks, so prefer keeping
        # their headings rather than burying them inside a neighboring section.
        structural_bonus = 35 if re.search(r"<(ul|ol|table|h3)\b", sec["content"], re.I) else 0
        scored.append((section_words + bonus + structural_bonus, idx))
    for _, idx in sorted(scored, reverse=True)[: max(0, target - len(keep))]:
        keep.add(idx)

    result: list[dict] = []
    for idx, sec in enumerate(sections):
        content = smooth_segment(sec["content"])
        if idx in keep or not result:
            result.append({"heading": sec["heading"], "content": content})
        else:
            result[-1]["content"] += content
            result[-1]["content"] = smooth_segment(result[-1]["content"])
    return result


def rebuild(body: str) -> str:
    intro, sections = split_sections(body)
    if not sections:
        return smooth_segment(body)
    total_words = wc(body)
    intro = smooth_segment(intro)
    sections = consolidate_sections(sections, total_words)
    chunks = [intro]
    for sec in sections:
        chunks.append(f"<h2>{sec['heading']}</h2>{sec['content']}")
    return "".join(chunks)


def align_translation_groups(records: list[tuple[Path, dict]]) -> None:
    by_number: dict[int, list[tuple[Path, dict]]] = defaultdict(list)
    for path, data in records:
        num = data.get("article_number")
        if isinstance(num, int):
            by_number[num].append((path, data))
    for _, items in by_number.items():
        if len(items) < 2:
            continue
        es = next((d for _, d in items if d.get("language") == "es"), None)
        canonical = (es or items[0][1]).get("translation_group")
        if canonical:
            for _, data in items:
                data["translation_group"] = canonical


def main() -> int:
    records: list[tuple[Path, dict]] = []
    for path in sorted(ARTICLES.glob("*/*.json")):
        records.append((path, json.loads(path.read_text(encoding="utf-8"))))

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
