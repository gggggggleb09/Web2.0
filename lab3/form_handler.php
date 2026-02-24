<?php
$host = 'localhost';
$dbname = 'u82378';
$username = 'u82378'; 
$password = '5427077';    

$success_message = null;
$error_message = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    
   
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $bio = trim($_POST['bio'] ?? '');
    $contract = isset($_POST['contract']) ? 1 : 0;
    
   
    $errors = [];
    
    
    if (empty($full_name)) {
        $errors[] = "Поле ФИО обязательно для заполнения";
    } elseif (!preg_match("/^[а-яА-ЯёЁa-zA-Z\s-]+$/u", $full_name)) {
        $errors[] = "ФИО должно содержать только буквы, пробелы и дефисы";
    } elseif (mb_strlen($full_name) > 150) {
        $errors[] = "ФИО не должно превышать 150 символов";
    }
    
   
    if (empty($phone)) {
        $errors[] = "Поле Телефон обязательно для заполнения";
    } elseif (!preg_match("/^[0-9\-\+\(\)\s]+$/", $phone)) {
        $errors[] = "Телефон содержит недопустимые символы";
    }
    
   
    if (empty($email)) {
        $errors[] = "Поле E-mail обязательно для заполнения";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный формат E-mail";
    }
    
    
    if (empty($birth_date)) {
        $errors[] = "Поле Дата рождения обязательно для заполнения";
    } else {
        $date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $birth_date) {
            $errors[] = "Некорректный формат даты рождения";
        } elseif ($date_obj > new DateTime()) {
            $errors[] = "Дата рождения не может быть в будущем";
        }
    }
    
    
    $allowed_genders = ['male', 'female', 'other'];
    if (empty($gender)) {
        $errors[] = "Поле Пол обязательно для заполнения";
    } elseif (!in_array($gender, $allowed_genders)) {
        $errors[] = "Выбрано недопустимое значение пола";
    }
    
   
    $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($languages)) {
        $errors[] = "Выберите хотя бы один язык программирования";
    } else {
        foreach ($languages as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors[] = "Выбран недопустимый язык программирования: " . htmlspecialchars($lang);
                break;
            }
        }
    }
    
   
    if (!$contract) {
        $errors[] = "Необходимо подтвердить ознакомление с контрактом";
    }
    
    
    if (empty($errors)) {
        try {
           
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            $pdo->beginTransaction();
            
            
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted) 
                VALUES (:full_name, :phone, :email, :birth_date, :gender, :bio, :contract)
            ");
            
            $stmt->execute([
                ':full_name' => $full_name,
                ':phone' => $phone,
                ':email' => $email,
                ':birth_date' => $birth_date,
                ':gender' => $gender,
                ':bio' => $bio,
                '


ct' => $contract
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            
            $lang_placeholders = implode(',', array_fill(0, count($languages), '?'));
            $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE language_name IN ($lang_placeholders)");
            $stmt->execute($languages);
            $language_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
           
            $stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, :language_id)");
            foreach ($language_ids as $lang_id) {
                $stmt->execute([':user_id' => $user_id, ':language_id' => $lang_id]);
            }
            
            
            $pdo->commit();
            
            $success_message = "Данные успешно сохранены! Спасибо за заполнение анкеты.";
            
           
            $_POST = [];
            
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "Ошибка базы данных: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>
