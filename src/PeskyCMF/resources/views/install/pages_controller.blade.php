<?php
/**
 * @var string $sectionName
 * @var string $urlPrefix
 */
echo "<?php\n";
?>

namespace App\{{ $sectionName }}\Http\Controllers;

use App\Http\Controllers\Controller;

class PagesController extends Controller {

    public function dashboard() {
        return view('cmf::page.dashboard');
    }
}