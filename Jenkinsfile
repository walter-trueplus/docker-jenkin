pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'pwd; ls -lah;sleep 3;service --status-all;service nginx status'
            }
        }
    }
}