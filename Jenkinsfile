pipeline {
    agent {
        docker {
            image 'httpd'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            agent { docker 'nginx:latest' }
            steps {
                sh 'ls -lah'
                sh 'pwd'
                sh 'ls -lah /home'
                sh 'docker ps -a'
                sh 'service apache2 status'
            }
        }
    }
}