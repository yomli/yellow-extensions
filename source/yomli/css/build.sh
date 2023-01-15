#!/usr/bin/env bash
set -uo pipefail

rm ./yomli.css
cat croissant-*.css style.css >> yomli.css

exit 0
