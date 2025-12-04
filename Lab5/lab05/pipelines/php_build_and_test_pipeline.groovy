pipeline {
    agent { label 'ssh-agent' }

    stages {

        stage('Clone PHP project') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/alexunderpitropov/Automation-Scripting.git'
            }
        }

        stage('Navigate to PHP project') {
            steps {
                sh '''
                    echo "Entering PHP project folder..."
                    cd Lab5/lab05/recipe-book
                    ls -la
                '''
            }
        }

        stage('Install dependencies') {
            steps {
                sh '''
                    echo "No Composer in this project — skipping installation"
                '''
            }
        }

        stage('Run tests') {
            steps {
                sh '''
                    echo "No PHPUnit tests in this project — skipping"
                '''
            }
        }

        stage('Build complete') {
            steps {
                echo "PHP project build pipeline completed successfully!"
            }
        }
    }
}
