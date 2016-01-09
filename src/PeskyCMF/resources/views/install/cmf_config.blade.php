<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
if (empty($urlPrefix)) {
    $urlPrefix = strtolower($sectionName);
}
$lowersavesSectionName = snake_case($sectionName);
echo "<?php\n";
?>

namespace App\{{ $sectionName }}\Config;

use PeskyCMF\Config\CmfConfig;

class {{ $sectionName }}Config extends CmfConfig {

    /**
     * Url prefix for routes
     * @return string
     */
    static public function url_prefix() {
        return '{{ $urlPrefix }}';
    }

    /**
     * Prefix to load custom views from.
     * For example
     * - if custom views stored in /resources/views/admin - prefix should be "admin."
     * - if you placed views under namespace "admin" - prefix should be "admin:"
     * @return string
     */
    static public function custom_views_prefix() {
        return '{{ $viewsNs }}.';
    }

    static public function routes_config_files() {
        return [
            __DIR__ . '/{{ $lowersavesSectionName }}.routes.php'
        ];
    }

    static public function layout_css_includes() {
        return [
            '/packages/admin/css/{{ $lowersavesSectionName }}.custom.css'
        ];
    }

    static public function layout_js_includes() {
        return [
            '/packages/admin/js/{{ $lowersavesSectionName }}.custom.js'
        ];
    }

    /**
     * The menu structure of the site.
     * @return array
     * Format:
     *    array(
     *        array(
     *              'label' => 'label',
     *              'url' => '/url',
     *              'icon' => 'icon',
     *         ),
     *         array(
     *              'label' => 'label',
     *              'icon' => 'icon',
     *              'submenu' => array(...)
     *         ),
     *    )
     */
    static public function menu() {
        return [
            [
                'label' => '{{ $lowersavesSectionName }}.dashboard.menu_title',
                'url' => '/page/dashboard',
                'icon' => 'glyphicon glyphicon-dashboard',
            ],
            /*[
                'label' => '{{ $lowersavesSectionName }}.users.menu_title',
                'url' => '/resource/users',
                'icon' => 'fa fa-group'
            ],*/
            /*[
                'label' => '{{ $lowersavesSectionName }}.menu.section_utils',
                'icon' => 'glyphicon glyphicon-align-justify',
                'submenu' => [
                    [
                        'label' => '{{ $lowersavesSectionName }}.admins.menu_title',
                        'url' => '/resource/admins',
                        'icon' => 'glyphicon glyphicon-user'
                    ],
                ]
            ]*/
        ];
    }

    static public function default_locale() {
        return 'en';
    }

    static public function locales() {
        return [
            'en'
        ];
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    /*static public function rows_per_page() {
        return 25;
    }*/

}