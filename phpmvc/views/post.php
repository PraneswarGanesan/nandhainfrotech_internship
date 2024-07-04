
<?php
session_start();
include '../dbconnection.php';
include 'logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post'])) {
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }

    if (empty($content)) {
        echo "Post content is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $content, $image);
            header("Location: home.php");

            try {
                $stmt->execute();
                writeToLog("Post Content");
                exit();
            } catch (mysqli_sql_exception $e) {
                echo "Error: " . $e->getMessage();
               
                writeToLog("Error: Empty post content.");
            }

            $stmt->close();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

mysqli_close($conn);
?>








	

