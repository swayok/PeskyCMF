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
    function () use ($cmfConfig) {

        Route::get('/', [
            'as' => $cmfConfig::getRouteName('cmf_start_page')
            'uses' => 'PagesController@redirectFromStartPage',
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