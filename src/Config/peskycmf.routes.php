<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Middleware\AjaxOnly;

/**
 * @var CmfConfig $cmfConfig
 */
$routeNamePrefix = $cmfConfig->routesNamesPrefix();
$apiControllerClass = $cmfConfig->cmfScaffoldApiControllerClass();
$generalControllerClass = $cmfConfig->cmfGeneralControllerClass();

Route::group(
    [
        'middleware' => AjaxOnly::class,
    ],
    function () use ($routeNamePrefix, $generalControllerClass) {
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getLoginTpl()
         */
        Route::get('login.html', [
            'uses' => $generalControllerClass . '@getLoginTpl',
            'log' => 'cmf.login',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_login'],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::doLogin()
         */
        Route::post('login', [
            'uses' => $generalControllerClass . '@doLogin',
            'log' => 'cmf.login',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_login'],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getRegistrationTpl()
         */
        Route::get('register.html', [
            'uses' => $generalControllerClass . '@getRegistrationTpl',
            'log' => 'cmf.registration',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_register'],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::doRegister()
         */
        Route::post('register', [
            'uses' => $generalControllerClass . '@doRegister',
            'log' => 'cmf.registration',
            'fallback' => ['route' => $routeNamePrefix . 'cmf_register'],
        ]);
    }
);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
 */
Route::get('login', [
    'as' => $routeNamePrefix . 'cmf_login',
    'uses' => $generalControllerClass . '@loadJsApp',
]);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
 */
Route::get('register', [
    'as' => $routeNamePrefix . 'cmf_register',
    'uses' => $generalControllerClass . '@loadJsApp',
]);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::logout()
 */
Route::get('logout', [
    'as' => $routeNamePrefix . 'cmf_logout',
    'uses' => $generalControllerClass . '@logout',
    'log' => 'cmf.logout',
]);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::ping()
 */
Route::post('/ping', [
    'as' => $routeNamePrefix . 'ping',
    'uses' => $generalControllerClass . '@ping',
    'log' => false,
]);

Route::group(
    [
        'middleware' => AjaxOnly::class,
        'fallback' => ['route' => $routeNamePrefix . 'cmf_forgot_password'],
    ],
    function () use ($generalControllerClass) {
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getForgotPasswordTpl()
         */
        Route::get('forgot_password.html', [
            'uses' => $generalControllerClass . '@getForgotPasswordTpl',
            'log' => 'cmf.forgot_password',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::sendPasswordReplacingInstructions()
         */
        Route::post('forgot_password', [
            'uses' => $generalControllerClass . '@sendPasswordReplacingInstructions',
            'log' => 'cmf.forgot_password',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getReplacePasswordTpl()
         */
        Route::get('replace_password/{access_key}.html', [
            'uses' => $generalControllerClass . '@getReplacePasswordTpl',
            'log' => 'cmf.replace_password',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::replacePassword()
         */
        Route::put('replace_password/{access_key}', [
            'uses' => $generalControllerClass . '@replacePassword',
            'log' => 'cmf.replace_password',
        ]);
    }
);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
 */
Route::get('forgot_password', [
    'as' => $routeNamePrefix . 'cmf_forgot_password',
    'uses' => $generalControllerClass . '@loadJsApp',
]);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
 */
Route::get('replace_password/{access_key}', [
    'as' => $routeNamePrefix . 'cmf_replace_password',
    'uses' => $generalControllerClass . '@loadJsApp',
]);

/**
 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getCachedUiTemplatesJs()
 */
Route::get('ui/templates.js', [
    'as' => $routeNamePrefix . 'cmf_cached_templates_js',
    'uses' => $generalControllerClass . '@getCachedUiTemplatesJs',
]);

Route::group(
    [
        'middleware' => $cmfConfig->authMiddleware(),
    ],
    function () use ($generalControllerClass, $routeNamePrefix) {
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::ckeditorUploadImage()
         */
        Route::post('ckeditor/upload/image', [
            'as' => $routeNamePrefix . 'cmf_ckeditor_upload_image',
            'uses' => $generalControllerClass . '@ckeditorUploadImage',
        ]);

        Route::group(
            [
                'middleware' => AjaxOnly::class,
                'fallback' => [
                    'route' => $routeNamePrefix . 'cmf_start_page',
                    'use_params' => false,
                ],
            ],
            function () use ($generalControllerClass, $routeNamePrefix) {
                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loginAsOtherUser()
                 */
                Route::get('service/login/as/{id}', [
                    'as' => $routeNamePrefix . 'cmf_login_as_other_admin',
                    'uses' => $generalControllerClass . '@loginAsOtherUser',
                    'log' => 'cmf.service_login_as',
                ]);

                // UI views
                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getBasicUiView()
                 */
                Route::get('ui/ui.html', [
                    'as' => $routeNamePrefix . 'cmf_main_ui',
                    'uses' => $generalControllerClass . '@getBasicUiView',
                ]);

                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getCustomUiView()
                 */
                Route::get('ui/{view}.html', [
                    'as' => $routeNamePrefix . 'cmf_custom_ui_view',
                    'uses' => $generalControllerClass . '@getCustomUiView',
                ]);

                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getMenuCounters()
                 */
                Route::get('page/menu/counters', [
                    'as' => $routeNamePrefix . 'cmf_menu_counters_data',
                    'uses' => $generalControllerClass . '@getMenuCounters',
                    'log' => false,
                ]);

                // Admin profile
                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getUserProfileData()
                 */
                Route::get('page/profile/data', [
                    'as' => $routeNamePrefix . 'cmf_profile_data',
                    'uses' => $generalControllerClass . '@getUserProfileData',
                ]);

                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::renderUserProfileView()
                 */
                Route::get('page/profile.html', [
                    'uses' => $generalControllerClass . '@renderUserProfileView',
                    'log' => 'cmf.profile',
                ]);

                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::updateUserProfile()
                 */
                Route::put('page/profile', [
                    'as' => $routeNamePrefix . 'cmf_profile',
                    'uses' => $generalControllerClass . '@updateUserProfile',
                    'log' => 'cmf.profile',
                ]);

                // Custom Pages
                /**
                 * @see \PeskyCMF\Http\Controllers\CmfGeneralController::getPage()
                 */
                Route::get('page/{page}.html', $generalControllerClass . '@getPage');
            }
        );

        // Custom Pages
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('page/{page}', $generalControllerClass . '@loadJsApp')
            ->name($routeNamePrefix . 'cmf_page')
            ->where('page', '^.*(?!\.html)$');

        // Switch locales
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::switchLocale()
         */
        Route::get('switch_locale/{locale}', $generalControllerClass . '@switchLocale');

        // Clean cache
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::cleanCache()
         */
        Route::get('cache/clean', $generalControllerClass . '@cleanCache');

        // Api docs
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::downloadApiRequestsCollectionForPostman()
         */
        Route::get(
            '/utils/api_docs/requests_collection_for_postman.json',
            $generalControllerClass . '@downloadApiRequestsCollectionForPostman'
        )
            ->name($routeNamePrefix . 'cmf_api_docs_download_postman_collection');
    }
);

Route::pattern('resource', '[a-z]+([_a-z0-9]*[a-z0-9])?');
// Scaffold pages and templates
Route::group(
    [
        'prefix' => 'resource',
        'middleware' => $cmfConfig->authMiddleware(),
    ],
    function () use ($apiControllerClass, $generalControllerClass, $routeNamePrefix) {
        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}', [
            'as' => $routeNamePrefix . 'cmf_items_table',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/create', [
            'as' => $routeNamePrefix . 'cmf_item_add_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/details/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_details',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/edit/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_edit_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/clone/{id}', [
            'as' => $routeNamePrefix . 'cmf_item_clone_form',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getCustomPageForItem()
         */
        Route::get('{resource}/{id}/page/{page}.html', [
            'middleware' => AjaxOnly::class,
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_custom_page',
                'use_params' => true,
            ],
            'uses' => $apiControllerClass . '@getCustomPageForItem',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/{id}/page/{page}', [
            'as' => $routeNamePrefix . 'cmf_item_custom_page',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getCustomPage()
         */
        Route::get('{resource}/page/{page}.html', [
            'middleware' => AjaxOnly::class,
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_resource_custom_page',
                'use_params' => true,
            ],
            'uses' => $apiControllerClass . '@getCustomPage',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfGeneralController::loadJsApp()
         */
        Route::get('{resource}/page/{page}', [
            'as' => $routeNamePrefix . 'cmf_resource_custom_page',
            'uses' => $generalControllerClass . '@loadJsApp',
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::performCustomAction()
         */
        Route::any('{resource}/action/{action}', [
            'as' => $routeNamePrefix . 'cmf_api_resource_custom_action',
            'uses' => $apiControllerClass . '@performCustomAction',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::performCustomActionForItem()
         */
        Route::any('{resource}/{id}/action/{action}', [
            'as' => $routeNamePrefix . 'cmf_api_item_custom_action',
            'uses' => $apiControllerClass . '@performCustomActionForItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);
    }
);

// Scaffold API
Route::group(
    [
        'prefix' => 'api',
        'middleware' => array_unique(
            array_merge(
                [AjaxOnly::class],
                $cmfConfig->authMiddleware(),
                $cmfConfig->middlewareForCmfScaffoldApiController()
            )
        ),
    ],
    function () use ($apiControllerClass, $routeNamePrefix) {
        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getTemplates()
         */
        Route::get('{resource}/service/templates', [
            'as' => $routeNamePrefix . 'cmf_api_get_templates',
            'uses' => $apiControllerClass . '@getTemplates',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getItemsList()
         */
        Route::get('{resource}/list', [
            'as' => $routeNamePrefix . 'cmf_api_get_items',
            'uses' => $apiControllerClass . '@getItemsList',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getOptionsAsJson()
         */
        Route::get('{resource}/service/options/{input_name}.json', [
            'as' => $routeNamePrefix . 'cmf_api_get_options_as_json',
            'uses' => $apiControllerClass . '@getOptionsAsJson',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getOptions()
         */
        Route::get('{resource}/service/options', [
            'as' => $routeNamePrefix . 'cmf_api_get_options',
            'uses' => $apiControllerClass . '@getOptions',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getItemDefaults()
         */
        Route::get('{resource}/service/defaults', [
            'as' => $routeNamePrefix . 'cmf_api_get_defaults',
            'uses' => $apiControllerClass . '@getItemDefaults',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_add_form',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getCustomData()
         */
        Route::get('{resource}/service/custom_data/{data_id}', [
            'as' => $routeNamePrefix . 'cmf_api_get_custom_data',
            'uses' => $apiControllerClass . '@getCustomData',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::deleteBulk()
         */
        Route::delete('{resource}/bulk', [
            'as' => $routeNamePrefix . 'cmf_api_delete_bulk',
            'uses' => $apiControllerClass . '@deleteBulk',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::updateBulk()
         */
        Route::put('{resource}/bulk', [
            'as' => $routeNamePrefix . 'cmf_api_edit_bulk',
            'uses' => $apiControllerClass . '@updateBulk',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::getItem()
         */
        Route::get('{resource}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_get_item',
            'uses' => $apiControllerClass . '@getItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_edit_form',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::updateItem()
         */
        Route::put('{resource}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_update_item',
            'uses' => $apiControllerClass . '@updateItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_edit_form',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::uploadTempFileForInput()
         */
        Route::post('{resource}/upload/temp/for/{input}', [
            'as' => $routeNamePrefix . 'cmf_upload_temp_file_for_input',
            'uses' => $apiControllerClass . '@uploadTempFileForInput',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => false,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::deleteTempFileForInput()
         */
        Route::post('{resource}/delete/temp/for/{input}', [
            'as' => $routeNamePrefix . 'cmf_delete_temp_file_for_input',
            'uses' => $apiControllerClass . '@deleteTempFileForInput',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => false,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::changeItemPosition()
         */
        Route::put('{resource}/move/{id}/{before_or_after}/{other_id}/order/{sort_column}/{sort_direction}', [
            'as' => $routeNamePrefix . 'cmf_api_change_item_position',
            'uses' => $apiControllerClass . '@changeItemPosition',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ])
            ->where([
                'before_or_after' => '^(before|after|BEFORE|AFTER)$',
                'sort_direction' => '^(asc|desc|ASC|DESC)$',
                'other_id' => '^\d+$',
                'sort_column' => '^[a-zA-Z_0-9]+$',
            ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::deleteItem()
         */
        Route::delete('{resource}/{id}', [
            'as' => $routeNamePrefix . 'cmf_api_delete_item',
            'uses' => $apiControllerClass . '@deleteItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_items_table',
                'use_params' => true,
            ],
        ]);

        /**
         * @see \PeskyCMF\Http\Controllers\CmfScaffoldApiController::addItem()
         */
        Route::post('{resource}', [
            'as' => $routeNamePrefix . 'cmf_api_create_item',
            'uses' => $apiControllerClass . '@addItem',
            'fallback' => [
                'route' => $routeNamePrefix . 'cmf_item_add_form',
                'use_params' => true,
            ],
        ]);
    }
);
