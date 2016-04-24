<?php

return [
    'ui' => [
        'js_component' => [
            'data_tables' => [










                // new lines added for easier comparison of language files









                'toolbar' => [
                    'reloadData' => 'Reload'
                ]
            ]
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
                'submit' => 'Filter'
            ],
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
            'delete_item' => 'Delete'
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
            'cancel' => 'Cancel',
            'submit' => 'Save',
            'create' => 'Add new',
            'delete' => 'Delete'
        ],
        'failed_to_save_resource_data' => 'Failed to save data',
        'validation_errors' => 'Invalid data detected',
        'resource_created_successfully' => 'Item successfully created',
        'resource_updated_successfully' => 'Item successfully updated',
    ],
    'item_details' => [
        'toolbar' => [
            'cancel' => 'Back',
            'edit' => 'Edit',
            'create' => 'Add new',
            'delete' => 'Delete'
        ],
        'field' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
            'no_relation' => 'Relation not exists'
        ]
    ],
    'action' => [
        'delete' => [
            'forbidden' => 'It is forbidden to delete Items from this section',
            'success' => 'Item successfully deleted',
            'please_confirm' => 'Confirm Item delete action',
            'forbidden_for_record' => 'It is forbidden to delete this Item',
        ],
        'create' => [
            'forbidden' => 'It is forbidden to create Items in this section',
        ],
        'edit' => [
            'forbidden' => 'It is forbidden to edit Items in this section',
            'forbidden_for_record' => 'It is forbidden to edit this Item',
        ],
        'item_details' => [
            'forbidden' => 'It is forbidden to view Items details in this section',
            'forbidden_for_record' => 'It is forbidden to view details of this Item',
        ],
        'back' => 'Back',
        'reload_page' => 'Reload page',
    ],
];
