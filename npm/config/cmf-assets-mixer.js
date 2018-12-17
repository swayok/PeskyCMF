const fs = require('fs');
const path = require('path');
const stylesheets = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-stylesheets-bundle.json'), 'utf8'));
const scripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-scripts-bundle.json'), 'utf8'));
const localizationScripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-plugins-localization-scripts.json'), 'utf8'));
const filesAndFoldersToPublish = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-files-to-publish.json'), 'utf8'));

const mixCmfStyles = function (mix) {
    for (let i in stylesheets) {
        mix.styles(stylesheets[i].files, stylesheets[i].output)
            .minify(stylesheets[i].output);
    }
};

const mixCmfScripts = function (mix) {
    for (let i in scripts) {
        mix.scripts(scripts[i].files, scripts[i].output)
            .minify(scripts[i].output);
    }
};


const mixCmfPluginsLocalizationScripts = function (mix) {
    for (let i in localizationScripts) {
        mix.scripts(localizationScripts[i].files, localizationScripts[i].output)
            .minify(localizationScripts[i].output);
    }
};

const publishFiles = function (mix) {
    for (let folder in filesAndFoldersToPublish.folders) {
        mix.copyDirectory(folder, filesAndFoldersToPublish.folders[folder]);
        console.log('Copied ' + folder + ' folder contents to ' + filesAndFoldersToPublish.folders[folder]);
    }
    for (let file in filesAndFoldersToPublish.files) {
        mix.copy(file, filesAndFoldersToPublish.files[file]);
        console.log('Copied ' + file + ' to ' + filesAndFoldersToPublish.files[file]);
    }
};

module.exports = {
    stylesheets: stylesheets,
    scripts: scripts,
    localizationScripts: localizationScripts,
    filesAndFoldersToPublish: filesAndFoldersToPublish,
    mixCmfStyles: mixCmfStyles,
    mixCmfScripts: mixCmfScripts,
    mixCmfPluginsLocalizationScripts: mixCmfPluginsLocalizationScripts,
    publishFiles: publishFiles,
    mixCmfAssets: function (mix) {
        publishFiles(mix);
        mixCmfStyles(mix);
        mixCmfScripts(mix);
        //mixCmfPluginsLocalizationScripts(mix);
        mix.webpackConfig({
            devtool: 'source-map'
        }).sourceMaps(true, 'source-map');
    }
};