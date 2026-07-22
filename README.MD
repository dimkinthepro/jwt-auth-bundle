# JWT authentication bundle for Symfony

### 1. Installation:
```bash
composer require dimkinthepro/jwt-auth-bundle
```

### 2. Check bundles config:
```php
# config/bundles.php

return [
#...
    Dimkinthepro\JwtAuth\DimkintheproJwtAuthBundle::class => ['all' => true],
];
```

### 3. Create bundle configuration:
```yaml
# config/packages/dimkinthepro_jwt_auth.yaml
dimkinthepro_jwt_auth:
    public_key_path: '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/public.pem'
    private_key_path: '%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/private.pem'
    passphrase: 'SomeRandomPassPhrase' # required, keep it stable: changing it invalidates all issued tokens
    token_ttl: 900 # 15 minutes
    algorithm: 'RS512'
    refresh_token_ttl: 2592000 # 1 month
    refresh_token_length: 128 # random bytes of token entropy; only a sha256 hash is stored in the DB
    issuer: 'my-app' # optional "iss" claim; omitted and not validated when null
    audience: 'my-client' # optional "aud" claim; omitted and not validated when null
    clock_skew_leeway: 60 # tolerated clock skew in seconds for "exp"/"nbf"/"iat" validation
    blocklist: # instant access token revocation by the "sid" claim
        enabled: false # costs one cache lookup per authenticated request
        cache_pool: 'cache.app' # PSR-6 pool; entries expire together with the tokens they block
    token_extractors: # enabled extractors are chained in this priority order
        authorization_header:
            enabled: true # "Authorization: Bearer <token>"
        split_cookie:
            enabled: false # "header.payload" in a JS-readable cookie + signature in an HttpOnly cookie
            payload_cookie_name: 'jwt_hp'
            signature_cookie_name: 'jwt_sig'
        cookie:
            enabled: false # whole token in a single cookie
            name: 'jwt_token'
        query_parameter:
            enabled: false # for WebSocket/SSE only: tokens in URLs leak into access logs
            name: 'jwt_token'
```

### 4. Add security configuration
```yaml
# config/packages/security.yaml

security:
  #...
  main:
      lazy: true
      auth_jwt: ~
      pattern: ^/api/
      stateless: true
      provider: your_app_user_provider
      json_login:
          check_path: /api/user/login
          username_path: email
          success_handler: Dimkinthepro\JwtAuth\Infrastructure\Security\SuccessAuthenticationHandler
          failure_handler: Dimkinthepro\JwtAuth\Infrastructure\Security\FailAuthenticationHandler
```

### 5. Add doctrine configuration
```yaml
# config/packages/doctrine.yaml
doctrine:
    #...
    orm:
        #...
        mappings:
            #...
            DimkintheproJwtAuthBundle:
                is_bundle: true
                type: xml
                prefix: Dimkinthepro\JwtAuth\Domain\Entity
```

### 6. Add Routes
```yaml
# config/routes.yaml
api_login:
  path: /api/login
  methods: [POST]

api_token_refresh:
  path: /api/token-refresh
  controller: Dimkinthepro\JwtAuth\Infrastructure\Controller\TokenRefreshAction
  methods: [POST]

api_sessions_list:
  path: /api/sessions
  controller: Dimkinthepro\JwtAuth\Infrastructure\Controller\SessionListAction
  methods: [GET]

api_session_revoke:
  path: /api/sessions/{sessionId}
  controller: Dimkinthepro\JwtAuth\Infrastructure\Controller\SessionRevokeAction
  methods: [DELETE]
```

### 7. Generate migrations:
```bash
php bin/console doctrine:migrations:diff

php bin/console doctrine:migrations:migrate
```

### 8. Generate key pair:
```bash
php bin/console dimkinthepro:jwt-auth:generate-key-pair
```

### 9. Schedule expired refresh tokens purge (e.g. daily cron):
```bash
php bin/console dimkinthepro:jwt-auth:purge-expired-refresh-tokens
```

### 10. Add custom JWT claims (optional):
Listen to `JwtTokenCreatedEvent` — reserved claims (`identifier`, `iat`, `exp`) cannot be overridden.
```php
use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserRolesClaimsListener
{
    public function __invoke(JwtTokenCreatedEvent $event): void
    {
        $event->setClaims($event->getClaims() + ['role' => 'admin']);
    }
}
```
Read the claims back from the verified token:
```php
use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenDecoder;

$jwtToken = $jwtTokenDecoder->decodeTokenFromString($encodedToken);
$role = $jwtToken->getClaim('role');
```

### 11. Hook into the token lifecycle with events (optional):
| Event | When | What listeners can do |
|---|---|---|
| `JwtTokenCreatedEvent` | before the token is signed | adjust claims (`getClaims()`/`setClaims()`) |
| `JwtTokenDecodedEvent` | after a token passed validation | run extra checks, `markAsInvalid()` to reject |
| `JwtTokenAuthenticatedEvent` | request authenticated with a JWT | add passport attributes from token claims |
| `JwtAuthenticationSuccessEvent` | successful login, before the response | enrich response data (`getData()`/`setData()`) |
| `JwtTokenNotFoundEvent` | protected endpoint hit without a token | replace the default 401 response |
| `JwtTokenInvalidEvent` | authentication failed: bad token | replace the default 401 response |
| `JwtTokenExpiredEvent` | authentication failed: expired token | replace the default 401 response |

The header and the reserved claims (`identifier`, `iat`, `exp`) cannot be changed from listeners.
```php
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtAuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class EnrichLoginResponseListener
{
    public function __invoke(JwtAuthenticationSuccessEvent $event): void
    {
        $event->setData($event->getData() + ['userEmail' => $event->getUser()->getUserIdentifier()]);
    }
}
```

### 12. Device sessions:
Every refresh token represents a device session. On login the bundle captures the optional
`deviceName` field of the JSON body (native clients know their exact model), the `User-Agent`
header and the client IP:
```json
{ "email": "user@example.com", "password": "...", "deviceName": "iPhone 13 Pro" }
```
The session identity (`sessionId`, `createdAt`, `deviceName`) survives token rotation;
`lastUsedAt` is updated on every refresh, and each issued JWT carries its session id
in the `sid` claim.

`GET /api/sessions` (authenticated) returns the devices of the current user, marking the session
the request was made from:
```json
{ "data": { "sessions": [ {
    "sessionId": "1f0d…", "deviceName": "iPhone 13 Pro", "userAgent": "…", "ip": "…",
    "createdAt": "2026-07-06T10:00:00+00:00", "lastUsedAt": "2026-07-06T12:30:00+00:00",
    "current": true
} ] } }
```
`DELETE /api/sessions/{sessionId}` revokes a session (204; foreign or unknown ids give 404),
`DELETE /api/sessions` revokes every session of the user (e.g. on account compromise).

Without the blocklist a revoked device keeps access until its short-lived JWT expires;
with `blocklist.enabled: true` the outstanding access tokens die instantly.

With the blocklist enabled every token must carry the `sid` claim: a token without a session id
could never be revoked, so it is rejected. Tokens issued by the login and refresh endpoints always
have it; when creating tokens manually, pass a session id to `JwtTokenManager::create()`.

### 13. Split cookies for browser SPAs (optional):
Enable the `split_cookie` extractor and set the cookies on login with a `kernel.response` listener —
the signature cookie is `HttpOnly`, so an XSS attack can never read a complete usable token:
```php
use Symfony\Component\HttpFoundation\Cookie;

$signatureOffset = (int) strrpos($encodedToken, '.');
$response->headers->setCookie(
    Cookie::create('jwt_hp', substr($encodedToken, 0, $signatureOffset))->withHttpOnly(false)->withSecure(true)
);
$response->headers->setCookie(
    Cookie::create('jwt_sig', substr($encodedToken, $signatureOffset + 1))->withHttpOnly(true)->withSecure(true)
);
```
