var mixCmfStyles = function (mix) {

    mix.styles([
        'public/packages/cmf-vendors/bootstrap/switches/css/bootstrap3/bootstrap-switch.css',
        'public/packages/cmf-vendors/bootstrap/datetimepicker/css/bootstrap-datetimepicker.css',
        'public/packages/cmf-vendors/bootstrap/select/css/bootstrap-select.css',
        'public/packages/cmf-vendors/bootstrap/fileinput/css/fileinput.min.css',
        'public/packages/cmf-vendors/bootstrap/awesome-bootstrap-checkbox.css'
    ], 'public/packages/cmf/compiled/css/bootstrap-plugins.min.css');

    mix.styles([
        'public/packages/cmf-vendors/scrollbar/simple-scrollbar.css',
        'public/packages/cmf-vendors/datatables/css/dataTables.bootstrap.css',
        'public/packages/cmf-vendors/datatables/extensions/FixedColumns/css/fixedColumns.bootstrap.min.css',
        'public/packages/cmf-vendors/select2/css/select2.min.css',
        'public/packages/cmf-vendors/toastr/toastr.css',
        'public/packages/cmf-vendors/cropperjs/cropper.css'
    ], 'public/packages/cmf/compiled/css/libs.min.css');

    mix.styles([
        'public/packages/cmf-vendors/db-query-builder/css/query-builder.default.css',
        'public/packages/cmf/css/helpers.css',
        'public/packages/cmf/css/cmf.app.css'
    ], 'public/packages/cmf/compiled/css/cmf.min.css');
};

var mixCmfScripts = function (mix) {

    mix.scripts([
        'public/packages/cmf-vendors/bootstrap/js/bootstrap.min.js',
        'public/packages/cmf-vendors/bootstrap/switches/js/bootstrap-switch.min.js',
        'public/packages/cmf-vendors/bootstrap/select/js/bootstrap-select.min.js',
        'public/packages/cmf-vendors/bootstrap/select/js/i18n/defaults-en_US.min.js',
        'public/packages/cmf-vendors/bootstrap/fileinput/js/fileinput.min.js',
        'public/packages/cmf-vendors/bootstrap/datetimepicker/js/bootstrap-datetimepicker.min.js',
        'public/packages/cmf-vendors/bootstrap/x-editale/js/bootstrap-editable.min.js',
        'public/packages/cmf-vendors/bootstrap/context-menu.min.js'
    ], 'public/packages/cmf/compiled/js/bootstrap-and-plugins.min.js');

    mix.scripts([
        'public/packages/cmf-vendors/datatables/js/jquery.dataTables.min.js',
        'public/packages/cmf-vendors/datatables/js/dataTables.bootstrap.min.js',
        'public/packages/cmf-vendors/datatables/extensions/Select/js/dataTables.select.min.js',
        'public/packages/cmf-vendors/datatables/extensions/FixedColumns/js/dataTables.fixedColumns.min.js'
    ], 'public/packages/cmf/compiled/js/datatables-and-plugins.min.js');

    mix.scripts([
        'public/packages/cmf-vendors/jQuery-extendext/jQuery.extendext.js',
        'public/packages/cmf-vendors/db-query-builder/js/query-builder.js',
        'public/packages/cmf-vendors/db-query-builder/plugins/bt-selectpicker-values/plugin.js',
        'public/packages/cmf-vendors/jquery.plugins.js',
        'public/packages/cmf-vendors/jquery-form/jquery.form.min.js',
        'public/packages/cmf-vendors/jquery.inputmask/jquery.inputmask.bundle.min.js'
    ], 'public/packages/cmf/compiled/js/jquery-plugins.min.js');

    mix.scripts([
        'public/packages/cmf-vendors/scrollbar/simple-scrollbar.js',
        'public/packages/cmf-vendors/modernizr.custom.js',
        'public/packages/cmf-vendors/dotjs/doT.js',
        'public/packages/cmf-vendors/router/page.js',
        'public/packages/cmf-vendors/moment/moment.js',
        'public/packages/cmf-vendors/toastr/toastr.min.js',
        'public/packages/cmf-vendors/ckeditor/ckeditor.js',
        'public/packages/cmf-vendors/ckeditor/adapters/jquery.js',
        'public/packages/cmf-vendors/cropperjs/cropper.min.js',
        'public/packages/cmf-vendors/sortable/Sortable.js',
        'public/packages/cmf-vendors/base64.js'
    ], 'public/packages/cmf/compiled/js/libs.min.js');

    mix.scripts([
        'public/packages/adminlte/js/app.js',
        'public/packages/cmf/js/cmf.config.js',
        'public/packages/cmf/js/debug.dialog.js',
        'public/packages/cmf/js/cmf.helpers.js',
        'public/packages/cmf/js/cmf.utils.js',
        'public/packages/cmf/js/cmf.routing.js',
        'public/packages/cmf/js/cmf.scaffolds.js',
        'public/packages/cmf/js/cmf.app.js'
    ], 'public/packages/cmf/compiled/js/cmf.min.js');
};

module.exports = {
    mixCmfStyles: mixCmfStyles,
    mixCmfScripts: mixCmfScripts,
    mixCmfAssets: function (mix) {
        mixCmfStyles(mix);
        mixCmfScripts(mix);
    }
};