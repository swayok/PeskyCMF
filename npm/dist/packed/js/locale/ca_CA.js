/*!
 * FileInput Català Translations
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

    $.fn.fileinputLocales['ca'] = {
        fileSingle: 'arxiu',
        filePlural: 'arxius',
        browseLabel: 'Examinar &hellip;',
        removeLabel: 'Treure',
        removeTitle: 'Treure arxius seleccionats',
        cancelLabel: 'Cancel',
        cancelTitle: 'Avortar la pujada en curs',
        uploadLabel: 'Pujar arxiu',
        uploadTitle: 'Pujar arxius seleccionats',
        msgNo: 'No',
        msgNoFilesSelected: '',
        msgCancelled: 'cancel·lat',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Vista prèvia detallada',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Arxiu "{name}" (<b>{size} KB</b>) excedeix la mida màxima permès de <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Heu de seleccionar almenys <b>{n}</b> {files} a carregar.',
        msgFilesTooMany: 'El nombre d\'arxius seleccionats a carregar <b>({n})</b> excedeix el límit màxim permès de <b>{m}</b>.',
        msgFileNotFound: 'Arxiu "{name}" no trobat.',
        msgFileSecured: 'No es pot accedir a l\'arxiu "{name}" perquè estarà sent usat per una altra aplicació o no tinguem permisos de lectura.',
        msgFileNotReadable: 'No es pot accedir a l\'arxiu "{name}".',
        msgFilePreviewAborted: 'Previsualització de l\'arxiu "{name}" cancel·lada.',
        msgFilePreviewError: 'S\'ha produït un error mentre es llegia el fitxer "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'Tipus de fitxer no vàlid per a "{name}". Només arxius "{types}" són permesos.',
        msgInvalidFileExtension: 'Extensió de fitxer no vàlid per a "{name}". Només arxius "{extensions}" són permesos.',
        msgFileTypes: {
            'image': 'image',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'audio',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'La càrrega d\'arxius s\'ha cancel·lat',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Error de validació',
        msgLoading: 'Pujant fitxer {index} de {files} &hellip;',
        msgProgress: 'Pujant fitxer {index} de {files} - {name} - {percent}% completat.',
        msgSelected: '{n} {files} seleccionat(s)',
        msgFoldersNotAllowed: 'Arrossegueu i deixeu anar únicament arxius. Omesa(es) {n} carpeta(es).',
        msgImageWidthSmall: 'L\'ample de la imatge "{name}" ha de ser almenys {size} px.',
        msgImageHeightSmall: 'L\'alçada de la imatge "{name}" ha de ser almenys {size} px.',
        msgImageWidthLarge: 'L\'ample de la imatge "{name}" no pot excedir de {size} px.',
        msgImageHeightLarge: 'L\'alçada de la imatge "{name}" no pot excedir de {size} px.',
        msgImageResizeError: 'No s\'ha pogut obtenir les dimensions d\'imatge per canviar la mida.',
        msgImageResizeException: 'Error en canviar la mida de la imatge.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Arrossegueu i deixeu anar aquí els arxius &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'Eliminar arxiu',
            uploadTitle: 'Pujar arxiu',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'Veure detalls',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'No pujat encara',
            indicatorSuccessTitle: 'Subido',
            indicatorErrorTitle: 'Pujar Error',
            indicatorLoadingTitle: 'Pujant ...'
        },
        previewZoomButtonTitles: {
            prev: 'View previous file',
            next: 'View next file',
            toggleheader: 'Toggle header',
            fullscreen: 'Toggle full screen',
            borderless: 'Toggle borderless mode',
            close: 'Close detailed preview'
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


    var ca = moment.defineLocale('ca', {
        months : {
            standalone: 'gener_febrer_març_abril_maig_juny_juliol_agost_setembre_octubre_novembre_desembre'.split('_'),
            format: 'de gener_de febrer_de març_d\'abril_de maig_de juny_de juliol_d\'agost_de setembre_d\'octubre_de novembre_de desembre'.split('_'),
            isFormat: /D[oD]?(\s)+MMMM/
        },
        monthsShort : 'gen._febr._març_abr._maig_juny_jul._ag._set._oct._nov._des.'.split('_'),
        monthsParseExact : true,
        weekdays : 'diumenge_dilluns_dimarts_dimecres_dijous_divendres_dissabte'.split('_'),
        weekdaysShort : 'dg._dl._dt._dc._dj._dv._ds.'.split('_'),
        weekdaysMin : 'dg_dl_dt_dc_dj_dv_ds'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM [de] YYYY',
            ll : 'D MMM YYYY',
            LLL : 'D MMMM [de] YYYY [a les] H:mm',
            lll : 'D MMM YYYY, H:mm',
            LLLL : 'dddd D MMMM [de] YYYY [a les] H:mm',
            llll : 'ddd D MMM YYYY, H:mm'
        },
        calendar : {
            sameDay : function () {
                return '[avui a ' + ((this.hours() !== 1) ? 'les' : 'la') + '] LT';
            },
            nextDay : function () {
                return '[demà a ' + ((this.hours() !== 1) ? 'les' : 'la') + '] LT';
            },
            nextWeek : function () {
                return 'dddd [a ' + ((this.hours() !== 1) ? 'les' : 'la') + '] LT';
            },
            lastDay : function () {
                return '[ahir a ' + ((this.hours() !== 1) ? 'les' : 'la') + '] LT';
            },
            lastWeek : function () {
                return '[el] dddd [passat a ' + ((this.hours() !== 1) ? 'les' : 'la') + '] LT';
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : 'd\'aquí %s',
            past : 'fa %s',
            s : 'uns segons',
            ss : '%d segons',
            m : 'un minut',
            mm : '%d minuts',
            h : 'una hora',
            hh : '%d hores',
            d : 'un dia',
            dd : '%d dies',
            M : 'un mes',
            MM : '%d mesos',
            y : 'un any',
            yy : '%d anys'
        },
        dayOfMonthOrdinalParse: /\d{1,2}(r|n|t|è|a)/,
        ordinal : function (number, period) {
            var output = (number === 1) ? 'r' :
                (number === 2) ? 'n' :
                (number === 3) ? 'r' :
                (number === 4) ? 't' : 'è';
            if (period === 'w' || period === 'W') {
                output = 'a';
            }
            return number + output;
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return ca;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/ca",[],function(){return{errorLoading:function(){return"La càrrega ha fallat"},inputTooLong:function(e){var n=e.input.length-e.maximum,r="Si us plau, elimina "+n+" car";return r+=1==n?"àcter":"àcters"},inputTooShort:function(e){var n=e.minimum-e.input.length,r="Si us plau, introdueix "+n+" car";return r+=1==n?"àcter":"àcters"},loadingMore:function(){return"Carregant més resultats…"},maximumSelected:function(e){var n="Només es pot seleccionar "+e.maximum+" element";return 1!=e.maximum&&(n+="s"),n},noResults:function(){return"No s'han trobat resultats"},searching:function(){return"Cercant…"},removeAllItems:function(){return"Treu tots els elements"}}}),e.define,e.require}();