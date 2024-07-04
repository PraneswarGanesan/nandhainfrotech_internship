<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($firstname, $lastname, $username, $email, $password) {
        $stmt = $this->conn->prepare("INSERT INTO usersfor (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($this->conn->error));
        }
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("sssss", $firstname, $lastname, $username, $email, $passwordHash);
        return $stmt->execute();
    }

    public function find($username) {
        $stmt = $this->conn->prepare("SELECT id, password FROM usersfor WHERE username = ?");
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($this->conn->error));
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
