<?php

return [
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
                'cmf_scaffold_html_inserts_plugin_title' => 'Insert HTML widget',
                'cmf_scaffold_inserts_dialog_insert_tag_is_span' => 'Inside existing text (span)',
                'cmf_scaffold_inserts_dialog_insert_tag_is_div' => 'As separate text block (div, p)',
            ],
        ]
    ],
    'error' => [
        'resource_item_not_found' => 'Requested Item not found',
        'db_record_not_exists' => 'Item not found in database',
        'invalid_data_received' => 'Invalid data received',
        'csrf_token_missmatch' => 'Current session is outdated or invalid. Page reloading is required.',
        'http404' => 'Requested page not found'
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
            'delete_selected_confirm' => 'Confirm selected Items delete action',
            'edit_selected' => '<span class="label label-primary">:count</span> Edit selected',
            'delete_filtered' => '<span class="label label-danger">:count</span> Delete filtered',
            'delete_filtered_confirm' => 'Confirm filtered Items delete action',
            'edit_filtered' => '<span class="label label-primary">:count</span> Edit filtered ',
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
            'show_children' => 'Show children',
            'hide_children' => 'Hide children',
        ],
        'filter' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ]
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
        'failed_to_save_resource_data' => 'Failed to save data',
        'validation_errors' => 'Invalid data detected',
        'resource_created_successfully' => 'Item successfully created',
        'resource_updated_successfully' => 'Item successfully updated',
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
                'row_delete_action_forbidden' => 'Row delete is forbidden. You need to maintain minimal required row count.'
            ],
        ],
        'bulk_edit' => [
            'enabler' => [
                'edit_input' => 'Change',
                'skip_input' => 'Skip'
            ]
        ]
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
    ],
    'action' => [
        'delete' => [
            'forbidden' => 'It is forbidden to delete Items from this section',
            'success' => 'Item successfully deleted',
            'please_confirm' => 'Confirm Item delete action',
            'forbidden_for_record' => 'It is forbidden to delete this Item',
        ],
        'delete_bulk' => [
            'success' => 'Items deleted: :count',
            'nothing_deleted' => 'No items deleted',
        ],
        'create' => [
            'forbidden' => 'It is forbidden to create Items in this section',
        ],
        'edit' => [
            'forbidden' => 'It is forbidden to edit Items in this section',
            'forbidden_for_record' => 'It is forbidden to edit this Item',
            'key_value_table' => [
                'no_foreign_key_value' => 'There is no ID for object that should be an owner of received values'
            ]
        ],
        'bulk_edit' => [
            'no_data_to_save' => 'No data received',
            'success' => 'Items updated: :count',
            'nothing_updated' => 'No items updated',
        ],
        'item_details' => [
            'forbidden' => 'It is forbidden to view Items details in this section',
            'forbidden_for_record' => 'It is forbidden to view details of this Item',
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
