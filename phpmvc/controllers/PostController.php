<?php
include '../models/Post.php';
include '../dbconnection.php';
include '../logger.php';

class PostController {
    private $post;
    private $conn;

    public function __construct($db) {
        $this->post = new Post($db);
        $this->conn = $db;
    }

    public function createPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post'])) {
            session_start();
            if (!isset($_SESSION['user_id'])) {
                echo "User not authenticated.";
                return;
            }

            $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
            $user_id = $_SESSION['user_id'];
            $image = '';

            if (!empty($_FILES['image']['name'])) {
                $target_dir = "./uploads/";
                $target_file = $target_dir . basename($_FILES["image"]["name"]);

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                } else {
                    echo "Error uploading the image.";
                    return;
                }
            }

            if (empty($content)) {
                echo "Post content is required.";
                return;
            }

            $stmt = $this->conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $content, $image);
                
                try {
                    $stmt->execute();
                    header("Location: ../views/home");
                    writeToLog("Post created successfully");
                   
        
                    exit();
                } catch (mysqli_sql_exception $e) {
                    echo "Error: " . $e->getMessage();
                    writeToLog("Error: " . $this->conn->error);
                }

                $stmt->close();
            } else {
                echo "Error preparing SQL statement.";
            }
        }
    }


    public function displayAll() {
        return $this->post->getAllPosts();
    }

    public function getUserPosts($user_id) {
        return $this->post->getUserPosts($user_id);
    }
}


$db = include('../dbconnection.php');

$postController = new PostController($db);  

$postController->createPost();
?>