/*!
 * FileInput Galician Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['gl'] = {
        fileSingle: 'arquivo',
        filePlural: 'arquivos',
        browseLabel: 'Examinar &hellip;',
        removeLabel: 'Quitar',
        removeTitle: 'Quitar aquivos seleccionados',
        cancelLabel: 'Cancelar',
        cancelTitle: 'Abortar a subida en curso',
        uploadLabel: 'Subir arquivo',
        uploadTitle: 'Subir arquivos seleccionados',
        msgNo: 'Non',
        msgNoFilesSelected: 'Non hay arquivos seleccionados',
        msgCancelled: 'Cancelado',
        msgPlaceholder: 'Seleccinar {files}...',
        msgZoomModalHeading: 'Vista previa detallada',
        msgFileRequired: 'Debes seleccionar un arquivo para subir.',
        msgSizeTooSmall: 'O arquivo "{name}" (<b>{size} KB</b>) é demasiado pequeno e debe ser maior de <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'O arquivo "{name}" (<b>{size} KB</b>) excede o tamaño máximo permitido de <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Debe seleccionar ao menos <b>{n}</b> {files} a cargar.',
        msgFilesTooMany: 'O número de arquivos seleccionados a cargar <b>({n})</b> excede do límite máximo permitido de <b>{m}</b>.',
        msgFileNotFound: 'Arquivo "{name}" non encontrado.',
        msgFileSecured: 'Non é posible acceder ao arquivo "{name}" porque estará sendo usado por outra aplicación ou non teñamos permisos de lectura.',
        msgFileNotReadable: 'Non é posible acceder ao arquivo "{name}".',
        msgFilePreviewAborted: 'Previsualización do arquivo "{name}" cancelada.',
        msgFilePreviewError: 'Ocurriu un erro mentras se lía o arquivo "{name}".',
        msgInvalidFileName: 'Caracteres non válidos ou non soportados no nome do arquivo "{name}".',
        msgInvalidFileType: 'Tipo de arquivo non válido para "{name}". Só se permiten arquivos do tipo "{types}".',
        msgInvalidFileExtension: 'Extensión de arquivo non válida para "{name}". Só se permiten arquivos "{extensions}".',
        msgFileTypes: {
            'image': 'imaxe',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'audio',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'A carga de arquivos cancelouse',
        msgUploadThreshold: 'Procesando...',
        msgUploadBegin: 'Inicializando...',
        msgUploadEnd: 'Feito',
        msgUploadEmpty: 'Non existen datos válidos para o envío.',
        msgUploadError: 'Erro',
        msgValidationError: 'Erro de validación',
        msgLoading: 'Subindo arquivo {index} de {files} &hellip;',
        msgProgress: 'Subindo arquivo {index} de {files} - {name} - {percent}% completado.',
        msgSelected: '{n} {files} seleccionado(s)',
        msgFoldersNotAllowed: 'Arrastra e solta unicamente arquivos. Omitida(s) {n} carpeta(s).',
        msgImageWidthSmall: 'O ancho da imaxe "{name}" debe ser de ao menos {size} px.',
        msgImageHeightSmall: 'A altura da imaxe "{name}" debe ser de ao menos {size} px.',
        msgImageWidthLarge: 'O ancho da imaxe "{name}" non pode exceder de {size} px.',
        msgImageHeightLarge: 'A altura da imaxe "{name}" non pode exceder de {size} px.',
        msgImageResizeError: 'Non se puideron obter as dimensións da imaxe para cambiar o tamaño.',
        msgImageResizeException: 'Erro ao cambiar o tamaño da imaxe. <pre>{errors}</pre>',
        msgAjaxError: 'Algo foi mal ca operación {operation}. Por favor, inténtao de novo máis tarde.',
        msgAjaxProgressError: 'A operación {operation} fallou',
        ajaxOperations: {
            deleteThumb: 'Arquivo borrado',
            uploadThumb: 'Arquivo subido',
            uploadBatch: 'Datos subidos en lote',
            uploadExtra: 'Datos do formulario subidos'
        },
        dropZoneTitle: 'Arrasta e solta aquí os arquivos &hellip;',
        dropZoneClickTitle: '<br>(ou fai clic para seleccionar {files})',
        fileActionSettings: {
            removeTitle: 'Eliminar arquivo',
            uploadTitle: 'Subir arquivo',
            uploadRetryTitle: 'Reintentar a subida',
            downloadTitle: 'Descargar arquivo',
            zoomTitle: 'Ver detalles',
            dragTitle: 'Mover / Reordenar',
            indicatorNewTitle: 'Non subido aínda',
            indicatorSuccessTitle: 'Subido',
            indicatorErrorTitle: 'Erro ao subir',
            indicatorLoadingTitle: 'Subindo...'
        },
        previewZoomButtonTitles: {
            prev: 'Ver arquivo anterior',
            next: 'Ver arquivo seguinte',
            toggleheader: 'Mostrar encabezado',
            fullscreen: 'Mostrar a pantalla completa',
            borderless: 'Activar o modo sen bordes',
            close: 'Cerrar vista detallada'
        }
    };
})(window.jQuery);

//! moment.js locale configuration

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';


    var gl = moment.defineLocale('gl', {
        months : 'xaneiro_febreiro_marzo_abril_maio_xuño_xullo_agosto_setembro_outubro_novembro_decembro'.split('_'),
        monthsShort : 'xan._feb._mar._abr._mai._xuñ._xul._ago._set._out._nov._dec.'.split('_'),
        monthsParseExact: true,
        weekdays : 'domingo_luns_martes_mércores_xoves_venres_sábado'.split('_'),
        weekdaysShort : 'dom._lun._mar._mér._xov._ven._sáb.'.split('_'),
        weekdaysMin : 'do_lu_ma_mé_xo_ve_sá'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D [de] MMMM [de] YYYY',
            LLL : 'D [de] MMMM [de] YYYY H:mm',
            LLLL : 'dddd, D [de] MMMM [de] YYYY H:mm'
        },
        calendar : {
            sameDay : function () {
                return '[hoxe ' + ((this.hours() !== 1) ? 'ás' : 'á') + '] LT';
            },
            nextDay : function () {
                return '[mañá ' + ((this.hours() !== 1) ? 'ás' : 'á') + '] LT';
            },
            nextWeek : function () {
                return 'dddd [' + ((this.hours() !== 1) ? 'ás' : 'a') + '] LT';
            },
            lastDay : function () {
                return '[onte ' + ((this.hours() !== 1) ? 'á' : 'a') + '] LT';
            },
            lastWeek : function () {
                return '[o] dddd [pasado ' + ((this.hours() !== 1) ? 'ás' : 'a') + '] LT';
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : function (str) {
                if (str.indexOf('un') === 0) {
                    return 'n' + str;
                }
                return 'en ' + str;
            },
            past : 'hai %s',
            s : 'uns segundos',
            ss : '%d segundos',
            m : 'un minuto',
            mm : '%d minutos',
            h : 'unha hora',
            hh : '%d horas',
            d : 'un día',
            dd : '%d días',
            M : 'un mes',
            MM : '%d meses',
            y : 'un ano',
            yy : '%d anos'
        },
        dayOfMonthOrdinalParse : /\d{1,2}º/,
        ordinal : '%dº',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return gl;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/gl",[],function(){return{errorLoading:function(){return"Non foi posíbel cargar os resultados."},inputTooLong:function(e){var t=e.input.length-e.maximum;return t===1?"Elimine un carácter":"Elimine "+t+" caracteres"},inputTooShort:function(e){var t=e.minimum-e.input.length;return t===1?"Engada un carácter":"Engada "+t+" caracteres"},loadingMore:function(){return"Cargando máis resultados…"},maximumSelected:function(e){return e.maximum===1?"Só pode seleccionar un elemento":"Só pode seleccionar "+e.maximum+" elementos"},noResults:function(){return"Non se atoparon resultados"},searching:function(){return"Buscando…"}}}),{define:e.define,require:e.require}})();