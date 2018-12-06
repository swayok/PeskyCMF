<?php

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Middleware\AjaxOnly;

/**
 * @var CmfConfig $cmfConfig
 */
$routeNamePrefix = $cmfConfig::routes_names_prefix();
$apiControllerClass = $cmfConfig::cmf_scaffold_api_controller_class();
$generalControllerClass = $cmfConfig::cmf_general_controller_class();

Route::group(
    [
        'middleware' => AjaxOnly::class,
    ],
    function () use ($routeNamePrefix, $generalControllerClass) {
        Route::get('login.html', [
            'uses' => $generalControllerClass . '@getLoginTpl',
            'log' => 'cmf.login',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_login']
        ]);

        Route::post('login', [
            'uses' => $generalControllerClass . '@doLogin',
            'log' => 'cmf.login',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_login']
        ]);

        Route::get('register.html', [
            'uses' => $generalControllerClass . '@getRegistrationTpl',
            'log' => 'cmf.registration',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_register']
        ]);

        Route::post('register', [
            'uses' => $generalControllerClass . '@doRegister',
            'log' => 'cmf.registration',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_register']
        ]);
    }
);

Route::get('login', [
    'as' => $routeNamePrefix . 'cmf_login',
    'uses' => $generalControllerClass . '@loadJsApp'
]);

Route::get('register', [
    'as' => $routeNamePrefix . 'cmf_register',
    'uses' => $generalControllerClass . '@loadJsApp'
]);

Route::get('logout', [
    'as' => $routeNamePrefix . 'cmf_logout',
    'uses' => $generalControllerClass . '@logout',
    'log' => 'cmf.logout'
]);

\Route::post('/ping', [
    'as' => $routeNamePrefix . 'ping',
    'uses' => $generalControllerClass . '@ping'
]);

Route::group(
    [
        'middleware' => AjaxOnly::class,
        'fallback' => ['route' => $routeNamePrefix . 'cmf_forgot_password']
    ],
    function () use ($generalControllerClass) {

        Route::get('forgot_password.html', [
            'uses' => $generalControllerClass . '@getForgotPasswordTpl',
            'log' => 'cmf.forgot_password'
        ]);

        Route::post('forgot_password', [
            'uses' => $generalControllerClass . '@sendPasswordReplacingInstructions',
            'log' => 'cmf.forgot_password'
        ]);

        Route::get('replace_password/{access_key}.html', [
            'uses' => $generalControllerClass . '@getReplacePasswordTpl',
            'log' => 'cmf.replace_password'
        ]);

        Route::put('replace_password/{access_key}', [
            'uses' => $generalControllerClass . '@replacePassword',
            'log' => 'cmf.replace_password'
        ]);
    }
);

Route::get('forgot_password', [
    'as' => $routeNamePrefix . 'cmf_forgot_password',
    'uses' => $generalControllerClass . '@loadJsApp'
]);

Route::get('replace_password/{access_key}', [
    'as' => $routeNamePrefix . 'cmf_replace_password',
    'uses' => $generalControllerClass . '@loadJsApp'
]);

\Route::get('ui/templates.js', [
    'as' => $routeNamePrefix . 'cmf_cached_templates_js',
    'uses' => $generalControllerClass . '@getCachedUiTemplatesJs',
]);

Route::group(
    [
        'middleware' => $cmfConfig::auth_middleware()
    ],
    function () use ($generalControllerClass, $routeNamePrefix) {

        Route::post('ckeditor/upload/image', [
            'as' => $routeNamePrefix . 'cmf_ckeditor_upload_image',
            'uses' => $generalControllerClass . '@ckeditorUploadImage'
        ]);

        Route::group(
            [
                'middleware' => AjaxOnly::class,
                'fallback' => [
                    'route' => $routeNamePrefix . 'cmf_start_page',
                    'use_params' => false
                ]
            ],
            function () use ($generalControllerClass, $routeNamePrefix) {

                Route::get('service/login/as/{id}', [
                    'as' => $routeNamePrefix . 'cmf_login_as_other_admin',
                    'uses' => $generalControllerClass . '@loginAsOtherUser',
                    'log' => 'cmf.service_login_as'
                ]);

                // UI views
                Route::get('ui/ui.html', [
                    'as' => $routeNamePrefix . 'cmf_main_ui',
                    'uses' => $generalControllerClass . '@getBasicUiView'
                ]);

                Route::get('ui/{view}.html', [
                    'uses' => $generalControllerClass . '@getUiView'
                ]);

                Route::get('page/menu/counters', [
                    'as' => $routeNamePrefix . 'cmf_menu_counters_data',
                    'uses' => $generalControllerClass . '@getMenuCounters',
                ]);

                // Admin profile
                Route::get('page/profile/data', [
                    'as' => $routeNamePrefix . 'cmf_profile_data',
                    'uses' => $generalControllerClass . '@getUserProfileData'
                ]);

                Route::get('page/profile.html', [
                    'uses' => $generalControllerClass . '@renderUserProfileView',
                    'log' => 'cmf.profile'
                ]);

                Route::put('page/profile', [
                    'as' => $routeNamePrefix . 'cmf_profile',
                    'uses' => $generalControllerClass . '@updateUserProfile',
                    'log' => 'cmf.profile'
                ]);

                // Custom Pages
                Route::get('page/{page}.html', $generalControllerClass . '@getPage');

            }
        );

        // Custom Pages
        Route::get('page/{page}',  $generalControllerClass . '@loadJsApp')
            ->name($routeNamePrefix . 'cmf_page')
            ->where('page', '^.*(?!\.html)$');

        // Switch locales
        Route::get('switch_locale/{locale}', $generalControllerClass . '@switchLocale');

        // Clean cache
        Route::get('cache/clean', $generalControllerClass . '@cleanCache');

        // Api docs
        Route::get(
                '/utils/api_docs/requests_collection_for_postman.json',
                $generalControllerClass . '@downloadApiRequestsCollectionForPostman'
            )
            ->name($routeNamePrefix . 'cmf_api_docs_download_postman_collection');
    }
);

Route::pattern('table_name', '[a-z]+([_a-z0-9]*[a-z0-9])?');
// Scaffold pages and templates
Route::group(
    [
        'prefix' => 'resource',
        'middleware' => $cmfConfig::auth_middleware()
    ],
    function () use ($apiControllerClass, $generalControllerClass, $routeNamePrefix) {
        Route::get('{table_name}', [
            'as' => $routeNamePrefix . 'cmf_items_table',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/create', [
            'as' => $routeNamePrefix . 'cmf_item_add_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/details/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_details',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/edit/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_edit_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/clone/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_clone_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/{id}/page/{page}.html', [
            'middleware' => AjaxOnly::class,
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_custom_page',
                'use_params' => true
            ],
            'uses' => $apiControllerClass . '@getCustomPageForItem',
        ]);

        Route::get('{table_name}/{id}/page/{page}', [
            'as' => $routeNamePrefix . 'cmf_item_custom_page',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        Route::get('{table_name}/page/{page}.html', [
            'middleware' => AjaxOnly::class,
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_resource_custom_page',
                'use_params' => true
            ],
            'uses' => $apiControllerClass . '@getCustomPage',
        ]);

        Route::get('{table_name}/page/{page}', [
            'as' => $routeNamePrefix . 'cmf_resource_custom_page',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);
    }
);

// Scaffold API
Route::group(
    [
        'prefix' => 'api',
        'middleware' => array_unique(array_merge(
            [AjaxOnly::class],
            $cmfConfig::auth_middleware(),
            $cmfConfig::middleware_for_cmf_scaffold_api_controller()
        ))
    ],
    function () use ($apiControllerClass, $routeNamePrefix) {

        Route::get('{table_name}/service/templates', [
            'as' => $routeNamePrefix . 'cmf_api_get_templates',
            'uses' => $apiControllerClass . '@getTemplates',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/list', [
            'as' => $routeNamePrefix . 'cmf_api_get_items',
            'uses' => $apiControllerClass . '@getItemsList',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/service/options/{column_name}.json', [
            'as' => $routeNamePrefix . 'cmf_api_get_options_as_json',
            'uses' => $apiControllerClass . '@getOptionsAsJson',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/service/options', [
            'as' => $routeNamePrefix . 'cmf_api_get_options',
            'uses' => $apiControllerClass . '@getOptions',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/service/defaults', [
            'as' => $routeNamePrefix . 'cmf_api_get_defaults',
            'uses' => $apiControllerClass . '@getItemDefaults',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_add_form',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/service/custom_data/{data_id}', [
            'as' => $routeNamePrefix . 'cmf_api_get_custom_data',
            'uses' => $apiControllerClass . '@getCustomData',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::delete('{table_name}/bulk', [
            'as' => $routeNamePrefix . 'cmf_api_delete_bulk',
            'uses' => $apiControllerClass . '@deleteBulk',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::put('{table_name}/bulk', [
            'as' => $routeNamePrefix . 'cmf_api_edit_bulk',
            'uses' => $apiControllerClass . '@updateBulk',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::any('{table_name}/action/{action}', [
            'as' => $routeNamePrefix . 'cmf_api_resource_custom_action',
            'uses' => $apiControllerClass . '@performAction',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::any('{table_name}/{id}/action/{action}', [
            'as' => $routeNamePrefix . 'cmf_api_item_custom_action',
            'uses' => $apiControllerClass . '@performActionForItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::get('{table_name}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_get_item',
            'uses' => $apiControllerClass . '@getItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_edit_form',
                'use_params' => true
            ]
        ]);

        Route::put('{table_name}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_update_item',
            'uses' => $apiControllerClass . '@updateItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_edit_form',
                'use_params' => true
            ]
        ]);

        Route::put('{table_name}/move/{id}/{before_or_after}/{other_id}/order/{sort_column}/{sort_direction}', [
                'as' => $routeNamePrefix . 'cmf_api_change_item_position',
                'uses' => $apiControllerClass . '@changeItemPosition',
                'fallback' => [
                    'route' => $routeNamePrefix . 'cmf_items_table',
                    'use_params' => true
                ]
            ])
            ->where([
                'before_or_after' => '^(before|after|BEFORE|AFTER)$',
                'sort_direction' => '^(asc|desc|ASC|DESC)$',
                'other_id' => '^\d+$',
                'sort_column' => '^[a-zA-Z_0-9]+$'
            ]);

        Route::delete('{table_name}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_delete_item',
            'uses' => $apiControllerClass . '@deleteItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true
            ]
        ]);

        Route::post('{table_name}', [
            'as' => $routeNamePrefix . 'cmf_api_create_item',
            'uses' => $apiControllerClass . '@addItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_add_form',
                'use_params' => true
            ]
        ]);
    }
);