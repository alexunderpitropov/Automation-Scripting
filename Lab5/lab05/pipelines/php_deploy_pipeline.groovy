pipeline {
    agent { label 'ansible-agent' }

    stages {
        stage('Clone PHP project') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/alexunderpitropov/Automation-Scripting.git'
            }
        }

        stage('Copy project to test server') {
            steps {
                sh """
                ansible-playbook -i Lab5/lab05/ansible/hosts.ini Lab5/lab05/ansible/deploy_php.yml
                """
            }
        }
    }

    post {
        success {
            echo "PHP project deployed successfully!"
        }
        failure {
            echo "PHP deployment FAILED!"
        }
    }
}
