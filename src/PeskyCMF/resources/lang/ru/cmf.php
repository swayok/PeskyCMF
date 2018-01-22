<?php

return [
    'test' => 'ok', //< used in PeskyCmfServiceProvider to load cmf dictionaries
    'ui' => [
        'close' => 'Закрыть',
        'modal' => [
            'open_in_new_tab' => 'Открыть в отдельной вкладке',
            'close' => 'Закрыть',
            'reload' => 'Обновить данные'
        ],
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
                'cmf_scaffold_data_inserts_plugin_title' => 'Вставить данные',
                'cmf_scaffold_data_inserts_dialog_insert_tag_name' => 'Тип вставки',
                'cmf_scaffold_html_inserts_plugin_title' => 'Вставить заготовку',
                'cmf_scaffold_inserts_dialog_insert_tag_is_span' => 'Вставить в строку другого текста (span)',
                'cmf_scaffold_inserts_dialog_insert_tag_is_div' => 'Вставить как отдельный блок (div, p)',
            ],
            'form' => [
                'invalid_data_received' => 'Получены недопустимые данные'
            ],
            'file_uploader' => [
                'no_file' => 'Файл еще не выбран'
            ]
        ]
    ],
    'message' => [
        'http404' => 'Запрошенная страница не найдена',
        'access_denied' => 'У Вас недостаточно прав для просмотра этой страницы',
        'resource_item_not_found' => 'Данные запрошенного объекта не найдены',
        'invalid_data_received' => 'Получены недопустимые данные',
        'invalid_date_received' => 'Некорректная дата',
        'access_denied_to_scaffold' => 'У Вас недостаточно прав для просмотра этого раздела',
    ],
    'bool' => [
        'yes' => 'Да',
        'no' => 'Нет',
        'on' => 'Вкл.',
        'off' => 'Выкл.'
    ],
    'month' => [
        '1' => 'Январь',
        '2' => 'Февраль',
        '3' => 'Март',
        '4' => 'Апрель',
        '5' => 'Май',
        '6' => 'Июнь',
        '7' => 'Июль',
        '8' => 'Август',
        '9' => 'Сентябрь',
        '01' => 'Январь',
        '02' => 'Февраль',
        '03' => 'Март',
        '04' => 'Апрель',
        '05' => 'Май',
        '06' => 'Июнь',
        '07' => 'Июль',
        '08' => 'Август',
        '09' => 'Сентябрь',
        '10' => 'Октябрь',
        '11' => 'Ноябрь',
        '12' => 'Декабрь',
        'in' => [
            '1' => 'Январе',
            '2' => 'Феврале',
            '3' => 'Марте',
            '4' => 'Апреле',
            '5' => 'Мае',
            '6' => 'Июне',
            '7' => 'Июле',
            '8' => 'Августе',
            '9' => 'Сентябре',
            '01' => 'Январе',
            '02' => 'Феврале',
            '03' => 'Марте',
            '04' => 'Апреле',
            '05' => 'Мае',
            '06' => 'Июне',
            '07' => 'Июле',
            '08' => 'Августе',
            '09' => 'Сентябре',
            '10' => 'Октябре',
            '11' => 'Ноябре',
            '12' => 'Декабре',
        ],
        'when' => [
            '1' => 'Января',
            '2' => 'Февраля',
            '3' => 'Марта',
            '4' => 'Апреля',
            '5' => 'Мая',
            '6' => 'Июня',
            '7' => 'Июля',
            '8' => 'Августа',
            '9' => 'Сентября',
            '01' => 'Января',
            '02' => 'Февраля',
            '03' => 'Марта',
            '04' => 'Апреля',
            '05' => 'Мая',
            '06' => 'Июня',
            '07' => 'Июля',
            '08' => 'Августа',
            '09' => 'Сентября',
            '10' => 'Октября',
            '11' => 'Ноября',
            '12' => 'Декабря',
        ]
    ],
    'format_seconds' => [
        'days_short' => ':days д. ',
        'days' => [
            ':days день ',
            ':days дня ',
            ':days дней ',
        ],
        'hours_short' => ':hours ч. ',
        'hours' => [
            ':hours час ',
            ':hours часа ',
            ':hours часов ',
        ],
        'minutes_short' => ':minutes мин. ',
        'minutes' => [
            ':minutes минута ',
            ':minutes минуты ',
            ':minutes минут ',
        ],
        'seconds_short' => ':seconds сек.',
        'seconds' => [
            ':seconds секунда',
            ':seconds секунды',
            ':seconds секунд',
        ],
        'less_then_a_minute' => 'Меньше минуты'
    ],
    'datagrid' => [
        'toolbar' => [
            'create_item' => 'Добавить',
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
            'edit_selected' => '<span class="label label-primary">:count</span> Редактировать выбранные',
            'delete_filtered' => '<span class="label label-danger">:count</span> Удалить отфильтрованные',
            'edit_filtered' => '<span class="label label-primary">:count</span> Редактировать отфильтрованные',
            'message' => [
                'delete_bulk' => [
                    'forbidden' => 'Массовое удаление объектов запрещено для этого раздела',
                    'delete_selected_confirm' => 'Подтвердите удаление выбранных объектов',
                    'delete_filtered_confirm' => 'Подтвердите удаление отфильтрованных объектов',
                    'success' => 'Объектов удалено: :count',
                    'nothing_deleted' => 'Не удалено ни одного объекта',
                ],
            ]
        ],
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ],
            'no_relation' => 'Связь отсутствует',
        ],
        'actions' => [
            'column_label' => 'Действия',
            'edit_item' => 'Редактировать',
            'view_item' => 'Просмотр',
            'delete_item' => 'Удалить',
            'select_all' => 'Выбрать все',
            'select_none' => 'Отменить выбор',
            'invert_selection' => 'Обратить выделение',
            'show_children' => 'Показать вложенные элементы',
            'hide_children' => 'Скрыть вложенные элементы',
            'clone_item' => 'Дублировать элемент',
        ],
        'context_menu' => [
            'edit_item' => 'Редактировать элемент',
            'view_item' => 'Просмотр элемента',
            'delete_item' => 'Удалить элемент',
            'clone_item' => 'Дублировать элемент',
        ],
        'filter' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ]
        ],
        'message' => [
            'delete_item_confirm' => 'Подтвердите удаление объекта',
            'change_position' => [
                'forbidden' => 'Изменение позиций объектов в этом разделе запрещено',
                'success' => 'Позиция объекта изменена',
            ],
        ]
    ],
    'form' => [
        'toolbar' => [
            'close' => 'Закрыть',
            'cancel' => 'Отмена',
            'submit' => 'Сохранить',
            'submit_and_add_another' => 'Сохранить и добавить еще 1',
            'create_item' => 'Добавить',
            'view_item' => 'Просмотр данных',
            'clone_item' => 'Дублировать данные',
            'delete_item' => 'Удалить'
        ],
        'message' => [
            'delete_item_confirm' => 'Подтвердите удаление объекта',
            'create' => [
                'forbidden' => 'Создание объектов запрещено для этого раздела',
                'success' => 'Объект успешно создан',
            ],
            'edit' => [
                'forbidden' => 'Редактирование объектов запрещено для этого раздела',
                'forbidden_for_record' => 'Редактирование этого объекта запрещено',
                'key_value_table' => [
                    'no_foreign_key_value' => 'Не задан ID объекта, которому принадлежат значения'
                ],
                'success' => 'Объект успешно изменен',
            ],
            'failed_to_save_resource_data' => 'Не удалось сохранить данные',
            'validation_errors' => 'Обнаружены недопустимые данные',
            'column_validation_errors' => [
                'value_cannot_be_null' => 'Получено запрещенное значение: NULL.',
                'value_must_be_boolean' => 'Ожидается логическое значение (да/нет).',
                'value_must_be_integer' => 'Ожидается целое число.',
                'value_must_be_float' => 'Ожидается целое или дробное число.',
                'value_must_be_image' => 'Ожидается картинка.',
                'value_must_be_file' => 'Ожидается файл.',
                'value_must_be_json' => 'Ожидается JSON-строка',
                'value_must_be_ipv4_address' => 'Неправильный IP адрас. Пример: 127.0.0.1',
                'value_must_be_email' => 'Ожидается email-адрес.',
                'value_must_be_timezone_offset' => 'Ожидается смещение по времени (часовому поясу).',
                'value_must_be_timestamp' => 'Ожидается дата и время.',
                'value_must_be_timestamp_with_tz' => 'Ожидается дата и время с указанием временной зоны.',
                'value_must_be_time' => 'Ожидается время.',
                'value_must_be_date' => 'Ожидается дата.',
                'value_is_not_allowed' => 'Получено запрещенное значение: :value.',
                'one_of_values_is_not_allowed' => 'Одно из полученных значение запрещено.',
                'value_must_be_string' => 'Ожидается строка.',
                'value_must_be_string_or_numeric' => 'Ожидается строка или число (целое или дробное).',
                'value_must_be_array' => 'Ожидается массив.',
                'invalid_image_type' => "Формат загруженной картинки ('%s') не разрешен для '%s'. Разрешенные форматы: %s.",
                'invalid_file_type' => "Формат загруженного файла ('%s') не разрешен для '%s'. Разрешенные форматы: %s.",
                'file_size_is_too_large' => "Размер загруженного файла превышает максимальный размер файла %s килобайт большой для '%s'.",
                'file_is_not_a_valid_image' => "Загруженный файл для '%s' поврежден или не является картинкой.",
            ]
        ],
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
            'has_many_related_records' => [
                'add_row' => 'Добавить',
                'delete_row' => 'Удалить',
                'row_delete_action_forbidden' => 'Нельзя удалить этот элемент т.к. достигнуто минимальное количество элементов'
            ]
        ],
        'bulk_edit' => [
            'toolbar' => [
                'close' => 'Закрыть',
                'cancel' => 'Отмена',
                'submit' => 'Сохранить',
            ],
            'enabler' => [
                'edit_input' => 'Изменить',
                'skip_input' => 'Пропустить',
                'tooltip' => 'Вкл./выкл. Редактирование. Если редакрирование выключено - значение не будет сохранено'
            ],
            'message' => [
                'forbidden' => 'Редактирование объектов запрещено для этого раздела',
                'no_data_to_save' => 'Нет данных для сохранения',
                'success' => 'Объектов изменено: :count',
                'nothing_updated' => 'Не изменено ни одного объекта',
            ]
        ],
        'modal' => [
            'open_in_new_tab' => 'Открыть в отдельной вкладке',
            'close' => 'Закрыть',
            'reload' => 'Обновить данные'
        ],
    ],
    'item_details' => [
        'toolbar' => [
            'cancel' => 'Назад',
            'close' => 'Закрыть',
            'edit_item' => 'Редактировать',
            'create_item' => 'Добавить',
            'delete_item' => 'Удалить',
            'clone_item' => 'Дублировать',
        ],
        'previous_item' => 'Предыдущий элемент',
        'next_item' => 'Следующий элемент',
        'field' => [
            'bool' => [
                'yes' => 'Да',
                'no' => 'Нет'
            ],
            'no_relation' => 'Связь отсутствует',
        ],
        'modal' => [
            'open_in_new_tab' => 'Открыть в отдельной вкладке',
            'close' => 'Закрыть',
            'reload' => 'Обновить данные'
        ],
        'message' => [
            'delete_item_confirm' => 'Подтвердите удаление объекта',
            'forbidden' => 'Просмотр информации об объектах этого раздела запрещен',
            'forbidden_for_record' => 'Просмотр информации об этом объекте запрещен',
        ],
    ],
    'action' => [
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
