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
            position: relative;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .user-info a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }

        .user-info a:hover {
            text-decoration: underline;
        }

        .form-content {
            padding: 40px;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .tab {
            padding: 10px 20px;
            background: none;
            border: none;
            font-size: 1em;
            cursor: pointer;
            color: #666;
            transition: all 0.3s;
        }

        .tab.active {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            margin-bottom: -2px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
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
        input[type="password"],
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

        .credentials-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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
            
            .user-info {
                position: static;
                text-align: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
    <h1>Регистрационная анкета</h1>
    <p>Пожалуйста, заполните все поля формы</p>
    <div class="user-info">
        <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
            Привет, <?php echo htmlspecialchars($_SESSION['login']); ?>
            <a href="?action=logout">Выйти</a>
        <?php endif; ?>
        <a href="admin.php" style="margin-left: 15px; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white;"> Админ-панель</a>
    </div>
</div>
        
        <div class="form-content">
            <?php if (isset($success_message) && $success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message) && $error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']): ?>
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('register')">Регистрация</button>
                    <button class="tab" onclick="switchTab('login')">Вход</button>
                </div>
                
                <div id="register-pane" class="tab-pane active">
                    <?php include 'form_template.php'; ?>
                </div>
                
                <div id="login-pane" class="tab-pane">
                    <form method="POST" action="">
                        <div class="form-group <?php echo isset($field_errors['login']) ? 'error-group' : ''; ?>">
                            <label for="login">Логин</label>
                            <input type="text" id="login" name="login" required>
                            <?php if (isset($field_errors['login'])): ?>
                                <span class="error-message"><?php echo htmlspecialchars($field_errors['login']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group <?php echo isset($field_errors['login']) ? 'error-group' : ''; ?>">
                            <label for="password">Пароль</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" name="login_action" class="btn-submit">Войти</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="credentials-box">
                    <strong>Вы авторизованы как:</strong> <?php echo htmlspecialchars($_SESSION['login']); ?>
                </div>
                <?php include 'form_template.php'; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.getElementById('register-pane').classList.remove('active');
            document.getElementById('login-pane').classList.remove('active');

            document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));

            if (tab === 'register') {
                document.getElementById('register-pane').classList.add('active');
                event.target.classList.add('active');
            } else {
                document.getElementById('login-pane').classList.add('active');
                event.target.classList.add('active');
            }
        }
    </script>
</body>
</html>
