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
        'header' => 'Authorization',
        'email_label' => 'E-mail',
        'password_label' => 'Password',
        'button_label' => 'Log in',
        'forgot_password_label' => 'Forgot password?',
        'registration_label' => 'Create account',
        'login_failed' => 'Invalid E-mail or password',
    ],
    'registration_form' => [
        'header' => 'Registration',
        'email_label' => 'E-mail',
        'login_label' => 'Login',
        'name_label' => 'Name',
        'password_label' => 'Password',
        'password_confirmation_label' => 'Confirm password',
        'button_label' => 'Create account',
        'login_to_account_label' => 'Login to account',
        'errors' => [
            'password' => [
                'min' => 'Minimum password length is :max symbols',
                'confirmed' => 'Passwords do not match'
            ],
            'email' => [
                'required' => 'Enter E-mail address',
                'email' => 'Enter valid E-mail address',
                'unique' => 'Entered E-mail address already in use'
            ],
            'login' => [
                'required' => 'Enter login',
                'regex' => 'Login may only contain latin letters, digits, "_", "-", "@", "."',
                'min' => 'Lugin must contain at least :min symbols',
                'unique' => 'Entered login already in use',
            ],
        ]
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
    ],
    'user' => [
        'profile_label' => 'Profile',
        'logout_label' => 'Log Out'
    ],
    'admins' => [
        'menu_title' => 'Administrators',
        'login_as' => [
            'success' => 'You have logged in to account :user. Use Logout button to return to your account',
            'fail' => 'Failed to login to account with ID :id',
            'same_user' => 'You are trying to login to your own account',
            'no_auth_token' => 'There is no auth token for current user'
        ],
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
            ],
            'filter' => [
                'cms_admins' => [
                    'id' => 'ID',
                    'parent_id' => 'Creator\'s ID',
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
                ],
                'parent_admin' => [
                    'email' => 'Creator\'s E-mail',
                    'login' => 'Creator\'s Login',
                    'name' => 'Creator\'s Name',
                ]
            ],
            'action' => [
                'login_as' => 'Login as this user'
            ],
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
            ],
            'action' => [
                'login_as' => 'Login as this user'
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
            'header' => 'Account settings',
            'input' => [
                'email' => 'E-mail',
                'new_password' => 'New password',
                'old_password' => 'Current passsword',
                'language' => 'Language',
                'name' => 'Name',
                'timezone' => 'Time zone',
                'no_timezone' => '[Not selected]',
                'timezone_search' => 'Search by time zone name',
            ],
            'saved' => 'Account settings saved',
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
                    'unique' => 'Entered E-mail address already in use'
                ],
                'login' => [
                    'required' => 'Enter login',
                    'regex' => 'Login may only contain latin letters, digits, "_", "-", "@", "."',
                    'min' => 'Lugin must contain at least :min symbols',
                    'unique' => 'Entered login already in use',
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
    'api_docs' => [
        'menu_title' => 'API Documentation',
        'header' => 'API Documentation',
        'description' => 'Description',
        'headers' => 'HTTP headers',
        'url_params' => 'URL parameters (parameters inside the URL)',
        'url_query_params' => 'HTTP GET parameters (URL query)',
        'post_params' => 'HTTP POST parameters',
        'response' => 'Server response on success',
        'errors' => 'Possible errors',
        'download_postman_collection' => 'Download requests collection for Postman',
        'postman_collection_file_name' => 'postman_collection_for_api_on_:http_host',
        'section' => [
            'no_section' => 'API methods without a group'
        ],
        'error' => [
            'auth_failure' => 'Authentication failure',
            'access_denied' => 'Access denied',
            'validation_errors' => 'Invalid data received',
            'server_error' => 'Critical error on server',
        ]
    ],
    'cms_pages' => [
        'menu_title' => 'Pages',
        'type' => [
            'page' => 'Page',
            'news' => 'News item',
            'category' => 'Category',
            'item' => 'Item',
            'text_element' => 'Text block',
            'menu' => 'Menu',
        ],
        'datagrid' => [
            'header' => 'Pages',
            'column' => [
                'id' => 'ID',
                'type' => 'Type',
                'comment' => 'Comment',
                'url_alias' => 'Relative URL',
                'relative_url' => 'URL',
                'page_code' => 'Readable ID',
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
                'title' => 'Title',
                'publish at' => 'Publishing date',
            ],
            'filter' => [
                'cms_pages' => [
                    'id' => 'ID',
                    'parent_id' => 'Parent page',
                    'type' => 'Type',
                    'title' => 'Title',
                    'comment' => 'Comment',
                    'url_alias' => 'Relative URL',
                    'page_code' => 'Readable ID',
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
                    'publish_at' => 'Publishing date',
                ],
                'parent' => [
                    'id' => 'Parent\'s ID',
                    'url_alias' => 'Parent\'s relative URL',
                    'title' => 'Parent\'s title',
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
                'page_code' => 'Readable ID (used by programmer when required)',
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
                'title' => 'Title (used only in administration panel)',
                'publish at' => 'Publishing date',
                'texts' => [
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
                    'text_block' => 'Text block',
                    'text_block_id_arg_label' => 'Select text block',
                    'text_block_insert_widget_title_template' => 'Insert text block ":page_id.label"',
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
            'tab' => [
                'general' => 'General',
                'images' => 'Images',
                'texts' => 'Texts (:language)'
            ],
            'field' => [
                'id' => 'ID',
                'type' => 'Type',
                'comment' => 'Comment',
                'url_alias' => 'Relative URL',
                'relative_url' => 'URL',
                'page_code' => 'Readable ID',
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
                'title' => 'Title',
                'publish at' => 'Publishing date',
                'texts' => [
                    'id' => 'ID',
                    'title' => 'Full title',
                    'language' => 'Language',
                    'menu_title' => 'Short title (for menus)',
                    'browser_title' => 'Browser title',
                    'comment' => 'Comment for text editing',
                    'content' => 'Text',
                    'meta_description' => 'Meta-description',
                    'meta_keywords' => 'Meta-keywords',
                    'custom_info' => 'Additional info',
                    'admin_id' => 'Last modifier',
                ],
            ]
        ]
    ],
    'cms_news' => [
        'menu_title' => 'News',
        'datagrid' => [
            'header' => 'News',
        ],
        'form' => [
            'header_create' => 'News item creation',
            'header_edit' => 'News item editing',
        ],
        'item_details' => [
            'header' => 'News item details',
        ]
    ],
    'cms_shop_categories' => [
        'menu_title' => 'Shop categories',
        'datagrid' => [
            'header' => 'Shop categories',
        ],
        'form' => [
            'header_create' => 'Shop category creation',
            'header_edit' => 'Shop category editing',
        ],
        'item_details' => [
            'header' => 'Shop category details',
        ]
    ],
    'cms_shop_items' => [
        'menu_title' => 'Shop items',
        'datagrid' => [
            'header' => 'Shop items',
        ],
        'form' => [
            'header_create' => 'Shop item creation',
            'header_edit' => 'Shop item editing',
        ],
        'item_details' => [
            'header' => 'Shop item details',
        ]
    ],
    'cms_text_elements' => [
        'menu_title' => 'Text blocks',
        'datagrid' => [
            'header' => 'Text blocks',
        ],
        'form' => [
            'header_create' => 'Text block creation',
            'header_edit' => 'Text block editing',
        ],
        'item_details' => [
            'header' => 'Text block details',
        ]
    ],
    'cms_menus' => [
        'menu_title' => 'Menus',
        'datagrid' => [
            'header' => 'Menus',
        ],
        'form' => [
            'header_create' => 'Menu creation',
            'header_edit' => 'Menu editing',
            'input' => [
                'Texts' => [
                    'menu_title' => 'Menu header',
                ]
            ]
        ],
        'item_details' => [
            'header' => 'Menu details',
            'field' => [
                'Texts' => [
                    'menu_title' => 'Menu header',
                ]
            ]
        ]
    ],
    'cms_redirects' => [
        'menu_title' => 'Redirects',
        'datagrid' => [
            'header' => 'Redirects',
            'column' => [
                'relative_url' => 'Redirect from',
                'from_url' => 'Redirect from',
                'to_url' => 'Redirect to',
                'page_id' => 'Redirect to page',
                'is_permanent' => 'Is permanent?',
                'id' => 'ID',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
            ],
            'filter' => [
                'cms_redirects' => [
                    'page_id' => 'Target page ID',
                    'from_url' => 'Redirect from URL',
                    'to_url' => 'Redirect to URL',
                    'is_permanent' => 'Is permanent?',
                    'id' => 'ID',
                    'admin_id' => 'Last modifier',
                    'created_at' => 'Created',
                    'updated_at' => 'Updated',
                ],
                'page' => [
                    'title' => 'Target page title',
                    'url_alias' => 'Target page URL (without its parent\'s URL)'
                ]
            ]
        ],
        'form' => [
            'header_create' => 'Redirect creation',
            'header_edit' => 'Redirect editing',
            'input' => [
                'from_url' => 'Redirect from URL',
                'to_url' => 'Redirect to URL',
                'page_id' => 'Redirect to page',
                'is_permanent' => 'Is permanent?',
                'id' => 'ID',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
                'page_types' => [
                    'page' => 'Pages',
                    'news' => 'News',
                    'category' => 'Shop categories',
                    'item' => 'Shop items'
                ]
            ],
            'tooltip' => [
                'to_url' => 'This input must be empty when Redirect to page selected'
            ]
        ],
        'item_details' => [
            'header' => 'Redirect details',
            'field' => [
                'from_url' => 'Redirect from URL',
                'to_url' => 'Redirect to URL',
                'page_id' => 'Redirect to page',
                'is_permanent' => 'Is permanent?',
                'id' => 'ID',
                'admin_id' => 'Last modifier',
                'created_at' => 'Created',
                'updated_at' => 'Updated',
            ]
        ]
    ],
    'http_request_stats' => [
        'menu_title' => 'HTTP requests stats',
        'datagrid' => [
            'header' => 'HTTP requests stats',
            'column' => [
                'http_method' => 'Method',
                'url' => 'Route and URL',
                'route' => 'Route and URL',
                'duration' => 'Duration',
                'duration_sql' => 'Duration (SQL)',
                'memory_usage_mb' => 'Memory',
                'is_cache' => 'Cached?',
                'url_params' => 'URL parameters',
                'sql' => 'SQL queries',
                'id' => 'ID',
                'created_at' => 'Created',
                'http_code' => 'HTTP code',
                'sql:statements_count' => 'SQL queries',
                'rows_affected' => 'Rows affected',
                'failed_statements' => 'Failed',
            ],
            'filter' => [
                'http_request_stats' => [
                    'http_method' => 'Method',
                    'url' => 'URL',
                    'route' => 'Route',
                    'duration' => 'Duration',
                    'duration_sql' => 'Duration (SQL)',
                    'memory_usage_mb' => 'Memory',
                    'is_cache' => 'Cached?',
                    'url_params' => 'URL parameters',
                    'sql' => 'SQL queries',
                    'id' => 'ID',
                    'created_at' => 'Created',
                    'http_code' => 'Response code',
                    'counters' => 'Counters',
                ],
            ],
        ],
        'item_details' => [
            'header' => '',
            'field' => [
                'http_method' => 'Method',
                'url' => 'URL',
                'route' => 'Route',
                'duration' => 'Duration',
                'duration_sql' => 'Duration (SQL)',
                'memory_usage_mb' => 'Memory',
                'is_cache' => 'Cached?',
                'url_params' => 'URL parameters',
                'sql' => 'SQL queries',
                'id' => 'ID',
                'created_at' => 'Created',
                'http_code' => 'Response code',
                'request_data' => 'Request data',
                'checkpoints' => 'Checkpoints',
                'counters' => 'Counters',
            ],
        ]
    ],
    'http_request_logs' => [
        'menu_title' => 'HTTP requests log',
        'datagrid' => [
            'header' => 'HTTP requests',
            'column' => [
                'url' => 'URL',
                'http_method' => 'HTTP method',
                'ip' => 'IP',
                'filter' => 'Filter code',
                'section' => 'Section',
                'response_code' => 'Response code',
                'response_type' => 'Response type',
                'request' => 'Request data',
                'response' => 'Response data',
                'table' => 'DB table',
                'item_id' => 'Record ID in DB',
                'data_before' => 'Data before change',
                'data_after' => 'Data after change',
                'created_at' => 'Created at',
                'responded_at' => 'Responded at',
                'id' => 'ID',
                'requester_table' => 'User type',
                'requester_id' => 'User',
                'requester_info' => 'User info',
                'debug' => 'Debug data',
            ],
            'filter' => [
                'http_request_logs' => [
                    'url' => 'URL',
                    'http_method' => 'HTTP method',
                    'ip' => 'IP',
                    'filter' => 'Filter code',
                    'section' => 'Section',
                    'response_code' => 'Response code',
                    'response_type' => 'Response type',
                    'request' => 'Request data',
                    'response' => 'Response data',
                    'table' => 'DB table',
                    'item_id' => 'Record ID in DB',
                    'data_before' => 'Data before change',
                    'data_after' => 'Data after change',
                    'created_at' => 'Created at',
                    'responded_at' => 'Responded at',
                    'id' => 'ID',
                    'requester_table' => 'User type',
                    'requester_id' => 'User ID',
                    'requester_info' => 'User info',
                    'debug' => 'Debug data',
                ]
            ]
        ],
        'item_details' => [
            'header' => 'HTTP request information',
            'field' => [
                'url' => 'URL',
                'http_method' => 'HTTP method',
                'ip' => 'IP',
                'filter' => 'Filter code',
                'section' => 'Section',
                'response_code' => 'Response code',
                'response_type' => 'Response type',
                'request' => 'Request data',
                'response' => 'Response data',
                'table' => 'DB table',
                'item_id' => 'Record ID in DB',
                'data_before' => 'Data before change',
                'data_after' => 'Data after change',
                'created_at' => 'Created at',
                'responded_at' => 'Responded at',
                'id' => 'ID',
                'requester_table' => 'User type',
                'requester_id' => 'User',
                'requester_info' => 'User info',
                'debug' => 'Debug data',
            ]
        ]
    ],
];

$dictionary['cms_news'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_news']);
$dictionary['cms_shop_categories'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_shop_categories']);
$dictionary['cms_shop_items'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_shop_items']);
$dictionary['cms_text_elements'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_text_elements']);
$dictionary['cms_menus'] = array_replace_recursive($dictionary['cms_pages'], $dictionary['cms_menus']);

return $dictionary;