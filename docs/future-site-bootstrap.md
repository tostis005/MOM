# Bootstrapping a future editorial site

A future site should reuse the Content Platform engine instead of cloning a vertical-specific importer.

Minimum site-owned files:

- `content/site.json`
- one or more `content/taxonomies/*.json`
- `content/articles/<language>/*.json`
- a small caller workflow that invokes the reusable import workflow

The shared engine remains in MOM until/unless it is extracted to a dedicated platform repository. The engine must remain vertical-neutral: new site vocabularies belong in the site's taxonomy registries, not in importer PHP.

A new site can use any dimensions appropriate to its domain. It is not required to use MOM's `topic`, `stage`, `audience`, or `article_type` names; the importer reads the dimensions declared by that site's manifest.
