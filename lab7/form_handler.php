<?php
require_once 'security.php';
session_start();

$host = 'localhost';
$dbname = 'u82378';
$username = 'u82378';
$password = '5427077';

$success_message = null;
$generated_login = null;
$generated_password = null;

$field_errors = [];
$form_data = [];

function generateLogin($full_name) {
    $base = preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $full_name);
    $base = substr($base, 0, 10);
    $random = rand(100, 999);
    return strtolower($base . $random);
}

function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

function authenticate($login, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return $user['id'];
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $user_id = authenticate($login, $password, $pdo);
        
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['login'] = $login;
            $_SESSION['authenticated'] = true;
            $success_message = "Вход выполнен успешно!";

            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?action=edit');
            exit;
        } else {
            $field_errors['login'] = "Неверный логин или пароль";
        }
    } catch (PDOException $e) {
        $field_errors['login'] = "Ошибка подключения к базе данных";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] && (!isset($_POST['submit']) || !empty($field_errors))) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $stmt = $pdo->prepare("
                SELECT pl.language_name 
                FROM user_languages ul 
                JOIN programming_languages pl ON ul.language_id = pl.id 
                WHERE ul.user_id = :user_id
            ");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $form_data = [
                'full_name' => $user_data['full_name'],
                'phone' => $user_data['phone'],
                'email' => $user_data['email'],
                'birth_date' => $user_data['birth_date'],
                'gender' => $user_data['gender'],
                'languages' => $languages,
                'bio' => $user_data['bio'],
                'contract' => $user_data['contract_accepted']
            ];
        }
    } catch (PDOException $e) {
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    $form_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'birth_date' => $_POST['birth_date'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'languages' => $_POST['languages'] ?? [],
        'bio' => trim($_POST['bio'] ?? ''),
        'contract' => isset($_POST['contract']) ? 1 : 0
    ];

    $errors = [];
    $field_errors = [];

    if (empty($form_data['full_name'])) {
        $errors[] = "Поле ФИО обязательно для заполнения";
        $field_errors['full_name'] = "Поле обязательно для заполнения";
    } elseif (!preg_match("/^[А-Яа-яЁёA-Za-z\s-]+$/u", $form_data['full_name'])) {
        $errors[] = "ФИО может содержать только буквы, пробелы и дефисы";
        $field_errors['full_name'] = "Используйте только буквы, пробелы и дефисы";
    } elseif (strlen($form_data['full_name']) > 150) {
        $errors[] = "ФИО не должно превышать 150 символов";
        $field_errors['full_name'] = "Максимальная длина 150 символов";
    }

    if (empty($form_data['phone'])) {
        $errors[] = "Поле Телефон обязательно для заполнения";
        $field_errors['phone'] = "Поле обязательно для заполнения";
    } elseif (!preg_match("/^[\+\d\s\(\)\-]{7,20}$/", $form_data['phone'])) {
        $errors[] = "Телефон должен содержать только цифры, пробелы, скобки, дефисы и знак + (от 7 до 20 символов)";
        $field_errors['phone'] = "Формат: +7 (999) 123-45-67";
    }

    $email_pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (empty($form_data['email'])) {
        $errors[] = "Поле E-mail обязательно для заполнения";
        $field_errors['email'] = "Поле обязательно для заполнения";
    } elseif (!preg_match($email_pattern, $form_data['email'])) {
        $errors[] = "Некорректный формат E-mail";
        $field_errors['email'] = "Формат: user@example.com";
    }

    if (empty($form_data['birth_date'])) {
        $errors[] = "Поле Дата рождения обязательно для заполнения";
        $field_errors['birth_date'] = "Поле обязательно для заполнения";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $form_data['birth_date'])) {
        $errors[] = "Некорректный формат даты рождения";
        $field_errors['birth_date'] = "Используйте формат ГГГГ-ММ-ДД";
    } else {
        $date_obj = DateTime::createFromFormat('Y-m-d', $form_data['birth_date']);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $form_data['birth_date']) {
            $errors[] = "Некорректная дата рождения";
            $field_errors['birth_date'] = "Укажите корректную дату";
        } elseif ($date_obj > new DateTime()) {
            $errors[] = "Дата рождения не может быть в будущем";
            $field_errors['birth_date'] = "Дата не может быть в будущем";
        }
    }

    $allowed_genders = ['male', 'female', 'other'];
    if (empty($form_data['gender'])) {
        $errors[] = "Поле Пол обязательно для заполнения";
        $field_errors['gender'] = "Выберите пол";
    } elseif (!in_array($form_data['gender'], $allowed_genders)) {
        $errors[] = "Выбрано недопустимое значение пола";
        $field_errors['gender'] = "Некорректное значение";
    }

    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($form_data['languages'])) {
        $errors[] = "Выберите хотя бы один язык программирования";
        $field_errors['languages'] = "Выберите минимум один язык";
    } else {
        foreach ($form_data['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors[] = "Выбран недопустимый язык программирования: " . htmlspecialchars($lang);
                $field_errors['languages'] = "Выберите язык из списка";
                break;
            }
        }
    }

    if (!empty($form_data['bio']) && !preg_match("/^[А-Яа-яЁёA-Za-z0-9\s.,!?()-]+$/u", $form_data['bio'])) {
        $errors[] = "Биография содержит недопустимые символы";
        $field_errors['bio'] = "Используйте буквы, цифры, пробелы и знаки препинания";
    }
    

    if (!$form_data['contract']) {
        $errors[] = "Необходимо подтвердить ознакомление с контрактом";
        $field_errors['contract'] = "Подтвердите ознакомление";
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $pdo->beginTransaction();
            
            $is_update = isset($_SESSION['authenticated']) && $_SESSION['authenticated'];
            
            if ($is_update) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = :full_name, phone = :phone, email = :email, 
                        birth_date = :birth_date, gender = :gender, bio = :bio, 
                        contract_accepted = :contract
                    WHERE id = :user_id
                ");
                
                $stmt->execute([
                    ':full_name' => $form_data['full_name'],
                    ':phone' => $form_data['phone'],
                    ':email' => $form_data['email'],
                    ':birth_date' => $form_data['birth_date'],
                    ':gender' => $form_data['gender'],
                    ':bio' => $form_data['bio'],
                    ':contract' => $form_data['contract'],
                    ':user_id' => $_SESSION['user_id']
                ]);
                
                $user_id = $_SESSION['user_id'];

                $stmt = $pdo->prepare("DELETE FROM user_languages WHERE user_id = :user_id");
                $stmt->execute([':user_id' => $user_id]);
                
                $success_message = "Данные успешно обновлены!";
                
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $form_data['email']]);
                if ($stmt->fetch()) {
                    throw new Exception("Пользователь с таким email уже существует");
                }

                $generated_login = generateLogin($form_data['full_name']);
                $generated_password = generatePassword(10);
                $password_hash = password_hash($generated_password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted, login, password_hash) 
                    VALUES (:full_name, :phone, :email, :birth_date, :gender, :bio, :contract, :login, :password_hash)
                ");
                
                $stmt->execute([
                    ':full_name' => $form_data['full_name'],
                    ':phone' => $form_data['phone'],
                    ':email' => $form_data['email'],
                    ':birth_date' => $form_data['birth_date'],
                    ':gender' => $form_data['gender'],
                    ':bio' => $form_data['bio'],
                    ':contract' => $form_data['contract'],
                    ':login' => $generated_login,
                    ':password_hash' => $password_hash
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                $success_message = "Данные успешно сохранены!<br>Ваш логин: <strong>" . htmlspecialchars($generated_login) . "</strong><br>Ваш пароль: <strong>" . htmlspecialchars($generated_password) . "</strong><br>Сохраните эти данные для входа!";
                $_SESSION['user_id'] = $user_id;
                $_SESSION['login'] = $generated_login;
                $_SESSION['authenticated'] = true;
            }

            $lang_placeholders = implode(',', array_fill(0, count($form_data['languages']), '?'));
            $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE language_name IN ($lang_placeholders)");
            $stmt->execute($form_data['languages']);
            $language_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, :language_id)");
            foreach ($language_ids as $lang_id) {
                $stmt->execute([':user_id' => $user_id, ':language_id' => $lang_id]);
            }
            
            $pdo->commit();

            $cookies_data = [
                'full_name' => $form_data['full_name'],
                'phone' => $form_data['phone'],
                'email' => $form_data['email'],
                'birth_date' => $form_data['birth_date'],
                'gender' => $form_data['gender'],
                'bio' => $form_data['bio']
            ];
            
            setcookie('saved_form_data', json_encode($cookies_data), time() + 365*24*60*60, '/');

            if (!$is_update) {
                $form_data = [];
            }
            
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Ошибка: " . $e->getMessage();
            $error_message = implode("<br>", $errors);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['errors_processed']) && !isset($_SESSION['authenticated'])) {
    if (isset($_COOKIE['saved_form_data'])) {
        $form_data = json_decode($_COOKIE['saved_form_data'], true);
        if (!is_array($form_data)) {
            $form_data = [];
        }
    }
    
    unset($_SESSION['errors_processed']);
}
?>
