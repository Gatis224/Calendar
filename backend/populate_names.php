<?php
include '/var/www/html/config.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_create_table = "CREATE TABLE IF NOT EXISTS names (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    day INT(2) NOT NULL,
    month INT(2) NOT NULL
)";

if ($conn->query($sql_create_table) === TRUE) {
    echo "Table names created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$jsonData = file_get_contents('vardadienas.json');

$data = json_decode($jsonData, true);

foreach ($data as $month => $monthData) {
    $month += 1;

    foreach ($monthData as $day => $names) {
        $day += 1;

        foreach ($names as $name) {
            $escapedName = mysqli_real_escape_string($conn, $name);
            $sql = "INSERT INTO names (name, day, month) VALUES ('$escapedName', '$day', '$month')";
            if ($conn->query($sql) === TRUE) {
                echo "Record inserted successfully: $name, Day $day, Month $month<br>";
            } else {
                echo "Error inserting record: $name, Day $day, Month $month - " . $conn->error . "<br>";
            }
        }
    }
}

$conn->close();
?>