"""Split assets/css/src/main.css into per-template bundles (core, home, listing, property, project, content).

Run: python3 build-css.py  (then `npm run build` to minify)
"""
import re

SRC = 'assets/css/src/main.css'
BUCKETS = [
    ('home', r'\.hero|\.search-box|\.field\b|\.field--|\.field__|\.tile|\.service|\.why|\.cta'),
    ('listing', r'\.listing|\.filters|\.check\b|\.pills|\.results-bar|\.sort\b|\.view-toggle|\.grid--results'),
    ('project', r'\.project-hero|\.key-facts|\.in-page-nav|\.pj-|\.dots\b|\.legend|\.dot\b|\.dot--|\.table-wrap|\.units|\.status|\.plan(?![-\w])|\.timeline|\.register'),
    ('property', r'\.mortgage|\.single-property|\.sp-|\.gallery|\.facts|\.tabs__|\.plan-img|\.video|\.nearby|\.agent(?![-\w])|\.agent__|\.agent-box|\.license-box|\.related|\.lightbox'),
    ('agents', r'\.grid--agents|\.card--agent|\.page-head--agent|\.agent-profile|\.agent-layout|\.agent-bio|\.avatar--xl'),
    ('content', r'\.with-sidebar|\.content-area|\.sidebar|\.widget|\.archive-desc|\.article|\.tags\b|\.post-navigation|\.entry-content|\.align|\.wp-caption|figcaption|\.comment|\.contact(?![-\w])|\.contact__|\.contact-cards|\.error-404'),
]


def split_rules(css):
    """Yield top level blocks: (prelude, body) where body may contain nested rules (@media)."""
    i, n, out = 0, len(css), []
    while i < n:
        j = css.find('{', i)
        if j < 0:
            break
        prelude = css[i:j].strip()
        depth, k = 1, j + 1
        while depth and k < n:
            if css[k] == '{':
                depth += 1
            elif css[k] == '}':
                depth -= 1
            k += 1
        out.append((prelude, css[j + 1:k - 1]))
        i = k
    return out


def bucket_of(selector):
    if selector.startswith('.block-page') or selector.startswith('.dara-block-empty'):
        return 'core'
    for name, pattern in BUCKETS:
        if re.search(pattern, selector):
            return name
    return 'core'


def split_selectors(prelude):
    """Group a selector list by bucket: {bucket: 'sel1,sel2'}."""
    groups = {}
    for sel in [s.strip() for s in prelude.split(',') if s.strip()]:
        groups.setdefault(bucket_of(sel), []).append(sel)
    return {k: ','.join(v) for k, v in groups.items()}


def main():
    css = re.sub(r'/\*.*?\*/', '', open(SRC, encoding='utf8').read(), flags=re.S)
    out = {name: [] for name in ['core'] + [b[0] for b in BUCKETS]}
    for prelude, body in split_rules(css):
        if prelude.startswith('@media') or prelude.startswith('@supports'):
            if prelude.startswith('@media print'):
                out['core'].append(f'{prelude}{{{body}}}')
                continue
            inner = {}
            for p2, b2 in split_rules(body):
                for name, sels in split_selectors(p2).items():
                    inner.setdefault(name, []).append(f'{sels}{{{b2.strip()}}}')
            for name, rules in inner.items():
                out[name].append(prelude + '{' + ''.join(rules) + '}')
        elif prelude.startswith('@font-face') or prelude.startswith('@keyframes') or prelude.startswith(':root'):
            out['core'].append(f'{prelude}{{{body.strip()}}}')
        else:
            for name, sels in split_selectors(prelude).items():
                out[name].append(f'{sels}{{{body.strip()}}}')
    for name, rules in out.items():
        with open(f'assets/css/{name}.css', 'w', encoding='utf8') as f:
            f.write('/* Dara – ' + name + ' (generated from src/main.css by build-css.py) */\n' + '\n'.join(rules) + '\n')
        print(name, sum(len(r) for r in rules))


main()
