<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studeninfo";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Initialize variables
$success_message = '';
$error_message = '';

// Check if the connection exists before making any queries
if ($conn) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the data from the form submission
        $roll_no = $_POST['roll_no'];
        $comments = $_POST['comments'];

        // Input validation
        if (empty($roll_no) || empty($comments)) {
            $error_message = 'Roll No and comments are required.';
        } else {
            // Use prepared statements to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO students (roll_no, level1_comments) VALUES (?, ?)");
            $stmt->bind_param("ss", $roll_no, $comments);

            if ($stmt->execute()) {
                $success_message = 'Student information and comments added successfully.';
            } else {
                $error_message = 'Failed to insert data: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
} else {
    $error_message = 'Database connection not established.';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Comments Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h2 {
            margin: 0 0 15px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 3px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            margin-bottom: 10px; /* Add space between submit button and logout button */

        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 10px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .logout-button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .logout-button-container {
            text-align: right;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Student Verification Form</h2>
      <!-- Logout Button -->
    <?php if ($success_message): ?>
        <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="roll_no">Roll No:</label>
        <input type="text" id="roll_no" name="roll_no" required>
        <label for="comments">Comments:</label>
        <textarea id="comments" name="comments" required></textarea>
        <button type="submit" name="submit">Submit</button>  <br>
        <div class="logout-button-container">
            <a href="login.php"><button type="button" class="logout-button">Logout</button></a>
        </div>
    </form>
</div>

</body>
</html>
