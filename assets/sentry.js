import * as Sentry from '@sentry/browser'

// Le DSN est fourni au runtime par le layout (balises meta, alimentées par SENTRY_JS_DSN) :
// sans DSN, Sentry n'est pas initialisé et rien n'est envoyé.
const meta = (name) => document.querySelector(`meta[name="sentry-${name}"]`)?.getAttribute('content') || undefined

const dsn = meta('dsn')

if (dsn) {
  Sentry.init({
    dsn,
    environment: meta('environment'),
    release: meta('release'),
    sendDefaultPii: false,
    // Ni tracing, ni session replay.
    tracesSampleRate: 0,
  })
}
