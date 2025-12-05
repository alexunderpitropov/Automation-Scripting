pipeline {
    agent { label 'ssh-agent' }

    stages {

        stage('Clone PHP project') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/alexunderpitropov/Automation-Scripting.git'
            }
        }

        stage('Install dependencies') {
            steps {
                sh '''
                    cd Lab5/lab05/recipe-book
                    composer install
                '''
            }
        }

        stage('Run PHPUnit tests') {
            steps {
                sh '''
                    cd Lab5/lab05/recipe-book
                    ./vendor/bin/phpunit --testdox
                '''
            }
        }
    }
}
