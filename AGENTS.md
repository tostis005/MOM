# MOM repository instructions

## Canonical content contract

All article JSON under `content/articles/<language>/` MUST use Content Contract v2.

- `schema_version` MUST be `2`.
- Do not use inherited FOOD taxonomy keys such as `food_family`, `food_subcategories`, `article_types`, or `primary_article_type`.
- The canonical taxonomy dimensions are declared in `content/site.json` and their allowed terms live in `content/taxonomies/*.json`.
- For MOM, every article MUST include `topic`, `stage`, `audience`, and `article_type` assignments.
- `topic.primary` is the main editorial area. Additional `topic.terms` may include registered child subtopics.
- ES and EN versions sharing a `translation_group` MUST have identical taxonomy IDs. Labels and slugs are localized by the taxonomy registry, not inside article JSON.
- Never invent a taxonomy ID inside an article. Add a justified reusable term to the corresponding registry first if a genuinely new cluster is needed.

Canonical shape:

```json
"taxonomy": {
  "topic": {
    "primary": "sleep",
    "terms": ["sleep", "bedtime-routines"]
  },
  "stage": {
    "primary": "baby",
    "terms": ["baby"]
  },
  "audience": {
    "primary": "parents",
    "terms": ["parents"]
  },
  "article_type": {
    "primary": "how-to",
    "terms": ["how-to", "routine-organization"]
  }
}
```

## Editorial preservation

When migrating or reclassifying an existing article, do not rewrite `title`, `slug`, `seo`, `excerpt`, `content_html`, `faq`, `sources`, `image`, `market_context`, `translation_group`, or any other editorial field unless the task explicitly asks for editorial changes. Taxonomy-only work must remain taxonomy-only.

## Editorial quality-review registry

Before selecting any existing article or batch for an editorial quality/recovery pass, **MUST read** `agents/EDITORIAL_QUALITY_REVIEW_REGISTRY.md`.

- Any article marked there as `QUALITY_REVIEWED` MUST be excluded from new editorial-recovery selection, even if the latest editorial audit assigns it a high risk score.
- `editorial-audit-ranking.tsv` is only a prioritization source among articles that are **not** already marked reviewed.
- Do not infer that an article needs another editorial rewrite solely from an automated audit score.
- After a completed editorial-recovery batch is merged, update the registry before selecting the next batch.
- Initial article creation, normalization, migration, taxonomy work, or publication alone do not count as an editorial quality review.
- An already reviewed article may only receive another deliberate quality pass when that new pass is explicitly requested; record the additional pass in the registry rather than erasing the previous one.

## Validation

Before committing article JSON, run:

```bash
python3 platform/normalize-mom-v2.py . --check
```

The repository automation enforces the same contract on `main` and automatically normalizes any legacy v1 JSON that an older process may still create.
