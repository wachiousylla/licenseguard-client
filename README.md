# LicenseGuard — client Laravel

Vérification de **licence / abonnement** pour une application Laravel : un middleware
interroge un **serveur de licence central** (appel signé HMAC), met le résultat en
cache, et **bloque le site** (page propre) si la licence n'est pas active.

- Compatible **Laravel 10, 11 et 12**, PHP 8.1+.
- **Auto-découverte** : provider, alias de middleware `license`, config et vue de
  blocage disponibles dès l'installation — rien à enregistrer à la main.
- **Fail-open** par défaut : si le serveur est injoignable, le site reste ouvert.
- Un résultat **autorisé** est mis en cache (6 h) ; un état **bloqué** est
  re-vérifié à chaque chargement (réactivation immédiate après paiement).

---

## 1. Installation

### Dépôt Git (prod)
```json
"repositories": [ { "type": "vcs", "url": "git@github.com:VOTRE-ORG/licenseguard-client.git" } ]
```
```bash
composer require licenseguard/laravel-client
```

### Dépôt « path » (dev local)
```json
"repositories": [ { "type": "path", "url": "../licenseguard-client" } ]
```
```bash
composer require "licenseguard/laravel-client:@dev"
```

---

## 2. Variables `.env` — c'est la SEULE chose à renseigner

```env
LICENSE_API_URL=http://localhost:8000/api/license/check
LICENSE_KEY=LIC-XXXX-XXXX-XXXX-XXXX
LICENSE_SECRET=le_secret_hmac_du_site
```

Options facultatives (défauts sûrs, à ne changer qu'au besoin) :
```env
LICENSE_ENABLED=true
LICENSE_FAIL_OPEN=true
LICENSE_CACHE_HOURS=6
LICENSE_TIMEOUT=5
```

> Le **domaine** de l'app (`request()->getHost()`) doit correspondre au domaine
> déclaré côté serveur de licence, sinon l'API renvoie 403.

---

## 3. Protéger les routes

Le package fournit l'alias `license`.

`routes/web.php` :
```php
Route::middleware(['web', 'license'])->group(function () {
    // routes à protéger
});
```
Ou globalement (`bootstrap/app.php`, Laravel 11/12) :
```php
$middleware->web(append: [
    \LicenseGuard\Client\Http\Middleware\CheckLicense::class,
]);
```
> login/logout/register/password/up/webhooks/assets sont déjà exclus (clé `except`).

---

## 4. Vérifier

```bash
php artisan license:check --fresh
```
Attendu si actif :
```
Statut   : active
Autorisé : OUI
Message  : Licence active
```

---

## 5. Personnalisation (optionnel)

```bash
php artisan vendor:publish --tag=license-config   # config/license.php
php artisan vendor:publish --tag=license-views    # resources/views/vendor/license/blocked.blade.php
```

---

## Contrat serveur

`POST {LICENSE_API_URL}` reçoit :
```json
{ "license_key":"...", "domain":"...", "app_url":"...",
  "laravel_version":"...", "php_version":"...",
  "timestamp":"ISO-8601", "signature":"hmac_sha256" }
```
Chaîne canonique signée (HMAC-SHA256, secret partagé) :
```
license_key | domain | app_url | laravel_version | php_version | timestamp
```
Réponse JSON attendue :
```json
{ "allowed": true, "status": "active", "message": "Licence active", "renewal_url": "https://..." }
```
`renewal_url` (optionnel) s'affiche en bouton sur la page de blocage.

## Licence
MIT
