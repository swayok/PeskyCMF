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
        'registration_label' => 'Зарегистрироваться',
        'login_failed' => 'Неправильный E-mail или пароль',
    ],
    'registration_form' => [
        'header' => 'Регистрация',
        'email_label' => 'E-mail',
        'login_label' => 'Логин',
        'name_label' => 'Имя',
        'password_label' => 'Пароль',
        'password_confirmation_label' => 'Повторите пароль',
        'button_label' => 'Зарегистрироваться',
        'login_to_account_label' => 'Авторизоваться',
        'errors' => [
            'password' => [
                'min' => 'Минимальная длина пароля :min символов',
                'confirmed' => 'Пароли не совпадают'
            ],
            'email' => [
                'required' => 'Введите E-mail адрес',
                'email' => 'Введите правильный E-mail адрес',
                'unique' => 'Такой E-mail адрес уже используется другим пользователем',
            ],
            'login' => [
                'required' => 'Введите логин',
                'regex' => 'Логин может содержать только латинские буквы, цифры, "_", "-", "@", "."',
                'min' => 'Минимальная длина логина: :min символов',
                'unique' => 'Такой логин уже используется другим пользователем',
            ],
        ]
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
    ],
    'user' => [
        'profile_label' => 'Профиль',
        'logout_label' => 'Выйти',
    ],
    'admins' => [
        'menu_title' => 'Администраторы',
        'login_as' => [
            'success' => 'Вы вошли под учетной записью :user. Чтобы вернуться в свою учетную запись нажмите кнопку "Выйти".',
            'fail' => 'Не удалось войти под учетной записью c ID :id',
            'same_user' => 'Вы пытаетесь зайти в свою же учетную запись',
            'no_auth_token' => 'У текущего пользователя отсутствует токен авторизации',
        ],
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
            ],
            'filter' => [
                'cms_admins' => [
                    'id' => 'ID',
                    'parent_id' => 'ID Создателя',
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
                ],
                'parent_admin' => [
                    'email' => 'E-mail Создателя',
                    'login' => 'Логин Создателя',
                    'name' => 'Имя Создателя',
                ]
            ],
            'action' => [
                'login_as' => 'Войти от имени этого пользователя'
            ],
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
            ],
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
            ],
            'action' => [
                'login_as' => 'Войти от имени этого пользователя'
            ],
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
            'header' => 'Настройки аккаунта',
            'input' => [
                'email' => 'E-mail',
                'new_password' => 'Новый пароль',
                'old_password' => 'Текущий пароль',
                'language' => 'Язык',
                'name' => 'Имя',
                'timezone' => 'Временная зона',
                'no_timezone' => '[Не выбрана]',
                'timezone_search' => 'Поиск по названию зоны',
            ],
            'saved' => 'Настройки аккаунта сохранены',
            'errors' => [
                'new_password' => [
                    'min' => 'Минимальная длина пароля :min символов'
                ],
                'old_password' => [
                    'required' => 'Введите текущий пароль чтобы сохранить данные',
                    'match' => 'Введен неправильный текущий пароль',
                ],
                'email' => [
                    'required' => 'Введите E-mail адрес',
                    'email' => 'Введите правильный E-mail адрес',
                    'unique' => 'Такой E-mail адрес уже используется другим пользователем',
                ],
                'login' => [
                    'required' => 'Введите логин',
                    'regex' => 'Логин может содержать только латинские буквы, цифры, "_", "-", "@", "."',
                    'min' => 'Минимальная длина логина: :min символов',
                    'unique' => 'Такой логин уже используется другим пользователем',
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
    'api_docs' => [
        'menu_title' => 'Документация к API',
        'header' => 'Документация к API',
        'description' => 'Описание',
        'headers' => 'HTTP headers',
        'header_name' => 'Имя',
        'header_value' => 'Значение',
        'header_description' => 'Описание',
        'url_params' => 'URL параметры (параметры внутри самого URL-адреса)',
        'url_query_params' => 'HTTP GET параметры (URL query params)',
        'post_params' => 'HTTP POST параметры',
        'response' => 'Ответ сервера при успешном выполнении запроса',
        'param_name' => 'Имя',
        'param_type' => 'Тип',
        'param_description' => 'Описание',
        'errors' => 'Ошибки, которые могут возникнуть',
        'error_http_code' => 'HTTP код',
        'error_title' => 'Ошибка',
        'error_response' => 'Ответ',
        'error_description' => 'Описание',
        'download_postman_collection' => 'Скачать коллекцию запросов для Postman',
        'postman_collection_file_name' => 'postman_collection_for_api_on_:http_host',
        'section' => [
            'no_section' => 'Не сгруппированные методы API'
        ],
        'error' => [
            'auth_failure' => [
                'title' => 'Не удалось авторизовать пользователя',
                'description' => '',
            ],
            'access_denied' => [
                'title' => 'Доступ запрещен',
                'description' => '',
            ],
            'validation_errors' => [
                'title' => 'Ошибки валидации данных',
                'description' => '',
            ],
            'server_error' => [
                'title' => 'Критическая ошибка на стороне сервера',
                'description' => '',
            ],
            'item_not_found' => [
                'title' => 'Объект не найден',
                'description' => 'Запрошенный объект не найден в базе данных',
            ]
        ]
    ],
    'cms_pages' => [
        'menu_title' => 'Страницы',
        'type' => [
            'page' => 'Страница',
            'news' => 'Новость',
            'category' => 'Категория',
            'item' => 'Товар',
            'text_element' => 'Текстовый блок',
            'menu' => 'Меню',
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
                'publish_at' => 'Дата публикации',
                'title' => 'Название',
            ],
            'filter' => [
                'cms_pages' => [
                    'id' => 'ID',
                    'parent_id' => 'ID родительской страницы',
                    'type' => 'Тип',
                    'title' => 'Название',
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
                    'publish_at' => 'Дата публикации',
                ],
                'parent' => [
                    'id' => 'ID родительской страницы',
                    'url_alias' => 'Относительный URL родительской страницы',
                    'title' => 'Название родительской страницы',
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Добавление страницы',
            'header_edit' => 'Редактирование страницы',
            'tab' => [
                'general' => 'Общее',
                'images' => 'Картинки',
                'texts' => 'Тексты (:language)'
            ],
            'input' => [
                'id' => 'ID',
                'type' => 'Тип',
                'comment' => 'Комментарий',
                'url_alias' => 'URL страницы',
                'url_alias_placeholder' => 'Примеры: /nazvanie-stranici, /ketegoriya/tovar',
                'page_code' => 'Текстовый ID (используется программистом по необходимости)',
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
                'publish_at' => 'Дата публикации',
                'title' => 'Название (используется только в административной панели)',
                'texts' => [
                    'title' => 'Полное название',
                    'menu_title' => 'Короткое название (для меню)',
                    'browser_title' => 'Заголовок браузера',
                    'comment' => 'Комментарий к редактированию текста',
                    'content' => 'Текcт',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                ],
                'content_inserts' => [
                    'part_of_other_page' => 'Часть другой страницы',
                    'page_id_arg_label' => 'Выберите страницу',
                    'page_field_arg_label' => 'Выберите какую часть выбранной страницы вставить',
                    'page_insert_widget_title_template' => 'Вставка поля ":page_field.label" со страницы ":page_id.label"',
                    'text_block' => 'Текстовый блок',
                    'text_block_id_arg_label' => 'Выберите текстовый блок',
                    'text_block_insert_widget_title_template' => 'Вставка текстового блока ":page_id.label"',
                    'link_to_other_page' => 'Ссылка на страницу',
                    'page_link_title_arg_label' => 'Текст ссылки (по умолчанию берется из поля "Короткое название" страницы)',
                    'insert_link_to_page_widget_title_template' => 'Ссылка на страницу ":page_id.label" (Текст: :title)',
                ],
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
            'tab' => [
                'general' => 'Общее',
                'images' => 'Картинки',
                'texts' => 'Тексты (:language)'
            ],
            'field' => [
                'id' => 'ID',
                'parent_id' => 'Принадлежит странице',
                'type' => 'Тип',
                'comment' => 'Комментарий',
                'relative_url' => 'URL',
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
                'text_id' => 'Тексты для страницы',
                'publish_at' => 'Дата публикации',
                'title' => 'Название',
                'texts' => [
                    'id' => 'ID',
                    'title' => 'Полное название',
                    'language' => 'Язык',
                    'menu_title' => 'Короткое название (для меню)',
                    'browser_title' => 'Заголовок браузера',
                    'comment' => 'Комментарий к редактированию текста',
                    'content' => 'Текcт',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'admin_id' => 'Последний редактор',
                ],
            ]
        ]
    ],
    'cms_news' => [
        'menu_title' => 'Новости',
        'datagrid' => [
            'header' => 'Новости',
        ],
        'form' => [
            'header_create' => 'Добавление новости',
            'header_edit' => 'Редактирование новости',
        ],
        'item_details' => [
            'header' => 'Просмотр новости',
        ]
    ],
    'cms_shop_categories' => [
        'menu_title' => 'Магазин: категории',
        'datagrid' => [
            'header' => 'Магазин: категории',
        ],
        'form' => [
            'header_create' => 'Добавление категории в магазин',
            'header_edit' => 'Редактирование категории магазина',
        ],
        'item_details' => [
            'header' => 'Просмотр категории магазина',
        ]
    ],
    'cms_shop_items' => [
        'menu_title' => 'Магазин: товары',
        'datagrid' => [
            'header' => 'Магазин: товары',
        ],
        'form' => [
            'header_create' => 'Добавление товара в магазин',
            'header_edit' => 'Редактирование товара в магазине',
        ],
        'item_details' => [
            'header' => 'Просмотр товара в магазине',
        ]
    ],
    'cms_text_elements' => [
        'menu_title' => 'Текстовые блоки',
        'datagrid' => [
            'header' => 'Текстовые блоки',
        ],
        'form' => [
            'header_create' => 'Добавление текстового блока',
            'header_edit' => 'Редактирование текстового блока',
        ],
        'item_details' => [
            'header' => 'Просмотр текстового блока',
        ]
    ],
    'cms_menus' => [
        'menu_title' => 'Меню',
        'datagrid' => [
            'header' => 'Меню',
        ],
        'form' => [
            'header_create' => 'Добавление меню',
            'header_edit' => 'Редактирование меню',
            'input' => [
                'texts' => [
                    'menu_title' => 'Заголовок меню',
                ]
            ]
        ],
        'item_details' => [
            'header' => 'Просмотр меню',
            'field' => [
                'texts' => [
                    'menu_title' => 'Заголовок меню',
                ]
            ]
        ]
    ],
    'cms_redirects' => [
        'menu_title' => 'Перенаправления',
        'datagrid' => [
            'header' => 'Перенаправления',
            'column' => [
                'page_id' => 'Конечная страница',
                'from_url' => 'Исходный URL',
                'to_url' => 'Конечный URL',
                'is_permanent' => 'Постоянное?',
                'id' => 'ID',
                'admin_id' => 'Создатель',
                'created_at' => 'Создано',
                'updated_at' => 'Изменено',
            ],
            'filter' => [
                'cms_redirects' => [
                    'page_id' => 'Конечная страница',
                    'from_url' => 'Исходный URL',
                    'to_url' => 'Конечный URL',
                    'is_permanent' => 'Постоянное?',
                    'id' => 'ID',
                    'admin_id' => 'Создатель',
                    'created_at' => 'Создано',
                    'updated_at' => 'Изменено',
                ],
                'page' => [
                    'title' => 'Название конечной страницы',
                    'url_alias' => 'URL конечной страницы (без URL ее родительской страницы)'
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Создание перенаправления',
            'header_edit' => 'Редактирование перенаправления',
            'input' => [
                'page_id' => 'Конечная страница',
                'from_url' => 'Исходный URL',
                'to_url' => 'Конечный URL',
                'is_permanent' => 'Постоянное?',
                'id' => 'ID',
                'admin_id' => 'Создатель',
                'created_at' => 'Создано',
                'updated_at' => 'Изменено',
                'page_types' => [
                    'page' => 'Страницы',
                    'news' => 'Новости',
                    'category' => 'Категории',
                    'item' => 'Товары'
                ]
            ],
            'tooltip' => [
                'to_url' => 'Если указана Конечная страница - поле должно быть пустым!'
            ]
        ],
        'item_details' => [
            'header' => 'Информация о перенаправлении',
            'field' => [
                'page_id' => 'Конечная страница',
                'from_url' => 'Исходный URL',
                'to_url' => 'Конечный URL',
                'is_permanent' => 'Постоянное?',
                'id' => 'ID',
                'admin_id' => 'Создатель',
                'created_at' => 'Создано',
                'updated_at' => 'Изменено',
            ]
        ]
    ],
    'http_request_stats' => [
        'menu_title' => 'Статистика HTTP запросов',
        'datagrid' => [
            'header' => 'Статистика HTTP запросов',
            'column' => [
                'http_method' => 'Метод',
                'url' => 'Route и URL',
                'route' => 'Route и URL',
                'duration' => 'Длительность',
                'duration_sql' => 'Длительность (SQL)',
                'duration_error' => 'Ошибка времени',
                'memory_usage_mb' => 'Память',
                'is_cache' => 'Кеш?',
                'url_params' => 'URL-параметры',
                'sql' => 'SQL запросы',
                'id' => 'ID',
                'created_at' => 'Создано',
                'http_code' => 'HTTP код',
                'sql:statements_count' => 'SQL запросов',
                'rows_affected' => 'Затронуто строк',
                'failed_statements' => 'Провалов',
            ],
            'filter' => [
                'http_request_stats' => [
                    'http_method' => 'Метод',
                    'url' => 'URL',
                    'route' => 'Route',
                    'duration' => 'Длительность',
                    'duration_sql' => 'Длительность (SQL)',
                    'memory_usage_mb' => 'Память',
                    'is_cache' => 'Кеширование?',
                    'url_params' => 'URL-параметры',
                    'sql' => 'SQL запросы',
                    'id' => 'ID',
                    'created_at' => 'Создано',
                    'http_code' => 'HTTP код',
                    'counters' => 'Счетчики',
                ],
            ],
        ],
        'item_details' => [
            'header' => 'Статистика HTTP запроса',
            'field' => [
                'http_method' => 'Метод',
                'url' => 'URL',
                'route' => 'Route',
                'duration' => 'Длительность',
                'duration_sql' => 'Длительность (SQL)',
                'duration_error' => 'Ошибка времени',
                'memory_usage_mb' => 'Память',
                'is_cache' => 'Кеширование?',
                'url_params' => 'URL-параметры',
                'sql' => 'SQL запросы',
                'id' => 'ID',
                'created_at' => 'Создано',
                'http_code' => 'HTTP код',
                'request_data' => 'Данные запроса',
                'checkpoints' => 'Замеры',
                'counters' => 'Счетчики',
            ],
        ]
    ],
    'http_request_logs' => [
        'menu_title' => 'Лог HTTP запросов',
        'datagrid' => [
            'header' => 'HTTP запросы',
            'column' => [
                'url' => 'URL',
                'http_method' => 'HTTP method',
                'ip' => 'IP',
                'filter' => 'Код лога',
                'section' => 'Секция',
                'response_code' => 'Код ответа',
                'response_type' => 'Тип ответа',
                'request' => 'Данные запроса',
                'response' => 'Данные ответа',
                'table' => 'Таблица БД',
                'item_id' => 'ID записи в БД',
                'data_before' => 'Данные до изменения',
                'data_after' => 'Данные после изменения',
                'created_at' => 'Создан',
                'responded_at' => 'Ответ произведен',
                'id' => 'ID',
                'requester_table' => 'Тип пользователя',
                'requester_id' => 'Пользователь',
                'requester_info' => 'Данные пользователя',
                'debug' => 'Данные для отладки',
            ],
            'filter' => [
                'http_request_logs' => [
                    'url' => 'URL',
                    'http_method' => 'HTTP method',
                    'ip' => 'IP',
                    'filter' => 'Код лога',
                    'section' => 'Секция',
                    'response_code' => 'Код ответа',
                    'response_type' => 'Тип ответа',
                    'request' => 'Данные запроса',
                    'response' => 'Данные ответа',
                    'table' => 'Таблица БД',
                    'item_id' => 'ID записи в БД',
                    'data_before' => 'Данные до изменения',
                    'data_after' => 'Данные после изменения',
                    'created_at' => 'Создан',
                    'responded_at' => 'Ответ произведен',
                    'id' => 'ID',
                    'requester_table' => 'Тип пользователя',
                    'requester_id' => 'ID Пользователя',
                    'requester_info' => 'Данные пользователя',
                    'debug' => 'Данные для отладки',
                ]
            ]
        ],
        'item_details' => [
            'header' => 'Информация по HTTP запросу',
            'field' => [
                'url' => 'URL',
                'http_method' => 'HTTP method',
                'ip' => 'IP',
                'filter' => 'Код лога',
                'section' => 'Секция',
                'response_code' => 'Код ответа',
                'response_type' => 'Тип ответа',
                'request' => 'Данные запроса',
                'response' => 'Данные ответа',
                'table' => 'Таблица БД',
                'item_id' => 'ID записи в БД',
                'data_before' => 'Данные до изменения',
                'data_after' => 'Данные после изменения',
                'created_at' => 'Создан',
                'responded_at' => 'Ответ произведен',
                'id' => 'ID',
                'debug' => 'Данные для отладки',
                'requester_table' => 'Тип пользователя',
                'requester_id' => 'Пользователь',
                'requester_info' => 'Данные пользователя',
            ]
        ]
    ],
];

$dictionary['cms_news'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_news']);
$dictionary['cms_shop_categories'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_shop_categories']);
$dictionary['cms_shop_items'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_shop_items']);
$dictionary['cms_text_elements'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_text_elements']);
$dictionary['cms_menus'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_menus']);

return $dictionary;
