#!/bin/bash

set -e
# go to repo root
cd $(dirname $0)/../../

# build all images, without dev overrides
docker compose -f docker-compose.yml build

# tag image with sha to force rollingUpdate
php_sha=$(docker inspect plateformcoop-ebs-php --format='{{.Id}}' | cut -d: -f2)
caddy_sha=$(docker inspect plateformcoop-ebs-caddy --format='{{.Id}}' | cut -d: -f2)
docker tag plateformcoop-ebs-php plateformcoop-ebs-php:$php_sha
docker tag plateformcoop-ebs-caddy plateformcoop-ebs-caddy:$caddy_sha

# push images to minikube
#minikube image load plateformcoop-ebs-php:$php_sha
#minikube image load plateformcoop-ebs-caddy:$caddy_sha
for image in plateformcoop-ebs-php:$php_sha plateformcoop-ebs-caddy:$caddy_sha; do
  minikube image ls | grep $image || minikube image load $image
done

# install or update deployment on minikube
helm upgrade --install demo ./helm/chart \
  --kube-context minikube \
  --namespace plateformcoop-ebs --create-namespace \
  --atomic \
  --wait \
  --debug \
  -f ./helm/chart/values-minikube.yml \
  --set   php.image.tag=$php_sha \
  --set caddy.image.tag=$caddy_sha

MINIKUBE_IP=$(minikube ip)
if ! grep -E "^$MINIKUBE_IP\s+(.+\s+)?ebs.chart-example.local" /etc/hosts; then
	echo Execute \"echo $MINIKUBE_IP ebs.chart-example.local \| sudo tee -a /etc/hosts\"
	exit=1
fi
if ! grep -E "^$MINIKUBE_IP\s+(.+\s+)?maildev.chart-example.local" /etc/hosts; then
	echo Execute \"echo $MINIKUBE_IP maildev.chart-example.local \| sudo tee -a /etc/hosts\"
	exit=1
fi

if [ -n "$exit" ]; then
    exit 1
fi

open http://ebs.chart-example.local
open http://maildev.chart-example.local
