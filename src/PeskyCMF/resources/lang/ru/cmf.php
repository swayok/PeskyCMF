<?php

return [
    'close' => 'Закрыть',
    'ui' => [
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
    'error' => [
        'resource_item_not_found' => 'Данные запрошенного объекта не найдены',
        'db_record_not_exists' => 'Объект не найден в базе данных',
        'invalid_data_received' => 'Получены недопустимые данные',
        'csrf_token_missmatch' => 'Сессия устарела или невалидна. Требуется перезагрузка страницы.',
        'http404' => 'Запрошенная страница не найдена'
    ],
    'datagrid' => [
        'toolbar' => [
            'create' => 'Добавить',
            'filter' => [
                'header' => 'Правила поиска',
                'reset' => 'Сбросить поиск',
                'submit' => 'Искать',
                'toggle' => 'Фильтры'
            ],
        ],
        'bulk_actions' => [
            'dropdown_label' => 'Массовые действия',
            'delete_selected' => '<span class="label label-danger">:count</span> Удалить выбранные',
            'delete_selected_confirm' => 'Подтвердите удаление выбранных объектов',
            'edit_selected' => '<span class="label label-primary"> Редактировать выбранные',
            'delete_filtered' => '<span class="label label-danger">:count</span> Удалить отфильтрованные',
            'delete_filtered_confirm' => 'Подтвердите удаление отфильтрованных объектов',
            'edit_filtered' => '<span class="label label-primary"> Редактировать отфильтрованные',
        ],
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ]
        ],
        'actions' => [
            'column_label' => 'Действия',
            'edit_item' => 'Редактировать',
            'view_item' => 'Просмотр',
            'delete_item' => 'Удалить',
            'select_all' => 'Выбрать все',
            'select_none' => 'Отменить выбор',
            'invert_selection' => 'Обратить выделение'
        ],
        'filter' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ]
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
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ],
        ],
        'bulk_edit' => [
            'enabler' => [
                'edit_field' => 'Изменить',
                'skip_field' => 'Пропустить'
            ]
        ]
    ],
    'item_details' => [
        'toolbar' => [
            'cancel' => 'Назад',
            'close' => 'Закрыть',
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
        ],
    ],
    'action' => [
        'delete' => [
            'forbidden' => 'Удаление объектов запрещено для этого раздела',
            'forbidden_for_record' => 'Удаление этого объекта запрещено',
            'success' => 'Объект успешно удален',
            'please_confirm' => 'Подтвердите удаление объекта'
        ],
        'delete_bulk' => [
            'success' => 'Объектов удалено: :count',
            'nothing_deleted' => 'Не удалено ни одного объекта',
        ],
        'create' => [
            'forbidden' => 'Создание объектов запрещено для этого раздела',
        ],
        'edit' => [
            'forbidden' => 'Редактирование объектов запрещено для этого раздела',
            'forbidden_for_record' => 'Редактирование этого объекта запрещено',
        ],
        'bulk_edit' => [
            'no_data_to_save' => 'Нет данных для сохранения',
            'success' => 'Объектов изменено: :count',
            'nothing_updated' => 'Не изменено ни одного объекта',
        ],
        'item_details' => [
            'forbidden' => 'Просмотр информации об объектах этого раздела запрещен',
            'forbidden_for_record' => 'Просмотр информации об этом объекте запрещен',
        ],
        'back' => 'Назад',
        'reload_page' => 'Обновить страницу',
    ],
];
