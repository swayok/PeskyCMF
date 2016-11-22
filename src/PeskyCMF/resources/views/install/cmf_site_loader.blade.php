<?php
/**
 * @var string $sectionName
 */
echo "<?php\n";
?>

namespace App\SiteLoaders;

use App\{{ $sectionName }}\{{ $sectionName }}Config;
use PeskyCMF\PeskyCmfSiteLoader;

class {{ $sectionName }}SiteLoader extends PeskyCmfSiteLoader {

    static protected $cmfConfigsClass = {{ $sectionName }}Config::class;
    /** @var {{ $sectionName }}Config */
    static protected $cmfConfig;
}