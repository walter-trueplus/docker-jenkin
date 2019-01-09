pipeline {
    agent {
        docker {
            image 'nginx:stable'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'service nginx status'
            }
        }
    }
}