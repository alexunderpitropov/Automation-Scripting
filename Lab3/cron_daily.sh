#!/bin/sh
YESTERDAY=$(date -d "yesterday" +%Y-%m-%d)
python3 /app/currency_exchange_rate.py --from MDL --to EUR --date "$YESTERDAY"
