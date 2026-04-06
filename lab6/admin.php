<?php

session_start();

$host = 'localhost';
$dbname = 'u82378';
$username = 'u82378';
$password = '5427077';

function isAdmin($pdo, $login, $password) {
    $stmt = $pdo->prepare("SELECT id, login, password_hash, role FROM users WHERE login = :login AND role = 'admin'");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return $user;
    }
    return false;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (!$is_authenticated) {
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        
        $admin_data = isAdmin($pdo, $user, $pass);
        
        if ($admin_data) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin_data['id'];
            $_SESSION['admin_login'] = $admin_data['login'];
            $is_authenticated = true;
        }
    }
}
if (!$is_authenticated) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel - Only for administrators"');

    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Требуется авторизация администратора</title>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .error {
                background: rgba(255,255,255,0.95);
                color: #333;
                padding: 30px;
                border-radius: 15px;
                display: inline-block;
                max-width: 500px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .error h2 { margin-top: 0; color: #dc3545; }
            .error p { margin: 15px 0; }
            .error code {
                background: #f4f4f4;
                padding: 8px 15px;
                border-radius: 5px;
                display: inline-block;
                margin: 5px;
                font-size: 1.1em;
            }
            .error .note {
                font-size: 0.85em;
                color: #666;
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
            }
            .btn {
                display: inline-block;
                background: #667eea;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>🔐 Доступ только для администраторов</h2>
            <p>Для доступа к панели администратора необходимо иметь учетную запись с правами администратора.</p>
            <p><strong>Логин:</strong> <code>admin</code></p>
            <p><strong>Пароль:</strong> <code>admin123</code></p>
            <div class="note">
                <small>Если вы администратор и не можете войти, обратитесь к техническому специалисту.</small>
            </div>
            <a href="admin.php" class="btn">↻ Попробовать снова</a>
        </div>
    </body>
    </html>';
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($action === 'delete' && $user_id) {
    if ($user_id == $_SESSION['admin_id']) {
        header('Location: admin.php?message=cant_delete_self');
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    header('Location: admin.php?message=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $update_id = $_POST['user_id'];
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET full_name = :full_name, phone = :phone, email = :email, 
            birth_date = :birth_date, gender = :gender, bio = :bio
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['birth_date'],
        ':gender' => $_POST['gender'],
        ':bio' => $_POST['bio'],
        ':id' => $update_id
    ]);
    
    $languages = $_POST['languages'] ?? [];
    $stmt = $pdo->prepare("DELETE FROM user_languages WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $update_id]);
    
    if (!empty($languages)) {
        $lang_placeholders = implode(',', array_fill(0, count($languages), '?'));
        $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE language_name IN ($lang_placeholders)");
        $stmt->execute($languages);
        $language_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, :language_id)");
        foreach ($language_ids as $lang_id) {
            $stmt->execute([':user_id' => $update_id, ':language_id' => $lang_id]);
        }
    }
    
    header('Location: admin.php?message=updated');
    exit;
}

$edit_user = null;
$user_languages = [];
if ($action === 'edit' && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_user) {
        $stmt = $pdo->prepare("
            SELECT pl.language_name 
            FROM user_languages ul 
            JOIN programming_languages pl ON ul.language_id = pl.id 
            WHERE ul.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        $user_languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$stmt = $pdo->prepare("
    SELECT pl.language_name, COUNT(ul.user_id) as count 
    FROM programming_languages pl
    LEFT JOIN user_languages ul ON pl.id = ul.language_id
    GROUP BY pl.id
    ORDER BY count DESC
");
$stmt->execute();
$language_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT u.*, 
           GROUP_CONCAT(pl.language_name SEPARATOR ', ') as languages
    FROM users u
    LEFT JOIN user_languages ul ON u.id = ul.user_id
    LEFT JOIN programming_languages pl ON ul.language_id = pl.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header h1 {
            font-size: 1.8em;
        }

        .admin-info {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .admin-info span {
            font-weight: bold;
        }

        .admin-info a {
            color: white;
            text-decoration: none;
        }

        .admin-info a:hover {
            text-decoration: underline;
        }

        .stats {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 10px;
        }

        .stats span {
            font-weight: bold;
            font-size: 1.2em;
        }

        .content {
            padding: 30px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 1em;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-card .count {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th,
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .users-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete, .btn-back, .btn-save, .btn-logout {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-block;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-save {
            background: #28a745;
            color: white;
            padding: 10px 20px;
        }

        .edit-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group select[multiple] {
            height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .users-table {
                font-size: 0.85em;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Панель администратора</h1>
            <div style="display: flex; gap: 15px;">
                <div class="stats">
                    Всего пользователей: <span><?php echo count($users); ?></span>
                </div>
                <div class="admin-info">
                    👤 <?php echo htmlspecialchars($_SESSION['admin_login'] ?? 'Admin'); ?>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($_GET['message'])): ?>
                <?php if ($_GET['message'] === 'deleted'): ?>
                    <div class="message message-success">✅ Пользователь успешно удален!</div>
                <?php elseif ($_GET['message'] === 'updated'): ?>
                    <div class="message message-success">✅ Данные пользователя успешно обновлены!</div>
                <?php elseif ($_GET['message'] === 'cant_delete_self'): ?>
                    <div class="message message-error">⚠️ Вы не можете удалить самого себя!</div>
                <?php endif; ?>
            <?php endif; ?>
        
            <h2>📊 Статистика по языкам программирования</h2>
            <div class="stats-grid">
                <?php foreach ($language_stats as $stat): ?>
                    <div class="stat-card">
                        <h3><?php echo htmlspecialchars($stat['language_name']); ?></h3>
                        <div class="count"><?php echo $stat['count']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
       
            <?php if ($edit_user): ?>
                <div class="edit-form">
                    <h2>✏️ Редактирование пользователя #<?php echo $edit_user['id']; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>ФИО</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Дата рождения</label>
                                <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Пол</label>
                                <select name="gender" required>
                                    <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                    <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                                    <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>Другой</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Любимые языки программирования</label>
                                <select name="languages[]" multiple>
                                    <?php foreach ($all_languages as $lang): ?>
                                        <option value="<?php echo $lang; ?>" 
                                            <?php echo (in_array($lang, $user_languages)) ? 'selected' : ''; ?>>
                                            <?php echo $lang; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Биография</label>
                            <textarea name="bio" rows="4"><?php echo htmlspecialchars($edit_user['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="update_user" class="btn-save">💾 Сохранить</button>
                            <a href="admin.php" class="btn-back">↩️ Назад</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
 
            <h2>📋 Список пользователей</h2>
            <div style="overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ФИО</th>
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Дата рождения</th>
                            <th>Пол</th>
                            <th>Языки</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['birth_date']; ?></td>
                                <td>
                                    <?php 
                                        $genders = ['male' => 'Мужской', 'female' => 'Женский', 'other' => 'Другой'];
                                        echo $genders[$user['gender']] ?? $user['gender'];
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['languages'] ?? '-'); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn-edit">✏️ Ред.</a>
                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">🗑️ Удалить</a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.85em;">(Вы)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
