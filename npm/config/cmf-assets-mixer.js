const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');
const assets = JSON.parse(fs.readFileSync(path.join(__dirname, 'cmf-assets.json'), 'utf8'));
const stylesheets = assets.stylesheets;
const scripts = assets.scripts;
const localizationScripts = assets.localizations;
const filesAndFoldersToPublish = assets.publishes;
const defaultRelativeDistPath = 'dist/';
var customRelativeDistPath = null;

const addDistPath = function (filePath) {
    return path.join(customRelativeDistPath || defaultRelativeDistPath, mix.inProduction() ? 'min/' : 'packed/', filePath);
};

const addDistSrcPath = function (filePath) {
    return path.join(customRelativeDistPath || defaultRelativeDistPath, 'src/', filePath);
};

const addUnpackedPath = function (filePath) {
    return path.join(customRelativeDistPath || defaultRelativeDistPath, 'raw/', filePath);
};

const mixCmfStyles = function () {
    for (let i in stylesheets) {
        mix.styles(stylesheets[i].files, addDistPath(stylesheets[i].output));
        for (let s in stylesheets[i].files) {
            mix.copy(stylesheets[i].files[s], addDistSrcPath(stylesheets[i].files[s]));
        }
    }
};

const mixCmfScripts = function () {
    for (let i in scripts) {
        mix.scripts(scripts[i].files, addDistPath(scripts[i].output));
        for (let s in scripts[i].files) {
            mix.copy(scripts[i].files[s], addDistSrcPath(scripts[i].files[s]));
        }
    }
};


const mixCmfPluginsLocalizationScripts = function () {
    for (let i in localizationScripts) {
        mix.scripts(localizationScripts[i].files, addDistPath(localizationScripts[i].output));
        for (let s in localizationScripts[i].files) {
            mix.copy(localizationScripts[i].files[s], addDistSrcPath(localizationScripts[i].files[s]));
        }
    }
};

const publishFiles = function () {
    for (let folder in filesAndFoldersToPublish.folders) {
        console.log('Copying ' + folder + ' folder contents to ' + addDistPath(filesAndFoldersToPublish.folders[folder]));
        mix.copyDirectory(folder, addUnpackedPath(filesAndFoldersToPublish.folders[folder]));
    }
    for (let file in filesAndFoldersToPublish.files) {
        if (Array.isArray(filesAndFoldersToPublish.files[file])) {
            for (let idx in filesAndFoldersToPublish.files[file]) {
                console.log('Copying ' + file + ' to ' + addDistPath(filesAndFoldersToPublish.files[file][idx]));
                mix.copy(file, addUnpackedPath(filesAndFoldersToPublish.files[file][idx]));
            }
        } else {
            console.log('Copying ' + file + ' to ' + addDistPath(filesAndFoldersToPublish.files[file]));
            mix.copy(file, addUnpackedPath(filesAndFoldersToPublish.files[file]));
        }
    }
};

const deleteFolderRecursive = function (dirPath) {
    if (!dirPath) {
        throw "Empty path";
    }
    console.log("Removing folder: " + dirPath);
    if (fs.existsSync(dirPath)) {
        fs.readdirSync(dirPath).forEach(function (file, index) {
            var curPath = path.join(dirPath, file);
            if (fs.lstatSync(curPath).isDirectory()) { // recurse
                deleteFolderRecursive(curPath);
            } else { // delete file
                fs.unlinkSync(curPath);
            }
        });
        fs.rmdirSync(dirPath);
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
        mix.disableNotifications();
        if (distFolderRelativePath) {
            customRelativeDistPath = distFolderRelativePath;
        }
        deleteFolderRecursive(path.join(__dirname, '/../', addDistPath('')));
        deleteFolderRecursive(path.join(__dirname, '/../', addDistSrcPath('')));
        deleteFolderRecursive(path.join(__dirname, '/../', addUnpackedPath('')));
        publishFiles();
        mixCmfStyles();
        mixCmfScripts();
        mixCmfPluginsLocalizationScripts();
        customRelativeDistPath = null;
    }
};