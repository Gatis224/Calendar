<?php
include '/var/www/html/config.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_holiday') {
            if(isset($_POST['date']) && isset($_POST['name'])) {
                $date = $_POST['date'];
                $name = $_POST['name'];

                $day = date('j', strtotime($date));
                $month = date('n', strtotime($date));

                $query_insert = "INSERT INTO holidays (name, day, month) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param("sii", $name, $day, $month);
                $stmt_insert->execute();

                header("Location: calendar.php?month=$month&year=" . date('Y', strtotime($date)));
                exit();
            } else {
                exit("Date or name is missing.");
            }
        } elseif ($_POST['action'] == 'edit_holiday') {
            if(isset($_POST['date']) && isset($_POST['name']) && isset($_POST['updated_name'])) {
                $date = $_POST['date'];
                $name = $_POST['name'];
                $updatedName = $_POST['updated_name'];

                $day = date('j', strtotime($date));
                $month = date('n', strtotime($date));

                $query_update = "UPDATE holidays SET name = ? WHERE day = ? AND month = ? AND name = ?";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param("siss", $updatedName, $day, $month, $name);
                $stmt_update->execute();

                header("Location: calendar.php?month=$month&year=" . date('Y', strtotime($date)));
                exit();
            } else {
                exit("Date, original name, or updated name is missing.");
            }
        } elseif ($_POST['action'] == 'delete_holiday') {
            if(isset($_POST['date']) && isset($_POST['name'])) {
                $date = $_POST['date'];
                $name = $_POST['name'];

                $day = date('j', strtotime($date));
                $month = date('n', strtotime($date));

                $query_delete = "DELETE FROM holidays WHERE day = ? AND month = ? AND name = ?";
                $stmt_delete = $conn->prepare($query_delete);
                $stmt_delete->bind_param("iss", $day, $month, $name);
                $stmt_delete->execute();

                header("Location: calendar.php?month=$month&year=" . date('Y', strtotime($date)));
                exit();
            } else {
                exit("Date or name is missing.");
            }
        }
    }
}

exit("Invalid request.");
?>
