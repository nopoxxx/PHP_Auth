<?php
include "../config/sqlConfig.php";
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$messageClass = "hidden";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $csrf_token = $_POST['csrf_token'];
    $remember_me = isset($_POST['remember_me']);

    if (hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        if (!empty($login) && !empty($password)) {
            $stmt = $conn->prepare("SELECT id, password, salt, role FROM users WHERE login = ?");
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashedPassword, $salt, $role);
                $stmt->fetch();

                if (password_verify($password . $salt, $hashedPassword)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $login;
                    $_SESSION['role'] = $role;

                    $message = "Добро пожаловать, $login!";
                    $messageClass = "success";

                    if ($remember_me) {
                        $token = bin2hex(random_bytes(16));
                        setcookie("remember_me", $token, time() + (86400 * 7), "/", "", true, true);
                        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $stmt->bind_param("si", $token, $user_id);
                        $stmt->execute();
                    }
                } else {
                    $message = "Неверный логин или пароль.";
                    $messageClass = "error";
                }
            } else {
                $message = "Неверный логин или пароль.";
                $messageClass = "error";
            }

            $stmt->close();
        } else {
            $message = "Пожалуйста, заполните все поля.";
            $messageClass = "error";
        }
    } else {
        $message = "Неверный CSRF-токен.";
        $messageClass = "error";
    }
}

if (isset($_GET['code'])) {
    $client_id = '52900147';
    $client_secret = '45fpZa7TqsHGzO7LuZvX';
    $redirect_uri = 'https://php_auth.test/auth/signin.php';

    $code = $_GET['code'];

    $token_url = "https://oauth.vk.com/access_token?client_id={$client_id}&client_secret={$client_secret}&redirect_uri={$redirect_uri}&code={$code}";
    $response = file_get_contents($token_url);
    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];
        $user_id = $data['user_id'];
        $email = $data['email'] ?? null;

        $user_info_url = "https://api.vk.com/method/users.get?user_ids={$user_id}&fields=photo_200&access_token={$access_token}&v=5.131";
        $user_info_response = file_get_contents($user_info_url);
        $user_info = json_decode($user_info_response, true);

        if (!empty($user_info['response'])) {
            $vk_user = $user_info['response'][0];
            $vk_name = $vk_user['first_name'] . ' ' . $vk_user['last_name'];

            $stmt = $conn->prepare("SELECT id FROM users WHERE vk_id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $stmt->bind_result($user_id);
                $stmt->fetch();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $vk_name;
                $_SESSION['role'] = "vk";
                $message = "Добро пожаловать, $vk_name!";
                $messageClass = "success";
            } else {

                $stmt = $conn->prepare("INSERT INTO users (vk_id, login, role) VALUES (?, ?, 'vk')");
                $stmt->bind_param("ss", $user_id, $vk_name);
                $stmt->execute();
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $vk_name;
                $message = "Вы зарегистрированы как $vk_name!";
                $messageClass = "success";
            }
        }
    } else {
        $message = "Ошибка авторизации через ВКонтакте.";
        $messageClass = "error";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Форма входа</title>
</head>
<body>
<header>
    <a href="../">Главная</a>
    <div>
        <a href="signup.php">Регистрация</a>
        <a href="signin.php">Вход</a>
    </div>
</header>
<div class="container">
    <div class="form-container">
        <h1>Вход</h1>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <label class="remember-me">
                <input type="checkbox" name="remember-me" id="remember-me" />
                Запомнить меня
            </label>
            <button type="submit">Войти</button>
        </form>
        <a href="https://oauth.vk.com/authorize?client_id=52900147&display=page&redirect_uri=https://php_auth.test/auth/signin.php&scope=email&response_type=code&v=5.131">
            <button class="vk-btn">Войти через ВКонтакте</button>
        </a>
    </div>
</div>
</body>
</html>
