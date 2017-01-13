<?php

return [
    'default_page_title' => 'PeskyCMF',
    'language' => [
        'en' => 'English',
        'ru' => 'Русский'
    ],
    'main_menu' => [
        'header' => 'Навигация',
    ],
    'login_form' => [
        'header' => 'Авторизация',
        'email_label' => 'E-mail',
        'password_label' => 'Пароль',
        'button_label' => 'Войти',
        'forgot_password_label' => 'Забыли пароль?',
        'login_failed' => 'Неправильный E-mail или пароль',
    ],
    'forgot_password' => [
        'header' => 'Восстановление пароля',
        'email_label' => 'Ваш E-mail',
        'button_label' => 'Выслать инструкции',
        'instructions_sent' => 'Инструкции по восстановлению пароля высланы на Ваш E-mail',
        'email_subject' => 'Инструкции по восстановлению пароля',
        'email_content' => '<p>Для задания нового пароля, пройдите по ссылке <a href=":url" target="_blank">Восстановить пароль</a></p>
            <p>Восстановление пароля будет доступно в течении часа</p>',
    ],
    'replace_password' => [
        'header' => 'Задание нового пароля',
        'password_label' => 'Новый нароль',
        'password_confirm_label' => 'Подтвердите новый пароль',
        'button_label' => 'Сохранить',
        'invalid_access_key' => 'Неправильный или просроченный ключ доступа к странице',
        'password_replaced' => 'Новый пароль сохранен',
        'failed_to_save' => 'Не удалось сохранить новый пароль',
    ],
    'user' => [
        'profile_label' => 'Профиль',
        'logout_label' => 'Выйти',
    ],
    'admins' => [
        'menu_title' => 'Администраторы',
        'role' => [
            'admin' => 'Администратор',
            'superadmin' => 'Суперадмин'
        ],
        'datagrid' => [
            'header' => 'Администраторы системы',
            'column' => [
                'id' => 'ID',
                'parent_id' => 'Создатель',
                'email' => 'E-mail',
                'name' => 'Имя',
                'is_active' => 'Действующий?',
                'is_superadmin' => 'Суперадмин?',
                'role' => 'Роль',
                'language' => 'Язык',
                'ip' => 'IP',
                'created_at' => 'Создан',
                'timezone' => 'Временная зона',
            ]
        ],
        'form' => [
            'header_create' => 'Добавление администратора',
            'header_edit' => 'Редактирование администратора',
            'input' => [
                'email' => 'E-mail',
                'password' => 'Пароль',
                'name' => 'Имя',
                'language' => 'Язык',
                'is_active' => 'Действующий?',
                'role' => 'Роль',
                'is_superadmin' => 'Имеет доступ ко всему (суперадмин)',
                'timezone' => 'Временная зона',
            ]

        ],
        'item_details' => [
            'header' => 'Информация об администраторе',
            'field' => [
                'id' => 'ID',
                'email' => 'E-mail',
                'password' => 'Пароль',
                'name' => 'Имя',
                'language' => 'Язык',
                'is_active' => 'Действующий?',
                'role' => 'Роль',
                'is_superadmin' => 'Имеет доступ ко всему (суперадмин)?',
                'parent_id' => 'Админ, создавший этот акаунт',
                'created_at' => 'Создан',
                'updated_at' => 'Изменен',
                'timezone' => 'Временная зона',
            ]
        ]
    ],
    'page' => [
        'about' => [
            'link_label' => 'О проекте'
        ],
        'dashboard' => [
            'header' => 'Добро пожаловать в Административную панель на основе PeskyCMF',
            'menu_title' => 'Главная'
        ],
        'profile' => [
            'header' => 'Профиль Администратора',
            'input' => [
                'email' => 'E-mail',
                'new_password' => 'Новый пароль',
                'old_password' => 'Текущий пароль',
                'language' => 'Язык',
                'name' => 'Имя',
                'timezone' => 'Временная зона',
                'no_timezone' => '[Не выбрана]',
                'timezone_search' => 'Поиск по названию зоны или смещению',
            ],
            'saved' => 'Профиль Администратора изменен',
            'errors' => [
                'new_password' => [
                    'min' => 'Минимальная длина пароля :max символов'
                ],
                'old_password' => [
                    'required' => 'Введите текущий пароль чтобы сохранить данные',
                    'match' => 'Введен неравильный текущий пароль',
                ],
                'email' => [
                    'required' => 'Введите E-mail адрес',
                    'email' => 'Введите правильный E-mail адрес',
                    'unique' => 'Такой E-mail адрес уже используется другим администратором',
                ],
                'login' => [
                    'required' => 'Введите логин',
                    'regex' => 'Логин может содержать только латинские буквы, цифры, "_", "-", "@", "."',
                    'min' => 'Минимальная длина логина: :min символов',
                    'unique' => 'Такой логин уже используется другим администратором',
                ],
                'language' => [
                    'required' => 'Выберите язык',
                    'in' => 'Язык не входит в список разрешенных. Выберите другой язык.',
                ],
                'timezone' => [
                    'required' => 'Выберите временную зону',
                    'exists' => 'Выбранная временная зона не найдена среди допустимых временных зон. Выберите другую временную зону.'
                ]
            ]
        ]
    ],
    'settings' => [
        'menu_title' => 'Настройки',
        'form' => [
            'header_create' => 'Настройки системы',
            'header_edit' => 'Настройки системы',
            'input' => [
                'default_browser_title' => 'Заголовок вкладки браузера по умолчанию',
                'browser_title_addition' => 'Дополнение к заголовку вкладки браузера',
                'languages' => 'Языки',
                'languages_key' => 'Код языка (2 символа)',
                'languages_value' => 'Название языка',
                'languages_add' => 'Добавить язык',
                'languages_delete' => 'Удалить язык',
            ],
            'tooltip' => [
                'browser_title_addition' => 'Не добавляется в случаях когда используется "Заголовок вкладки браузера по умолчанию"',
                'languages' => [
                    'Требуется указать как минимум 1 язык',
                    'Код языка должен содержать точно 2 латиских буквы, Пример: ru, en, fr'
                ]
            ],
        ],
    ]
];
