#!/usr/bin/env bash
set -uo pipefail

rm ./guitarfirst.css
cat croissant-*.css style.css >> guitarfirst.css

exit 0
