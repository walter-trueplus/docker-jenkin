pipeline {
    agent {
        docker {
            image 'magestore/compose'
        }
    }
    environment {
        CI = 'true'
    }
    stages {
        stage('PullCode') {
            steps {
                sh './.jenkins/pull.sh'
            }
        }
        stage('UnitTest') {
            steps {
                sh './.jenkins/client-test.sh'
            }
        }
        stage('Build') {
            steps {
                sh './.jenkins/build.sh'
            }
        }
        stage('CheckCode') {
            steps {
                sh './.jenkins/magento-standard.sh'
            }
        }
        stage('Di Compile') {
            steps {
                sh './.jenkins/magento-di-compile.sh'
            }
        }
        stage('APITest') {
            steps {
                sh './.jenkins/api-test.sh'
            }
        }
        stage('UATest') {
            steps {
                sh './.jenkins/test.sh'
            }
        }
        stage('Deliver') {
            steps {
                sh './.jenkins/deliver.sh'
            }
        }
    }
}
