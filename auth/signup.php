<?php
include "../config/sqlConfig.php";

$message = "";
$messageClass = "hidden";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $message = "Пожалуйста, заполните все поля.";
        $messageClass = "error";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Этот логин уже занят. Пожалуйста, выберите другой.";
            $messageClass = "error";
        } else {
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($password . $salt, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (login, password, salt) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $login, $hashedPassword, $salt);

            if ($stmt->execute()) {
                $message = "Регистрация прошла успешно!";
                $messageClass = "success";
            } else {
                $message = "Ошибка регистрации. Попробуйте ещё раз.";
                $messageClass = "error";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="../style.css">
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
    <h1>Регистрация</h1>
    <div class="message <?php echo $messageClass; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <form method="POST" action="">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
</div>
</div>
</body>
</html>