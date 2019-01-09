pipeline {
    agent {
        docker {
            image 'nginx'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'ls -lah'
                sh 'pwd'
                sh 'ls -lah /home'
                sh 'docker ps -a'
                sh 'service nginx status'
            }
        }
    }
}