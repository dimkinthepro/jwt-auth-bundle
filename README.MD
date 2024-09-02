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
    passphrase: 'SomeRandomPassPhrase'
    token_ttl: 36000 # 10 hour
    algorithm: 'RS512'
    refresh_token_ttl: 2592000 # 1 month
    refresh_token_length: 128 # max=255
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
