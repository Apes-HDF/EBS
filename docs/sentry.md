# Suivi des erreurs avec Sentry

Sentry est installé côté back (`sentry/sentry-symfony`, config dans `config/packages/sentry.yaml`)
et côté navigateur (`@sentry/browser`, `assets/sentry.js`, chargé par `assets/app.js`).
**Rien n'est actif tant que le DSN correspondant est vide ou absent** : dev local, CI et tests
n'envoient rien. Les DSN ne sont jamais dans le dépôt (public) : ils viennent de l'environnement.

## Variables d'environnement

| Variable | Côté | Rôle | Défaut |
|---|---|---|---|
| `SENTRY_DSN` | back | DSN du projet Sentry `ebs`. Vide = back inactif. | vide |
| `SENTRY_JS_DSN` | front | DSN du projet Sentry `ebs-js`. Vide = front inactif. Lu **au runtime** (balise `<meta name="sentry-dsn">` du layout), pas au build. | vide |
| `SENTRY_ENVIRONMENT` | back | Environnement (`production`, `nonprod`). | `APP_ENV` |
| `SENTRY_JS_ENVIRONMENT` | front | Idem pour le front. | `APP_ENV` |
| `SENTRY_RELEASE` | back + front | Release (le chart Helm y met le tag de l'image : `nonprod-<sha>` ou `prod-<sha>`). | vide |
| `SENTRY_TRACES_SAMPLE_RATE` | back | Taux de tracing (0 à 1). | `0` |

Le front n'active ni tracing ni session replay, et aucun des deux côtés n'envoie de données
personnelles (`send_default_pii: false`). Les erreurs HTTP 4xx, les accès refusés et les échecs
d'authentification sont filtrés (`App\Sentry\BeforeSendCallback`).

## Où les définir

* Déploiement Kubernetes : les clés `SENTRY_DSN` et `SENTRY_JS_DSN` se créent dans le Secret
  `external-secrets` (voir `helm/chart/values.yaml`). Elles sont optionnelles : sans elles, le pod
  démarre et Sentry reste éteint. `SENTRY_ENVIRONMENT`, `SENTRY_JS_ENVIRONMENT` et `SENTRY_RELEASE`
  sont posées par le chart (`php.sentryEnvironment` dans `values-nonprod.yaml` / `values-prod.yaml`).
* Local : `.env.local`, avec un DSN de test si besoin. Vérification du back : `bin/console sentry:test`.
