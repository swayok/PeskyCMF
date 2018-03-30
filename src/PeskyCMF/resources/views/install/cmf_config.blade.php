<?php
/**
 * @var string $sectionName
 * @var string $cmfCongigClassName
 * @var string $configsFileName
 */
echo "<?php\n";
?>

namespace App\{{ $sectionName }};

use PeskyCMF\Config\CmfConfig;

class {{ $cmfCongigClassName }} extends CmfConfig {

    /**
     * File name for this site section in 'configs' folder of project's root directory (without '.php' extension)
     * Example: 'admin' for config/admin.php;
     */
    static protected function configsFileName() {
        return '{{ $configsFileName }}';
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
        return array_merge(
            [
                [
                    'label' => self::transCustom('.page.dashboard.menu_title'),
                    'url' => routeToCmfPage('dashboard'),
                    'icon' => 'glyphicon glyphicon-dashboard',
                ],
                /*[
                    'label' => self::transCustom('.users.menu_title'),
                    'url' => routeToCmfItemsTable('users'),
                    'icon' => 'fa fa-group'
                ],*/
                /*[
                    'label' => self::transCustom('.menu.section_utils'),
                    'icon' => 'glyphicon glyphicon-align-justify',
                    'submenu' => [
                        [
                            'label' => self::transCustom('.admins.menu_title'),
                            'url' => routeToCmfItemsTable('admins'),
                            'icon' => 'glyphicon glyphicon-user'
                        ],
                    ]
                ]*/
            ],
            static::getMenuItems()
        );
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    /*static public function rows_per_page() {
        return 25;
    }*/

}