<?php
require_once 'config.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password) || strlen($password) < 6) {
        $error = "Логин и пароль (минимум 6 символов) обязательны.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Пользователь с таким логином уже существует.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)")
                ->execute([$username, $hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => (int)$userId,
                'username' => $username,
                'role' => $role
            ];
            header('Location: index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="form-container">
        <h2>Регистрация</h2>
        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Логин" required><br>
            <input type="password" name="password" placeholder="Пароль (мин. 6 символов)" required minlength="6"><br>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
    </div>
</body>

</html>