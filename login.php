<?php
// Configuration
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

// Login form submission
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Input validation
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            if (password_verify($password, $user_data['password'])) {
                // Login successful, redirect to corresponding page
                switch ($user_data['level']) {
                    case 'level1':
                        header('Location: level1.php');
                        break;
                    case 'level2':
                        header('Location: level2.php');
                        break;
                    case 'intermediatelevel':
                        header('Location: intermediatelevel.php');
                        break;
                    case 'deanlevel':
                        header('Location: deanlevel.php');
                        break;
                    default:
                        $error = 'Invalid user role';
                        break;
                }
                exit;
            } else {
                // Login failed, display error message
                $error = 'Invalid username or password';
            }
        } else {
            // Login failed, display error message
            $error = 'Invalid ';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" name="submit" value="Login">
    <?php if (isset($error)) { echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>'; } ?>
</form>
