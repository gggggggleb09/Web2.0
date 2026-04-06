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
    
    // Проверяем, существует ли поле role, и добавляем если нет
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
        echo "Поле 'role' успешно добавлено в таблицу users<br>";
    } else {
        echo "Поле 'role' уже существует в таблице users<br>";
    }
    
    // Создаем администратора
    $admin_login = 'admin';
    $admin_password = 'admin123'; // Измените на свой пароль
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Проверяем, существует ли уже администратор
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = :login");
    $stmt->execute([':login' => $admin_login]);
    $existingAdmin = $stmt->fetch();
    
    if (!$existingAdmin) {
        // Создаем нового администратора
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted, login, password_hash, role) 
            VALUES ('Administrator', '+7 (999) 123-45-67', 'admin@example.com', '1990-01-01', 'male', '', 1, :login, :hash, 'admin')
        ");
        
        $stmt->execute([
            ':login' => $admin_login,
            ':hash' => $password_hash
        ]);
        
        echo "Администратор успешно создан!<br>";
    } else {
        // Обновляем существующего пользователя до администратора
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password_hash = :hash WHERE login = :login");
        $stmt->execute([
            ':login' => $admin_login,
            ':hash' => $password_hash
        ]);
        
        echo "Пользователь 'admin' обновлен до администратора!<br>";
    }
    
    echo "<br><strong>Данные для входа:</strong><br>";
    echo "Логин: admin<br>";
    echo "Пароль: admin123<br>";
    echo "<span style='color: red;'>Измените пароль после первого входа!</span>";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
