# Chart HELM

to test locally with [minikube](https://minikube.sigs.k8s.io/docs/)

This chart requires the [CloudNativePG](https://cloudnative-pg.io/) operator and the
[External Secrets Operator](https://external-secrets.io/) (ESO) to be pre-installed in the
cluster: the CNPG owner role's password is generated once by ESO
(`templates/cnpg-app-secret.yaml`), never committed to git. Install both operators on minikube
before running `./test_minikube.sh`.

```bash
minikube start
minikube addons enable ingress
kubectx minikube
kubectl create ns plateforme-ebs
kubens plateforme-ebs
```

The app also reads its S3 storage credentials from a `bucket-credentials` Secret
(`AWS_ACCESS_KEY_ID`/`AWS_SECRET_ACCESS_KEY`/`AWS_ENDPOINT_URL`/`AWS_DEFAULT_REGION`/`BUCKET_APP`
keys), provisioned by infra (Tofu, `opentofu-scaleway-infra-mut`) on real clusters -- not by this
chart. Create it by hand in the `plateforme-ebs` namespace before deploying on minikube, e.g.:

```bash
kubectl create secret generic bucket-credentials \
  --from-literal=AWS_ACCESS_KEY_ID=minio \
  --from-literal=AWS_SECRET_ACCESS_KEY=miniosecret \
  --from-literal=AWS_ENDPOINT_URL=http://minio:9000 \
  --from-literal=AWS_DEFAULT_REGION=fr-par \
  --from-literal=BUCKET_APP=plateforme-ebs-app
```

Likewise, `SMS_DSN`/`PAYUM_APIKEY`/`PAYUM_GATEWAY` (and `MAILER_DSN` when `maildev.enabled` is
false) are read from an `external-secrets` Secret, provisioned by infra (Tofu) from Scaleway
Secret Manager on real clusters. Create it by hand too if you disable maildev on minikube:

```bash
kubectl create secret generic external-secrets \
  --from-literal=SMS_DSN="null://null" \
  --from-literal=PAYUM_APIKEY="CHANGEME!" \
  --from-literal=PAYUM_GATEWAY=mollie \
  --from-literal=MAILER_DSN=smtp://localhost:1025
```

get minikube ip via `minikube ip`

add in your `/etc/hosts` file:

```
192.168.x.x ebs.chart-example.local maildev.chart-example.local
```

Then run `./test_minikube.sh` to build prod images, push them to minikube and deploy the app with helm
