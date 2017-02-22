<?php

$dictionary = [
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
                'login' => 'Логин',
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
                'login' => 'Логин',
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
                'login' => 'Логин',
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
                'default_language' => 'Основной язык сайта',
                'languages' => 'Языки',
                'languages_key' => 'Код языка (2 символа)',
                'languages_value' => 'Название языка',
                'languages_add' => 'Добавить язык',
                'languages_delete' => 'Удалить язык',
                'fallback_languages' => 'Таблица замен локализаций',
                'fallback_languages_key' => 'Для языка (2 символа)',
                'fallback_languages_value' => 'Выдавать перевод на язык (2 символа)',
                'fallback_languages_add' => 'Добавить замену',
                'fallback_languages_delete' => 'Удалить замену',
            ],
            'tooltip' => [
                'browser_title_addition' => 'Не добавляется в случаях когда используется "Заголовок вкладки браузера по умолчанию"',
                'languages' => [
                    'Требуется указать как минимум 1 язык',
                    'Код языка должен содержать точно 2 латиских буквы, Пример: ru, en, fr'
                ],
                'fallback_languages' => [
                    'Код языка должен содержать точно 2 латиских буквы, Пример: ru, en, fr',
                    'Эта таблица будет использоваться в случаях когда не найден перевод на требуемый язык, но при этом "Основной язык" не подходит',
                    'Пример: английский язык выставленный как "Основной язык" не подходит для замены украинского. В этом случае русский язык будет подходящей заменой'
                ]
            ],
            'tab' => [
                'general' => 'Общее',
                'localization' => 'Локализации'
            ]
        ],
    ],
    'pages' => [
        'menu_title' => 'Страницы',
        'types' => [
            'page' => 'Страница',
            'news' => 'Новость',
            'category' => 'Категория',
            'item' => 'Товар'
        ],
        'datagrid' => [
            'header' => 'Страницы',
            'column' => [
                'id' => 'ID',
                'type' => 'Тип',
                'comment' => 'Комментарий',
                'url_alias' => 'Относительный URL',
                'relative_url' => 'URL',
                'page_code' => 'Текстовый ID',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Порядковый номер',
                'with_contact_form' => 'Форма связи?',
                'custom_info' => 'Доп. информация',
                'admin_id' => 'Последний редактор',
                'is_published' => 'Опубликована?',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'text_id' => 'Заголовок страницы',
            ],
            'filter' => [
                'pages' => [
                    'id' => 'ID',
                    'parent_id' => 'ID родительской страницы',
                    'type' => 'Тип',
                    'comment' => 'Комментарий',
                    'url_alias' => 'Относительный URL',
                    'page_code' => 'Текстовый ID',
                    'images' => 'Картинки',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'order' => 'Порядковый номер',
                    'with_contact_form' => 'Добавить форму обратной связи?',
                    'custom_info' => 'Доп. информация',
                    'admin_id' => 'Последний редактор',
                    'is_published' => 'Опубликована?',
                    'created_at' => 'Создана',
                    'updated_at' => 'Изменена',
                ],
                'primary_text' => [
                    'id' => 'ID текста',
                    'title' => 'Заголовок страницы',
                    'browser_title' => 'Заголовок браузера',
                    'menu_title' => 'Название в меню',
                    'content' => 'Текcт страницы',
                ],
                'parent' => [
                    'id' => 'ID родительской страницы',
                    'url_alias' => 'Относительный URL родительской страницы'
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Добавление страницы',
            'header_edit' => 'Редактирование страницы',
            'tab' => [
                'general' => 'Общее',
                'images' => 'Картинки'
            ],
            'input' => [
                'id' => 'ID',
                'type' => 'Тип',
                'comment' => 'Комментарий',
                'url_alias' => 'URL страницы',
                'url_alias_placeholder' => 'Примеры: /nazvanie-stranici, /ketegoriya/tovar',
                'page_code' => 'Текстовый идентификатор страницы (используется программистом по необходимости)',
                'images' => 'Картинки',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Порядковый номер',
                'with_contact_form' => 'Добавить форму обратной связи?',
                'custom_info' => 'Доп. информация',
                'admin_id' => 'Последний редактор',
                'is_published' => 'Опубликована?',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'text_id' => 'Тексты для страницы',
            ],
            'tooltip' => [
                'meta_description' => 'Используется в случае если Meta-description не указан в прикрепленных текстах',
                'meta_keywords' => 'Используется в случае если Meta-keywords не указан в прикрепленных текстах',
                'url_alias' => 'Должен начинаться с символа "/" и может содержать только латинские буквы, цифры, "-", "_" и "/"',
            ],
            'validation' => [
                'unique_page_url' => 'Страница с тким URL уже <a href=":url" target="_blank">существует</a>'
            ]
        ],
        'item_details' => [
            'header' => 'Информация о странице',
            'field' => [
                'id' => 'ID',
                'parent_id' => 'Принадлежит странице',
                'type' => 'Тип',
                'comment' => 'Комментарий',
                'relative_url' => 'URL',
                'url_alias' => 'Относительный URL',
                'page_code' => 'Текстовый идентификатор страницы',
                'images' => 'Картинки',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Порядковый номер',
                'with_contact_form' => 'Добавить форму обратной связи?',
                'custom_info' => 'Доп. информация',
                'admin_id' => 'Последний редактор',
                'is_published' => 'Опубликована?',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'text_id' => 'Тексты для страницы',
            ]
        ]
    ],
    'texts' => [
        'menu_title' => 'Тексты для страниц',
        'datagrid' => [
            'header' => 'Тексты для страниц',
            'column' => [
                'id' => 'ID',
                'parent_id' => 'Перевод для',
                'language' => 'Язык текстов',
                'title' => 'Полное название',
                'menu_title' => 'Короткое название (для меню)',
                'browser_title' => 'Заголовок браузера',
                'comment' => 'Комментарий',
                'content' => 'Текcт',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Доп. информация',
                'admin_id' => 'Последний редактор',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена'
            ],
            'filter' => [
                'texts' => [
                    'id' => 'ID',
                    'parent_id' => 'Перевод для',
                    'language' => 'Язык текстов',
                    'title' => 'Полное название',
                    'menu_title' => 'Короткое название (для меню)',
                    'browser_title' => 'Заголовок браузера',
                    'comment' => 'Комментарий',
                    'content' => 'Текcт',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'custom_info' => 'Доп. информация',
                    'created_at' => 'Создана',
                    'updated_at' => 'Изменена',
                    'admin_id' => 'Последний редактор'
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Добавление текстов для страницы',
            'header_edit' => 'Редактирование текстов для страницы',
            'tab' => [
                'general' => 'Общее',
                'content' => 'Текст',
            ],
            'input' => [
                'id' => 'ID',
                'is_translation' => 'Перевод существующих текстов?',
                'parent_id' => 'Перевод для текстов',
                'language' => 'Язык текстов',
                'title' => 'Полное название',
                'menu_title' => 'Короткое название (для меню)',
                'browser_title' => 'Заголовок браузера',
                'comment' => 'Комментарий к редактированию текста',
                'content' => 'Текcт',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Доп. информация',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'insert_other_text_widget_title_template' => 'Вставка поля ":text_field.label" из текстов ":text_id.label"'
            ],
            'validation' => [
                'unique_language_within_parent_id' => 'Перевод для текста ":parent_title" на указанный язык уже <a href=":url" data-toggle="tooltip" title="Загрузить перевод">существует</a>'
            ]
        ],
        'item_details' => [
            'header' => 'Тексты для страницы',
            'field' => [
                'id' => 'ID',
                'parent_id' => 'Перевод для текстов',
                'language' => 'Язык текстов',
                'title' => 'Полное название',
                'menu_title' => 'Короткое название (для меню)',
                'browser_title' => 'Заголовок браузера',
                'comment' => 'Комментарий',
                'content' => 'Текcт',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Доп. информация',
                'created_at' => 'Создана',
                'updated_at' => 'Изменена',
                'admin_id' => 'Последний редактор'
            ]
        ]
    ],
    'texts_for_pages' => [
    ],
    'texts_for_news' => [
        'menu_title' => 'Тексты для новостей',
        'datagrid' => [
            'header' => 'Тексты для новостей',
        ],
        'form' => [
            'header_create' => 'Добавление текстов для новости',
            'header_edit' => 'Редактирование текстов для новости',
        ],
        'item_details' => [
            'header' => 'Тексты для новости',
        ]
    ],
    'texts_for_categories' => [
        'menu_title' => 'Тексты для категорий',
        'datagrid' => [
            'header' => 'Тексты для категорий',
        ],
        'form' => [
            'header_create' => 'Добавление текстов для категории',
            'header_edit' => 'Редактирование текстов для категории',
        ],
        'item_details' => [
            'header' => 'Тексты для категории',
        ]
    ],
    'texts_for_items' => [
        'menu_title' => 'Тексты для товаров',
        'datagrid' => [
            'header' => 'Тексты для товаров',
        ],
        'form' => [
            'header_create' => 'Добавление текстов для товара',
            'header_edit' => 'Редактирование текстов для товара',
        ],
        'item_details' => [
            'header' => 'Тексты для товара',
        ]
    ],
];

$dictionary['texts_for_pages'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_pages']);
$dictionary['texts_for_news'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_news']);
$dictionary['texts_for_categories'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_categories']);
$dictionary['texts_for_items'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_items']);

return $dictionary;