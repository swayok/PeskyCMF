<?php

return [
    'default_page_title' => 'PeskyCMF',
    'language' => [
        'en' => 'English',
        'ru' => 'Русский'
    ],
    'error' => [
        'db_record_not_exists' => 'Объект не найден в базе данных'
    ],
    'login_form' => [
        'header' => 'Авторизация',
        'email_label' => 'E-mail',
        'password_label' => 'Пароль',
        'button_label' => 'Войти',
        'forgot_password_label' => 'Забыли пароль?',
        'login_failed' => 'Неправильный E-mail или пароль',
    ],
    'ui' => [
        'main_menu_header' => 'Навигация',
        'js_component' => [
            'data_tables' => [
                'processing' => 'Подождите...',
                'search' => 'Поиск:',
                'lengthMenu' => 'Показать _MENU_ записей',
                'info' => 'Записи с _START_ до _END_ из _TOTAL_ записей',
                'infoEmpty' => 'Записи с 0 до 0 из 0 записей',
                'infoFiltered' => '(отфильтровано из _MAX_ записей)',
                'infoPostFix' => '',
                'loadingRecords' => 'Загрузка записей...',
                'zeroRecords' => 'Записи отсутствуют.',
                'emptyTable' => 'В таблице отсутствуют данные',
                'paginate' => [
                    'first' => 'Первая',
                    'previous' => 'Предыдущая',
                    'next' => 'Следующая',
                    'last' => 'Последняя'
                ],
                'aria' => [
                    'sortAscending' => ': активировать для сортировки столбца по возрастанию',
                    'sortDescending' => ': активировать для сортировки столбца по убыванию'
                ],
                'toolbar' => [
                    'reloadData' => 'Обновить'
                ]
            ]
        ]
    ],
    'user' => [
        'profile_label' => 'Профиль',
        'logout_label' => 'Выйти',
    ],
    'admins' => [
        'menu_title' => 'Администраторы',
        'role' => [
            'admin' => 'Администратор'
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
                'created_at' => 'Создан'
            ]
        ],
        'form' => [
            'header_create' => 'Добавление администратора',
            'header_edit' => 'Редактирование администратора',
            'field' => [
                'email' => 'E-mail',
                'password' => 'Пароль',
                'name' => 'Имя',
                'language' => 'Язык',
                'is_active' => 'Действующий?',
                'role' => 'Роль',
                'is_superadmin' => 'Имеет доступ ко всему (суперадмин)'
            ]

        ],
        'item_details' => [
            'header' => 'Информация об администраторе',
            'field' => [
                'email' => 'E-mail',
                'password' => 'Пароль',
                'name' => 'Имя',
                'language' => 'Язык',
                'is_active' => 'Действующий?',
                'role' => 'Роль',
                'is_superadmin' => 'Имеет доступ ко всему (суперадмин)?',
                'parent_id' => 'Админ, создавший этот акаунт',
                'created_at' => 'Создан',
                'updated_at' => 'Изменен'
            ]
        ]
    ],
    'datagrid' => [
        'toolbar' => [
            'create' => 'Добавить',
            'filter' => [
                'header' => 'Правила поиска',
                'reset' => 'Сбросить поиск',
                'submit' => 'Искать'
            ],
        ],
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ]
        ],
        'actions' => [
            'edit_item' => 'Редактировать',
            'view_item' => 'Просмотр',
            'delete_item' => 'Удалить'
        ]
    ],
    'form' => [
        'toolbar' => [
            'cancel' => 'Отмена',
            'submit' => 'Сохранить',
            'create' => 'Добавить',
            'delete' => 'Удалить'
        ],
        'failed_to_save_resource_data' => 'Не удалось сохранить данные',
        'validation_errors' => 'Обнаружены недопустимые данные',
        'resource_created_successfully' => 'Объект успешно создан',
        'resource_updated_successfully' => 'Объект успешно изменен',
    ],
    'item_details' => [
        'toolbar' => [
            'cancel' => 'Назад',
            'edit' => 'Редактировать',
            'create' => 'Добавить',
            'delete' => 'Удалить'
        ],
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ],
            'no_relation' => 'Связь отсутствует'
        ]
    ],
    'error' => [
        'resource_item_not_found' => 'Данные запрошенного объекта не найдены'
    ],
    'action' => [
        'delete' => [
            'forbidden' => 'Удаление объектов запрещено для этого раздела',
            'success' => 'Объект успешно удален',
            'please_confirm' => 'Подтвердите удаление объекта'
        ],
        'create' => [
            'forbidden' => 'Создание объекстов запрещено для этого раздела',
        ],
        'edit' => [
            'forbidden' => 'Редактирование объекстов запрещено для этого раздела',
        ],
        'item_details' => [
            'forbidden' => 'Просмотр информации об объекстах этого раздела запрещен',
        ],
        'back' => 'Назад',
        'reload_page' => 'Обновить страницу',
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
                    'unique' => 'Такой E-mail адрес уже используется другим администратором'
                ],
                'language' => [
                    'required' => 'Выберите язык',
                    'in' => 'Язык не входит в список разрешенных. Выберите другой язык.',
                ]
            ]
        ]
    ]
];
