pipeline {
    agent {
        docker {
            image 'nginx:stable'
            args '-u root:root -v /var/run/docker.sock:/var/run/docker.sock'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'ls'
            }
        }
    }
}