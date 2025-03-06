<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "easyscan");

$message = "";

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["xray_image"])) {
    $targetDir = "uploads/";  // Directory to store uploaded images
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);  // Create folder if it doesn’t exist
    }

    $fileName = basename($_FILES["xray_image"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;  // Unique filename
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file formats
    $allowedTypes = array("jpg", "jpeg", "png", "gif");
    if (in_array($fileType, $allowedTypes)) {
        if ($_FILES["xray_image"]["size"] <= 5 * 1024 * 1024) { // 5MB limit
            if (move_uploaded_file($_FILES["xray_image"]["tmp_name"], $targetFilePath)) {
                // Save file path in database
                $stmt = $conn->prepare("INSERT INTO xray_images (user_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $_SESSION["user_id"], $targetFilePath);

                if ($stmt->execute()) {
                    $message = "<p class='success'>Image uploaded successfully!</p>";
                } else {
                    $message = "<p class='error'>Database error: " . $conn->error . "</p>";
                }

                $stmt->close();
            } else {
                $message = "<p class='error'>Error uploading file.</p>";
            }
        } else {
            $message = "<p class='error'>File size exceeds 5MB limit.</p>";
        }
    } else {
        $message = "<p class='error'>Invalid file format. Only JPG, JPEG, PNG, and GIF allowed.</p>";
    }
}

// Fetch uploaded X-rays
$result = $conn->query("SELECT file_path FROM xray_images WHERE user_id = " . $_SESSION["user_id"]);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
    <a href="dashboard.php">🏥 X-Ray Diagnosis System</a>
    <a href="logout.php">Logout</a>
</div>

<div class="dashboard-container">
    <h2>Upload X-ray Image</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="xray_image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <?php echo $message; ?>

    <h2>Your Uploaded X-rays</h2>
    <div class="image-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="xray-item">
                <img src="<?php echo $row['file_path']; ?>" alt="X-ray Image">
            </div>
        <?php endwhile; ?>
    </div>

    <div class="diagnosis-container">
        <h3>Diagnosis Result</h3>
        <p id="diagnosis-text">Awaiting model analysis...</p>
    </div>
</div>

</body>
</html>
