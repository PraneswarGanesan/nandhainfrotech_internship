<?php
class Post {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
    public function create($user_id, $content, $image) {
        $stmt = $this->conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($this->conn->error));
        }
        $stmt->bind_param("iss", $user_id, $content, $image);
        return $stmt->execute();
    }

    public function getAllPosts() {
        $sql = "SELECT p.*, u.username FROM posts p JOIN usersfor u ON p.user_id = u.id ORDER BY p.created_at DESC";
        $result = $this->conn->query($sql);
    
        $posts = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    public function getUserPosts($user_id) {
        $query = "SELECT p.id, p.content, p.image, p.created_at, u.username FROM posts p JOIN usersfor u ON p.user_id = u.id WHERE p.user_id = ? ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function displayhome(){
        return header("Location: home.php");
    }

}
?>
