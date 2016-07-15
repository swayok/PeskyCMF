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
                'submit' => 'Apply filter',
                'toggle' => 'Filters'
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
            'invert_selection' => 'Invert selection'
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
        'field' => [
            'bool' => [
                'yes' => 'Yes',
                'no' => 'No'
            ],
        ],
        'bulk_edit' => [
            'enabler' => [
                'edit_field' => 'Change',
                'skip_field' => 'Skip'
            ]
        ]
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
        ],
        'item_details' => [
            'forbidden' => 'It is forbidden to view Items details in this section',
            'forbidden_for_record' => 'It is forbidden to view details of this Item',
        ],
        'back' => 'Back',
        'reload_page' => 'Reload page',
    ],
];
