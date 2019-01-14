pipeline {
    agent any
    stages {
        stage('Something always wrong, but true') {
            agent {
                docker {
                    image 'marstrueplus/apache2-php7.0.14:v1'
                }
            }
            steps {
                sh 'ls -lah'
                sh 'apache2 -V'
                sh 'ls -lah /home'
            }
        }
    }
}