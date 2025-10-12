# Лабораторная работа по предмету *Automation and Scripting*

**Тема:** Автоматизация задач в Linux с помощью Shell-скриптов
**Студент:** Alexandr Pitropov
**Дата:** 13.09.2025

---

## Цель

Научиться создавать и выполнять простые Shell-скрипты для автоматизации рутинных задач в операционной системе Linux.

## Задание

Реализовать скрипт **backup.sh**, который выполняет резервное копирование каталога.

### Условия:

* Скрипт принимает два аргумента:

  1. путь к каталогу, который нужно архивировать;
  2. путь к каталогу для сохранения архива (необязательный, по умолчанию `/backup`).
* Архив создаётся в формате `tar.gz` и содержит текущую дату в имени файла.
* Скрипт проверяет существование каталогов и выводит сообщения об ошибках.

---

## Код скрипта backup.sh

```bash
#!/bin/bash

if [ $# -lt 1 ]; then
    echo "Использование: $0 <директория_для_резервного_копирования> [директория_для_сохранения]"
    exit 1
fi

SOURCE_DIR=$1
DEST_DIR=${2:-/backup}

if [ ! -d "$SOURCE_DIR" ]; then
    echo "Ошибка: директория $SOURCE_DIR не существует."
    exit 1
fi

if [ ! -d "$DEST_DIR" ]; then
    mkdir -p "$DEST_DIR" || { echo "Ошибка: не удалось создать директорию $DEST_DIR."; exit 1; }
fi

DATE=$(date +%F)
ARCHIVE_NAME="backup-$DATE.tar.gz"

tar -czf "$DEST_DIR/$ARCHIVE_NAME" -C "$(dirname "$SOURCE_DIR")" "$(basename "$SOURCE_DIR")"

if [ $? -eq 0 ]; then
    echo "Резервная копия успешно создана: $DEST_DIR/$ARCHIVE_NAME"
else
    echo "Ошибка при создании архива."
    exit 1
fi
```

![image](https://i.imgur.com/GC9mC1o.png)

---

## Проверка работы скрипта

### 1. Создание тестовой директории и файлов

```bash
mkdir ~/testdir
echo "Hello world" > ~/testdir/file1.txt
echo "Log entry" > ~/testdir/file2.log
```

### 2. Запуск скрипта

```bash
./backup.sh ~/testdir
```

**Вывод:**

```
Резервная копия успешно создана: /backup/backup-2025-09-13.tar.gz
```

### 3. Проверка наличия архива

```bash
ls -lh /backup
```

**Вывод (пример):**

```
-rw-r--r-- 1 sasha sasha 208 Sep 13 11:54 backup-2025-09-13.tar.gz
```

### 4. Просмотр содержимого архива

```bash
tar -tzf /backup/backup-2025-09-13.tar.gz
```

**Вывод:**

```
testdir/
testdir/file1.txt
testdir/file2.log
```

![image](https://i.imgur.com/jhBZ2Wy.jpeg)

---

## Вывод

Скрипт **backup.sh** успешно выполняет резервное копирование, создаёт архив с текущей датой и проверяет корректность входных данных.
Все условия лабораторной работы выполнены.

---

## Библиография (онлайн-источники)

1. GNU Bash Manual — [https://www.gnu.org/software/bash/manual/](https://www.gnu.org/software/bash/manual/)
2. Документация по утилите `tar` — [https://www.gnu.org/software/tar/manual/](https://www.gnu.org/software/tar/manual/)
3. Linux man-pages (онлайн) — [https://man7.org/linux/man-pages/](https://man7.org/linux/man-pages/)
4. Linuxize: How to Use Tar Command — [https://linuxize.com/post/how-to-use-tar-command-in-linux/](https://linuxize.com/post/how-to-use-tar-command-in-linux/)
