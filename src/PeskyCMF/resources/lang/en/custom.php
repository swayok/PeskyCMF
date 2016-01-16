<?php

return [
    'default_page_title' => 'PeskyCMF',
    'language' => [
        'en' => 'English',
        'ru' => 'Русский'
    ],
    'main_menu' => [
        'header' => 'Navigation',
    ],
    'login_form' => [
        'header' => 'Authorisation',
        'email_label' => 'E-mail',
        'password_label' => 'Password',
        'button_label' => 'Log in',
        'forgot_password_label' => 'Forgot password?',
        'login_failed' => 'Invalid E-mail or password',
    ],
    'user' => [
        'profile_label' => 'Profile',
        'logout_label' => 'Log Out'
    ],
    'admins' => [
        'menu_title' => 'Administrators',
        'role' => [
            'admin' => 'Administrator'
        ],
        'datagrid' => [
            'header' => 'System administrators',
            'column' => [
                'id' => 'ID',
                'parent_id' => 'Creator',
                'email' => 'E-mail',
                'name' => 'Name',
                'is_active' => 'Active?',
                'is_superadmin' => 'Superadmin?',
                'role' => 'Role',
                'language' => 'Language',
                'ip' => 'IP',
                'created_at' => 'Created'
            ]
        ],
        'form' => [
            'header_create' => 'Adding administrator',
            'header_edit' => 'Editing administrator',
            'field' => [
                'email' => 'E-mail',
                'password' => 'Password',
                'name' => 'Name',
                'language' => 'Language',
                'is_active' => 'Active?',
                'role' => 'Role',
                'is_superadmin' => 'Has full access (superadmin)?'
            ]
        ],
        'item_details' => [
            'header' => 'Administrator details',
            'field' => [
                'email' => 'E-mail',
                'password' => 'Password',
                'name' => 'Name',
                'language' => 'Language',
                'is_active' => 'Active?',
                'role' => 'Role',
                'is_superadmin' => 'Has full access (superadmin)?',
                'parent_id' => 'Administrator who created this account',
                'created_at' => 'Created',
                'updated_at' => 'Updated'
            ]
        ]
    ],
    'page' => [
        'about' => [
            'link_label' => 'About project'
        ],
        'dashboard' => [
            'header' => 'Welcome to administration panel based on PeskyCMF',
            'menu_title' => 'Home'
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
