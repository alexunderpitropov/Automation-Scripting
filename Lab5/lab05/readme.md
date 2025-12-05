# Лабораторная работа №5. Ansible playbook для конфигурации сервера

**Студент:** Питропов Александр, группа I2302

**Дата:** 29.11.2025

**Тема:** *Автоматизация конфигурации сервера с помощью Ansible и интеграция в CI/CD пайплайны Jenkins*

---

## **Цель работы**

Научиться использовать Ansible для автоматизации конфигурации серверов, а также встроить настройку окружения и деплой PHP‑проекта в процесс CI/CD через Jenkins.

---

# **1. Описание проекта**

В рамках лабораторной работы была создана полноценная DevOps‑инфраструктура, включающая:

* **Docker Compose окружение** из четырёх сервисов:

  * Jenkins Controller
  * SSH Agent
  * Ansible Agent
  * Test Server (Apache2 + PHP)
* PHP‑приложение *recipe-book*, размещённое в каталоге:

  ```
  Lab05/lab05/recipe-book
  ```
* **Ansible playbook**, автоматически настраивающий сервер.
* **Три Jenkins pipeline**:

  Сборка и тестирование PHP‑проекта.
     
  Настройка тестового сервера через Ansible.
     
  Деплой PHP‑приложения.

Итог: проект автоматически собирается, тестируется, сервер конфигурируется, код деплоится, сайт открывается по адресу:

```
http://localhost:8081
```

---

# **2. Создание инфраструктуры через Docker Compose**

Файл `compose.yaml` содержит четыре сервиса:

* `jenkins-controller`
* `ssh-agent`
* `ansible-agent`
* `test-server`

Каждый контейнер соединён в единую сеть и выполняет свою функцию.

### **Назначение сервисов**

| Сервис                 | Назначение                                  |
| ---------------------- | ------------------------------------------- |
| **jenkins-controller** | запуск CI/CD пайплайнов                     |
| **ssh-agent**          | выполнение composer + phpunit               |
| **ansible-agent**      | запуск ansible-playbook на тестовом сервере |
| **test-server**        | окружение для деплоя PHP‑приложения         |

Докер‑инфраструктура запускается командой:

```bash
docker compose up -d --build
```

---

# **3. Установка и настройка Jenkins Controller**

1. После запуска контейнера Jenkins открыт по адресу:

   ```
   http://localhost:8080
   ```
2. Был введён первоначальный пароль и выполнена стандартная настройка.
3. Установлены необходимые плагины:

   * Docker
   * Docker Pipeline
   * GitHub Integration
   * SSH Agent

Настройка Jenkins завершена успешно, контроллер доступен и готов к работе.

---

# **4. Настройка SSH‑агента**

### **4.1 Dockerfile.ssh_agent**

Для SSH‑агента создан собственный Dockerfile, в который добавлены:

* php-cli
* composer
* git
* необходимые библиотеки для запуска тестов

### **4.2 Создание SSH‑ключей**

Сгенерированы ключи `id_rsa` для взаимодействия Jenkins ↔ SSH Agent.

### **4.3 Добавление ключей в Jenkins**

В разделе Credentials → SSH Username with private key был создан доступ, используемый пайплайном сборки.

SSH‑агент успешно подключается к Jenkins и выполняет билд.

---

# **5. Создание Ansible‑агента**

### **5.1 Dockerfile.ansible_agent**

Создан Dockerfile, основанный на Ubuntu, содержащий:

* ansible
* ssh‑client
* python3
* пользователя `ansible`
* директорию `.ssh` с корректными правами

### **5.2 SSH‑ключи Ansible → Test Server**

На агенте создан ключ, публичная часть добавлена на тестовый сервер.

### **5.3 Проверка подключения**

Тест:

```bash
ansible -i hosts.ini test-server-lab05 -m ping
```

Подключение успешно.

---

# **6. Создание тестового сервера**

### **6.1 Dockerfile.test_server**

Тестовый сервер основан на Ubuntu 22.04 и содержит:

* Apache2
* PHP 8.1 + расширения
* OpenSSH
* пользователя `ansible` с sudo без пароля

Проброшены порты:

```
8081 → 80 (HTTP)
2223 → 22 (SSH)
```

Сервер успешно запускается и доступен для настройки.

---

# **7. Ansible playbook для настройки тестового сервера**

Папка `lab05/ansible` содержит:

### **Файл hosts.ini**

Инвентарь описывает подключение к серверу test-server-lab05.

### **Playbook setup_test_server.yml**

Выполняет:

1. Установку Apache2
2. Установку PHP и модулей
3. Создание webroot (`/var/www/html`)
4. Создание структуры проекта (`public`, `src`, `storage`)
5. Перезапуск Apache
6. Настройку virtual host (`/etc/apache2/sites-available/000-default.conf`)

После выполнения проект корректно обрабатывается сервером.

---

# **8. Jenkins Pipeline №1 — сборка и тестирование PHP‑проекта**

Файл:

```
pipelines/php_build_and_test_pipeline.groovy
```

Пайплайн выполняет:

1. клонирование репозитория
2. установку зависимостей composer
3. запуск phpunit
4. вывод отчёта

Билд проходит успешно.

---

# **9. Jenkins Pipeline №2 — настройка сервера через Ansible**

Файл:

```
pipelines/ansible_setup_pipeline.groovy
```

Пайплайн выполняет:

* клонирование репозитория
* запуск ansible-playbook

При первом запуске возникла ошибка:
**Host key verification failed** → решено удалением старых known_hosts и корректной генерацией ключей.

После исправлений сервер успешно конфигурируется.

---

# **10. Jenkins Pipeline №3 — деплой PHP‑проекта**

Файл:

```
pipelines/php_deploy_pipeline.groovy
```

Пайплайн выполняет:

* копирование проекта с помощью rsync (установлен через apt)
* очистку старого webroot
* размещение public/, src/, storage/
* применение настроек Apache
* рестарт сервера

После устранения ошибок с rsync и путями деплой проходит успешно.

---

# **11. Результат: сайт работает**

После успешной настройки и деплоя приложение *recipe-book* доступно по адресу:

```
http://localhost:8081
```

На странице отображаются последние добавленные рецепты.

---

# **12. Ответы на контрольные вопросы**

### **1. Преимущества использования Ansible для настройки серверов**

* полностью автоматизированная конфигурация
* идемпотентность: одно действие не выполняется повторно
* масштабируемость на десятки серверов
* простой YAML‑синтаксис
* отсутствие агента (работает через SSH)
* лёгкая интеграция с Jenkins

### **2. Какие модули Ansible используются для управления конфигурацией**

Чаще всего применяются:

* `apt` — установка пакетов
* `copy` — копирование файлов
* `file` — создание директорий
* `template` — файлы с Jinja2
* `service` — управление службами
* `command`, `shell` — выполнение команд
* `synchronize`/`rsync` — копирование больших проектов
* `apache2_module` — включение модулей Apache

### **3. Проблемы, возникшие при создании playbook, и их решения**

**Проблема 1:** SSH "host key verification failed"
**Решение:** очистка known_hosts, пересоздание ключей.

**Проблема 2:** rsync отсутствовал на тестовом сервере
**Решение:** установка `rsync` через playbook.

**Проблема 3:** неправильный DocumentRoot → отображался phpinfo()
**Решение:** настройка VirtualHost на `public/index.php`.

**Проблема 4:** Jenkins использовал старую версию файлов
**Решение:** очистка workspace + пересборка.

---

# **Вывод**

В ходе лабораторной работы была создана полноценная CI/CD‑цепочка, включающая:

* автоматическую сборку PHP‑кода,
* автоматическую настройку тестового сервера через Ansible,
* автоматизированный деплой PHP‑приложения.

Инфраструктура полностью контейнеризирована, а сервер конфигурируется воспроизводимо и надёжно.

---

# **Библиография**

- **Docker Documentation.** Dockerfiles, Compose, Container Networking.  
  https://docs.docker.com/

- **Red Hat Ansible Documentation.** User Guide, Playbooks, Inventory, Modules.  
  https://docs.ansible.com/

- **Jenkins Documentation.** Continuous Integration and Delivery.  
  https://www.jenkins.io/doc/

- **OpenSSH Documentation.** SSH Architecture and Key Authentication.  
  https://www.openssh.org/manual.html

- **PHP Documentation.** PHP manual, CLI, extensions.  
  https://www.php.net/manual/en/

- **Apache HTTP Server Documentation.** Virtual Hosts, Modules, Configuration.  
  https://httpd.apache.org/docs/




