<?php

$dictionary = [
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
                'login' => 'Login',
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
                'login' => 'Login',
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
                'login' => 'Login',
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
            'tab' => [
                'general' => 'General',
                'localization' => 'Localizations'
            ]
        ],
    ],
    'pages' => [
        'menu_title' => 'Pages',
        'types' => [
            'page' => 'Page',
            'news' => 'News item',
            'category' => 'Category',
            'item' => 'Item'
        ],
        'datagrid' => [
            'header' => 'Pages',
            'column' => [
                'id' => 'ID',
                'type' => 'Type',
                'comment' => 'Comment',
                'url_alias' => 'Relative URL',
                'relative_url' => 'URL',
                'page_code' => 'Text ID',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Position',
                'with_contact_form' => 'Contact form?',
                'custom_info' => 'Info',
                'admin_id' => 'Last modifier',
                'is_published' => 'Is published?',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'text_id' => 'Page title',
            ],
            'filter' => [
                'pages' => [
                    'id' => 'ID',
                    'type' => 'Type',
                    'comment' => 'Comment',
                    'url_alias' => 'Relative URL',
                    'page_code' => 'Text ID',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'order' => 'Position',
                    'with_contact_form' => 'Contact form?',
                    'custom_info' => 'Info',
                    'admin_id' => 'Last modifier',
                    'is_published' => 'Is published?',
                    'created_at' => 'Created',
                    'updated_at' => 'Updated',
                    'text_id' => 'Text ID',
                    'parent_id' => 'Parent page',
                ],
                'primary_text' => [
                    'id' => 'Text ID',
                    'title' => 'Page title',
                    'browser_title' => 'Browser title',
                    'menu_title' => 'Menu title',
                    'content' => 'Page content',
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Page creation',
            'header_edit' => 'Page edititng',
            'tab' => [
                'general' => 'General',
                'images' => 'Images',
                'texts' => 'Texts (:language)'
            ],
            'input' => [
                'id' => 'ID',
                'type' => 'Type',
                'comment' => 'Comment',
                'url_alias' => 'URL',
                'url_alias_placeholder' => 'Examples: /page-title, /category/item',
                'page_code' => 'Text ID (used by programmer when required)',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Position',
                'with_contact_form' => 'Add contact form?',
                'custom_info' => 'Additional info',
                'admin_id' => 'Last modifier',
                'is_published' => 'Is published?',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'text_id' => 'Texts for the page',
                'images' => 'Images',
                'Texts' => [
                    'title' => 'Full title',
                    'menu_title' => 'Short title (for menus)',
                    'browser_title' => 'Browser title',
                    'comment' => 'Comment for text editing',
                    'content' => 'Text',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'custom_info' => 'Additional info',
                ],
                'content_inserts' => [
                    'part_of_other_page' => 'Part of other page',
                    'page_id_arg_label' => 'Select page',
                    'page_field_arg_label' => 'Select page\'s field',
                    'page_insert_widget_title_template' => 'Insert field ":page_field.label" from texts ":page_id.label"',
                    'part_of_text' => 'Text element',
                    'text_id_arg_label' => 'Select text element',
                    'text_field_arg_label' => 'Select element\'s field',
                    'text_insert_widget_title_template' => 'Insert field ":text_field.label" from texts ":text_id.label"',
                    'link_to_other_page' => 'Link to page',
                    'page_link_title_arg_label' => 'Link label (by default: "Short title" field value of selected page)',
                    'insert_link_to_page_widget_title_template' => 'Link to ":page_id.label" (Label: :title)',
                ],
            ],
            'tooltip' => [
                'meta_description' => 'Used in cases when Meta-description is not provided by attached texts',
                'meta_keywords' => 'Used in cases when Meta-keywords is not provided by attached texts',
                'url_alias' => 'Must start with "/" symbol and may only contain latin letters, digits, "-", "_" and "/"',
            ],
            'validation' => [
                'unique_page_url' => 'Page with same URL already <a href=":url" target="_blank">exists</a>'
            ]
        ],
        'item_details' => [
            'header' => 'Page details',
            'field' => [
                'id' => 'ID',
                'type' => 'Type',
                'comment' => 'Comment',
                'url_alias' => 'Relative URL',
                'relative_url' => 'URL',
                'page_code' => 'Text ID',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'order' => 'Position',
                'with_contact_form' => 'Add contact form?',
                'custom_info' => 'Additional info',
                'admin_id' => 'Last modifier',
                'is_published' => 'Is published?',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'text_id' => 'Texts for the page',
                'images' => 'Images',
                'parent_id' => 'Parent page',
            ]
        ]
    ],
    'texts' => [
        'menu_title' => 'Texts for pages',
        'datagrid' => [
            'header' => 'Texts for pages',
            'column' => [
                'id' => 'ID',
                'parent_id' => 'Translation for',
                'language' => 'Language',
                'title' => 'Full title',
                'menu_title' => 'Short title (for menus)',
                'browser_title' => 'Browser title',
                'comment' => 'Comment',
                'content' => 'Text',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Info',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
            ],
            'filter' => [
                'texts' => [
                    'id' => 'ID',
                    'parent_id' => 'Translation for',
                    'language' => 'Language',
                    'title' => 'Full title',
                    'menu_title' => 'Short title (for menus)',
                    'browser_title' => 'Browser title',
                    'comment' => 'Comment',
                    'content' => 'Text',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'custom_info' => 'Info',
                    'admin_id' => 'Last modifier',
                    'created_at' => 'Created',
                    'updated_at' => 'Updated',
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Texts for pages: creation',
            'header_edit' => 'Texts for pages: editing',
            'tab' => [
                'general' => 'General',
                'content' => 'Text',
            ],
            'input' => [
                'id' => 'ID',
                'is_translation' => 'Is translation of existing texts?',
                'parent_id' => 'Translation for',
                'language' => 'Language',
                'title' => 'Full title',
                'menu_title' => 'Short title (for menus)',
                'browser_title' => 'Browser title',
                'comment' => 'Comment for text editing',
                'content' => 'Text',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Additional info',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'insert_other_text_widget_title_template' => 'Insert field ":text_field.label" from texts ":text_id.label"',
            ],
            'validation' => [
                'unique_language_within_parent_id' => 'Translation for ":parent_title" texts already <a href=":url" data-toggle="tooltip" title="Загрузить перевод">exists</a> for selected language',
            ]
        ],
        'item_details' => [
            'header' => 'Texts for page: details',
            'field' => [
                'id' => 'ID',
                'parent_id' => 'Translation for',
                'language' => 'Language',
                'title' => 'Full title',
                'menu_title' => 'Short title (for menus)',
                'browser_title' => 'Browser title',
                'comment' => 'Comment for text editing',
                'content' => 'Text',
                'meta_description' => 'Meta-description',
                'meta_keywords' => 'Meta-keywords',
                'custom_info' => 'Additional info',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
            ]
        ]
    ],
    'texts_for_pages' => [
    ],
    'texts_for_news' => [
        'menu_title' => 'Texts for news',
        'datagrid' => [
            'header' => 'Texts for news',
        ],
        'form' => [
            'header_create' => 'Texts for news item: creation',
            'header_edit' => 'Texts for news item: editing',
        ],
        'item_details' => [
            'header' => 'Texts for news item: details',
        ]
    ],
    'texts_for_categories' => [
        'menu_title' => 'Texts for categories',
        'datagrid' => [
            'header' => 'Texts for categories',
        ],
        'form' => [
            'header_create' => 'Texts for category: creation',
            'header_edit' => 'Texts for category: editing',
        ],
        'item_details' => [
            'header' => 'Texts for category: details',
        ]
    ],
    'texts_for_items' => [
        'menu_title' => 'Texts for items',
        'datagrid' => [
            'header' => 'Texts for items',
        ],
        'form' => [
            'header_create' => 'Texts for item: creation',
            'header_edit' => 'Texts for item: editing',
        ],
        'item_details' => [
            'header' => 'Texts for item: details',
        ]
    ],
    'common_texts' => [
        'menu_title' => 'Common texts',
        'datagrid' => [
            'header' => 'Common texts',
        ],
        'form' => [
            'header_create' => 'Common texts: creation',
            'header_edit' => 'Common texts: editing',
        ],
        'item_details' => [
            'header' => 'Common texts: details',
        ]
    ],
];

$dictionary['texts_for_pages'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_pages']);
$dictionary['texts_for_news'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_news']);
$dictionary['texts_for_categories'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_categories']);
$dictionary['texts_for_items'] = array_replace_recursive($dictionary['texts'], $dictionary['texts_for_items']);
$dictionary['common_texts'] = array_replace_recursive($dictionary['texts'], $dictionary['common_texts']);

return $dictionary;