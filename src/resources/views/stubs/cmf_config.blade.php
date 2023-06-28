<?php

declare(strict_types=1);

/**
 * @var string $sectionName
 * @var string $cmfConfigClassName
 * @var string $configsFileName
 */
echo "<?php\n";
?>

declare(strict_types=1);

namespace App\{{ $sectionName }};

use PeskyCMF\Config\CmfConfig;
use PeskyCMF\CmfUrl;

class {{ $cmfConfigClassName }} extends CmfConfig
{
    /**
     * File name for this site section in 'configs' folder of project's root directory (without '.php' extension)
     * Example: 'admin' for config/admin.php;
     */
    protected function configsFileName(): string
    {
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
    public function menu(): array
    {
        return array_merge(
            [
                [
                    'label' => $this->transCustom('page.dashboard.menu_title'),
                    'url' => CmfUrl::toPage('dashboard'),
                    'icon' => 'glyphicon glyphicon-dashboard',
                ],
                /*[
                    'label' => self::transCustom('users.menu_title'),
                    'url' => CmfUrl::toItemsTable('users'),
                    'icon' => 'fa fa-group'
                ],*/
                /*[
                    'label' => self::transCustom('menu.section_utils'),
                    'icon' => 'glyphicon glyphicon-align-justify',
                    'submenu' => [
                        [
                            'label' => self::transCustom('admins.menu_title'),
                            'url' => CmfUrl::toItemsTable('admins'),
                            'icon' => 'glyphicon glyphicon-user'
                        ],
                    ]
                ]*/
            ],
            $this->getMenuItems()
        );
    }

    /**
     * How much rows to display in data tables
     * @return int
     */
    /*public static function rowsPerPage(): int {
        return 25;
    }*/
}
