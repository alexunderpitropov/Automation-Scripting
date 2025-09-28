import argparse
import json
import os
import logging
import re
from pathlib import Path
from datetime import datetime, date, UTC
import requests
from dotenv import load_dotenv

# Загружаем ключ
load_dotenv()
API_KEY = os.getenv("API_KEY", "EXAMPLE_API_KEY")

BASE_URL = "http://localhost:8080/"
DATA_DIR = Path(__file__).resolve().parent.parent / "data"
LOG_FILE = Path(__file__).resolve().parent.parent / "error.log"

# Логи
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.FileHandler(LOG_FILE, encoding="utf-8"),
        logging.StreamHandler()
    ]
)

def validate_currency(code: str) -> str:
    if not re.fullmatch(r"[A-Z]{3}", code):
        raise ValueError(f"Неверный код валюты: {code}. Используйте 3 заглавные буквы, например USD.")
    return code

def validate_date(value: str) -> date:
    try:
        dt = datetime.strptime(value, "%Y-%m-%d").date()
    except ValueError:
        raise ValueError(f"Неверный формат даты: {value}. Используйте YYYY-MM-DD.")
    start = date(2025, 1, 1)
    end = date(2025, 9, 15)
    if not (start <= dt <= end):
        raise ValueError(f"Дата {value} вне допустимого диапазона {start} — {end}.")
    return dt

def get_exchange_rate(currency_from, currency_to, dt: date):
    params = {"from": currency_from, "to": currency_to, "date": dt.isoformat()}
    try:
        response = requests.post(BASE_URL, params=params, data={"key": API_KEY}, timeout=10)
        response.raise_for_status()
        data = response.json()
        if data.get("error"):
            raise ValueError(data["error"])
        return data
    except Exception as e:
        logging.error(f"Ошибка при запросе API: {e}")
        raise

def save_to_file(currency_from, currency_to, dt: date, payload):
    DATA_DIR.mkdir(parents=True, exist_ok=True)
    filename = f"rate_{currency_from}_{currency_to}_{dt.isoformat()}.json"
    filepath = DATA_DIR / filename
    enriched = {
        "request": {
            "from": currency_from,
            "to": currency_to,
            "date": dt.isoformat(),
            "saved_at": datetime.now(UTC).isoformat(),
        },
        "response": payload
    }
    with open(filepath, "w", encoding="utf-8") as f:
        json.dump(enriched, f, ensure_ascii=False, indent=2)
    logging.info(f"Данные сохранены: {filepath}")
    print(f"Saved: {filepath.relative_to(Path(__file__).resolve().parent.parent)}")

def main():
    parser = argparse.ArgumentParser(description="Currency Exchange Rate Script")
    parser.add_argument("--from", dest="currency_from", required=True, help="Исходная валюта (например USD)")
    parser.add_argument("--to", dest="currency_to", required=True, help="Целевая валюта (например EUR)")
    parser.add_argument("--date", required=True, help="Дата в формате YYYY-MM-DD")
    args = parser.parse_args()

    try:
        currency_from = validate_currency(args.currency_from.upper())
        currency_to = validate_currency(args.currency_to.upper())
        if currency_from == currency_to:
            raise ValueError("Исходная и целевая валюты не могут совпадать.")

        dt = validate_date(args.date)

        payload = get_exchange_rate(currency_from, currency_to, dt)
        save_to_file(currency_from, currency_to, dt, payload)

    except Exception as e:
        logging.error(f"Ошибка: {e}")
        print(f"[ERROR] {e}")

if __name__ == "__main__":
    main()
