<?php
$host = 'localhost';
$db = 'businessdb';
$user = 'root';
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    echo "<script>Console.log('Db connected lol');</script>";
}

return $conn;
?>
