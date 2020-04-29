<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 * @var string $dbClassesAppSubfolder
 */
if (empty($urlPrefix)) {
    $urlPrefix = strtolower($sectionName);
}
$lowercasedSectionName = snake_case($sectionName);
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
        return '{{ $lowercasedSectionName }}.';
    }

    static public function routes_config_files() {
        return [
            __DIR__ . '/{{ $lowercasedSectionName }}.routes.php'
        ];
    }

    static public function layout_css_includes() {
        return [
            '/packages/{{ $lowercasedSectionName }}/css/{{ $lowercasedSectionName }}.custom.css'
        ];
    }

    static public function layout_js_includes() {
        return [
            '/packages/{{ $lowercasedSectionName }}/js/{{ $lowercasedSectionName }}.custom.js'
        ];
    }

    static public function base_db_model_class() {
        return \App\{{ str_replace('/', '\\', $dbClassesAppSubfolder) }}\BaseTable::class;
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
                'label' => self::transCustom('.page.dashboard.menu_title'),
                'url' => '/page/dashboard',
                'icon' => 'glyphicon glyphicon-dashboard',
            ],
            [
                'label' => self::transCustom('.admins.menu_title'),
                'url' => '/resource/admins',
                'icon' => 'fa fa-group'
            ]
            /*[
                'label' => self::transCustom('.users.menu_title'),
                'url' => '/resource/users',
                'icon' => 'fa fa-group'
            ],*/
            /*[
                'label' => self::transCustom('.menu.section_utils'),
                'icon' => 'glyphicon glyphicon-align-justify',
                'submenu' => [
                    [
                        'label' => self::transCustom('.admins.menu_title'),
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