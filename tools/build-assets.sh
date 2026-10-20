#!/usr/bin/env bash
# Rebuild minified theme assets + manifest. Run from the repo root after editing any CSS/JS:
#   npm i -g terser csso-cli   (once)
#   bash tools/build-assets.sh
# The theme serves *.min.* only while the source size matches assets/min-manifest.json
# (inc/perf.php), so forgetting to rebuild falls back to the readable file, never stale code.
set -e
T=dck-elite-pro
cd "$T"
echo "{" > assets/min-manifest.json
first=1
add() { [ $first -eq 1 ] && first=0 || echo "," >> assets/min-manifest.json; printf '"%s": %s' "$1" "$(wc -c < "$1" | tr -d ' ')" >> assets/min-manifest.json; }
for f in $(find assets/js -name '*.js' ! -name '*.min.js'); do terser "$f" -c -m -o "${f%.js}.min.js"; add "$f"; done
for f in style.css $(find assets/css -name '*.css' ! -name '*.min.css'); do csso "$f" -o "${f%.css}.min.css"; add "$f"; done
echo "}" >> assets/min-manifest.json
echo "Done."
