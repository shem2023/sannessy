<?php
$conn = new mysqli("localhost", "root", "", "easyscan");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $message = "<p class='error'>Username already exists! Try another.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("Location: login.php?success=1");
            exit();
        } else {
            $message = "<p class='error'>Error: " . $conn->error . "</p>";
        }

        $stmt->close();
    }

    $checkStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="navbar">
    <a href="#">🏥 X-Ray Diagnosis System</a>
</div>

<form method="POST">
    <h2>Register</h2>
    <?php echo $message; ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login</a></p>
</form>

</body>
</html>
