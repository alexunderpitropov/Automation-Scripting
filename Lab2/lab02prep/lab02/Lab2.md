# Лабораторная работа №2

**Создание Python-скрипта для взаимодействия с API обмена валют**

## Студент

**Александр Питропов, группа I2302**
**Дата выполнения: 28.09.2025**

---

## Цель работы

Изучить работу с **Web API** при помощи Python-скрипта: передача параметров, обработка ошибок, сохранение результата в JSON и ведение логов.

---

## Подготовка

1. Скачан и распакован проект из архива `lab02prep`.
2. Запуск сервиса API осуществляется через Docker:

```powershell
cd lab02prep
cp sample.env .env
docker compose up -d
```

После старта сервис доступен по адресу: **[http://localhost:8080](http://localhost:8080)**

Для проверки работы API можно выполнить:

```powershell
curl "http://localhost:8080/?currencies" -Method POST -Body "key=EXAMPLE_API_KEY"
```

Вывод должен содержать список доступных валют, например:
`{"error":"","data":["MDL","USD","EUR","RON","RUS","UAH"]}`

---

## Установка зависимостей

В корне проекта создаём и активируем виртуальное окружение:

```powershell
python -m venv .venv
.venv\Scripts\Activate.ps1
```

Устанавливаем необходимые пакеты:

```powershell
pip install requests python-dotenv
```

Создаём файл `requirements.txt` и фиксируем зависимости:

```text
requests
python-dotenv
```

---

## Структура проекта

```
lab02prep/
│   requirements.txt
│   error.log
│   README.md
│
├───lab02/
│       currency_exchange_rate.py   # скрипт лабораторной работы
│
└───data/
        .gitkeep                    # папка для сохранения JSON
```

---

## Описание скрипта `currency_exchange_rate.py`

### Основные функции:

* **`validate_currency(code)`** — проверка кода валюты (3 заглавные буквы).
* **`validate_date(value)`** — проверка формата даты и диапазона (01.01.2025 – 15.09.2025).
* **`get_exchange_rate(base, target, date)`** — обращение к API, обработка ошибок.
* **`save_to_file(...)`** — сохранение результата в JSON с дополнительной служебной информацией.
* **`main()`** — точка входа: парсинг аргументов командной строки, запуск функций.

---

## Код скрипта

```python
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
```

---

## Примеры запуска

### Получение курса за 06.03.2025:

```powershell
python .\lab02\currency_exchange_rate.py --from USD --to EUR --date 2025-03-06
```

**Вывод в консоль:**

```
2025-09-28 11:49:42,797 [INFO] Данные сохранены: C:\Automation and Scripting\Lab2\lab02prep\data\rate_USD_EUR_2025-03-06.json
Saved: data\rate_USD_EUR_2025-03-06.json
```

---

## Тестирование

По условию выбраны **5 дат с равными интервалами**:

* 2025-01-01
* 2025-03-06
* 2025-05-09
* 2025-07-13
* 2025-09-15

Для каждой даты выполнялась команда, пример:

```powershell
python .\lab02\currency_exchange_rate.py --from USD --to EUR --date 2025-01-01
```

---

## Примеры файлов JSON

### `rate_USD_EUR_2025-03-06.json`

```json
{
  "request": {
    "from": "USD",
    "to": "EUR",
    "date": "2025-03-06",
    "saved_at": "2025-09-28T08:54:06.868729+00:00"
  },
  "response": {
    "error": "",
    "data": {
      "from": "USD",
      "to": "EUR",
      "rate": 1.067999121458379,
      "date": "2025-03-06"
    }
  }
}
```

### `rate_USD_EUR_2025-09-15.json`

```json
{
  "request": {
    "from": "USD",
    "to": "EUR",
    "date": "2025-09-15",
    "saved_at": "2025-09-28T08:54:06.868729+00:00"
  },
  "response": {
    "error": "",
    "data": {
      "from": "USD",
      "to": "EUR",
      "rate": 1.1733974764738153,
      "date": "2025-09-15"
    }
  }
}
```

---

## Вывод

В ходе работы:

* Развёрнут сервис API в Docker.
* Реализован Python-скрипт для взаимодействия с API, валидации параметров и обработки ошибок.
* Результаты сохраняются в JSON с расширенной структурой и логируются в файл.
* Проведено тестирование на пяти датах в диапазоне **01.01.2025 – 15.09.2025**, результаты корректно сохранены.