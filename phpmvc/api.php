

<?php
require __DIR__ . '/vendor/autoload.php';


$postController = new PostController($db);

$app = \Slim\Factory\AppFactory::create();
$app->post('/api/posts', function ($request, $response) use ($postController) {
    $data = $request->getParsedBody();
    $content = filter_var($data['content'], FILTER_SANITIZE_STRING);
    $user_id = $_SESSION['user_id'];
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            return $response->withStatus(500)->withJson(['error' => 'Error uploading the image.']);
        }
    }

    if (empty($content)) {
        return $response->withStatus(400)->withJson(['error' => 'Post content is required.']);
    }

    $result = $postController->createPost($user_id, $content, $image);

    if ($result) {
        return $response->withJson(['message' => 'Post created successfully']);
    } else {
        return $response->withStatus(500)->withJson(['error' => 'Error creating the post.']);
    }
});

$app->get('/api/posts', function ($request, $response) use ($postController) {
    $posts = $postController->displayAll();

    $encodedPosts = [];
    foreach ($posts as $post) {
        $encodedImage = base64_encode(file_get_contents($post['image']));
        $encodedPost = array_merge($post, ['image' => $encodedImage]);
        $encodedPosts[] = $encodedPost;
    }

    return $response->withJson($encodedPosts);
});

$app->get('/api/posts/{user_id}', function ($request, $response, $args) use ($postController) {
    $user_id = $args['user_id'];

    $posts = $postController->getUserPosts($user_id);

    $encodedPosts = [];
    foreach ($posts as $post) {
        $encodedImage = base64_encode(file_get_contents($post['image']));
        $encodedPost = array_merge($post, ['image' => $encodedImage]);
        $encodedPosts[] = $encodedPost;
    }

    return $response->withJson($encodedPosts);
});

$app->run();




