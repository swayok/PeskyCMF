let mix = require('laravel-mix');
let path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

let cmfMixer = require(path.join(__dirname, '/config/cmf-assets-mixer'));

cmfMixer.mixCmfAssets(mix);

//todo: add cmfMixer.mixCmfPluginsLocalizationScripts() for all available locales
