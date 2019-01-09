pipeline {
    agent { docker { image 'maven:3.3.3' } }
    stages {
        stage('build') {
            steps {
                sh 'mvn --version'
            }
        }
	stage('Just list folder structure') {
	    steps {
		sh 'ls -lah'
	    }
	}
    }
}
