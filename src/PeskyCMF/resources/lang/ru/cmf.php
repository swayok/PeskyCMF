<?php

return [
    'ui' => [
        'close' => 'Закрыть',
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
            ],
            'ckeditor' => [
                'cmf_scaffold_inserts_plugin_title' => 'Вставка данных в текст',
                'cmf_scaffold_inserts_dialog_insert_tag_name' => 'Тип вставки',
                'cmf_scaffold_inserts_dialog_insert_tag_is_span' => 'Вставить в строку другого текста (span)',
                'cmf_scaffold_inserts_dialog_insert_tag_is_div' => 'Вставить как отдельный блок (div, p)',
            ],
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
                'toggle' => 'Фильтры',
                'close' => 'Закрыть'
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
            'invert_selection' => 'Обратить выделение',
            'show_children' => 'Показать дочерние элементы',
            'hide_children' => 'Скрыть дочерние элементы',
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
        'input' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ],
            'file_uploads' => [
                'add_file' => 'Добавить файл',
                'add_image' => 'Добавить картинку'
            ],
            'key_value_set' => [
                'add_row' => 'Добавить строку',
                'delete_row' => 'Удалить строку',
                'row_delete_action_forbidden' => 'Нельзя удалить эту строку т.к. достигнуто минимальное количество строк'
            ],
        ],
        'bulk_edit' => [
            'enabler' => [
                'edit_input' => 'Изменить',
                'skip_input' => 'Пропустить'
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
            'no_relation' => 'Связь отсутствует',
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
            'key_value_table' => [
                'no_foreign_key_value' => 'Не задан ID объекта, которому принадлежат значения'
            ]
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
    'ckeditor' => [
        'fileupload' => [
            'cannot_detect_table_and_field' => 'Не удалось обнаружить имя таблицы и имя поля в имени редактора. Ожидаемое имя редактора: "table_name:field_name". Полученное имя редактора: ":editor_name"',
            'cannot_find_field_in_scaffold' => 'Поле ":field_name" не найдено среди полей кофигурации формы в :scaffold_class. Имя редактора: ":editor_name"',
            'is_not_wysiwyg_field_config' => 'Поле ":field_name" из кофигурации формы :scaffold_class не является объектом класса :wysywig_class',
            'image_uploading_folder_not_set' => 'Не указана папка, в которую нужно сохранять картинки для поля ":field_name" из кофигурации формы :scaffold_class',
            'failed_to_resize_image' => 'Не удалось изменить размер изображения',
            'invalid_or_corrupted_image' => 'Файл не является картинкой либо картинка повреждена',
            'failed_to_save_image_to_fs' => 'Не удалось сохранить файл в хранилище',
        ]
    ],

];
