# End-to-End CI/CD Pipeline for a 3-Tier PHP Application on AWS

## Technologies

- GitHub
- Jenkins
- Docker
- Amazon ECR
- Amazon EKS
- Kubernetes
- Helm
- MySQL
- Prometheus
- Grafana

## CI/CD Workflow

GitHub -> Jenkins -> Amazon ECR -> Helm -> Amazon EKS

## Application Architecture

PHP Application -> Kubernetes MySQL Service -> MySQL -> Persistent Volume

## Monitoring

Amazon EKS -> Prometheus -> Grafana
