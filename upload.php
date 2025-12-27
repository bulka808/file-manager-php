<?php
require_once 'config.php';
require_login();

$user = $_SESSION['user'];
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// === Функция транслитерации ===
function translitToLatin($str)
{
    $str = (string)$str;
    $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

    $tr = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya', 'а' => 'a', 'б' => 'b',
        'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
        'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
        'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

       
        ' ' => '_', '-' => '_','\'' => '_', '"' => '_', '(' => '_', ')' => '_',
        '[' => '_', ']' => '_', '{' => '_', '}' => '_', ',' => '_', 
        ';' => '_', ':' => '_', '.' => '_', '\\' => '_', '/' => '_',
        '@' => '_', '#' => '_', '$' => '_', '%' => '_', '^' => '_',
        '&' => '_', '*' => '_', '+' => '_', '=' => '_', '?' => '_',
        '~' => '_', '`' => '_', '|' => '_', '<' => '_', '>' => '_',
    ];

    $str = strtr($str, $tr);

    // Оставляем только латиницу, цифры и _
    $str = preg_replace('/[^a-zA-Z0-9_]/', '_', $str);

    // Убираем множественные подчёркивания
    $str = preg_replace('/_+/', '_', $str);

    // Убираем _ в начале и конце
    $str = trim($str, '_');

    return $str === '' ? 'file' : $str;
}



$error = '';



if (!isset($_FILES['file'])) {
    $error = "Файл не был отправлен на сервер.";
} elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = "Ошибка загрузки: " . $_FILES['file']['error'];
} else {
    $file = $_FILES['file'];
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);
    $size = $file['size'];

    if ($size > $maxSize) {
        $error = "Файл слишком большой (макс. 5 МБ).";
    } elseif (in_array($ext, $forbiddenExtensions)) {
        $error = "Загрузка файлов с расширением .$ext запрещена.";
    } elseif (in_array($mime, $forbiddenMimes)) {
        $error = "Тип файла '$mime' запрещён.";
    } else {
        // Транслитерируем имя (без расширения)
        $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
        $cleanName = translitToLatin($nameWithoutExt);

        // Формируем безопасное имя: имя + уникальный ID + расширение
        $storedName = $cleanName . '_' . uniqid() . '.' . $ext;

        // Защита на случай, если после транслитерации имя пустое
        if ($cleanName === '') {
            $storedName = 'file_' . uniqid() . '.' . $ext;
        }

        $path = UPLOAD_DIR . $storedName;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            $pdo->prepare("INSERT INTO files (user_id, original_name, stored_name, mime_type) VALUES (?, ?, ?, ?)")
                ->execute([$userId, $originalName, $storedName, $mime]);
            header('Location: index.php?success=1');
            exit;
        } else {
            $error = "Не удалось сохранить файл на сервере.";
        }
    }
}

$_SESSION['upload_error'] = $error;
header('Location: index.php');
exit;
