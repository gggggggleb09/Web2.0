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
                   <?php echo (isset($form_data
