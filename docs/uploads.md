# Uploads settings

To change the file upload max size, see:
 
- in `docker/php/conf.d/app.ini`
  - `upload_max_size`
  - `post_max_size` (if you need more than the 8M default)
- `nginx.ingress.kubernetes.io/proxy-body-size` annotation in `helm/chart/values.yaml`
