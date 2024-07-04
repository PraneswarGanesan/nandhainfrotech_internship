<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('./header.html');
require_once('../controllers/PostController.php');
$db = include('../dbconnection.php');

$postController = new PostController($db);

if (isset($_SESSION['user_id'])) {
    $posts = $postController->getUserPosts($_SESSION['user_id']);
} else {
    $posts = null; // or handle the case where the user is not logged in
}

?>
<DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<style>

    .image-post{
        height: 55%;
        width: 45%; 
    }

    </style>
<body>

<div class="container mt-4">
    <h1>Account Page</h1>
    <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
    <a href="../logout.php" class="btn btn-primary">Logout</a>

    <hr>

    <h2>Your Posts</h2>

    <!-- Display user's posts -->
    <?php if ($posts && count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post mb-4">
                <h3><?php echo isset($post['username']) ? htmlspecialchars($post['username']) : 'Unknown'; ?></h3>
                <p><?php echo isset($post['content']) ? htmlspecialchars($post['content']) : ''; ?></p>
                <?php if (!empty($post['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($post['image']); ?>" style='width: 20%; height: 60%px;' alt="Post Image" class="image-post" >
                 
                    <?php endif; ?>
                <p><small class="text-muted"><?php echo isset($post['created_at']) ? $post['created_at'] : ''; ?></small></p>

                <!-- Delete form -->
                <!-- <form action="" method="post" style="display: inline;">
                    <input type="hidden" name="delete_post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form> -->

                <hr>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts yet.</p>
    <?php endif; ?>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
