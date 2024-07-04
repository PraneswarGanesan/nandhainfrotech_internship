<?php
require_once '../models/User.php';
require_once '../dbconnection.php';
require_once '../vendor/autoload.php'; 
use \Firebase\JWT\JWT;
// use \Firebase\JWT\Key;

class UserController {
    private $user;
    private $secret_key;

    public function __construct($db) {
        $this->user = new User($db);

        $this->secret_key = $this->getSecretKey();
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
            $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
    
            if ($this->user->create($firstname, $lastname, $username, $email, $password)) {
                session_start();
                $_SESSION['username'] = $username;

    
                $this->secret_key = $this->getSecretKey();
                $token = $this->generateJWT($this->user->find($username));
    
                header("Location: ../views/account?token=$token");
                exit();
            } else {
                echo "Error: Could not sign up.";
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
    
            $user = $this->user->find($username);
    
            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
    
                $this->secret_key = $this->getSecretKey();
                $token = $this->generateJWT($user); 
    
                header("Location: ../views/account?token=$token");
                exit();
            } else {
                echo "Invalid username or password.";
            }
        }
    }
    private function generateJWT($user) {
        $payload = [
            'iss' => "localhost",
            'iat' => time(),
            'exp' => time() + (60*60), 
            'userId' => isset($user['id']) ? $user['id'] : null,
            'username' => isset($user['username']) ? $user['username'] : null
        ];
    
        $token = JWT::encode($payload, $this->secret_key, 'HS256');
        echo "<script>console.log('Token: $token')</script>"; 
        return $token;
    }


    private function getSecretKey() {
        $bytes = random_bytes(32);
        return bin2hex($bytes);
    }

}
?>
