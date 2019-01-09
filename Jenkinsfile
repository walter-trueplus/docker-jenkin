pipeline {
    agent {
        docker {
            image 'nginx:stable'
        }
    }
    stages {
        stage('Something always wrong, but true') {
            steps {
                sh '''
                service nginx status
                echo 'Tiep theo la man trinh dien nghe thuat'
                cat /etc/nginx/conf.d/default.conf
                '''
            }
        }
    }
}