<?php

return [
    'test' => 'ok', //< used in PeskyCmfServiceProvider to load cmf dictionaries
    'ui' => [
        'close' => 'Close',
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
    ],
    'bool' => [
        'yes' => 'Yes',
        'no' => 'No',
        'on' => 'On',
        'off' => 'Off'
    ],
    'month' => [
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
            'create' => 'Add new',
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
            ]
        ],
        'actions' => [
            'column_label' => 'Actions',
            'edit_item' => 'Edit',
            'view_item' => 'View',
            'delete_item' => 'Delete',
            'select_all' => 'Select all',
            'select_none' => 'Select none',
            'invert_selection' => 'Invert selection',
            'show_children' => 'Show nested items',
            'hide_children' => 'Hide nested items',
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
            'create' => 'Add new',
            'delete' => 'Delete'
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
                'row_delete_action_forbidden' => 'Row removal is forbidden. You need to maintain minimal required row count.'
            ],
            'has_many_related_records' => [
                'add_row' => 'Add element',
                'delete_row' => 'Remove element',
                'row_delete_action_forbidden' => 'Element removal is forbidden. You need to maintain minimal required elements count.'
            ]
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
            'edit' => 'Edit',
            'create' => 'Add new',
            'delete' => 'Delete',
            'close' => 'Close',
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
    'delete' => [
        'forbidden' => 'It is forbidden to delete Items from this section',
        'forbidden_for_record' => 'It is forbidden to delete this Item',
        'success' => 'Item successfully deleted',
    ],
    'action' => [
        'delete' => [

        ],
        'delete_bulk' => [
        ],
        'create' => [
        ],
        'edit' => [
        ],
        'bulk_edit' => [

        ],
        'item_details' => [
        ],
        'change_position' => [
        ],
        'back' => 'Back',
        'reload_page' => 'Reload page',
    ],
    'ckeditor' => [
        'fileupload' => [
            'cannot_detect_table_and_field' => 'Failed to detect table name and field name in editor name. Expected editor name: "table_name:field_name". Received editor name: ":editor_name"',
            'cannot_find_field_in_scaffold' => 'Field ":field_name" was not found in form configuretion in :scaffold_class. Editor name: ":editor_name"',
            'is_not_wysiwyg_field_config' => 'Field ":field_name" form configuration of :scaffold_class is not an instance of class :wysywig_class',
            'image_uploading_folder_not_set' => 'The folder to save images for field ":field_name" form configuration of :scaffold_class is not configured',
            'failed_to_resize_image' => 'Failed to resize image',
            'invalid_or_corrupted_image' => 'File is not an image or image is corrupted',
            'failed_to_save_image_to_fs' => 'Failed to save file to storage',
        ]
    ],
];
