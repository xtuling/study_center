#!groovy

node {
    stage('SCM') {
        git 'git@gitlab.vchangyi.com:php/studycenter-php.git'
    }
    stage('SonarQube analysis') {
        // requires SonarQube Scanner 2.8+
        def scannerHome = tool 'SonarQube Scanner';
        withSonarQubeEnv('SonarQube Server') {
            sh "${scannerHome}/bin/sonar-scanner"
        }
    }
}
