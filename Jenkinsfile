pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            agent {
                docker {
                    image 'nginx:1.14'
                }
            }
            steps {
                sh 'ls -lah'
                sh 'nginx --version'
            }
        }
    }
}