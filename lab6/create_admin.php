<?php
// Запустите этот файл один раз для создания администратора
// Затем удалите его или закомментируйте

$host = 'localhost';
$dbname = 'u82378';
$username = 'u82378';
$password = '5427077';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Добавляем поле role если его нет
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'");
    
    // Создаем администратора
    $admin_login = 'admin';
    $admin_password = 'admin123'; // Измените на свой пароль
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted, login, password_hash, role) 
        VALUES ('Administrator', '+7 (999) 123-45-67', 'admin@example.com', '1990-01-01', 'male', '', 1, :login, :hash, 'admin')
        ON DUPLICATE KEY UPDATE role = 'admin', password_hash = :hash
    ");
    
    $stmt->execute([
        ':login' => $admin_login,
        ':hash' => $password_hash
    ]);
    
    echo "Администратор успешно создан!<br>";
    echo "Логин: admin<br>";
    echo "Пароль: admin123<br>";
    echo "Измените пароль после первого входа!";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
