<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database credentials
$servername = "db";
$username   = "user";
$password   = "password";
$dbname     = "simple_app";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit;
}

// -----------------------------------------------------------------------
// Vulnerability #15: JWT Algorithm Confusion (A02)
// login.php now creates RS256 tokens by default (using an on-disk RSA key).
// dashboard.php is vulnerable: when alg=HS256 it uses the PUBLIC KEY as the
// HMAC secret — an attacker who knows the public key can forge tokens.
// -----------------------------------------------------------------------

$keyDir     = __DIR__ . '/jwt_keys';
$privateKey = $keyDir . '/private.pem';
$publicKey  = $keyDir . '/public.pem';

// Generate RSA key pair on first run (stored in plaintext on disk — also a finding)
if (!file_exists($privateKey)) {
    if (!is_dir($keyDir)) { mkdir($keyDir, 0755, true); }
    $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($res, $privPem);
    $pubDetails = openssl_pkey_get_details($res);
    $pubPem     = $pubDetails['key'];
    file_put_contents($privateKey, $privPem);
    file_put_contents($publicKey,  $pubPem);
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Creates a proper RS256-signed JWT (default, secure path)
function createRS256JWT($payload, $privateKeyPath) {
    $header    = base64url_encode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $body      = base64url_encode(json_encode($payload));
    $signing   = "$header.$body";
    $privKey   = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    openssl_sign($signing, $signature, $privKey, OPENSSL_ALGO_SHA256);
    return "$signing." . base64url_encode($signature);
}

// Legacy "none" algorithm — kept for backward compat (Vulnerability #1 from original)
function createNoneJWT($payload) {
    $header = base64url_encode(json_encode(["alg" => "none", "typ" => "JWT"]));
    $body   = base64url_encode(json_encode($payload));
    return "$header.$body.";
}

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username'], $input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    $uname = $input['username'];
    $pass  = $input['password'];

    // Vulnerability: SQL Injection — no parameterized query
    // Vulnerability #10: compares against MD5 hash
    $hashedPass = md5($pass);
    $sql        = "SELECT * FROM users WHERE username = '$uname' AND password = '$hashedPass'";
    $result     = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $payload = [
            "id"       => $user['id'],
            "username" => $user['username'],
            "role"     => $user['role'],
            "iat"      => time(),
            "exp"      => time() + 3600
        ];

        // Issue RS256 token by default
        $jwt = createRS256JWT($payload, $privateKey);

        $response = [
            "message" => "Login successful!",
            "token"   => $jwt
        ];

        // Vulnerability #7: Open Redirect
        // If a `redirect` field is present in the request body, it is returned
        // verbatim in the response as `redirect_url`.  The front-end (login.html)
        // reads this field and calls window.location.href = data.redirect_url
        // without any validation, allowing redirect to arbitrary external URLs.
        if (!empty($input['redirect'])) {
            $response['redirect_url'] = $input['redirect'];
        }

        echo json_encode($response);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid username or password"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
