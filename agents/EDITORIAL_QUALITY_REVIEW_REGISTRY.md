# MOM editorial quality-review registry

This file is the persistent source of truth for editorial-recovery work on existing MOM articles.

## Mandatory rule

Before selecting any article or range for an editorial quality/recovery pass, read this file first.

- Any article covered by a range marked `QUALITY_REVIEWED` **MUST be excluded** from new editorial-recovery selection, even if `editorial-audit-ranking.tsv` later assigns it a high risk score.
- The editorial audit is only a prioritization signal **after** the reviewed set below has been excluded.
- Do not infer that an article needs another rewrite merely because a heuristic/auditor score is high.
- Do not mark a range reviewed merely because the articles were originally created, normalized, migrated, or reclassified. `QUALITY_REVIEWED` means a deliberate editorial quality pass was completed.
- If a genuinely new editorial pass is intentionally requested for an already reviewed article, record that as a new pass instead of silently treating the previous review as nonexistent.
- After every completed editorial-recovery batch, update this registry in the same operational cycle.

## Selection algorithm for the next recovery batch

1. Read this registry and build the exclusion set from every `QUALITY_REVIEWED` range.
2. Read the latest `editorial-audit-ranking.tsv` from current `main`.
3. Remove all reviewed article numbers from the ranking candidates.
4. Rank only the remaining, unreviewed articles by editorial risk.
5. Prefer a coherent contiguous batch when possible, but never cross into a reviewed range just to make a batch of ten.
6. Before editing, also check for an existing branch or PR for the candidate range.
7. After merge, add the completed range here before choosing another batch.

## Quality-reviewed ranges

`Languages` refers to the paired EN + ES article files for each article number.

| Article range | Languages | Editorial status | Evidence / traceability |
|---|---|---|---|
| 001–010 | EN + ES | `QUALITY_REVIEWED` | Deliberate manual editorial quality review completed on 2026-09-09. All 20 existing JSON files were read in full and judged already substantive, well differentiated and naturally localized; no article rewrite was required. |
| 011–020 | EN + ES | `QUALITY_REVIEWED` | Historical quality review explicitly confirmed by project owner on 2026-09-09. Do not reselect from audit ranking. |
| 021–030 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #32. |
| 031–040 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #33. |
| 041–050 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #34. |
| 051–060 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #35; subsequent UTF-8 repair did not invalidate the editorial review. |
| 061–070 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #37; subsequent encoding repairs did not invalidate the editorial review. |
| 071–080 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #31. |
| 081–090 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #15. |
| 091–100 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #16. |
| 101–110 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #17. |
| 111–120 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #18. |
| 121–130 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #19. |
| 131–140 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #20. |
| 141–150 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #21. |
| 1001–1010 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #47. |
| 1011–1020 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #45. |
| 1021–1030 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #40. |
| 1031–1040 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #41. |
| 1041–1050 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #42. |
| 1051–1060 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #43. |
| 1061–1070 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #29. |
| 1071–1080 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #30. |
| 1081–1090 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #22. |
| 1091–1100 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #23. |
| 1101–1110 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #24. |
| 1111–1120 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #25. |
| 1121–1130 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #26. |
| 1131–1140 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #27. |
| 1141–1150 | EN + ES | `QUALITY_REVIEWED` | Merged editorial recovery PR #28. |

## Additional editorial passes on reviewed ranges

These entries record deliberate extra passes that were explicitly performed on ranges already covered by `QUALITY_REVIEWED`. They do not replace or erase the original review evidence above.

| Article range | Pass | Languages | Editorial status | Evidence / traceability |
|---|---:|---|---|---|
| 141–150 | 2 | EN + ES | `QUALITY_REVIEWED` | Deliberate second editorial pass merged in PR #50 on 2026-09-09. Only `content_html` was rewritten across the 20 existing JSON files; URLs/slugs and metadata were preserved. |

## Explicit exclusion set

For automated or manual candidate selection, the currently reviewed article numbers are:

- `001–150`.
- `1001–1150`.

This compact exclusion set is equivalent to the detailed rows above. If future work creates a gap, partial batch, or non-contiguous exception, update both this section and the detailed table rather than assuming the compact ranges remain continuous.

## Publication status is a separate concept

`QUALITY_REVIEWED` answers only: “Has this article already received the deliberate editorial quality pass?” It is the field used to prevent duplicate editorial work.

WordPress/import confirmation is operational deployment evidence and must not be used to decide whether an article needs another editorial rewrite. Where an exact import summary has been explicitly verified, it can be recorded separately. Known exact confirmations from the recovery work include at least:

- 061–070: `created=0 updated=20 skipped=0 failed=0`
- 101–110: `created=0 updated=20 skipped=0 failed=0`
- 141–150 pass 2 (PR #50; import run 34396853504): `created=0 updated=20 skipped=0 failed=0`
- 1011–1020: `created=0 updated=20 skipped=0 failed=0`
- 1021–1030: `created=0 updated=20 skipped=0 failed=0`
- 1051–1060: `created=0 updated=20 skipped=0 failed=0`

Absence from this short deployment list means “not re-verified in this registry,” not “not published.”

## Maintenance

When a new editorial-recovery batch is completed:

1. Add or extend the relevant row/range only after the quality pass has actually been merged.
2. Record the PR number or other durable evidence.
3. Keep EN/ES scope explicit.
4. Update the compact exclusion set.
5. Never remove an old reviewed range because an automated audit score later rises.

Last reconstructed from repository history and project confirmation: 2026-09-09.
