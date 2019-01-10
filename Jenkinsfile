pipeline {
    agent {label 'something like that'}
    stages {
        stage('Something always wrong, but true') {
            agent {
                docker {
                    label 'something like that'
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