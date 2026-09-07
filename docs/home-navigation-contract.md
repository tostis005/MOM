# MOM home and landing navigation contract

MOM exposes three independent homepage entry axes from `content/site.json`:

1. `topic` — explore by subject.
2. `stage` — explore by family/child stage.
3. `audience` — explore by who the content is especially for.

`article_type` remains an independent non-home dimension for filtering and related-content logic.

Top-level homepage items and landing subcategories come from the taxonomy registries. A theme should call the generic runtime helpers rather than maintain a second hardcoded category list.

- `content_platform_home_axes()` returns homepage dimensions.
- `content_platform_home_terms($dimension)` returns top-level homepage terms.
- `content_platform_child_terms($dimension, $term_id)` returns children for landing subnavigation.

This lets a topic landing such as Sleep show registered child clusters (bedtime routines, night wakings, naps, sleep transitions, etc.) while a stage landing can cross-filter the same article inventory by topic.
