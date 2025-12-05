pipeline {
    agent {
        label 'ansible-agent'    
    }

    stages {
        stage('Clone Ansible repository') {
            steps {
                git branch: 'main',
                    url: 'https://github.com/alexunderpitropov/Automation-Scripting.git'
            }
        }

        stage('Run Ansible Playbook') {
            steps {
                dir('Lab5/lab05/ansible') {
                    sh '''
                        ansible-playbook -i hosts.ini setup_test_server.yml
                    '''
                }
            }
        }
    }

    post {
        success {
            echo "Ansible test server setup completed successfully!"
        }
        failure {
            echo "Ansible setup FAILED!"
        }
    }
}
