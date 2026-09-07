# Reusable content importer architecture

The importer is split into a universal engine and per-site declarative configuration.

## Shared engine

`platform/import-content.php` and `platform/content-platform.php` contain no MOM topic vocabulary. They consume the site manifest and taxonomy registries at runtime.

The reusable GitHub Actions workflow is `.github/workflows/reusable-content-import.yml`. Future sites can call this workflow rather than copy the importer implementation.

## Per-site contract

Each site supplies:

- `content/site.json`: languages, post type, navigation axes, taxonomy registry paths and content behavior.
- `content/taxonomies/*.json`: stable taxonomy IDs, hierarchy, localized labels/slugs and navigation metadata.
- `content/articles/<language>/*.json`: articles using Content Contract v2 assignments.

The frontend and importer therefore share the same taxonomy source of truth. Homepage blocks, landing children and WordPress assignments do not require separate hardcoded lists.

## WordPress metadata

Managed posts use neutral `_content_*` metadata. A future vertical should not introduce project-prefixed importer metadata unless it represents genuinely site-specific application data.

## Compatibility

FOOD / Quinnoa keeps its existing importer and schema unchanged. MOM and future sites use Content Contract v2.
