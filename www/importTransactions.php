<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vulnerability: XXE - XML External Entity Injection (A05)
// LIBXML_NOENT causes libxml to substitute entity references, including external entities.
// An attacker can define an external entity that reads local files:
//
// <?xml version="1.0" encoding="UTF-8"?>
// <!DOCTYPE foo [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>
// <transactions><transaction><amount>&xxe;</amount></transaction></transactions>
//
// This leaks the content of /etc/passwd inside the parsed XML output.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    // Accept raw XML body or a JSON wrapper with an `xml` field
    if (strpos($contentType, 'application/xml') !== false || strpos($contentType, 'text/xml') !== false) {
        $xmlData = file_get_contents('php://input');
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $xmlData = $input['xml'] ?? '';
    }

    if (!$xmlData) {
        http_response_code(400);
        echo json_encode(["message" => "XML data is required"]);
        exit;
    }

    // VULNERABLE: LIBXML_NOENT enables external entity substitution
    // libxml_disable_entity_loader() is NOT called — external entities are enabled
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOENT);

    if ($xml === false) {
        $errors = libxml_get_errors();
        $errMessages = array_map(fn($e) => trim($e->message), $errors);
        libxml_clear_errors();
        http_response_code(400);
        echo json_encode(["message" => "Invalid XML", "errors" => $errMessages]);
        exit;
    }

    // Convert parsed XML to array and return it (including any injected entity content)
    $transactions = [];
    foreach ($xml->transaction as $txn) {
        $transactions[] = [
            "amount"      => (string)($txn->amount ?? ''),
            "type"        => (string)($txn->type ?? ''),
            "description" => (string)($txn->description ?? ''),
            "date"        => (string)($txn->date ?? ''),
        ];
    }

    echo json_encode([
        "message"      => "Transactions imported successfully",
        "count"        => count($transactions),
        "transactions" => $transactions
    ]);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}
?>
