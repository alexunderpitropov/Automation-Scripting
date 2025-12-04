pipeline {
    agent { label 'ansible-agent' }

    stages {

        stage('Checkout repository') {
            steps {
                git url: 'https://github.com/alexunderpitropov/Automation-Scripting.git', branch: 'main'
            }
        }

        stage('Deploy PHP project') {
            steps {
                dir('Lab5/lab05/ansible') {
                    sh '''
                        echo "Running Ansible deploy..."
                        ansible-playbook -i hosts.ini deploy_php.yml
                    '''
                }
            }
        }
    }

    post {
        success { echo "PHP project deployed successfully!" }
        failure { echo "Deployment failed!" }
    }
}
