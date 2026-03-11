<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$uploadDir = __DIR__ . '/uploads/';

// Check if the uploads directory exists, if not, create it
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Create the directory with default permissions
}

// Change the permissions to 777 for the uploads directory
chmod($uploadDir, 0777);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        echo json_encode(["message" => "No file uploaded."]);
        exit;
    }

    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $fileType = $file['type'];
    $fileTmpPath = $file['tmp_name'];

    // Fake server-side validation
    $validExtensions = ["image/jpeg", "image/png", "image/gif"];
    if (!in_array($fileType, $validExtensions)) {
        echo json_encode(["message" => "Only image files are allowed!"]);
        exit;
    }

    // Save the uploaded file
    $uploadPath = $uploadDir . $fileName;
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        echo json_encode(["message" => "File uploaded successfully!", "path" => $uploadPath]);
    } else {
        echo json_encode(["message" => "Failed to upload file."]);
    }
} else {
    echo json_encode(["message" => "Invalid request method."]);
}
