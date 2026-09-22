<?php
$link = mysqli_connect('127.0.0.1','root','','apsdreamhome');
$result = mysqli_query($link, 'DESCRIBE realtime_notifications');
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}
mysqli_close($link);
?>