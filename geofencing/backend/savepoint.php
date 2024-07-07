<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type'); 


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $data = json_decode(file_get_contents('php://input'), true);

    $latitude = isset($data['latitude']) ? $data['latitude'] : null;
    $longitude = isset($data['longitude']) ? $data['longitude'] : null;

    if ($latitude !== null && $longitude !== null) {
     
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "businessdb";

       
        $conn = new mysqli($servername, $username, $password, $dbname);

      
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

      
        $sql = "INSERT INTO points (latitude, longitude) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dd", $latitude, $longitude);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Point saved successfully"));
        } else {
            echo json_encode(array("message" => "Error saving point"));
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(array("message" => "Latitude or longitude missing"));
    }
}
?>
