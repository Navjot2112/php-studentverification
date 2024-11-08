<?php
// Array of passwords (these should be the raw, unhashed passwords you want to hash)
$passwords = array(
    'navjot@2003',
    'singh@2003',
    'jandu@2003',
    'harwinder@2003'
);

// Corresponding usernames
$usernames = array(
    'dealing_hand',
    'section_incharge',
    'intermediatelevel',
    'dean_user'
);

// Corresponding levels
$levels = array(
    'level1',
    'level2',
    'intermediatelevel',
    'deanlevel'
);

// Iterate over the arrays and hash the passwords
foreach ($passwords as $index => $password) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $username = $usernames[$index];
    $level = $levels[$index];

    // Output the SQL insert statement
    echo "INSERT INTO users (username, password, level) VALUES ('$username', '$hashed_password', '$level');\n";
}
?>
