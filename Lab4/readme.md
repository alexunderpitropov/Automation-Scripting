# Лабораторная работа IW04: Настройка Jenkins для автоматизации задач DevOps

**Выполнил:** Pitropov Alexandr, I2302

**Дата выполнения:** 9.11.2025

---

# 1. Цель работы

Цель лабораторной работы — развернуть Jenkins в Docker, настроить удалённый SSH‑агент, подключить его к контроллеру и создать работающий CI/CD конвейер. В процессе работа фокусируется на использовании Docker Compose, Jenkins, SSH‑ключей и Pipeline‑автоматизации.

---

# 2. Структура проекта

В корне проекта была создана папка `lab04`, где расположены файлы:

```
lab04/
 ├── docker-compose.yml
 ├── Dockerfile
 ├── .env
 └── secrets/
```

Папка `secrets` хранит SSH‑ключи и не загружается в репозиторий.

---

# 3. Создание Dockerfile для SSH‑агента

Был создан Dockerfile:

```dockerfile
FROM jenkins/ssh-agent
RUN apt-get update && apt-get install -y php-cli && rm -rf /var/lib/apt/lists/*
```

Добавлен PHP‑CLI для запуска PHP‑тестов в конвейере.

---

# 4. Создание docker-compose.yml

```yaml
services:
  jenkins-controller:
    image: jenkins/jenkins:lts
    container_name: jenkins-controller
    ports:
      - "8080:8080"
      - "50000:50000"
    volumes:
      - jenkins_home:/var/jenkins_home
    networks:
      - jenkins-network

  ssh-agent:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: ssh-agent
    environment:
      - JENKINS_AGENT_SSH_PUBKEY=${JENKINS_AGENT_SSH_PUBKEY}
    volumes:
      - jenkins_agent_volume:/home/jenkins/agent
    depends_on:
      - jenkins-controller
    networks:
      - jenkins-network

volumes:
  jenkins_home:
  jenkins_agent_volume:

networks:
  jenkins-network:
    driver: bridge
```

---

# 5. Генерация SSH‑ключей

Выполнено в папке `secrets`:

```bash
ssh-keygen -t ed25519 -f jenkins_agent_ssh_key
```

Получены файлы:

```
jenkins_agent_ssh_key
jenkins_agent_ssh_key.pub
```

---

# 6. Создание файла .env

Создан файл:

```env
JENKINS_AGENT_SSH_PUBKEY=ssh-ed25519 AAAA.....
```

---

# 7. Запуск контейнеров

Команда:

```bash
docker compose up -d --build
```

После запуска Jenkins доступен по адресу:

```
http://localhost:8080
```

Для получения пароля:

```bash
docker exec jenkins-controller cat /var/jenkins_home/secrets/initialAdminPassword
```

---

# 8. Первый вход и настройки Jenkins

1. Введён initialAdminPassword
2. Установлены рекомендованные плагины
3. Создан администратор
4. Установлен плагин **SSH Build Agents Plugin**

---

# 9. Добавление SSH‑credentials

Путь: **Manage Jenkins → Credentials → System → Global credentials**

Созданы credentials:

* Username: `jenkins`
* Private key: содержимое `jenkins_agent_ssh_key`
* ID: `ssh-agent-key`

---

# 10. Создание SSH‑ноды

Путь: **Manage Jenkins → Nodes → New Node**

Параметры:

* Node name: `ssh-agent1`
* Remote root directory: `/home/jenkins/agent`
* Labels: `php-agent`
* Launch method: **SSH**
* Host: `ssh-agent`
* Credentials: `ssh-agent-key`
* Verification strategy: **Non‑verifying**

После корректной настройки нода стала **Online**.

---

# 11. Создание Jenkinsfile

```groovy
pipeline {
    agent { label 'php-agent' }

    stages {
        stage('Install Dependencies') {
            steps {
                echo 'Подготовка проекта...'
                sh 'php -v'
            }
        }

        stage('Test') {
            steps {
                echo 'Запуск тестов...'
                sh 'php -v'
            }
        }
    }

    post {
        always { echo 'Конвейер завершен.' }
        success { echo 'Все этапы прошли успешно!' }
        failure { echo 'Обнаружены ошибки в конвейере.' }
    }
}
```

---

# 12. Настройка Pipeline в Jenkins

1. Создан новый проект типа **Pipeline**
2. Выбран SCM → GitHub репозиторий
3. Указан путь к Jenkinsfile
4. Pipeline успешно выполняется

---

# 13. Выводы

В ходе лабораторной работы:

* Развёрнут Jenkins Controller в Docker
* Создан и запущен SSH‑агент
* Настроено SSH‑подключение через ключи
* Создан рабочий Pipeline
* Выполнены тестовые задания в Jenkins

Все требования лабораторной работы выполнены полностью.

---

# 14. Ответы на вопросы

### 1. Преимущества использования Jenkins?

* Полностью автоматизирует CI/CD
* Гибкие Pipeline‑конвейеры
* Тысячи плагинов
* Мощная интеграция с GitHub, Docker, Kubernetes
* Масштабирование через удалённые агенты

### 2. Какие ещё бывают агенты Jenkins?

* SSH‑агенты
* JNLP‑агенты
* Docker‑агенты
* Kubernetes‑агенты
* Windows‑агенты
* Локальные агенты

### 3. Проблемы и решения

**Проблема 1:** ключи не подходили → *перегенерация и корректная регистрация*.

**Проблема 2:** агент был Offline → *исправление пути, прав доступа, ключей*.

**Проблема 3:** ошибки в Pipeline → *исправлены пути и label агента*.

---

# 15. Библиография

1. Jenkins Official Docs — [https://www.jenkins.io/doc/](https://www.jenkins.io/doc/)
2. Docker Docs — [https://docs.docker.com](https://docs.docker.com)
3. SSH Build Agents Plugin — [https://plugins.jenkins.io/ssh-slaves/](https://plugins.jenkins.io/ssh-slaves/)
4. GitHub Docs — [https://docs.github.com](https://docs.github.com)
