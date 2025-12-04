pipeline {
    agent { label 'ssh-agent' }

    stages {

        stage('Checkout PHP project') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/YOUR_USERNAME/YOUR_PHP_PROJECT.git'
            }
        }

        stage('Install dependencies') {
            steps {
                sh 'composer install'
            }
        }

        stage('Run PHPUnit tests') {
            steps {
                sh './vendor/bin/phpunit --testdox --log-junit test-results.xml'
            }
        }

        stage('Archive test results') {
            steps {
                junit 'test-results.xml'
                archiveArtifacts artifacts: 'test-results.xml', fingerprint: true
            }
        }
    }

    post {
        always {
            echo "Pipeline finished."
        }
    }
}
