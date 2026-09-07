# Content Platform v2

## Goal

Use one article contract and one importer for MOM and future editorial sites, while leaving Quinnoa/FOOD v1 untouched.

The platform separates four layers that were coupled in the FOOD importer:

1. **Article data** — universal JSON fields plus site-defined taxonomy dimensions.
2. **Site manifest** — languages, home navigation axes and which taxonomy files exist.
3. **Taxonomy registries** — the vocabulary for each dimension, including localized labels and optional home ordering.
4. **Importer/runtime** — generic code with no knowledge of food, parenting or any future vertical.

FOOD remains on its current v1 importer. The v2 importer contains a legacy adapter so a v1-shaped site can be connected later without rewriting the engine.

## Universal article taxonomy shape

Schema v2 does not hard-code `food_family`, `stage`, `audience` or any other vertical-specific field. Every dimension follows the same contract:

```json
"taxonomy": {
  "topic": {
    "primary": "sleep",
    "terms": ["sleep", "bedtime-routines"]
  },
  "stage": {
    "terms": ["baby"]
  },
  "audience": {
    "terms": ["parents"]
  },
  "article_type": {
    "primary": "how-to",
    "terms": ["how-to", "routine-organization"]
  }
}
```

A future site can use different dimensions — for example `ingredient`, `technique` and `intent` — without changing the importer.

## MOM navigation model

MOM declares three home axes in `content/site.json`:

- `topic` — explore by subject.
- `stage` — explore by life/child stage.
- `audience` — explore by who the content is for.

`article_type` is stored and imported but is not a primary home axis. It is available for related-content logic, filters and secondary landing pages.

The home implementation should read these same registries rather than maintain a second hard-coded category list. That makes the JSON, WordPress taxonomies, landing pages and home navigation share one source of truth.

## Runtime metadata

New sites use neutral WordPress metadata such as:

- `_content_source_id`
- `_content_source_hash`
- `_content_article_number`
- `_content_language`
- `_content_translation_group`
- `_content_taxonomy`
- `_content_primary_<dimension>`
- `_content_faq`
- `_content_sources`
- `_content_image_concept`
- `_content_image_alt`

No new site should introduce `_mom_*`, `_home_*` or other project-specific importer metadata.

## Reusable importer

`platform/import-content.php` is idempotent and configuration-driven. It:

- matches articles by stable source ID, then by slug + language;
- skips unchanged JSON using SHA-256;
- imports title, body, excerpt, status and SEO metadata;
- stores FAQ, sources and image brief metadata;
- registers and assigns all configured taxonomy dimensions generically;
- links translations when Polylang is available;
- supports schema v2 and an optional v1 adapter;
- records the last successful Git SHA so failed deployments are not silently skipped later.

`platform/content-platform.php` is installed as a WordPress MU plugin by the reusable workflow. It registers site-configured taxonomies and exposes helpers that a theme/home can use to read home axes and localized taxonomy labels.

## Adding a future site

A future site should only need to provide:

1. `content/site.json`
2. one or more `content/taxonomies/*.json` registries
3. `content/articles/<language>/*.json`
4. a thin workflow that calls the shared reusable importer

Example caller workflow:

```yaml
name: Import content
on:
  push:
    branches: [main]
    paths:
      - 'content/articles/**/*.json'
      - 'content/site.json'
      - 'content/taxonomies/*.json'

jobs:
  import:
    uses: tostis005/MOM/.github/workflows/reusable-content-import.yml@main
    secrets: inherit
```

The repository secrets use the same names on every site: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY`, and optionally `WP_ROOT` when the server contains more than one WordPress installation.

## Quinnoa compatibility

Do not change FOOD now. Its current importer is tightly coupled to `food_family`, `food_topic` and `_food_*` metadata and already manages substantial live content. The universal engine is deliberately separate.

If FOOD is ever moved to the platform, its existing JSON can be supported through a FOOD-specific `content/site.json` legacy adapter that maps the v1 fields to generic dimensions. That migration is optional and does not block MOM or future sites.

## MOM activation sequence

The shared engine can coexist with the current MOM v1 JSON while the contract is introduced, but the live MOM importer should not be enabled until the existing article JSON files have been migrated to the v2 taxonomy dimensions (or explicit legacy value maps have been added). Current inherited values such as `baby-sleep` are not the canonical v2 term IDs.

Recommended rollout:

1. merge the platform/manifest/taxonomy registries;
2. migrate the existing MOM JSON inventory to schema v2 without changing article copy;
3. validate the complete bilingual inventory against the registries;
4. add the thin MOM caller workflow and perform a controlled draft import;
5. enable normal push-driven imports only after that verification.

This keeps the architecture universal without making the compatibility layer the permanent content model.
