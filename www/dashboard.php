<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (strpos($authHeader, 'Bearer ') === 0) {
        $jwt = substr($authHeader, 7); // Extract the token
        $parts = explode('.', $jwt);

        if (count($parts) === 3) {
            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);

            if ($header['alg'] === 'none' && time() < $payload['exp']) {
                echo json_encode([
                    "user_id" => $payload['id'],
                    "username" => $payload['username'],
                    "role" => $payload['role'] // Include the role in the response
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
