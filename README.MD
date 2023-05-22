# JWT authentication bundle for Symfony

## 1. Installation:
```bash
composer require dimkinthepro/jwt-auth-bundle
```

```php
# config/bundles.php

return [
#...
    \JwtAuthBundle::class => ['all' => true],
];
```

```dotenv
# .env

#...
DIMKINTHEPRO_JWT_AUTH_PUBLIC_KEY=%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/public.pem
DIMKINTHEPRO_JWT_AUTH_PRIVATE_KEY=%kernel.project_dir%/var/dimkinthepro/jwt-auth-bundle/private.pem
DIMKINTHEPRO_JWT_AUTH_PASSPHRASE=SomeRandomPassPhrase
```

```yaml
# config/packages/security.yaml

security:
  #...
  main:
      lazy: true
      auth_jwt: ~
      pattern: ^/api/
      stateless: true
      provider: app_user_provider
      json_login:
          check_path: /api/user/login
          username_path: email
          success_handler: DimkinThePro\JwtAuth\Infrastructure\Security\SuccessAuthenticationHandler
          failure_handler: DimkinThePro\JwtAuth\Infrastructure\Security\FailAuthenticationHandler
```

```yaml
# config/packages/doctrine.yaml
doctrine:
    #...
    orm:
        #...
        mappings:
            #...
            JwtAuthBundle:
                is_bundle: true
                type: xml
                dir: Domain/Entity
```

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        default_bus: query_bus
        buses:
            query_bus:
            command_bus:
                middleware:
                    - doctrine_transaction
#...
```

### 2. Generate key pair:
```bash
bin/console dimkinthepro:jwt-auth:generate-key-pair
```
