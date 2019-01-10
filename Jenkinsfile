pipeline {
    agent { docker 'richxsl/rhel7' }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh 'ls -lah'
                docker ps
            }
        }
    }
}