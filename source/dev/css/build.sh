#!/usr/bin/env bash
set -uo pipefail

rm ./dev.css
cat croissant-*.css style.css >> dev.css

exit 0
