pipeline {
    agent {
        docker {
            image 'httpd'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'ls -lah'
                sh 'pwd'
                sh 'ls -lah /home'
                sh 'service apache2 status'
                sh 'docker ps -a'
            }
        }
    }
}