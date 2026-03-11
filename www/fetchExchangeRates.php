<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $url = $input['url'] ?? null;

    if (!$url) {
        http_response_code(400);
        echo json_encode(["message" => "URL is required"]);
        exit;
    }

    // Vulnerability: No validation of the input URL
    try {
        $content = file_get_contents($url); // Fetch data from the provided URL
        if (!$content) {
            http_response_code(400);
            echo json_encode(["message" => "Failed to fetch the URL content"]);
            exit;
        }

        // Check if the response is JSON
        $jsonData = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // If it's valid JSON, return it
            echo json_encode([
                "message" => "Fetched JSON successfully",
                "data" => $jsonData
            ]);
        } else {
            // Return the plain text content
            echo json_encode([
                "message" => "Fetched plain text successfully",
                "data" => $content
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "An error occurred while fetching the URL"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}
