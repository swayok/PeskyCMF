const fs = require('fs');
const path = require('path');
const stylesheets = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-stylesheets-bundle.json'), 'utf8'));
const scripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-scripts-bundle.json'), 'utf8'));
const localizationScripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-plugins-localization-scripts.json'), 'utf8'));
const filesAndFoldersToPublish = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-files-to-publish.json'), 'utf8'));

const mixCmfStyles = function (mix) {
    for (let i in stylesheets) {
        mix.styles(stylesheets[i].files, stylesheets[i].output)
            .minify(stylesheets[i].output)
            .sourceMaps();
    }
};

const mixCmfScripts = function (mix) {
    for (let i in scripts) {
        mix.scripts(scripts[i].files, scripts[i].output)
            .minify(scripts[i].output)
            .sourceMaps();
    }
};
/**
 * @param mix
 * @param localeShort - 'en'
 * @param localeWithSuffixUnderscored - 'en_US'
 * @param localeWithSuffixDashed - 'en-US'
 */
const mixCmfPluginsLocalizationScripts = function (mix, localeShort, localeWithSuffixUnderscored, localeWithSuffixDashed) {
    const replacers = [
        {
            regexp: /{{ localeShort }}/g,
            replace: localeShort
        },
        {
            regexp: /{{ localeWithSuffixUnderscored }}/g,
            replace: localeWithSuffixUnderscored
        },
        {
            regexp: /{{ localeWithSuffixDashed }}/g,
            replace: localeWithSuffixDashed
        }
    ];
    // update file paths with locales
    for (let fileIndex in localizationScripts.files) {
        let filePath = localizationScripts.files[fileIndex];
        for (let replacerIndex in replacers) {
            filePath = filePath.replace(replacers[replacerIndex].regexp, replacers[replacerIndex].replace);
        }
        localizationScripts.files[fileIndex] = filePath;
    }
    let outputFile = localizationScripts.output.replace(replacers[2].regexp, replacers[2].replace);
    mix.scripts(localizationScripts.files, outputFile);
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
        mixCmfPluginsLocalizationScripts(mix, 'en', 'en_US', 'en-US');
    }
};