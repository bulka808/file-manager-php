<?php
require_once 'config.php';
require_login();

$user = $_SESSION['user'];
$userId = $user['id'];
$role = $user['role'];

// Загрузка файлов
if ($role === 'admin') {
    $stmt = $pdo->prepare("SELECT f.*, u.username FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.uploaded_at DESC");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$userId]);
}
$files = $stmt->fetchAll();

$error = $_SESSION['upload_error'] ?? '';
$success = $_GET['success'] ?? false;
unset($_SESSION['upload_error']);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Файловый менеджер</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Файловый менеджер</h1>
        <?php
        if (isset($_SESSION['error'])):
            echo '<div class="error">' . h($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        endif;

        if (isset($_SESSION['success'])):
            echo '<div class="success">' . h($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        endif;
        ?>
        <p>Привет, <strong><?= h($user['username']) ?></strong>
            <?php if ($role === 'admin'): ?>
                <span style="color: #d35400;">(админ)</span>
            <?php endif; ?>
            | <a href="logout.php">Выйти</a>
        </p>

        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success">Файл успешно загружен!</div>
        <?php endif; ?>

        <div class="upload-form">
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <button type="submit">Загрузить</button>
            </form>
        </div>

        <?php if (empty($files)): ?>
            <p>Нет загруженных файлов.</p>
        <?php else: ?>
            <div class="files-grid">
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <div class="file-preview">
                            <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                <img src="<?= UPLOAD_URL . h($file['stored_name']) ?>" alt="<?= h($file['original_name']) ?>">
                            <?php else: ?>
                                <div class="file-icon">📄</div>
                            <?php endif; ?>
                        </div>
                        <div class="file-info">
                            <strong><?= h($file['original_name']) ?></strong><br>
                            
                            <?= h($file['mime_type']) ?>

                            <?php if ($role === 'admin'): ?>
                                <br><small>Владелец: <?= h($file['username']) ?></small>
                            <?php endif; ?>

                            <br>
                            <a href="<?= UPLOAD_URL . h($file['stored_name']) ?>" target="_blank" download>Скачать</a>

                            <!-- Кнопка удаления -->
                            <?php
                            $canDelete = ($role === 'admin') || ($userId == $file['user_id']);
                            if ($canDelete):
                            ?>
                                <form method="POST" action="delete.php" onsubmit="return confirm('Удалить файл?')" style="display:inline;">
                                    <input type="hidden" name="file_id" value="<?= h($file['id']) ?>">
                                    <button type="submit" style="background: #e74c3c; margin-left: 5px;">Удалить</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>