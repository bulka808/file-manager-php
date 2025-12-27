<?php
require_once 'config.php';
require_login();

$user = $_SESSION['user'];
$userId = $user['id'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['file_id'])) {
    header('Location: index.php');
    exit;
}

$fileId = (int)$_POST['file_id'];

// Получаем информацию о файле
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file) {
    $_SESSION['error'] = "Файл не найден.";
    header('Location: index.php');
    exit;
}

// Проверяем права: только владелец или админ может удалять
if ($role !== 'admin' && $userId !== $file['user_id']) {
    $_SESSION['error'] = "У вас нет прав на удаление этого файла.";
    header('Location: index.php');
    exit;
}

// Удаляем файл с диска
$filePath = UPLOAD_DIR . $file['stored_name'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Удаляем запись из БД
$pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$fileId]);

$_SESSION['success'] = "Файл успешно удалён.";
header('Location: index.php');
exit;