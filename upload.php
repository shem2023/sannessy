<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["message" => "Unauthorized access"]);
    exit();
}

$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["xray_image"])) {
    $file = $_FILES["xray_image"];
    $fileName = basename($file["name"]);
    $filePath = $uploadDir . $fileName;

    // File size limit (2MB)
    if ($file["size"] > 2 * 1024 * 1024) {
        echo json_encode(["message" => "File is too large. Max size is 2MB."]);
        exit();
    }

    // Allowed types
    $allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
    if (!in_array($file["type"], $allowedTypes)) {
        echo json_encode(["message" => "Invalid file type. Only JPG, JPEG, and PNG allowed."]);
        exit();
    }

    // Move file and save to database
    if (move_uploaded_file($file["tmp_name"], $filePath)) {
        $conn = new mysqli("localhost", "root", "", "easyscan");
        $stmt = $conn->prepare("INSERT INTO xray_images (file_name, file_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $fileName, $filePath);
        $stmt->execute();
        echo json_encode(["message" => "File uploaded successfully."]);
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["message" => "Failed to upload file."]);
    }
} else {
    echo json_encode(["message" => "No file uploaded."]);
}
?>
