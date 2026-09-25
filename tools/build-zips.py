# -*- coding: utf-8 -*-

import io
import os
import re
import sys
import zipfile

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DIST = os.path.join(ROOT, 'dist')

SKIP_DIRS = {'.git', '__pycache__', 'node_modules', '.idea', '.vscode'}
SKIP_FILES = {'.DS_Store', 'Thumbs.db'}
SKIP_SUFFIXES = ('.pyc', '.map.orig', '.orig', '.rej')


def version_from(path, pattern):
    source = io.open(path, encoding='utf-8', errors='replace').read(4000)
    match = re.search(pattern, source, re.MULTILINE)
    return match.group(1).strip() if match else '0.0.0'


def build(source_dir, slug, version):
    if not os.path.isdir(DIST):
        os.makedirs(DIST)

    target = os.path.join(DIST, '%s-%s.zip' % (slug, version))
    count = 0

    with zipfile.ZipFile(target, 'w', zipfile.ZIP_DEFLATED) as archive:
        for base, dirs, files in os.walk(source_dir):
            dirs[:] = [d for d in sorted(dirs) if d not in SKIP_DIRS]

            for name in sorted(files):
                if name in SKIP_FILES or name.endswith(SKIP_SUFFIXES):
                    continue

                full = os.path.join(base, name)
                inside = os.path.relpath(full, source_dir).replace(os.sep, '/')
                archive.write(full, '%s/%s' % (slug, inside))
                count += 1

    print('%-28s %5d αρχεία  %6.1f KB' % (os.path.basename(target), count, os.path.getsize(target) / 1024.0))
    return target


def main():
    plugin_dir = os.path.join(ROOT, 'plugins', 'kosmiteia-core')
    theme_dir = os.path.join(ROOT, 'kosmiteia')

    plugin_version = version_from(
        os.path.join(plugin_dir, 'kosmiteia-core.php'),
        r'^\s*\*\s*Version:\s*(.+)$'
    )
    theme_version = version_from(
        os.path.join(theme_dir, 'style.css'),
        r'^\s*\*?\s*Version:\s*(.+)$'
    )

    build(plugin_dir, 'kosmiteia-core', plugin_version)
    build(theme_dir, 'kosmiteia', theme_version)

    print('\nΈτοιμα στο dist/. Ανεβάστε τα από τη διαχείριση του WordPress.')


if __name__ == '__main__':
    main()
