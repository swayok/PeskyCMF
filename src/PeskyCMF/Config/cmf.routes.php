<?php
/**
 * @var CmfConfig $cmfConfig
 */

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Middleware\AjaxOnly;

Route::group(
    [
        'middleware' => AjaxOnly::class,
        'fallback' => ['route' => 'cmf_login']
    ],
    function () use ($cmfConfig) {
        Route::get('login.html', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@getLoginTpl'
        ]);

        Route::post('login', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@doLogin'
        ]);
    }
);

Route::get('login', [
    'as' => 'cmf_login',
    'uses' => $cmfConfig::cmf_general_controller_class() . '@getLogin'
]);

Route::get('logout', [
    'as' => 'cmf_logout',
    'uses' => $cmfConfig::cmf_general_controller_class() . '@logout'
]);

Route::group(
    [
        'middleware' => AjaxOnly::class,
        'fallback' => ['route' => 'cmf_forgot_password']
    ],
    function () use ($cmfConfig) {

        Route::get('forgot_password.html', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@getForgotPasswordTpl'
        ]);

        Route::post('forgot_password', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@sendPasswordReplacingInstructions',
        ]);

        Route::get('replace_password/{access_key}.html', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@getReplacePasswordTpl'
        ]);

        Route::put('replace_password/{access_key}', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@replacePassword',
        ]);
    }
);

Route::get('forgot_password', [
    'as' => 'cmf_forgot_password',
    'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp'
]);

Route::get('replace_password/{access_key}', [
    'as' => 'cmf_replace_password',
    'uses' => $cmfConfig::cmf_general_controller_class() . '@getReplacePassword'
]);

Route::group(
    [
        'middleware' => $cmfConfig::middleware_for_routes_that_require_authorisation()
    ],
    function () use ($cmfConfig) {

        Route::group(
            [
                'middleware' => AjaxOnly::class,
                'fallback' => ['route' => 'cmf_login']
            ],
            function () use ($cmfConfig) {

                // UI views
                Route::get('ui/ui.html', [
                    'as' => 'cmf_main_ui',
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getBasicUiView'
                ]);

                Route::get('ui/{view}.html', [
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getUiView'
                ]);

                // Admin profile
                Route::get('page/profile/data', [
                    'as' => 'cmf_profile_data',
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getAdminInfo'
                ]);

                Route::get('page/profile.html', [
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getAdminProfile'
                ]);

                Route::put('page/profile', [
                    'as' => 'cmf_profile',
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@updateAdminProfile'
                ]);

                // Custom Pages
                Route::get('page/about.html', [
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getAboutCmfPage'
                ]);

                Route::get('page/{page}.html', [
                    'uses' => $cmfConfig::cmf_general_controller_class() . '@getPage'
                ]);

            }
        );

        // Custom Pages
        Route::get('page/{page}', [
            'as' => 'cmf_page',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp'
        ]);

        // Switch locales
        Route::get('switch_locale/{locale}', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@switchLocale'
        ]);

        // Clean cache
        Route::get('cache/clean', [
            'uses' => $cmfConfig::cmf_general_controller_class() . '@cleanCache'
        ]);
    }
);

Route::pattern('table_name', '[a-z]+([_a-z0-9]*[a-z0-9])?');
// Scaffold pages and templates
Route::group(
    [
        'prefix' => 'resource',
        'middleware' => $cmfConfig::middleware_for_routes_that_require_authorisation()
    ],
    function () use ($cmfConfig) {
        Route::get('{table_name}', [
            'as' => 'cmf_items_table',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp',
        ]);

        Route::get('{table_name}/create', [
            'as' => 'cmf_item_add_form',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp',
        ]);

        Route::get('{table_name}/details/{id}', [
            'as' => 'cmf_item_details',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp',
        ]);

        Route::get('{table_name}/edit/{id}', [
            'as' => 'cmf_item_edit_form',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp',
        ]);

        Route::get('{table_name}/{id}/page/{page}.html', [
            'middleware' => AjaxOnly::class,
            'fallback' => [
                'route' => 'cmf_item_custom_page',
                'params' => true
            ],
            'uses' => $cmfConfig::cmf_general_controller_class() . '@handlerForRouteNotFound',
        ]);

        Route::get('{table_name}/{id}/page/{page}', [
            'as' => 'cmf_item_custom_page',
            'uses' => $cmfConfig::cmf_general_controller_class() . '@loadJsApp',
        ]);
    }
);

// Scaffold API
Route::group(
    [
        'prefix' => 'api',
        'middleware' => array_unique(array_merge(
            [AjaxOnly::class],
            $cmfConfig::middleware_for_routes_that_require_authorisation(),
            $cmfConfig::middleware_for_cmf_scaffold_api_controller()
        ))
    ],
    function () use ($cmfConfig) {

        Route::get('{table_name}/service/templates', [
            'as' => 'cmf_api_get_templates',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@getTemplates',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);

        Route::get('{table_name}/list', [
            'as' => 'cmf_api_get_items',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@getItemsList',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);

        Route::get('{table_name}/service/options', [
            'as' => 'cmf_api_get_options',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@getOptions',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);

        Route::post('{table_name}', [
            'as' => 'cmf_api_create_item',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@addItem',
            'fallback' => [
                'route' => 'cmf_item_add_form',
                'params' => true
            ]
        ]);

        Route::get('{table_name}/service/defaults', [
            'as' => 'cmf_api_get_item',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@getItemDefaults',
            'fallback' => [
                'route' => 'cmf_item_add_form',
                'params' => true
            ]
        ]);

        Route::delete('{table_name}/bulk', [
            'as' => 'cmf_api_delete_bulk',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@deleteBulk',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);

        Route::put('{table_name}/bulk', [
            'as' => 'cmf_api_edit_bulk',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@updateBulk',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);

        Route::get('{table_name}/{id}', [
            'as' => 'cmf_api_get_item',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@getItem',
            'fallback' => [
                'route' => 'cmf_item_edit_form',
                'params' => true
            ]
        ]);

        Route::put('{table_name}/{id}', [
            'as' => 'cmf_api_update_item',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@updateItem',
            'fallback' => [
                'route' => 'cmf_item_edit_form',
                'params' => true
            ]
        ]);

        Route::delete('{table_name}/{id}', [
            'as' => 'cmf_api_delete_item',
            'uses' => $cmfConfig::cmf_scaffold_api_controller_class() . '@deleteItem',
            'fallback' => [
                'route' => 'cmf_items_table',
                'params' => true
            ]
        ]);
    }
);