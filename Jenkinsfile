pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'sleep 3;service --status-all;service nginx status'
            }
        }
    }
}