#!/bin/sh
LAST_FRIDAY=$(date -d "last friday" +%Y-%m-%d)
python3 /app/currency_exchange_rate.py --from MDL --to USD --date "$LAST_FRIDAY"
