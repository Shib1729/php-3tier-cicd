pipeline {
    agent any

    environment {
        AWS_REGION     = 'ap-south-1'
        ECR_REPOSITORY = 'php-3tier-app'
        EKS_CLUSTER    = 'php-3tier-cluster'
        HELM_RELEASE   = 'php-app'
        K8S_NAMESPACE      = 'php-app'
        MYSQL_ROOT_PASSWORD = credentials('mysql-root-password')
        MYSQL_APP_PASSWORD  = credentials('mysql-app-password')
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Prepare') {
            steps {
                script {
                    env.AWS_ACCOUNT_ID = sh(
                        script: 'aws sts get-caller-identity --query Account --output text',
                        returnStdout: true
                    ).trim()

                    env.ECR_REGISTRY =
                        "${env.AWS_ACCOUNT_ID}.dkr.ecr.${env.AWS_REGION}.amazonaws.com"

                    env.IMAGE_URI =
                        "${env.ECR_REGISTRY}/${env.ECR_REPOSITORY}:${env.BUILD_NUMBER}"
                }

                sh '''
                    echo "Build Number: ${BUILD_NUMBER}"
                    echo "ECR Repository: ${ECR_REPOSITORY}"
                    echo "Image: ${IMAGE_URI}"
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build \
                      -t ${ECR_REPOSITORY}:${BUILD_NUMBER} .
                '''
            }
        }

        stage('Login to ECR') {
            steps {
                sh '''
                    aws ecr get-login-password \
                      --region ${AWS_REGION} | \
                    docker login \
                      --username AWS \
                      --password-stdin ${ECR_REGISTRY}
                '''
            }
        }

        stage('Tag and Push Image') {
            steps {
                sh '''
                    docker tag \
                      ${ECR_REPOSITORY}:${BUILD_NUMBER} \
                      ${IMAGE_URI}

                    docker push ${IMAGE_URI}
                '''
            }
        }

        stage('Configure EKS') {
            steps {
                sh '''
                    aws eks update-kubeconfig \
                      --region ${AWS_REGION} \
                      --name ${EKS_CLUSTER}

                    kubectl get nodes
                '''
            }
        }

        stage('Deploy with Helm') {
            steps {
                sh '''
                    helm upgrade --install ${HELM_RELEASE} helm/php-app \
                      --namespace ${K8S_NAMESPACE} \
                      --create-namespace \
                      --set php.image.repository=${ECR_REGISTRY}/${ECR_REPOSITORY} \
                      --set php.image.tag=${BUILD_NUMBER} \
                      --set-string mysql.rootPassword="${MYSQL_ROOT_PASSWORD}" \
                      --set-string mysql.password="${MYSQL_APP_PASSWORD}" \
                      --wait \
                      --timeout 10m
                '''
            }
        }

        stage('Verify Deployment') {
            steps {
                sh '''
                    echo "===== Pods ====="
                    kubectl get pods -n ${K8S_NAMESPACE}

                    echo "===== Services ====="
                    kubectl get svc -n ${K8S_NAMESPACE}

                    echo "===== PVC ====="
                    kubectl get pvc -n ${K8S_NAMESPACE}

                    echo "===== Helm Release ====="
                    helm list -n ${K8S_NAMESPACE}
                '''
            }
        }
    }

    post {
        success {
            echo 'CI/CD Pipeline completed successfully.'
        }

        failure {
            echo 'CI/CD Pipeline failed. Check the failed stage above.'
        }
    }
}
