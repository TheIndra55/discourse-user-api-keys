# Discourse User API Keys

A minimal example of implementing Discourse User API keys in PHP.

![image](https://user-images.githubusercontent.com/15322107/194785883-9dcd2d10-f097-4108-9d3f-77ff8c478191.png)

## Keypair

Discourse redirects the user back with an encrypted payload, which will be encrypted with your keypair public key.
To generate a keypair run the following OpenSSL command:

```
openssl genrsa -out keypair.pem 2048
```
