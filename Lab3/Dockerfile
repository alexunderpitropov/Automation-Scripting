FROM python:3.11-slim

# Устанавливаем cron и создаём симлинк python3 → python
RUN apt-get update && apt-get install -y cron && \
    ln -s /usr/local/bin/python /usr/bin/python3 && \
    rm -rf /var/lib/apt/lists/*

# Рабочая директория
WORKDIR /app

# Копируем все нужные файлы
COPY currency_exchange_rate.py cronjob cron_daily.sh cron_weekly.sh entrypoint.sh /app/

# Даём права на выполнение shell-скриптов
RUN chmod +x /app/entrypoint.sh /app/cron_daily.sh /app/cron_weekly.sh

#Устанавливаем зависимости Python
COPY requirements.txt /app/
RUN pip install -r requirements.txt

# Добавляем задания в cron
RUN crontab /app/cronjob

# Лог-файл cron
RUN touch /var/log/cron.log

ENTRYPOINT ["/app/entrypoint.sh"]
