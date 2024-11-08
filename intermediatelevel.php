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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_nos = $_POST['roll_no'];
    $level1_comments = $_POST['level1_comments'];
    $level2_comments = $_POST['level2_comments'];
    $level3_comments = $_POST['level3_comments'];
    $verification_status = $_POST['verification_status'];
    $action = $_POST['action'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Prepare SQL statement
        $stmt = $conn->prepare('INSERT INTO level2_comments (roll_no, level1_comments, level2_comments, level3_comments, verification_status, action) 
                                VALUES (?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE level1_comments = VALUES(level1_comments), 
                                                        level2_comments = VALUES(level2_comments), 
                                                        level3_comments = VALUES(level3_comments), 
                                                        verification_status = VALUES(verification_status),
                                                        action = VALUES(action)');
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($roll_nos as $roll_no) {
            $prev_level1_comment = isset($level1_comments[$roll_no]) ? $level1_comments[$roll_no] : '';
            $prev_level2_comment = isset($level2_comments[$roll_no]) ? $level2_comments[$roll_no] : '';
            $comment = isset($level3_comments[$roll_no]) ? $level3_comments[$roll_no] : '';
            $status = isset($verification_status[$roll_no]) ? $verification_status[$roll_no] : 'Not Verified';
            $action_value = isset($action[$roll_no]) ? $action[$roll_no] : '';

            // Bind parameters and execute statement
            $stmt->bind_param("ssssss", $roll_no, $prev_level1_comment, $prev_level2_comment, $comment, $status, $action_value);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed for roll number $roll_no: " . $stmt->error);
            }
        }

        // Commit the transaction
        $conn->commit();
        $success_message = 'Verification status and comments updated successfully.';
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $error_message = "Transaction failed: " . $e->getMessage();
    }
}

// Fetch roll numbers and comments from students table
$query = "SELECT roll_no, level1_comments, level2_comments FROM level1_comments";
$result = $conn->query($query);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Level 3 Comments</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .container {
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Intermediate Level remarks</h2>
        <?php if ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <table>
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Dealing Hand 1 remarks</th>
                        <th>Section Incharge remarks</th>
                        <th>Intermediate Level remarks</th>
                        <th>Verification Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                                <td>
                                    <input type="hidden" name="level1_comments[<?php echo htmlspecialchars($row['roll_no']); ?>]" value="<?php echo htmlspecialchars($row['level1_comments']); ?>">
                                    <?php echo htmlspecialchars($row['level1_comments']); ?>
                                </td>
                                <td>
                                    <textarea name="level2_comments[<?php echo htmlspecialchars($row['roll_no']); ?>]"><?php echo htmlspecialchars($row['level2_comments']); ?></textarea>
                                </td>
                                <td>
                                    <textarea name="level3_comments[<?php echo htmlspecialchars($row['roll_no']); ?>]"></textarea>
                                </td>
                                <td>
                                    <input type="radio" name="verification_status[<?php echo htmlspecialchars($row['roll_no']); ?>]" value="Verified"> Verified
                                    <input type="radio" name="verification_status[<?php echo htmlspecialchars($row['roll_no']); ?>]" value="Not Verified"> Not Verified
                                </td>
                                <td>
                                    <input type="text" name="action[<?php echo htmlspecialchars($row['roll_no']); ?>]">
                                </td>
                                <input type="hidden" name="roll_no[]" value="<?php echo htmlspecialchars($row['roll_no']); ?>">
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br>
            <button type="submit">Submit</button>
            <br>
            <div class="logout-button-container">
            <a href="login.php"><button type="button" class="logout-button">Logout</button></a>
        </div>
        </form>
    </div>
</body>
</html>
