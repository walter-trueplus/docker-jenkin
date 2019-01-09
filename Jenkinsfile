pipeline {
    agent none
    stages {
        stage('Something always wrong, but true') {
            agent { docker 'nginx' }
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