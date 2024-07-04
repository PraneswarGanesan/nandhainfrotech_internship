<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../controllers/PostController.php');
$db = include('../dbconnection.php');

$postController = new PostController($db);
$posts = $postController->displayAll();
?>

<?php include('header.html'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .img-fluid {
            max-width: 100%;
            height: auto;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 600px;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .5);
            z-index: 1050;
        }

        .popup-header {
            padding: 15px;
            border-bottom: 1px solid #ccc;
        }

        .popup-body {
            padding: 15px;
        }

        .popup-footer {
            padding: 15px;
            border-top: 1px solid #ccc;
            text-align: right;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
    <!-- Input bar to trigger the popup -->
    <input type="text" class="form-control" placeholder="What do you want to share?" id="inputBar">
    <hr>
    <h2>Posts</h2>

    <!-- Display existing posts -->
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <?php if (!empty($post['image'])): ?>
                    <img src="../<?php echo htmlspecialchars($post['image']); ?>" style='width: 20%; height: 60%px;' alt="Post Image" class="img-fluid">
                <?php endif; ?>
                <p><small><?php echo htmlspecialchars($post['created_at']); ?></small></p>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts yet.</p>
    <?php endif; ?>
</div>

<!-- Popup for posting -->
<div class="overlay" id="overlay"></div>
<div class="popup" id="popup">
    <div class="popup-header">
        <h5>Share something</h5>
        <button type="button" class="btn-close" aria-label="Close" id="closePopup"></button>
    </div>
    <div class="popup-body">
    <form action="../controllers/PostController.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="postText" class="form-label">Your Post</label>
                <textarea class="form-control" name="content" id="postText" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="postImage" class="form-label">Upload Image</label>
                <input class="form-control" type="file" name="image" id="postImage">
            </div>
            <div class="popup-footer">
                <button type="button" class="btn btn-secondary" id="closePopupFooter">Close</button>
                <button type="submit" class="btn btn-primary" name="post">Post</button>
            </div>
        </form>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<!-- JavaScript to handle popup display -->
<script>
    const inputBar = document.getElementById('inputBar');
    const popup = document.getElementById('popup');
    const overlay = document.getElementById('overlay');
    const closePopup = document.getElementById('closePopup');
    const closePopupFooter = document.getElementById('closePopupFooter');

    inputBar.addEventListener('click', () => {
        overlay.style.display = 'block';
        popup.style.display = 'block';
    });

    const closePopupFunction = () => {
        overlay.style.display = 'none';
        popup.style.display = 'none';
    };

    closePopup.addEventListener('click', closePopupFunction);
    closePopupFooter.addEventListener('click', closePopupFunction);
    overlay.addEventListener('click', closePopupFunction);
</script>

</body>
</html>
