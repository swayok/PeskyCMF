<?php
declare(strict_types=1);
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
echo "<?php\n";
?>

namespace App\{{ $sectionName }}\Http\Controllers;

use PeskyCMF\Http\Controllers\CmfController;

class PagesController extends CmfController {

    public function redirectFromStartPage() {
        return redirectToCmfPage('dashboard');
    }

    public function dashboard() {
        return view('cmf::page.dashboard');
    }

}