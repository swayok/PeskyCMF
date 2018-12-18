const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');
const stylesheets = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-stylesheets-bundle.json'), 'utf8'));
const scripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-scripts-bundle.json'), 'utf8'));
const localizationScripts = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-plugins-localization-scripts.json'), 'utf8'));
const filesAndFoldersToPublish = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-files-to-publish.json'), 'utf8'));
const defaultRelativeDistPath = 'dist/';
var customRelativeDistPath = null;

const addDistPath = function (filePath) {
    return path.join(customRelativeDistPath || defaultRelativeDistPath, mix.inProduction() ? 'min/' : 'packed/', filePath)
};

const mixCmfStyles = function () {
    for (let i in stylesheets) {
        mix.styles(stylesheets[i].files, addDistPath(stylesheets[i].output));
    }
};

const mixCmfScripts = function () {
    for (let i in scripts) {
        mix.scripts(scripts[i].files, addDistPath(scripts[i].output));
    }
};


const mixCmfPluginsLocalizationScripts = function () {
    for (let i in localizationScripts) {
        mix.scripts(localizationScripts[i].files, addDistPath(localizationScripts[i].output));
    }
};

const publishFiles = function () {
    for (let folder in filesAndFoldersToPublish.folders) {
        mix.copyDirectory(folder, addDistPath(filesAndFoldersToPublish.folders[folder]));
        console.log('Copying ' + folder + ' folder contents to ' + addDistPath(filesAndFoldersToPublish.folders[folder]));
    }
    for (let file in filesAndFoldersToPublish.files) {
        mix.copy(file, addDistPath(filesAndFoldersToPublish.files[file]));
        console.log('Copying ' + file + ' to ' + addDistPath(filesAndFoldersToPublish.files[file]));
    }
};

const deleteFolderRecursive = function (path) {
    if (!path) {
        throw "Empty path";
    }
    if (fs.existsSync(path)) {
        fs.readdirSync(path).forEach(function (file, index) {
            var curPath = path + "/" + file;
            if (fs.lstatSync(curPath).isDirectory()) { // recurse
                deleteFolderRecursive(curPath);
            } else { // delete file
                fs.unlinkSync(curPath);
            }
        });
        fs.rmdirSync(path);
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
    mixCmfAssets: function (distFolderRelativePath) {
        if (distFolderRelativePath) {
            customRelativeDistPath = distFolderRelativePath;
        }
        deleteFolderRecursive(path.join(__dirname, '/../' + distFolderRelativePath));
        publishFiles();
        mixCmfStyles();
        mixCmfScripts();
        mixCmfPluginsLocalizationScripts();
        customRelativeDistPath = null;
    }
};