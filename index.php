<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: auth/signin.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="/">Главная</a>
    <div>
        <a href="auth/signup.php">Регистрация</a>
        <a href="auth/signin.php">Вход</a>
    </div>
</header>
<div class="container">
    <div class="content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc metus risus, imperdiet sed metus vel, malesuada iaculis diam. Donec in commodo lacus. Sed porttitor scelerisque ornare. Morbi et ante eros. Nulla lobortis, neque quis finibus imperdiet, est ante gravida diam, et tempus arcu neque vel justo. Quisque sapien arcu, luctus eu porttitor id, bibendum id ante. Mauris cursus egestas neque id dictum. Sed nulla velit, venenatis id purus sed, interdum malesuada arcu. Fusce commodo, mi at accumsan blandit, nulla leo venenatis ligula, sed condimentum sapien urna vitae dolor. Etiam id nunc congue, elementum tellus et, dictum magna.</p>
<?php echo $role !== 'vk' ? '' : '<img src="img/dog.png">'; ?>
    </div>
</div>
</body>
</html>
