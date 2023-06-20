<?php

declare(strict_types=1);

use PeskyORM\ORM\TableStructure\TableColumn\ColumnValueValidationMessages\ColumnValueValidationMessagesInterface;

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
            ],
            'error' => [
                'csrf_token_missmatch' => 'Сессия просрочена. Страница будет перезагружена через 5 секунд.',
                'session_timed_out' => 'Сессия просрочена. Страница будет перезагружена через 5 секунд.',
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
        'delete' => [
            'success' => 'Объект удален',
            'forbidden_for_record' => 'Удаление этого объекта запрещено',
            'ids_missmatch' => 'ID объекта в URL не совпадает с ID объекта в полученных данных',
            'forbidden' => 'Удаление объектов в этом разделе запрещено'
        ]
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
    'year_suffix' => [
        'full' => ' года',
        'short' => 'г.'
    ],
    'time' => [
        'at' => 'в',
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
                'dropdown_label' => 'Массовые действия',
                'delete_selected' => '<span class="label label-danger">:count</span> Удалить выбранные',
                'edit_selected' => '<span class="label label-primary">:count</span> Редактировать выбранные',
                'delete_filtered' => '<span class="label label-danger">:count</span> Удалить отфильтрованные',
                'edit_filtered' => '<span class="label label-primary">:count</span> Редактировать отфильтрованные',
                'forbidden' => 'Массовое удаление объектов запрещено для этого раздела',
                    'delete_selected_confirm' => 'Подтвердите удаление выбранных объектов',
                    'delete_filtered_confirm' => 'Подтвердите удаление отфильтрованных объектов',
                    'success' => 'Объектов удалено: :count',
                    'nothing_deleted' => 'Не удалено ни одного объекта',
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
            'show_children' => 'Показать вложенные объекты',
            'hide_children' => 'Скрыть вложенные объекты',
            'clone_item' => 'Дублировать объект',
        ],
        'context_menu' => [
            'edit_item' => 'Редактировать объект',
            'view_item' => 'Просмотр объекта',
            'delete_item' => 'Удалить объект',
            'clone_item' => 'Дублировать объект',
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
                'ids_missmatch' => 'ID объекта в URL не совпадает с ID объекта в полученных данных',
                'key_value_table' => [
                    'no_foreign_key_value' => 'Не задан ID объекта, которому принадлежат значения'
                ],
                'success' => 'Объект успешно изменен',
            ],
            'failed_to_save_resource_data' => 'Не удалось сохранить данные',
            'validation_errors' => 'Обнаружены недопустимые данные',
            'column_validation_errors' => [
                ColumnValueValidationMessagesInterface::EXCEPTION_MESSAGE => 'Ошибки валидации данных: %s.',
                ColumnValueValidationMessagesInterface::VALUE_CANNOT_BE_NULL => 'Получено запрещенное значение: NULL.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_BOOLEAN =>
                    'Ожидается логическое значение (да/нет).',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_INTEGER => 'Ожидается целое число.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_POSITIVE_INTEGER =>
                    'Ожидается целое число больше нуля.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_FLOAT => 'Ожидается целое или дробное число.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_IMAGE => 'Ожидается картинка.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_FILE => 'Ожидается файл.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_JSON_OR_JSONABLE =>
                    'Значение должно быть JSON строкой или быть объектом, который можно конвертировать в JSON.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_JSON_ARRAY_OR_OBJECT =>
                    'Значение должно быть массивом или объектом закодированным в JSON или PHP массивом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_JSON_ARRAY =>
                    'Значение должно быть индексированным массивом закодированным в JSON или индексированным PHP массивом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_JSON_OBJECT =>
                    'Значение должно быть объектом закодированным в JSON или ассоциативным PHP массивом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_IPV4_ADDRESS =>
                    'Значение должно быть IPv4 адресом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_EMAIL =>
                    'Значение должно быть корректным E-mail адресом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_TIMEZONE =>
                    'Значение должно быть названием временной зоны или смещением временной зоны по UTC от -12:00 до +14:00.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_TIMESTAMP =>
                    'Значение должно содержать дату и время.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_TIMESTAMP_OR_INTEGER =>
                    'Значение должно содержать дату и время или положительным целым числом (UNIX timestamp).',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_TIME => 'Значение должно содержать время.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_DATE => 'Значение должно содержать дату.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_STRING => 'Значение должно быть строкой.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_PASSWORD_HASH =>
                    'Значение должно содержать хеш пароля.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_PLAIN_PASSWORD =>
                    'Значение должно быть незахешированным паролем (получен хеш).',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_ARRAY => 'Значение должно быть массивом.',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_RESOURCE =>
                    'Значение должно быть ресурсом (resource).',
                ColumnValueValidationMessagesInterface::VALUE_MUST_BE_STRING_OR_RESOURCE =>
                    'Значение должно быть строкой или ресурсом (resource).',
                ColumnValueValidationMessagesInterface::VALUE_FROM_DB_CANNOT_BE_DB_EXPRESSION =>
                    'Значение полученное из БД не можеть быть экземпляром класса DbExpr.',
                ColumnValueValidationMessagesInterface::VALUE_FROM_DB_CANNOT_BE_QUERY_BUILDER =>
                    'Значение полученное из БД не можеть быть экземпляром класса SelectQueryBuilderInterface.',
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
                'row_delete_action_forbidden' =>
                    'Нельзя удалить эту строку т.к. достигнуто минимальное количество строк',
                'table_header_for_abstract_value' => 'Значение',
            ],
            'has_many_related_records' => [
                'add_row' => 'Добавить',
                'delete_row' => 'Удалить',
                'row_delete_action_forbidden' =>
                    'Нельзя удалить этот объект т.к. достигнуто минимальное количество объектов'
            ],
            'async_files_uploads' => [
                'file_name' => 'Имя',
                'file_size' => 'Размер файла',
                'image_dimensions' => 'Размеры изображения',
                'file_size_measure_mb' => 'МБ',
                'cancel_uploading' => 'Отмена',
                'retry_upload' => 'Повторить',
                'delete_file' => 'Удалить',
                'reorder' => 'Перетяните эту строку вверх или вниз для изменения порядка строк',
                'tooltip' => [
                    'uploaded' => 'Файл загружен на сервер',
                    'failed_to_upload' => 'Ошибка при загрузке файла на сервер',
                ],
                'invalid_encoded_info' => 'Получены некорректные данные по загруженному файлу.',
                'js_locale' => [
                    'drop_files_here' => 'Перетащите файлы сюда ...',
                    'error' => [
                        'mime_type_forbidden' => 'Файлы этого типа запрещены.',
                        'mime_type_and_extension_missmatch' => 'Расширение файла не соответствует его типу.',
                        'already_attached' => 'Файл {name} уже прикреплен.',
                        'too_many_files' => 'Достигнуто максимальное количество файлов: {limit}.',
                        'not_enough_files' => 'Минимальное количество прикрепленных файлов: {limit}.',
                        'file_too_large' => 'Размер файла превышает максимально допустимый размер ({max_size_mb} МБ).',
                        'server_error' => 'Произошла неопределенная ошибка при сохранении файла.',
                        'unexpected_error' => 'Не удалось обработать и сохранить файл.',
                        'non_json_validation_error' => 'Файл не прошел проверку на стороне сервера.',
                        'invalid_response' => 'Получен некорректный ответ от сервера.',
                    ]
                ]
            ],
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
        'previous_item' => 'Предыдущий объект',
        'next_item' => 'Следующий объект',
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
            'cannot_detect_resource_and_field' => 'Не удалось обнаружить имя ресурса и имя поля в имени редактора. Ожидаемое имя редактора: "resource_name:field_name". Полученное имя редактора: ":editor_name"',
            'cannot_find_field_in_scaffold' => 'Поле ":field_name" не найдено среди полей кофигурации формы в :scaffold_class. Имя редактора: ":editor_name"',
            'is_not_wysiwyg_field_config' => 'Поле ":field_name" из кофигурации формы :scaffold_class не является объектом класса :wysywig_class',
            'image_uploading_folder_not_set' => 'Не указана папка, в которую нужно сохранять картинки для поля ":field_name" из кофигурации формы :scaffold_class',
            'failed_to_resize_image' => 'Не удалось изменить размер изображения',
            'invalid_or_corrupted_image' => 'Файл не является картинкой либо картинка повреждена',
            'failed_to_save_image_to_fs' => 'Не удалось сохранить файл в хранилище',
        ]
    ],

];
