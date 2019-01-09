pipeline {
    agent {
        docker {
            image 'nginx'
            args '-v /var/run/docker.sock:/var/run/docker.sock'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'sleep 3;service --status-all;service nginx status'
            }
        }
    }
}