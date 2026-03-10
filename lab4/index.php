<?php
require_once 'form_handler.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета пользователя</title>
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
            max-width: 800px;
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
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .form-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group.error-group label {
            color: #dc3545;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95em;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .error-group input,
        .error-group select,
        .error-group textarea {
            border-color: #dc3545 !important;
            background-color: #fff8f8;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .radio-group.error-group {
            padding: 10px;
            border: 2px solid #dc3545;
            border-radius: 8px;
            background-color: #fff8f8;
        }

        .radio-group label {
            display: inline;
            margin-right: 10px;
            font-weight: normal;
        }

        .radio-group input[type="radio"] {
            margin-right: 5px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group.error-group {
            padding: 10px;
            border: 2px solid #dc3545;
            border-radius: 8px;
            background-color: #fff8f8;
        }

        .checkbox-group label {
            display: inline;
            margin: 0;
            font-weight: normal;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        select[multiple] {
            height: 150px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
            font-weight: normal;
        }

        .error-summary {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #f5c6cb;
        }

        .error-summary ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        .field-hint {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-content {
                padding: 20px;
            }
            
            .radio-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Регистрационная анкета</h1>
            <p>Пожалуйста, заполните все поля формы</p>
        </div>
        
        <div class="form-content">
            <?php if (isset($success_message) && $success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($field_errors)): ?>
                <div class="error-summary">
                    <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul>
                        <?php foreach ($field_errors as $field => $error): ?>
                            <?php if ($field !== 'db_error'): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group <?php echo isset($field_errors['full_name']) ? 'error-group' : ''; ?>">
                    <label for="full_name">ФИО *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo isset($form_data['full_name']) ? htmlspecialchars($form_data['full_name']) : ''; ?>"
                           placeholder="Иванов Иван Иванович" required>
                    <span class="field-hint">Только буквы, пробелы и дефисы</span>
                    <?php if (isset($field_errors['full_name'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['full_name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($field_errors['phone']) ? 'error-group' : ''; ?>">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>"
                           placeholder="+7 (999) 123-45-67" required>
                    <span class="field-hint">Цифры, пробелы, скобки, дефисы и знак +</span>
                    <?php if (isset($field_errors['phone'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['phone']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($field_errors['email']) ? 'error-group' : ''; ?>">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>"
                           placeholder="ivanov@example.com" required>
                    <span class="field-hint">Формат: user@domain.com</span>
                    <?php if (isset($field_errors['email'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['email']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($field_errors['birth_date']) ? 'error-group' : ''; ?>">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" 
                           value="<?php echo isset($form_data['birth_date']) ? htmlspecialchars($form_data['birth_date']) : ''; ?>"
                           required>
                    <span class="field-hint">Формат: ГГГГ-ММ-ДД</span>
                    <?php if (isset($field_errors['birth_date'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['birth_date']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Пол *</label>
                    <div class="radio-group <?php echo isset($field_errors['gender']) ? 'error-group' : ''; ?>">
                        <input type="radio" id="male" name="gender" value="male" 
                               <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'male') ? 'checked' : ''; ?> required>
                        <label for="male">Мужской</label>
                        
                        <input type="radio" id="female" name="gender" value="female"
                               <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'female') ? 'checked' : ''; ?> required>
                        <label for="female">Женский</label>
                        
                        <input type="radio" id="other" name="gender" value="other"
                               <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'other') ? 'checked' : ''; ?> required>
                        <label for="other">Другой</label>
                    </div>
                    <?php if (isset($field_errors['gender'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['gender']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($field_errors['languages']) ? 'error-group' : ''; ?>">
                    <label for="languages">Любимые языки программирования * (можно выбрать несколько)</label>
                    <select id="languages" name="languages[]" multiple required>
                        <?php
                        $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                        $selected_languages = isset($form_data['languages']) ? $form_data['languages'] : [];
                        foreach ($languages as $lang):
                        ?>
                            <option value="<?php echo $lang; ?>" 
                                <?php echo (is_array($selected_languages) && in_array($lang, $selected_languages)) ? 'selected' : ''; ?>>
                                <?php echo $lang; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($field_errors['languages'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['languages']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($field_errors['bio']) ? 'error-group' : ''; ?>">
                    <label for="bio">Биография</label>
                    <textarea id="bio" name="bio" placeholder="Расскажите о себе..."><?php echo isset($form_data['bio']) ? htmlspecialchars($form_data['bio']) : ''; ?></textarea>
                    <span class="field-hint">Буквы, цифры, пробелы и знаки препинания</span>
                    <?php if (isset($field_errors['bio'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['bio']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group <?php echo isset($field_errors['contract']) ? 'error-group' : ''; ?>">
                        <input type="checkbox" id="contract" name="contract" value="1" required
                               <?php echo isset($form_data['contract']) && $form_data['contract'] ? 'checked' : ''; ?>>
                        <label for="contract">Я ознакомлен(а) с контрактом *</label>
                    </div>
                    <?php if (isset($field_errors['contract'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($field_errors['contract']); ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="submit" class="btn-submit">Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>
