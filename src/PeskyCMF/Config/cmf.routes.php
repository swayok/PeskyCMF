<?php

use PeskyCMF\Http\Middleware\AjaxOnly;
use PeskyCMF\Http\Middleware\ValidateAdmin;
use PeskyCMF\Http\Middleware\LoadModelAndScaffoldConfig;

function __cmf_general_controller_class() {
    return \PeskyCMF\Config\CmfConfig::getInstance()->cmf_general_controller_class();
}

function __cmf_scaffold_api_controller_class() {
    return \PeskyCMF\Config\CmfConfig::getInstance()->cmf_scaffold_api_controller_class();
}

Route::group(
    [
        'prefix' => \PeskyCMF\Config\CmfConfig::getInstance()->url_prefix(),
        'middleware' => ['web']
    ],
    function () {
        Route::group(
            [
                'middleware' => AjaxOnly::class,
                'fallback' => ['route' => 'cmf_login']
            ],
            function () {
                Route::get('login.html', [
                    'uses' => __cmf_general_controller_class() . '@getLoginTpl'
                ]);

                Route::post('login', [
                    'uses' => __cmf_general_controller_class() . '@doLogin'
                ]);
            }
        );

        Route::get('login', [
            'as' => 'cmf_login',
            'uses' => __cmf_general_controller_class() . '@getLogin'
        ]);

        Route::get('logout', [
            'as' => 'cmf_logout',
            'uses' => __cmf_general_controller_class() . '@logout'
        ]);

        Route::group(
            [
                'middleware' => AjaxOnly::class,
                'fallback' => ['route' => 'cmf_forgot_password']
            ],
            function () {

                Route::get('forgot_password.html', [
                    'uses' => __cmf_general_controller_class() . '@getForgotPasswordTpl'
                ]);

                Route::post('forgot_password', [
                    'uses' => __cmf_general_controller_class() . '@sendPasswordReplacingInstructions',
                ]);

                Route::get('replace_password/{access_key}.html', [
                    'uses' => __cmf_general_controller_class() . '@getReplacePasswordTpl'
                ]);

                Route::put('replace_password/{access_key}', [
                    'uses' => __cmf_general_controller_class() . '@replacePassword',
                ]);
            }
        );

        Route::get('forgot_password', [
            'as' => 'cmf_forgot_password',
            'uses' => __cmf_general_controller_class() . '@loadJsApp'
        ]);

        Route::get('replace_password/{access_key}', [
            'as' => 'cmf_replace_password',
            'uses' => __cmf_general_controller_class() . '@getReplacePassword'
        ]);

        Route::group(
            [
                'middleware' => ValidateAdmin::class
            ],
            function () {

                Route::group(
                    [
                        'middleware' => AjaxOnly::class,
                        'fallback' => ['route' => 'cmf_login']
                    ],
                    function () {

                        // UI views
                        Route::get('ui/ui.html', [
                            'as' => 'cmf_main_ui',
                            'uses' => __cmf_general_controller_class() . '@getBasicUiView'
                        ]);

                        Route::get('ui/{view}.html', [
                            'uses' => __cmf_general_controller_class() . '@getUiView'
                        ]);

                        // Admin profile
                        Route::get('page/profile/data', [
                            'as' => 'cmf_profile_data',
                            'uses' => __cmf_general_controller_class() . '@getAdminInfo'
                        ]);

                        Route::get('page/profile.html', [
                            'uses' => __cmf_general_controller_class() . '@getAdminProfile'
                        ]);

                        Route::put('page/profile', [
                            'as' => 'cmf_profile',
                            'uses' => __cmf_general_controller_class() . '@updateAdminProfile'
                        ]);

                        // Custom Pages
                        Route::get('page/about.html', function () {
                            return view('cmf::page.about');
                        });

                        Route::get('page/{page}.html', [
                            'uses' => __cmf_general_controller_class() . '@getPage'
                        ]);

                    }
                );

                // Custom Pages
                Route::get('page/{page}', [
                    'as' => 'cmf_page',
                    'uses' => __cmf_general_controller_class() . '@loadJsApp'
                ]);

                // Switch locales
                Route::get('switch_locale/{locale}', [
                    'uses' => __cmf_general_controller_class() . '@switchLocale'
                ]);

                // Clean cache
                Route::get('cache/clean', [
                    'uses' => __cmf_general_controller_class() . '@cleanCache'
                ]);
            }
        );

        Route::pattern('table_name', '[a-z]+([_a-z0-9]*[a-z0-9])?');
        // Scaffold pages and templates
        Route::group(
            [
                'prefix' => 'resource',
                'middleware' => [
                    LoadModelAndScaffoldConfig::class,
                    ValidateAdmin::class
                ]
            ],
            function () {
                Route::get('{table_name}', [
                    'as' => 'cmf_items_table',
                    'uses' => __cmf_general_controller_class() . '@loadJsApp',
                ]);

                Route::get('{table_name}/create', [
                    'as' => 'cmf_item_add_form',
                    'uses' => __cmf_general_controller_class() . '@loadJsApp',
                ]);

                Route::get('{table_name}/details/{id}', [
                    'as' => 'cmf_item_details',
                    'uses' => __cmf_general_controller_class() . '@loadJsApp',
                ]);

                Route::get('{table_name}/edit/{id}', [
                    'as' => 'cmf_item_edit_form',
                    'uses' => __cmf_general_controller_class() . '@loadJsApp',
                ]);
            }
        );

        // Scaffold API
        Route::group(
            [
                'prefix' => 'api',
                'middleware' => [
                    AjaxOnly::class,
                    LoadModelAndScaffoldConfig::class,
                    ValidateAdmin::class,
                ]
            ],
            function () {

                Route::get('{table_name}/service/templates', [
                    'as' => 'cmf_api_get_templates',
                    'uses' => __cmf_scaffold_api_controller_class() . '@getTemplates',
                    'fallback' => [
                        'route' => 'cmf_items_table',
                        'params' => true
                    ]
                ]);

                Route::get('{table_name}/list', [
                    'as' => 'cmf_api_get_items',
                    'uses' => __cmf_scaffold_api_controller_class() . '@getItemsList',
                    'fallback' => [
                        'route' => 'cmf_items_table',
                        'params' => true
                    ]
                ]);

                Route::get('{table_name}/service/options', [
                    'as' => 'cmf_api_get_options',
                    'uses' => __cmf_scaffold_api_controller_class() . '@getOptions',
                    'fallback' => [
                        'route' => 'cmf_items_table',
                        'params' => true
                    ]
                ]);

                Route::post('{table_name}', [
                    'as' => 'cmf_api_create_item',
                    'uses' => __cmf_scaffold_api_controller_class() . '@addItem',
                    'fallback' => [
                        'route' => 'cmf_item_add_form',
                        'params' => true
                    ]
                ]);

                Route::get('{table_name}/service/defaults', [
                    'as' => 'cmf_api_get_item',
                    'uses' => __cmf_scaffold_api_controller_class() . '@getItemDefaults',
                    'fallback' => [
                        'route' => 'cmf_item_add_form',
                        'params' => true
                    ]
                ]);

                Route::get('{table_name}/{id}', [
                    'as' => 'cmf_api_get_item',
                    'uses' => __cmf_scaffold_api_controller_class() . '@getItem',
                    'fallback' => [
                        'route' => 'cmf_item_edit_form',
                        'params' => true
                    ]
                ]);

                Route::put('{table_name}/{id}', [
                    'as' => 'cmf_api_update_item',
                    'uses' => __cmf_scaffold_api_controller_class() . '@updateItem',
                    'fallback' => [
                        'route' => 'cmf_item_edit_form',
                        'params' => true
                    ]
                ]);

                Route::delete('{table_name}/{id}', [
                    'as' => 'cmf_api_delete_item',
                    'uses' => __cmf_scaffold_api_controller_class() . '@deleteItem',
                    'fallback' => [
                        'route' => 'cmf_items_table',
                        'params' => true
                    ]
                ]);
            }
        );
    }
);