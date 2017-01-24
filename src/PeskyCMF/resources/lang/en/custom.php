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
    'forgot_password' => [
        'header' => 'Password restoration',
        'email_label' => 'Your E-mail',
        'button_label' => 'Send instructions',
        'instructions_sent' => 'Password recovery instructions were sent to your E-mail',
        'email_subject' => 'Password recovery instructions',
        'email_content' => '<p>To set new password, visit your personal <a href=":url" target="_blank">Password recovery page</a></p>
            <p>This page will be available for an hour</p>',
    ],
    'replace_password' => [
        'header' => 'Replace password',
        'password_label' => 'New password',
        'password_confirm_label' => 'Confirm new password',
        'button_label' => 'Save',
        'invalid_access_key' => 'Access key to this page is invalid or outdated',
        'password_replaced' => 'New password saved',
        'failed_to_save' => 'Failed to save new password',
    ],
    'user' => [
        'profile_label' => 'Profile',
        'logout_label' => 'Log Out'
    ],
    'admins' => [
        'menu_title' => 'Administrators',
        'role' => [
            'admin' => 'Administrator',
            'superadmin' => 'Superadmin'
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
                'created_at' => 'Created',
                'timezone' => 'Timezone',
            ]
        ],
        'form' => [
            'header_create' => 'Adding administrator',
            'header_edit' => 'Editing administrator',
            'input' => [
                'email' => 'E-mail',
                'password' => 'Password',
                'name' => 'Name',
                'language' => 'Language',
                'is_active' => 'Active?',
                'role' => 'Role',
                'is_superadmin' => 'Has full access (superadmin)?',
                'timezone' => 'Timezone',
            ]
        ],
        'item_details' => [
            'header' => 'Administrator details',
            'field' => [
                'id' => 'ID',
                'email' => 'E-mail',
                'password' => 'Password',
                'name' => 'Name',
                'language' => 'Language',
                'is_active' => 'Active?',
                'role' => 'Role',
                'is_superadmin' => 'Has full access (superadmin)?',
                'parent_id' => 'Administrator who created this account',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'timezone' => 'Timezone',
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
                'timezone' => 'Time zone',
                'no_timezone' => '[Not selected]',
                'timezone_search' => 'Search time zone by name or offset',
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
                'login' => [
                    'required' => 'Enter login',
                    'regex' => 'Login may only contain latin letters, digits, "_", "-", "@", "."',
                    'min' => 'Lugin must contain at least :min symbols',
                    'unique' => 'Entered login already in use by another Administrator',
                ],
                'language' => [
                    'required' => 'Select language',
                    'in' => 'Selected language is not in list of allowed languages. Select another language.',
                ],
                'timezone' => [
                    'required' => 'Select time zone',
                    'exists' => 'Selected time zone is not in list of allowed time zones. Select another time zone.'
                ]
            ]
        ]
    ],
    'settings' => [
        'menu_title' => 'Settings',
        'form' => [
            'header_create' => 'System settings',
            'header_edit' => 'System settings',
            'input' => [
                'default_browser_title' => 'Default page title in browser',
                'browser_title_addition' => 'Addition for page title in browser',
                'default_language' => 'Main language for site',
                'languages' => 'Languages',
                'languages_key' => 'Language code (2 symbols)',
                'languages_value' => 'Language title',
                'languages_add' => 'Add language',
                'languages_delete' => 'Delete language',
                'fallback_languages' => 'Languages translations replacements map',
                'fallback_languages_key' => 'For language (2 symbols)',
                'fallback_languages_value' => 'Use translation to language (2 symbols)',
                'fallback_languages_add' => 'Add language replacement',
                'fallback_languages_delete' => 'Delete language replacement',
            ],
            'tooltip' => [
                'browser_title_addition' => 'Will not be added if pages title provided by "Default page title in browser" setting',
                'languages' => [
                    'It is required to have at least 1 language configured',
                    'Language code must contain exactly 2 latin letters'
                ],
                'fallback_languages' => [
                    'Language codes must contain exactly 2 latin letters',
                    'This mapping may be used when to provide correct translation language when "Main language" is not preferred',
                    'For example: "de" (German) as "Main language" is not preferred when "fr" language code is requred. "en" language will fit better here'
                ]
            ],
        ],
    ]
];
