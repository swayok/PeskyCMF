<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
echo "<?php\n";
?>
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\Middleware\AjaxOnly;

/**
 * @var CmfConfig $cmfConfig
 */

Route::group(
    [
        'middleware' => $cmfConfig::middleware_for_routes_that_require_authentication()
    ],
    function () {

        Route::get('/', [
            'as' => 'cmf_start_page',
            function () {
                return redirectToCmfPage('dashboard');
            }
        ]);

        Route::group(
            [
                'middleware' => [
                    AjaxOnly::class
                ]
            ],
            function () {

                Route::get('/page/dashboard.html', [
                    'uses' => 'PagesController@dashboard'
                ]);
            }

        );
    }
);