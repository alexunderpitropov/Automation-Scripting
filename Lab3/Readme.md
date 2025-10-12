# Лабораторная работа №3

## Тема

**Автоматизация выполнения скрипта с помощью планировщика задач cron в Docker**

## Цель

Научиться использовать планировщик задач `cron` для автоматического выполнения Python-скриптов по расписанию в изолированном контейнере Docker.

## Подготовка

Работа основана на лабораторной № 2. Был скопирован готовый Python-скрипт `currency_exchange_rate.py` из папки `lab02` в новую директорию `Lab3`:

```bash
cd /mnt/x/Automation\ and\ Scripting
mkdir Lab3
cp -r Lab2/* Lab3/
```

## Структура проекта

```
Lab3/
├── currency_exchange_rate.py   # Скрипт из ЛР2
├── cron_daily.sh               # Скрипт ежедневного запуска
├── cron_weekly.sh              # Скрипт еженедельного запуска
├── cronjob                     # Файл с cron-заданиями
├── Dockerfile                  # Сборка образа
├── docker-compose.yml          # Поднятие контейнера
├── entrypoint.sh               # Скрипт запуска cron в контейнере
├── requirements.txt            # Зависимости Python (requests, python-dotenv)
└── Readme.md                   # Этот файл
```

## Cron задания

Файл `cronjob` содержит:

```cron
# Ежедневно в 6:00 — MDL → EUR (за вчера)
0 6 * * * /app/cron_daily.sh >> /var/log/cron.log 2>&1

# Еженедельно по пятницам в 17:00 — MDL → USD (за прошлую пятницу)
0 17 * * 5 /app/cron_weekly.sh >> /var/log/cron.log 2>&1
```

Файлы `cron_daily.sh` и `cron_weekly.sh`:

```sh
#!/bin/sh
YESTERDAY=$(date -d "yesterday" +%Y-%m-%d)
python3 /app/currency_exchange_rate.py --from MDL --to EUR --date "$YESTERDAY"
```

(аналогично для weekly — с USD)

## Dockerfile

```dockerfile
FROM python:3.11-slim

# Устанавливаем cron и создаём симлинк python3 → python
RUN apt-get update && apt-get install -y cron && \
    ln -s /usr/local/bin/python /usr/bin/python3 && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY currency_exchange_rate.py cronjob cron_daily.sh cron_weekly.sh entrypoint.sh /app/
RUN chmod +x /app/entrypoint.sh /app/cron_daily.sh /app/cron_weekly.sh

# Устанавливаем зависимости Python
COPY requirements.txt /app/
RUN pip install -r requirements.txt

# Добавляем cron задачи
RUN crontab /app/cronjob
RUN touch /var/log/cron.log

ENTRYPOINT ["/app/entrypoint.sh"]
```

## docker-compose.yml

```yaml
services:
  lab3-cron:
    build: .
    container_name: lab3_cron_service
    volumes:
      - ./lab03_logs:/var/log
    restart: unless-stopped


## entrypoint.sh

```sh
#!/bin/sh
create_log_file() {
    echo "Creating log file..."
    touch /var/log/cron.log
    chmod 666 /var/log/cron.log
    echo "Log file created at /var/log/cron.log"
}

monitor_logs() {
    echo "=== Monitoring cron logs ==="
    tail -f /var/log/cron.log
}

run_cron() {
    echo "=== Starting cron daemon ==="
    exec cron -f
}

env > /etc/environment
create_log_file
monitor_logs &
run_cron
```

## Ход выполнения

Все действия выполнялись в среде **WSL**.

### 1. Проверка файлов и прав

```bash
cd /mnt/x/Automation\ and\ Scripting/Lab3
ls -l
chmod +x cron_daily.sh cron_weekly.sh entrypoint.sh
```

### 2. Проверка cronjob

```bash
crontab cronjob
crontab -l
```

### 3. Сборка Docker-образа

```bash
docker compose build --no-cache
```

### 4. Запуск контейнера

```bash
docker compose up -d
docker ps
```

### 5. Просмотр логов

```bash
docker logs lab3_cron_service
```

Пример вывода:

```
Creating log file...
Log file created at /var/log/cron.log
=== Monitoring cron logs ===
=== Starting cron daemon ===
[ERROR] Дата 2025-10-11 вне допустимого диапазона 2025-01-01 — 2025-09-15.
```

Ошибка с датой — это бизнес-логика из ЛР2 (валидация диапазона). С точки зрения ЛР3 cron и контейнер работают корректно.

## Подробные логи и отладка

Ниже приведены реальные логи контейнера во время настройки, которые демонстрируют процесс отладки.

### Ошибка отсутствия `python3` в cron

```
/app/cron_daily.sh: 3: python3: not found
/app/cron_daily.sh: 3: python3: not found
/app/cron_daily.sh: 3: python3: not found
```

Причина: cron запускается в среде без `/usr/bin/python3`.
Решение: добавление симлинка во время сборки образа.

### Ошибки отсутствия Python-библиотек

```
Traceback (most recent call last):
  File "/app/currency_exchange_rate.py", line 8, in <module>
    import requests
ModuleNotFoundError: No module named 'requests'

Traceback (most recent call last):
  File "/app/currency_exchange_rate.py", line 9, in <module>
    from dotenv import load_dotenv
ModuleNotFoundError: No module named 'dotenv'
```

Причина: зависимости не были установлены в образе.
Решение: добавить файл `requirements.txt` и установить пакеты через `pip install -r requirements.txt`.

### Логи после настройки

```
2025-10-12 17:48:01,249 [ERROR] Ошибка: Дата 2025-10-11 вне допустимого диапазона 2025-01-01 — 2025-09-15.
[ERROR] Дата 2025-10-11 вне допустимого диапазона 2025-01-01 — 2025-09-15.
```

Эта ошибка соответствует бизнес-логике ЛР2 и не является проблемой cron или Docker.

## Проверка выполнения

* Контейнер собирается без ошибок
* Cron-задания установлены и запускаются автоматически
* Логи пишутся в `/var/log/cron.log` и отображаются через `docker logs`
* Python-скрипт вызывается по расписанию

## Заключение

В ходе работы был настроен автоматический запуск Python-скрипта в контейнере Docker с использованием планировщика cron. Контейнер собирается и работает стабильно, cron-задания выполняются по расписанию, логи сохраняются. Ошибка с датами относится к логике из ЛР2 и не является ошибкой данной лабораторной.

Лабораторная работа № 3 выполнена полностью.
