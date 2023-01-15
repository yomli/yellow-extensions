#!/usr/bin/env bash
set -uo pipefail

rm ./editions.css
cat croissant-*.css style.css >> editions.css

exit 0
