<?php

session_start();

// Change these
define("FORUM_URL", "https://forum.cfx.re");
define("REDIRECT_URL", "http://localhost:8000");
define("APP_NAME", "Your app");
define("APP_CLIENT_ID", "yourapp");

// Open our keypair
$keypair = openssl_pkey_get_private(file_get_contents("keypair.pem"));

if ($keypair === false)
{
    die("Failed to open keypair");
}

if (!isset($_GET["payload"]))
{
    // Get the public key which will be sent to Discourse
    $pub = openssl_pkey_get_details($keypair)["key"];
    
    // Generate a random nonce
    $nonce = bin2hex(random_bytes(16));
    $_SESSION["nonce"] = $nonce;

    // Redirect to the authorize page
    $query = http_build_query([
        "auth_redirect"     => REDIRECT_URL,
        "application_name"  => APP_NAME,
        "client_id"         => APP_CLIENT_ID,
        "scopes"            => "session_info",
        "nonce"             => $nonce,
        "public_key"        => $pub
    ]);

    $url = FORUM_URL . "/user-api-key/new?" . $query;

    header("Location: " . $url);
}
else
{
    // Decrypt the payload from Discourse with our pkeypair
    $payload = base64_decode($_GET["payload"]);

    if (openssl_private_decrypt($payload, $data, $keypair) === false)
    {
        die("Failed to decrypt payload");
    }

    if (($response = json_decode($data, false)) === false)
    {
        die("Failed to decode payload");
    }

    // Check the nonce
    if ($response->nonce != $_SESSION["nonce"])
    {
        die("Invalid nonce");
    }

    $key = $response->key;

    // Fetch the current session
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, FORUM_URL . "/session/current.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Api-Key: " . $key, "User-Api-Client-Id: " . APP_CLIENT_ID]);

    if (($body = curl_exec($ch)) == false)
    {
        curl_close($ch);

        die("Failed to get session information");
    }

    curl_close($ch);

    if (($session = json_decode($body, false)) === false)
    {
        die("Failed to decode session information");
    }

    // Display information about the current user
    echo "<pre>" . print_r($session->current_user, true) . "</pre>";
}
