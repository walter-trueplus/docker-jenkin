pipeline {
    agent {
        docker {
            image 'nginx:stable'
            args '-u root:root -v /var/run/docker.sock:/var/run/docker.sock -v $JENKINS_DATA:$JENKINS_DATA -e JENKINS_DATA=$JENKINS_DATA'
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