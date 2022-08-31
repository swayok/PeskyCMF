<?php
declare(strict_types=1);
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
        'middleware' => $cmfConfig::auth_middleware()
    ],
    function () use ($cmfConfig) {

        Route::get('/', [
            'as' => $cmfConfig::getRouteName('cmf_start_page'),
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