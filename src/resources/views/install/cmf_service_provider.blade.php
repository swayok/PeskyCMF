<?php
/**
 * @var string $sectionName
 */
echo "<?php\n";
?>

namespace App\{{ $sectionName }};

use App\{{ $sectionName }}\Config\{{ $sectionName }}Config;
use PeskyCMF\PeskyCmfServiceProvider;

class {{ $sectionName }}ServiceProvider extends PeskyCmfServiceProvider {

    protected $cmfConfigsClass = {{ $sectionName }}Config::class;
    /** @var {{ $sectionName }}Config */
    protected $cmfConfig = null;

    /**
     * Custom configurations
     */
    protected function configure() {
        parent::configure();
    }

}
