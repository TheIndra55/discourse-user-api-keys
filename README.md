# Discourse User API Keys

Minimal example for implementing Discourse User API keys in PHP.

## Keypair

Discourse redirects the user back with an encrypted payload, encrypted using your keypair public key.
To generate a keypair run the following openssl command:

```
openssl genrsa -out keypair.pem 2048
```
