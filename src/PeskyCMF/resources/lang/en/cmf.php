<?php

return [
    'test' => 'ok', //< used in PeskyCmfServiceProvider to load cmf dictionaries
    'ui' => [
        'close' => 'Close',
        'modal' => [
            'open_in_new_tab' => 'Open in new tab',
            'close' => 'Close',
            'reload' => 'Reload data'
        ],
        'js_component' => [
            'data_tables' => [










                // new lines added for easier comparison of language files









                'toolbar' => [
                    'reloadData' => 'Reload'
                ]
            ],
            'ckeditor' => [
                'cmf_scaffold_data_inserts_plugin_title' => 'Insert data',
                'cmf_scaffold_data_inserts_dialog_insert_tag_name' => 'Display:',
                'cmf_scaffold_html_inserts_plugin_title' => 'Insert template',
                'cmf_scaffold_inserts_dialog_insert_tag_is_span' => 'Inside existing text (span)',
                'cmf_scaffold_inserts_dialog_insert_tag_is_div' => 'As separate text block (div, p)',
            ],
            'form' => [
                'invalid_data_received' => 'Invalid data received'
            ],
            'file_uploader' => [
                'no_file' => 'File not selected yet'
            ],
            'error' => [
                'csrf_token_missmatch' => 'Your session has timed out. Page will be reloaded in 5 seconds.',
                'session_timed_out' => 'Your session has timed out. Page will be reloaded in 5 seconds.',
            ]
        ]
    ],
    'message' => [
        'http404' => 'Requested page not found',
        'access_denied' => 'You do not have enough rights to access requested page',
        'resource_item_not_found' => 'Requested Item not found',
        'access_denied_to_scaffold' => 'You do not have enough rights to access requested section',
        'invalid_data_received' => 'Invalid data received',
        'invalid_date_received' => 'Invalid date',
        'delete' => [
            'success' => 'Item deleted',
            'forbidden_for_record' => 'This item cannot be deleted',
            'forbidden' => 'Items deletion in this section is forbidden'
        ]
    ],
    'bool' => [
        'yes' => 'Yes',
        'no' => 'No',
        'on' => 'On',
        'off' => 'Off'
    ],
    'month' => [
        '1' => 'January',
        '2' => 'February',
        '3' => 'Match',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '01' => 'January',
        '02' => 'February',
        '03' => 'Match',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
        'in' => [
            '1' => 'January',
            '2' => 'February',
            '3' => 'Match',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '01' => 'January',
            '02' => 'February',
            '03' => 'Match',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ],
        'when' => [
            '1' => 'January',
            '2' => 'February',
            '3' => 'Match',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '01' => 'January',
            '02' => 'February',
            '03' => 'Match',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ]
    ],
    'year_suffix' => [
        'full' => '',
        'short' => ''
    ],
    'format_seconds' => [
        'days_short' => ':days d. ',
        'days' => [
            ':days day ',
            ':days days ',
            ':days days ',
        ],
        'hours_short' => ':hours h. ',
        'hours' => [
            ':hours hour ',
            ':hours hours ',
            ':hours hours ',
        ],
        'minutes_short' => ':minutes min. ',
        'minutes' => [
            ':minutes minute ',
            ':minutes minutes ',
            ':minutes minutes ',
        ],
        'seconds_short' => ':seconds sec.',
        'seconds' => [
            ':seconds second',
            ':seconds seconds',
            ':seconds seconds',
        ],
        'less_then_a_minute' => 'Less then a minute'
    ],
    'datagrid' => [
        'toolbar' => [
            'create_item' => 'Add new',
            'filter' => [
                'header' => 'Filtering rules',
                'reset' => 'Reset filter',
                'submit' => 'Apply filter',
                'toggle' => 'Filters',
                'close' => 'Close',
            ],
        ],
        'bulk_actions' => [
            'dropdown_label' => 'Bulk actions',
            'delete_selected' => '<span class="label label-danger">:count</span> Delete selected',
            'edit_selected' => '<span class="label label-primary">:count</span> Edit selected',
            'delete_filtered' => '<span class="label label-danger">:count</span> Delete filtered',
            'edit_filtered' => '<span class="label label-primary">:count</span> Edit filtered ',
            'message' => [
                'delete_selected' => '<span class="label label-danger">:count</span> Удалить выбранные',
                'edit_selected' => '<span class="label label-primary">:count</span> Редактировать выбранные',
                'delete_filtered' => '<span class="label label-danger">:count</span> Удалить отфильтрованные',
                'edit_filtered' => '<span class="label label-primary">:count</span> Редактировать отфильтрованные',
                'forbidden' => 'It is forbidden to delete bulk of Items from this section',
                'delete_selected_confirm' => 'Confirm selected Items delete action',
                'delete_filtered_confirm' => 'Confirm filtered Items delete action',
                'success' => 'Items deleted: :count',
                'nothing_deleted' => 'No items deleted',
                'delete_bulk' => [
                    'forbidden' => 'It is forbidden to delete bulk of Items from this section',
                    'delete_selected_confirm' => 'Confirm selected Items delete action',
                    'delete_filtered_confirm' => 'Confirm filtered Items delete action',
                    'success' => 'Items deleted: :count',
                    'nothing_deleted' => 'No items deleted',
                ],
            ]
        ],
        'field' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            'no_relation' => 'Relation not exists'
        ],
        'actions' => [
            'column_label' => 'Actions',
            'edit_item' => 'Edit',
            'view_item' => 'View',
            'delete_item' => 'Delete',
            'clone_item' => 'Duplicate item',
            'show_children' => 'Show nested items',
            'hide_children' => 'Hide nested items',
            'select_all' => 'Select all',
            'select_none' => 'Select none',
            'invert_selection' => 'Invert selection',
        ],
        'context_menu' => [
            'edit_item' => 'Edit item',
            'view_item' => 'View item',
            'delete_item' => 'Delete item',
            'clone_item' => 'Duplicate item',
        ],
        'filter' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ]
        ],
        'message' => [
            'delete_item_confirm' => 'Confirm Item delete action',
            'change_position' => [
                'forbidden' => 'It is forbidden to change items positions in this section',
                'success' => 'Item\'s Position changed successfully',
            ],
        ]
    ],
    'form' => [
        'toolbar' => [
            'close' => 'Close',
            'cancel' => 'Cancel',
            'submit' => 'Save',
            'submit_and_add_another' => 'Save and add another one',
            'create_item' => 'Add new',
            'view_item' => 'Item details',
            'clone_item' => 'Duplicate data',
            'delete_item' => 'Delete'
        ],
        'message' => [
            'delete_item_confirm' => 'Confirm Item delete action',
            'create' => [
                'forbidden' => 'It is forbidden to create Items in this section',
                'success' => 'Item successfully created',
            ],
            'edit' => [
                'forbidden' => 'It is forbidden to edit Items in this section',
                'forbidden_for_record' => 'It is forbidden to edit this Item',
                'key_value_table' => [
                    'no_foreign_key_value' => 'There is no ID for object that should be an owner of received values'
                ],
                'success' => 'Item successfully updated',
            ],
            'failed_to_save_resource_data' => 'Failed to save data',
            'validation_errors' => 'Invalid data detected',
            'column_validation_errors' => [
                'value_cannot_be_null' => 'Null value is not allowed.',
                'value_must_be_boolean' => 'Value must be of a boolean data type.',
                'value_must_be_integer' => 'Value must be of an integer data type.',
                'value_must_be_float' => 'Value must be of a numeric data type.',
                'value_must_be_image' => 'Value must be an uploaded image info.',
                'value_must_be_file' => 'Value must be an uploaded file info.',
                'value_must_be_json' => 'Value must be of a json data type.',
                'value_must_be_ipv4_address' => 'Value must be an IPv4 address.',
                'value_must_be_email' => 'Value must be an email.',
                'value_must_be_timezone_offset' => 'Value must be a valid timezone offset.',
                'value_must_be_timestamp' => 'Value must be a valid timestamp.',
                'value_must_be_timestamp_with_tz' => 'Value must be a valid timestamp with time zone.',
                'value_must_be_time' => 'Value must be a valid time.',
                'value_must_be_date' => 'Value must be a valid date.',
                'value_is_not_allowed' => 'Value is not allowed: :value.',
                'one_of_values_is_not_allowed' => 'One of values in the received array is not allowed.',
                'value_must_be_string' => 'Value must be a string.',
                'value_must_be_string_or_numeric' => 'Value must be a string or a number.',
                'value_must_be_array' => 'Value must be an array.',
                'invalid_image_type' => "Uploaded image type '%s' is not allowed for '%s'. Allowed file types: %s.",
                'invalid_file_type' => "Uploaded file type '%s' is not allowed for '%s'. Allowed file types: %s.",
                'file_size_is_too_large' => "Uploaded file size is too large for '%s'. Maximum file size is %s kilobytes.",
                'file_is_not_a_valid_image' => "Uploaded file for '%s' is corrupted or it is not a valid image.",
                'file_is_not_a_valid_upload' => "Data received for '%s' is not a valid upload.",
            ]
        ],
        'input' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            'file_uploads' => [
                'add_file' => 'Add file',
                'add_image' => 'Add image'
            ],
            'key_value_set' => [
                'add_row' => 'Add row',
                'delete_row' => 'Remove row',
                'row_delete_action_forbidden' => 'Row removal is forbidden. You need to maintain minimal required row count.',
                'table_header_for_abstract_value' => 'Value',
            ],
            'has_many_related_records' => [
                'add_row' => 'Add element',
                'delete_row' => 'Remove element',
                'row_delete_action_forbidden' => 'Element removal is forbidden. You need to maintain minimal required elements count.'
            ],
            'async_files_uploads' => [
                'file_name' => 'Name',
                'file_size' => 'Size',
                'file_size_measure_mb' => 'MB',
                'cancel_uploading' => 'Cancel',
                'retry_upload' => 'Retry',
                'delete_file' => 'Delete',
                'tooltip' => [
                    'uploaded' => 'File uploaded',
                    'failed_to_upload' => 'Failed to upload file',
                ],
                'invalid_encoded_info' => 'Received invalid data for uploaded file.',
                'js_locale' => [
                    'error' => [
                        'mime_type_forbidden' => 'Files of this type are forbidden.',
                        'mime_type_and_extension_missmatch' => 'File extension does not fit file type.',
                        'already_attached' => 'File {name} already attached.',
                        'too_many_files' => 'Minimum number of files required: {limit}.',
                        'not_enough_files' => 'Maximum files limit reached: {limit}.',
                        'file_too_large' => 'File size is bigger then maximum allowed file size ({max_size_mb} MB).',
                        'server_error' => 'Unknown error occured during file saving.',
                        'unexpected_error' => 'Failed to process and save file.',
                        'non_json_validation_error' => 'File validation failed on server side.',
                        'invalid_response' => 'Invalid response received from server.',
                    ]
                ]
            ],
        ],
        'bulk_edit' => [
            'toolbar' => [
                'close' => 'Close',
                'cancel' => 'Cancel',
                'submit' => 'Save',
            ],
            'enabler' => [
                'edit_input' => 'Change',
                'skip_input' => 'Skip',
                'tooltip' => 'Enable/disable editing. If editing is disabled - value will not be saved on form submit'
            ],
            'message' => [
                'forbidden' => 'It is forbidden to edit Items in this section',
                'no_data_to_save' => 'No data received',
                'success' => 'Items updated: :count',
                'nothing_updated' => 'No items updated',
            ]
        ],
        'modal' => [
            'open_in_new_tab' => 'Open in new tab',
            'close' => 'Close',
            'reload' => 'Reload data'
        ],
    ],
    'item_details' => [
        'toolbar' => [
            'cancel' => 'Back',
            'close' => 'Close',
            'edit_item' => 'Edit',
            'create_item' => 'Add new',
            'delete_item' => 'Delete',
            'clone_item' => 'Duplicate',
        ],
        'previous_item' => 'Previous item',
        'next_item' => 'Next item',
        'field' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            'no_relation' => 'Relation not exists'
        ],
        'modal' => [
            'open_in_new_tab' => 'Open in new tab',
            'close' => 'Close',
            'reload' => 'Reload data'
        ],
        'message' => [
            'delete_item_confirm' => 'Confirm Item delete action',
            'forbidden' => 'It is forbidden to view Items details in this section',
            'forbidden_for_record' => 'It is forbidden to view details of this Item',
        ],
    ],
    'action' => [
        'back' => 'Back',
        'reload_page' => 'Reload page',
    ],
    'ckeditor' => [
        'fileupload' => [
            'cannot_detect_resource_and_field' => 'Failed to detect resource name and field name in editor name. Expected editor name: "resource_name:field_name". Received editor name: ":editor_name"',
            'cannot_find_field_in_scaffold' => 'Field ":field_name" was not found in form configuretion in :scaffold_class. Editor name: ":editor_name"',
            'is_not_wysiwyg_field_config' => 'Field ":field_name" form configuration of :scaffold_class is not an instance of class :wysywig_class',
            'image_uploading_folder_not_set' => 'The folder to save images for field ":field_name" form configuration of :scaffold_class is not configured',
            'failed_to_resize_image' => 'Failed to resize image',
            'invalid_or_corrupted_image' => 'File is not an image or image is corrupted',
            'failed_to_save_image_to_fs' => 'Failed to save file to storage',
        ]
    ],
];
