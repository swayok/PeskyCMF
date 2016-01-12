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
        'header' => 'Authorisation',
        'email_label' => 'E-mail',
        'password_label' => 'Password',
        'button_label' => 'Log in',
        'forgot_password_label' => 'Forgot password?',
        'login_failed' => 'Invalid E-mail or password',
    ],
    'ui' => [
        'main_menu_header' => 'Navigation',
        'js_component' => [
            'data_tables' => [










                // new lines added for easier comparison of language files









                'toolbar' => [
                    'reloadData' => 'Reload'
                ]
            ]
        ]
    ],
    'user' => [
        'profile_label' => 'Profile',
        'logout_label' => 'Log Out'
    ],
    'admins' => [
        'menu_title' => 'Administrators'
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
            'edit_item' => 'Edit',
            'view_item' => 'View',
            'delete_item' => 'Delete'
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
    'error' => [
        'resource_item_not_found' => 'Requested Item not found'
    ],
    'action' => [
        'delete' => [
            'forbidden' => 'It is forbidden to delete Items from this section',
            'success' => 'Item successfully deleted',
            'please_confirm' => 'Confirm Item delete action'
        ],
        'create' => [
            'forbidden' => 'It is forbidden to create Items in this section',
        ],
        'edit' => [
            'forbidden' => 'It is forbidden to edit Items in this section',
        ],
        'item_details' => [
            'forbidden' => 'It is forbidden to view Items details in this section',
        ],
        'back' => 'Back',
        'reload_page' => 'Reload page',
    ],
    'page' => [
        'about' => [
            'link_label' => 'About project'
        ],
        'dashboard' => [
            'header' => 'Welcome to administration panel based on PeskyCMF'
        ],
        'profile' => [
            'header' => 'Administrator profile',
            'input' => [
                'email' => 'E-mail',
                'new_password' => 'New password',
                'old_password' => 'Current passsword',
                'language' => 'Language',
                'name' => 'Name',
            ],
            'saved' => 'Administrator profile updated',
            'errors' => [
                'new_password' => [
                    'min' => 'Minimum password length is :max symbols'
                ],
                'old_password' => [
                    'required' => 'Enter current password to save changes',
                    'match' => 'Invalid current password entered',
                ],
                'email' => [
                    'required' => 'Enter E-mail address',
                    'email' => 'Enter valid E-mail address',
                    'unique' => 'Entered E-mail address already in use by another Administrator'
                ],
                'language' => [
                    'required' => 'Select language',
                    'in' => 'Selected language is not in list of allowed languages. Select another language.',
                ]
            ]
        ]
    ]
];
