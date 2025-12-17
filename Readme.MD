# Installation

```console
docker compose up -d --wait
docker compose exec web php bin/console doctrine:schema:drop --force
docker compose exec web php bin/console doctrine:schema:update --force
docker compose exec web php bin/console doctrine:fixtures:load --no-interaction
```

## Run this if you need to trust tls certificates

### Linux
```console
docker compose cp web:/data/caddy/pki/authorities/local/root.crt /usr/local/share/ca-certificates/root.crt && sudo update-ca-certificates
```

### Mac
```console
docker compose cp web:/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt
```

### Windows
```console
docker compose cp web:/data/caddy/pki/authorities/local/root.crt %TEMP%/root.crt && certutil -addstore -f "ROOT" %TEMP%/root.crt
```

# Api usage

api doc: `https://localhost/api/doc`

