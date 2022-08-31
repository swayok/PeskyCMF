<?php

require_once __DIR__ . '/../../../autoload.php';

use Illuminate\Support\Arr;
use Swayok\Utils\Folder;

$folders = [
    'node_modules/jQuery-QueryBuilder/dist/i18n/' => '%^query-builder.([a-zA-Z]{2}).js$%',
    'node_modules/bootstrap-fileinput/js/locales/' => '%^([a-zA-Z]{2}).js$%',
    'node_modules/moment/locale/' => '%^([a-zA-Z]{2})(?:-([a-zA-Z]{2,}))?.js$%',
    'node_modules/bootstrap-select/dist/js/i18n/' => '%^defaults-([a-zA-Z]{2})_([a-zA-Z]{2,}).js$%',
    'node_modules/ajax-bootstrap-select/dist/js/locale/' => '%^ajax-bootstrap-select.([a-zA-Z]{2})-([a-zA-Z]{2,}).js$%',
    'node_modules/select2/dist/js/i18n/' => '%^([a-zA-Z]{2}).js$%',
];
$locales = [];
foreach ($folders as $folderPath => $fileNameRegex) {
    $absolutePath = __DIR__ . '/' . $folderPath;
    $folder = Folder::load($absolutePath);
    if (!$folder->exists()) {
        echo 'Folder ' . str_replace('/', DIRECTORY_SEPARATOR, $absolutePath) . ' not found';
        exit(404);
    }
    [, $folderFiles] = $folder->read(false);
    foreach ($folderFiles as $fileName) {
        if (preg_match($fileNameRegex, $fileName, $matches)) {
            $locale = strtolower($matches[1]);
            if (!isset($locales[$locale])) {
                $locales[$locale] = [
                    'locale' => $locale,
                    'versions' => [

                    ]
                ];
            }
            if (empty($matches[2])) {
                $locales[$locale]['versions'][$locale][$folderPath] = $fileName;
                $locales[$locale]['versions'][$locale . '_' . strtoupper($locale)][$folderPath] = $fileName;
            } else {
                $suffix = strtolower($matches[2]);
                $locales[$locale]['versions'][$locale . '_' . strtoupper($suffix)][$folderPath] = $fileName;
            }
        }
    }
}
// make configs
$cmfMixerConfigs = [];
$outputDir = 'js/locale/';
$defaultSuffixes = [
    'en' => 'US',
    'pt' => 'BR',
    'zh' => 'CN'
];

foreach ($locales as $locale => $details) {
    $localeWithDefaultSuffix = $locale . '_' . strtoupper(isset($defaultSuffixes[$locale]) ? $defaultSuffixes[$locale] : $locale);
    $baseFiles = array_merge(
        Arr::get($details['versions'], $locale, []),
        Arr::get($details['versions'], $localeWithDefaultSuffix, [])
    );
    // fix situations like ja_JP where only 1 suffix exists while there are not enough files in $baseFiles
    $differentLocales = array_values(array_diff(
        array_keys($details['versions']),
        [
            $locale,
            $locale . '_' . strtoupper($locale),
            $localeWithDefaultSuffix
        ]
    ));
    if (count($differentLocales) === 1 && count($baseFiles) < 4) {
        /** @noinspection SlowArrayOperationsInLoopInspection */
        $baseFiles = array_merge($details['versions'][$differentLocales[0]], $baseFiles);
    }
    foreach ($details['versions'] as $localeVersion => $localeVersionFiles) {
        $localeFiles = array_merge($baseFiles, $localeVersionFiles);
        foreach ($localeFiles as $path => &$fileName) {
            $fileName = $path . $fileName;
        }
        unset($fileName);
        $cmfMixerConfigs[$localeVersion] = [
            'output' => $outputDir . $localeVersion . '.js',
            'files' => array_unique(array_values($localeFiles))
        ];
    }

}
ksort($cmfMixerConfigs);
echo json_encode($cmfMixerConfigs, JSON_UNESCAPED_SLASHES);
