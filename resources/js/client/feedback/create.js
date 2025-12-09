document.addEventListener('DOMContentLoaded', function() {
    const switchInput = document.getElementById('is_anonymous_switch');
    const hiddenInput = document.getElementById('is_anonymous');
    const contactFields = document.getElementById('contact-fields');
    const fullNameField = document.getElementById('full_name');
    const phoneField = document.getElementById('phone');
    const messageField = document.getElementById('message');
    const form = document.getElementById('feedback-form');

    // Валидация полей
    const validators = {
        full_name: function(value) {
            if (!value || value.trim().length === 0) {
                return { valid: false, message: 'Пожалуйста, укажите ваше ФИО' };
            }
            if (value.trim().length < 3) {
                return { valid: false, message: 'ФИО должно содержать минимум 3 символа' };
            }
            return { valid: true, message: '' };
        },
        phone: function(value) {
            if (!value || value.trim().length === 0) {
                return { valid: false, message: 'Пожалуйста, укажите ваш номер телефона' };
            }
            // Удаляем все пробелы и дефисы для проверки
            const cleanPhone = value.replace(/\s|-/g, '');
            // Проверка формата: 996 + 9 цифр = 12 цифр всего
            if (!/^996\d{9}$/.test(cleanPhone)) {
                return { valid: false, message: 'Телефон должен быть в формате 996XXXXXXXXX (996 + 9 цифр)' };
            }
            return { valid: true, message: '' };
        },
        message: function(value) {
            if (!value || value.trim().length === 0) {
                return { valid: false, message: 'Пожалуйста, напишите ваше сообщение' };
            }
            if (value.trim().length < 10) {
                return { valid: false, message: 'Сообщение должно содержать минимум 10 символов' };
            }
            if (value.length > 5000) {
                return { valid: false, message: 'Сообщение не должно превышать 5000 символов' };
            }
            return { valid: true, message: '' };
        }
    };

    // Функция для валидации поля
    function validateField(field, fieldName, forceShow = false) {
        const value = field.value;
        const validator = validators[fieldName];

        if (!validator) return true;

        const result = validator(value);
        const feedbackElement = document.getElementById(fieldName + '_feedback');

        // Убираем старые классы
        field.classList.remove('is-valid', 'is-invalid');

        // Показываем ошибки если поле не пустое ИЛИ если принудительно показать (при submit)
        if (value.length > 0 || forceShow) {
            if (result.valid && value.length > 0) {
                field.classList.add('is-valid');
                if (feedbackElement) {
                    feedbackElement.innerHTML = '<div class="valid-feedback"></div>';
                    feedbackElement.className = 'validation-feedback';
                }
            } else {
                field.classList.add('is-invalid');
                if (feedbackElement) {
                    feedbackElement.innerHTML = '<div class="invalid-feedback"><strong>' + result.message + '</strong></div>';
                    feedbackElement.className = 'validation-feedback';
                }
            }
        } else {
            // Если поле пустое и не принудительно, убираем все индикаторы
            if (feedbackElement) {
                feedbackElement.innerHTML = '';
                feedbackElement.className = 'validation-feedback';
            }
        }

        return result.valid;
    }

    // Функция для форматирования телефона (автоматически добавляет 996)
    function formatPhone(value) {
        // Удаляем все нецифровые символы
        let digits = value.replace(/\D/g, '');

        // Ограничиваем до 12 цифр (996 + 9 цифр)
        digits = digits.substring(0, 12);

        return digits;
    }

    function toggleContactFields() {
        // Switch включен = показать контакты (is_anonymous = 0)
        // Switch выключен = скрыть контакты (is_anonymous = 1)
        const showContacts = switchInput.checked;

        // Обновляем скрытое поле
        hiddenInput.value = showContacts ? '0' : '1';

        if (showContacts) {
            // Показываем поля контактов с плавной анимацией
            contactFields.style.display = 'block';
            // Небольшая задержка для плавной анимации
            setTimeout(() => {
                contactFields.style.opacity = '1';
                contactFields.style.transform = 'translateY(0)';
            }, 10);
        } else {
            // Скрываем поля контактов с плавной анимацией
            contactFields.style.opacity = '0';
            contactFields.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                contactFields.style.display = 'none';
            }, 300);

            // Очищаем поля и сбрасываем валидацию
            if (fullNameField) {
                fullNameField.value = '';
                fullNameField.classList.remove('is-valid', 'is-invalid');
                const feedback = document.getElementById('full_name_feedback');
                if (feedback) feedback.innerHTML = '';
                if (document.activeElement === fullNameField) {
                    fullNameField.blur();
                }
            }
            if (phoneField) {
                phoneField.value = '';
                phoneField.classList.remove('is-valid', 'is-invalid');
                const feedback = document.getElementById('phone_feedback');
                if (feedback) feedback.innerHTML = '';
                if (document.activeElement === phoneField) {
                    phoneField.blur();
                }
            }
        }
    }

    // Добавляем обработчики событий для switch
    if (switchInput) {
        switchInput.addEventListener('change', toggleContactFields);
    }

    // Валидация в реальном времени для ФИО
    if (fullNameField) {
        fullNameField.addEventListener('input', function() {
            validateField(this, 'full_name');
        });
        fullNameField.addEventListener('blur', function() {
            validateField(this, 'full_name');
        });
    }

    // Валидация в реальном времени для телефона с автоформатированием
    if (phoneField) {
        phoneField.addEventListener('input', function(e) {
            const formatted = formatPhone(this.value);
            if (formatted !== this.value) {
                this.value = formatted;
            }
            validateField(this, 'phone');
        });
        phoneField.addEventListener('blur', function() {
            validateField(this, 'phone');
        });
    }

    // Валидация в реальном времени для сообщения
    if (messageField) {
        messageField.addEventListener('input', function() {
            validateField(this, 'message');
        });
        messageField.addEventListener('blur', function() {
            validateField(this, 'message');
        });
    }

    // Валидация формы перед отправкой
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Валидируем сообщение всегда (с принудительным показом ошибок)
            if (messageField) {
                if (!validateField(messageField, 'message', true)) {
                    isValid = false;
                }
            }

            // Валидируем контактные поля только если switch включен (с принудительным показом ошибок)
            if (switchInput && switchInput.checked) {
                if (fullNameField) {
                    if (!validateField(fullNameField, 'full_name', true)) {
                        isValid = false;
                    }
                }
                if (phoneField) {
                    if (!validateField(phoneField, 'phone', true)) {
                        isValid = false;
                    }
                }
            }

            if (!isValid) {
                e.preventDefault(); // Блокируем отправку только если есть ошибки
                // Прокручиваем к первому невалидному полю
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        firstInvalid.focus();
                    }, 300);
                }
                return false;
            }

            // Если все валидно, форма отправится автоматически
        });
    }

    // Инициализация при загрузке страницы
    toggleContactFields();

    // Валидация существующих значений при загрузке
    if (fullNameField && fullNameField.value) {
        validateField(fullNameField, 'full_name');
    }
    if (phoneField && phoneField.value) {
        validateField(phoneField, 'phone');
    }
    if (messageField && messageField.value) {
        validateField(messageField, 'message');
    }
});

