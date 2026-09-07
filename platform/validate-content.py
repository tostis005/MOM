#!/usr/bin/env python3
import argparse
import json
import re
import sys
from pathlib import Path

CORE_REQUIRED = {
    'schema_version', 'id', 'article_number', 'translation_group', 'language',
    'title', 'slug', 'seo', 'excerpt', 'taxonomy', 'content_html', 'faq',
    'sources', 'image', 'status'
}
HTML_TAG = re.compile(r'</?[A-Za-z][^>]*>')


def load(path: Path):
    try:
        return json.loads(path.read_text(encoding='utf-8'))
    except Exception as exc:
        raise ValueError(f'{path}: invalid JSON: {exc}') from exc


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('root', nargs='?', default='.')
    args = parser.parse_args()
    root = Path(args.root)

    site_path = root / 'content/site.json'
    site = load(site_path)
    taxonomy_defs = {}
    term_ids = {}
    for relative in site.get('taxonomies', []):
        path = root / relative
        definition = load(path)
        dimension = definition.get('dimension')
        if not dimension:
            raise SystemExit(f'{path}: missing dimension')
        if dimension in taxonomy_defs:
            raise SystemExit(f'{path}: duplicate dimension {dimension}')
        taxonomy_defs[dimension] = definition
        ids = [str(t['id']) for t in definition.get('terms', []) if isinstance(t, dict) and t.get('id')]
        if len(ids) != len(set(ids)):
            raise SystemExit(f'{path}: duplicate term id')
        term_ids[dimension] = set(ids)

    home_axes = site.get('navigation', {}).get('home_axes', [])
    unknown_axes = sorted(set(home_axes) - set(taxonomy_defs))
    if unknown_axes:
        raise SystemExit(f'{site_path}: unknown home axes {unknown_axes}')

    strict = bool(site.get('content', {}).get('strict_taxonomy_terms', False))
    legacy_enabled = bool(site.get('legacy_v1', {}).get('enabled', False))
    failures = []
    count = 0

    for path in sorted((root / 'content/articles').glob('*/*.json')):
        count += 1
        try:
            data = load(path)
            missing = CORE_REQUIRED - data.keys()
            if missing:
                failures.append(f'{path}: missing {sorted(missing)}')
                continue
            if not isinstance(data.get('article_number'), int) or data['article_number'] < 1:
                failures.append(f'{path}: invalid article_number')
            if not isinstance(data.get('excerpt'), str) or HTML_TAG.search(data.get('excerpt', '')):
                failures.append(f'{path}: excerpt must be plain text')
            lang = data.get('language')
            if lang not in site.get('languages', {}):
                failures.append(f'{path}: language {lang!r} not declared in content/site.json')

            version = int(data.get('schema_version', 1))
            if version >= 2:
                taxonomy = data.get('taxonomy')
                if not isinstance(taxonomy, dict):
                    failures.append(f'{path}: taxonomy must be an object')
                    continue
                for dimension, assignment in taxonomy.items():
                    if dimension not in taxonomy_defs:
                        failures.append(f'{path}: unknown taxonomy dimension {dimension!r}')
                        continue
                    if not isinstance(assignment, dict) or not isinstance(assignment.get('terms'), list):
                        failures.append(f'{path}: taxonomy.{dimension}.terms must be an array')
                        continue
                    terms = [str(v) for v in assignment['terms']]
                    if len(terms) != len(set(terms)):
                        failures.append(f'{path}: taxonomy.{dimension}.terms contains duplicates')
                    primary = assignment.get('primary')
                    if primary and str(primary) not in terms:
                        failures.append(f'{path}: taxonomy.{dimension}.primary must also appear in terms')
                    if strict:
                        unknown = sorted(set(terms) - term_ids[dimension])
                        if unknown:
                            failures.append(f'{path}: unknown {dimension} terms {unknown}')
            elif not legacy_enabled:
                failures.append(f'{path}: schema_version {version} is legacy but legacy_v1 is disabled')
        except Exception as exc:
            failures.append(str(exc))

    if not count:
        failures.append('No article JSON files found')

    if failures:
        print('\n'.join(failures), file=sys.stderr)
        return 1

    print(f'Validated {count} article JSON files, {len(taxonomy_defs)} taxonomy dimensions, home axes={home_axes}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
