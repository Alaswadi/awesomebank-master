<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// -----------------------------------------------------------------------
// Vulnerability #15: JWT Algorithm Confusion (A02)
//
// This endpoint accepts three JWT algorithms:
//
//   alg=RS256  →  verifies using RSA public key (correct)
//   alg=HS256  →  VULNERABLE: uses the PUBLIC KEY PEM content as the
//                 HMAC-SHA256 secret. An attacker who has the public key
//                 (which may be exposed at /jwt_keys/public.pem) can craft
//                 a forged token signed with HS256 using that key as the secret.
//   alg=none   →  VULNERABLE: no verification at all (original bug preserved)
// -----------------------------------------------------------------------

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) { $data .= str_repeat('=', 4 - $remainder); }
    return base64_decode(strtr($data, '-_', '+/'));
}

$keyDir    = __DIR__ . '/jwt_keys';
$publicKey = $keyDir . '/public.pem';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (strpos($authHeader, 'Bearer ') === 0) {
        $jwt   = substr($authHeader, 7);
        $parts = explode('.', $jwt);

        if (count($parts) === 3) {
            $header  = json_decode(base64url_decode($parts[0]), true);
            $payload = json_decode(base64url_decode($parts[1]), true);
            $alg     = $header['alg'] ?? '';

            $verified = false;

            if ($alg === 'none') {
                // Vulnerability: no signature check
                $verified = true;

            } elseif ($alg === 'RS256') {
                // Correct path: verify RSA signature with public key
                if (file_exists($publicKey)) {
                    $pubKeyRes   = openssl_pkey_get_public(file_get_contents($publicKey));
                    $signingData = $parts[0] . '.' . $parts[1];
                    $signature   = base64url_decode($parts[2]);
                    $result      = openssl_verify($signingData, $signature, $pubKeyRes, OPENSSL_ALGO_SHA256);
                    $verified    = ($result === 1);
                }

            } elseif ($alg === 'HS256') {
                // VULNERABLE: uses the RSA public key PEM file as the HMAC secret.
                // An attacker who can read /jwt_keys/public.pem can compute a valid
                // HS256 signature for any payload they choose.
                if (file_exists($publicKey)) {
                    $secret      = file_get_contents($publicKey);   // <-- the bug
                    $signingData = $parts[0] . '.' . $parts[1];
                    $expected    = rtrim(strtr(base64_encode(hash_hmac('sha256', $signingData, $secret, true)), '+/', '-_'), '=');
                    $verified    = hash_equals($expected, $parts[2]);
                }
            }

            if ($verified && isset($payload['exp']) && time() < $payload['exp']) {
                echo json_encode([
                    "user_id"  => $payload['id'],
                    "username" => $payload['username'],
                    "role"     => $payload['role']
                ]);
                exit;
            }
        }
    }

    http_response_code(401);
    echo json_encode(["message" => "Invalid or expired token"]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only GET requests are allowed"]);
}
?>
