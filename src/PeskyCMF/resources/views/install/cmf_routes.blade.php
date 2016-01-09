<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
echo "<?php\n";
?>

Route::group(
    [
        'prefix' => \App\{{ $sectionName }}\Config\{{ $sectionName }}Config::url_prefix(),
        'namespace' => 'App\{{ $sectionName }}\Http\Controllers',
        'middleware' => [
            PeskyCMF\Http\Middleware\ValidateAdmin::class
        ]
    ],
    function () {

        Route::get('/', [
            'as' => 'cmf_start_page',
            function () {
                return Redirect::route('cmf_page', ['dashboard']);
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