pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            agent {
                docker {
                    image 'httpd'
                }
            }
            steps {
                sh 'ls -lah'
                sh 'apache2 -V'
                sh 'ls -lah /home'
            }
        }
    }
}