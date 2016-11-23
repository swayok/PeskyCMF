<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
echo "<?php\n";
?>

Route::group(
    [
        'middleware' => [
            PeskyCMF\Http\Middleware\ValidateAdmin::class
        ]
    ],
    function () {

        Route::get('/', [
            'as' => 'cmf_start_page',
            function () {
                return routeToCmfPage('dashboard');
            }
        ]);

        Route::group(
            [
                'middleware' => [
                    \PeskyCMF\Http\Middleware\AjaxOnly::class
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