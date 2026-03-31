<?php
session_start();

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'u82378';
$username = 'root';
$password = '';

$success_message = null;

// Массив для хранения ошибок по полям
$field_errors = [];
$form_data = [];

// Загружаем сохраненные данные из Cookies при первом открытии формы
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['errors_processed'])) {
    // Проверяем, есть ли сохраненные Cookies с данными
    if (isset($_COOKIE['saved_form_data'])) {
        $form_data = json_decode($_COOKIE['saved_form_data'], true);
        if (!is_array($form_data)) {
            $form_data = [];
        }
    }
    
    // Очищаем флаг обработки ошибок
    unset($_SESSION['errors_processed']);
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    
    // Сбор данных из формы
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
    
    // Массив для сбора ошибок
    $errors = [];
    $field_errors = [];
    
    // 1. Валидация ФИО с регулярным выражением
    if (empty($form_data['full_name'])) {
        $errors[] = "Поле ФИО обязательно для заполнения";
        $field_errors['full_name'] = "Поле обязательно для заполнения";
    } elseif (!preg_match("/^[А-Яа-яЁёA-Za-z\s-]+$/u", $form_data['full_name'])) {
        $errors[] = "ФИО может содержать только буквы, пробелы и дефисы";
        $field_errors['full_name'] = "Используйте только буквы, пробелы и дефисы";
    } elseif (mb_strlen($form_data['full_name']) > 150) {
        $errors[] = "ФИО не должно превышать 150 символов";
        $field_errors['full_name'] = "Максимальная длина 150 символов";
    }
    
    // 2. Валидация телефона с регулярным выражением
    if (empty($form_data['phone'])) {
        $errors[] = "Поле Телефон обязательно для заполнения";
        $field_errors['phone'] = "Поле обязательно для заполнения";
    } elseif (!preg_match("/^[\+\d\s\(\)\-]{7,20}$/", $form_data['phone'])) {
        $errors[] = "Телефон должен содержать только цифры, пробелы, скобки, дефисы и знак + (от 7 до 20 символов)";
        $field_errors['phone'] = "Формат: +7 (999) 123-45-67";
    }
    
    // 3. Валидация email с регулярным выражением
    $email_pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (empty($form_data['email'])) {
        $errors[] = "Поле E-mail обязательно для заполнения";
        $field_errors['email'] = "Поле обязательно для заполнения";
    } elseif (!preg_match($email_pattern, $form_data['email'])) {
        $errors[] = "Некорректный формат E-mail";
        $field_errors['email'] = "Формат: user@example.com";
    }
    
    // 4. Валидация даты рождения
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
    
    // 5. Валидация пола
    $allowed_genders = ['male', 'female', 'other'];
    if (empty($form_data['gender'])) {
        $errors[] = "Поле Пол обязательно для заполнения";
        $field_errors['gender'] = "Выберите пол";
    } elseif (!in_array($form_data['gender'], $allowed_genders)) {
        $errors[] = "Выбрано недопустимое значение пола";
        $field_errors['gender'] = "Некорректное значение";
    }
    
    // 6. Валидация языков программирования
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
    
    // 7. Валидация биографии (необязательное поле, но проверяем допустимые символы)
    if (!empty($form_data['bio']) && !preg_match("/^[А-Яа-яЁёA-Za-z0-9\s.,!?()-]+$/u", $form_data['bio'])) {
        $errors[] = "Биография содержит недопустимые символы";
        $field_errors['bio'] = "Используйте буквы, цифры, пробелы и знаки препинания";
    }
    
    // 8. Валидация контракта
    if (!$form_data['contract']) {
        $errors[] = "Необходимо подтвердить ознакомление с контрактом";
        $field_errors['contract'] = "Подтвердите ознакомление";
    }
    
    // Если есть ошибки - сохраняем в Cookies и перенаправляем GET
    if (!empty($errors)) {
        // Сохраняем ошибки и данные формы в Cookies
        setcookie('form_errors', json_encode($field_errors), 0, '/'); // До конца сессии
        setcookie('form_data', json_encode($form_data), 0, '/');
        
        // Перенаправляем обратно на форму методом GET
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } 
    // Если ошибок нет - сохраняем в БД и устанавливаем долгосрочные Cookies
    else {
        try {
            // Подключение к БД
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Подготовленный запрос для вставки пользователя
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, phone, email, birth_date, gender, bio, contract_accepted) 
                VALUES (:full_name, :phone, :email, :birth_date, :gender, :bio, :contract)
            ");
            
            $stmt->execute([
                ':full_name' => $form_data['full_name'],
                ':phone' => $form_data['phone'],
                ':email' => $form_data['email'],
                ':birth_date' => $form_data['birth_date'],
                ':gender' => $form_data['gender'],
                ':bio' => $form_data['bio'],
                ':contract' => $form_data['contract']
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Получаем ID языков из БД
            $lang_placeholders = implode(',', array_fill(0, count($form_data['languages']), '?'));
            $stmt = $pdo->prepare("SELECT id FROM programming_languages WHERE language_name IN ($lang_placeholders)");
            $stmt->execute($form_data['languages']);
            $language_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Вставляем связи пользователя с языками
            $stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (:user_id, :language_id)");
            foreach ($language_ids as $lang_id) {
                $stmt->execute([':user_id' => $user_id, ':language_id' => $lang_id]);
            }
            
            // Фиксируем транзакцию
            $pdo->commit();
            
            // Сохраняем данные в Cookies на 1 год для автозаполнения в будущем
            $cookies_data = [
                'full_name' => $form_data['full_name'],
                'phone' => $form_data['phone'],
                'email' => $form_data['email'],
                'birth_date' => $form_data['birth_date'],
                'gender' => $form_data['gender'],
                'bio' => $form_data['bio']
            ];
            
            setcookie('saved_form_data', json_encode($cookies_data), time() + 365*24*60*60, '/');
            
            $success_message = "Данные успешно сохранены! Спасибо за заполнение анкеты.";
            
            // Очищаем данные формы
            $form_data = [];
            
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Ошибка базы данных: " . $e->getMessage();
            
            // Сохраняем ошибку и перенаправляем
            setcookie('form_errors', json_encode(['db_error' => 'Ошибка сохранения в БД']), 0, '/');
            setcookie('form_data', json_encode($form_data), 0, '/');
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
    }
}

// Загружаем ошибки и данные из Cookies при GET запросе
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_COOKIE['form_errors'])) {
        $field_errors = json_decode($_COOKIE['form_errors'], true);
        // Удаляем Cookies после использования
        setcookie('form_errors', '', time() - 3600, '/');
    }
    
    if (isset($_COOKIE['form_data'])) {
        $cookie_data = json_decode($_COOKIE['form_data'], true);
        if (is_array($cookie_data)) {
            // Объединяем с существующими данными, но приоритет у Cookies
            $form_data = array_merge($form_data, $cookie_data);
        }
        setcookie('form_data', '', time() - 3600, '/');
    }
    
    // Устанавливаем флаг, что ошибки обработаны
    $_SESSION['errors_processed'] = true;
}
?>
