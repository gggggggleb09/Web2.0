<?php
// Тестовый файл для проверки создания администратора

$host = 'localhost';
$dbname = 'u82378';
$username = 'u82378';
$password = '5427077';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Проверяем существующих пользователей
    $stmt = $pdo->query("SELECT id, login, role FROM users");
    echo "<h3>Существующие пользователи:</h3>";
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Login: {$row['login']}, Role: {$row['role']}<br>";
    }
    
    // Создаем администратора с простым паролем
    $admin_login = 'admin';
    $admin_password = 'admin123';
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Проверяем, существует ли уже администратор
    $stmt = $pdo->prepare("SELECT id FROM users WHERE login = :login");
    $stmt->execute([':login' => $admin_login]);
    $existingAdmin = $stmt->fetch();
    
    if (!$existingAdmin) {
        // Добавляем поле role если его нет
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
            echo "<br>Поле role добавлено<br>";
        } catch (PDOException $e) {
            echo "<br>Поле role уже существует или ошибка: " . $e->getMessage() . "<br>";
        }
        
        // Создаем администратора
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted, login, password_hash, role) 
            VALUES ('Administrator', '+7 (999) 123-45-67', 'admin@example.com', '1990-01-01', 'male', '', 1, :login, :hash, 'admin')
        ");
        
        $stmt->execute([
            ':login' => $admin_login,
            ':hash' => $password_hash
        ]);
        
        echo "<br style='color: green;'>✅ Администратор успешно создан!<br>";
    } else {
        // Обновляем пароль и роль существующего пользователя
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin', password_hash = :hash WHERE login = :login");
        $stmt->execute([
            ':login' => $admin_login,
            ':hash' => $password_hash
        ]);
        
        echo "<br style='color: green;'>✅ Пароль и роль администратора обновлены!<br>";
    }
    
    // Проверяем, правильно ли сохранился пароль
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE login = :login");
    $stmt->execute([':login' => $admin_login]);
    $user = $stmt->fetch();
    
    echo "<br><strong>Проверка пароля:</strong><br>";
    echo "Логин: admin<br>";
    echo "Пароль: admin123<br>";
    
    if (password_verify('admin123', $user['password_hash'])) {
        echo "<span style='color: green;'>✅ Пароль верный!</span><br>";
    } else {
        echo "<span style='color: red;'>❌ Ошибка: Пароль не совпадает!</span><br>";
    }
    
    echo "<br><strong>Данные для входа в админ-панель:</strong><br>";
    echo "Логин: <strong>admin</strong><br>";
    echo "Пароль: <strong>admin123</strong><br>";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
