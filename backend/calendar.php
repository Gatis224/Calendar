<?php
include '/var/www/html/config.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['month']) && isset($_GET['year'])) {
    $currentMonth = $_GET['month'];
    $currentYear = $_GET['year'];
} else {
    $currentMonth = date('n');
    $currentYear = date('Y');
}

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

$calendarData = [];

$query_names = "SELECT name, day FROM names WHERE month = ?";
$stmt_names = $conn->prepare($query_names);
$stmt_names->bind_param("i", $currentMonth);
$stmt_names->execute();
$result_names = $stmt_names->get_result();

while ($row = $result_names->fetch_assoc()) {
    $day = $row['day'];
    $calendarData[$day]['names'][] = $row['name'];
}

$query_holidays = "SELECT DISTINCT name, day FROM holidays WHERE month = ?";
$stmt_holidays = $conn->prepare($query_holidays);
$stmt_holidays->bind_param("i", $currentMonth);
$stmt_holidays->execute();
$result_holidays = $stmt_holidays->get_result();

$holidayNames = [];
while ($row = $result_holidays->fetch_assoc()) {
    $day = $row['day'];
    $calendarData[$day]['holidays'][] = $row['name'];
    $holidayNames[] = $row['name'];
}
$holidayNames = array_unique($holidayNames);

sort($holidayNames);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link href="/frontend/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="calendar">
        <h2><?php echo date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)); ?></h2>
        <div class="navigation">
            <?php
            $prevMonth = ($currentMonth == 1) ? 12 : ($currentMonth - 1);
            $nextMonth = ($currentMonth == 12) ? 1 : ($currentMonth + 1);
            $prevYear = ($currentMonth == 1) ? ($currentYear - 1) : $currentYear;
            $nextYear = ($currentMonth == 12) ? ($currentYear + 1) : $currentYear;
            ?>
            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-previous">Previous Month</a>
            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-next">Next Month</a>
        </div>
        <table>
            <tr>
                <th>Week</th>
                <th>Mo</th>
                <th>Tu</th>
                <th>We</th>
                <th>Th</th>
                <th>Fr</th>
                <th>Sa</th>
                <th>Su</th>
            </tr>
            <?php
            $firstDayOfMonth = date('N', strtotime("$currentYear-$currentMonth-01"));

            $startOffset = $firstDayOfMonth - 1;
            $dayCount = 0;
            $weekNumber = date('W', strtotime("$currentYear-$currentMonth-01"));

            echo '<tr>';
            echo '<td>' . sprintf('%02d', $weekNumber) . '</td>';
            for ($i = 0; $i < $startOffset; $i++) {
                echo '<td></td>';
                $dayCount++;
            }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                echo '<td';
                if (($dayCount % 7) == 5) {
                    echo ' class="saturday"';
                } elseif (($dayCount % 7) == 6) {
                    echo ' class="sunday"';
                }
                if ($day == date('j') && $currentMonth == date('n') && $currentYear == date('Y')) {
                    echo ' class="current-day"';
                }
                if (isset($_GET['day']) && $day == $_GET['day']) {
                    echo ' class="selected-day"';
                }
                if (isset($calendarData[$day]) && isset($calendarData[$day]['holidays'])) {
                    echo ' style="border: 2px solid darkred;"';
                }
                echo '>';
                echo '<div class="day-number">' . $day . '</div>';
                echo '<div class="namedays">';
                if (isset($calendarData[$day]) && isset($calendarData[$day]['names'])) {
                    foreach ($calendarData[$day]['names'] as $name) {
                        echo $name . '<br>';
                    }
                }
                echo '</div>';
                if (isset($calendarData[$day]) && isset($calendarData[$day]['holidays'])) {
                    foreach ($calendarData[$day]['holidays'] as $holiday) {
                        echo '<div class="holiday">' . $holiday . '</div>';
                    }
                }
                echo '</td>';
                if (($dayCount % 7) == 6 || $day == $daysInMonth) {
                    $weekNumber++;
                    if ($day != $daysInMonth) {
                        echo '</tr><tr>';
                        echo '<td>' . sprintf('%02d', $weekNumber) . '</td>';
                    }
                }
                $dayCount++;
            }
            ?>
        </table>
    </div>

    <div class="holiday-forms">
        <form method="post" action="edit_holiday.php" class="form-column">
            <input type="hidden" name="action" value="add_holiday">
            <label for="add_date">Select Date:</label>
            <input type="date" id="add_date" name="date" min="<?php echo date('Y-m-01', strtotime("$currentYear-$currentMonth")); ?>" max="<?php echo date('Y-m-t', strtotime("$currentYear-$currentMonth")); ?>" required>
            <br>
            <label for="add_holiday_name">New Holiday Name:</label>
            <input type="text" id="add_holiday_name" name="name" required>
            <br>
            <input type="submit" value="Add Holiday">
        </form>

        <form method="post" action="edit_holiday.php" class="form-column">
            <input type="hidden" name="action" value="edit_holiday">
            <label for="edit_date">Select Date:</label>
            <input type="date" id="edit_date" name="date" min="<?php echo date('Y-m-01', strtotime("$currentYear-$currentMonth")); ?>" max="<?php echo date('Y-m-t', strtotime("$currentYear-$currentMonth")); ?>" required>
            <br>
            <label for="edit_holiday_name">Select Existing Holiday Name:</label>
            <select id="edit_holiday_name" name="name" required>
                <?php foreach ($holidayNames as $holiday) {
                    echo "<option value=\"$holiday\">$holiday</option>";
                } ?>
            </select>
            <br>
            <label for="update_holiday_name">Updated Holiday Name:</label>
            <input type="text" id="update_holiday_name" name="updated_name" required>
            <br>
            <input type="submit" value="Edit Holiday">
        </form>

        <form method="post" action="edit_holiday.php" class="form-column">
            <input type="hidden" name="action" value="delete_holiday">
            <label for="delete_date">Select Date:</label>
            <input type="date" id="delete_date" name="date" min="<?php echo date('Y-m-01', strtotime("$currentYear-$currentMonth")); ?>" max="<?php echo date('Y-m-t', strtotime("$currentYear-$currentMonth")); ?>" required>
            <br>
            <label for="delete_holiday_name">Select Existing Holiday Name:</label>
            <select id="delete_holiday_name" name="name" required>
                <?php foreach ($holidayNames as $holiday) {
                    echo "<option value=\"$holiday\">$holiday</option>";
                } ?>
            </select>
            <br>
            <input type="submit" value="Delete Holiday">
        </form>
    </div>

</body>
</html>
