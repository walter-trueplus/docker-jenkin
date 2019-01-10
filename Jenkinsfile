pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            agent {
                docker {
                    image 'maven:3-alpine'
                }
            }
            steps {
                sh 'ls -lah'
                sh 'mvn --version'
            }
        }
    }
}