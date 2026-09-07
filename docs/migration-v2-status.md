# MOM Content Contract v2 migration

This repository has been migrated from the inherited FOOD-shaped article taxonomy to Content Contract v2.

## Guarantees

- Article editorial fields are preserved during migration; only `schema_version` and `taxonomy` are rewritten.
- ES/EN articles in the same `translation_group` use identical taxonomy IDs.
- MOM uses four dimensions: `topic`, `stage`, `audience`, and `article_type`.
- The homepage axes are `topic`, `stage`, and `audience`; article type remains available for filtering, related content, and editorial organization.
- Topic children live in `content/taxonomies/topic.json` and drive landing-page subnavigation.
- Legacy v1 input is disabled in `content/site.json`.
- `normalize-content-v2.yml` automatically converts any legacy JSON created by an older process before it can be imported.
- `import-content.yml` only invokes the universal importer after the full repository passes the v2 contract.

## Validation

Use:

```bash
python3 platform/normalize-mom-v2.py . --check
```

FOOD / Quinnoa is intentionally unchanged.
