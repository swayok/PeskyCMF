/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Romanian (ro)
 * Author: ArianServ
 * Licensed under MIT (https://opensource.org/licenses/MIT)
 */

(function(root, factory) {
    if (typeof define == 'function' && define.amd) {
        define(['jquery', 'query-builder'], factory);
    }
    else {
        factory(root.jQuery);
    }
}(this, function($) {
"use strict";

var QueryBuilder = $.fn.queryBuilder;

QueryBuilder.regional['ro'] = {
  "__locale": "Romanian (ro)",
  "__author": "ArianServ",
  "add_rule": "Adaugă regulă",
  "add_group": "Adaugă grup",
  "delete_rule": "Şterge",
  "delete_group": "Şterge",
  "conditions": {
    "AND": "ŞI",
    "OR": "SAU"
  },
  "operators": {
    "equal": "egal",
    "not_equal": "diferit",
    "in": "în",
    "not_in": "nu în",
    "less": "mai puţin",
    "less_or_equal": "mai puţin sau egal",
    "greater": "mai mare",
    "greater_or_equal": "mai mare sau egal",
    "begins_with": "începe cu",
    "not_begins_with": "nu începe cu",
    "contains": "conţine",
    "not_contains": "nu conţine",
    "ends_with": "se termină cu",
    "not_ends_with": "nu se termină cu",
    "is_empty": "este gol",
    "is_not_empty": "nu este gol",
    "is_null": "e nul",
    "is_not_null": "nu e nul"
  }
};

QueryBuilder.defaults({ lang_code: 'ro' });
}));
/*!
 * FileInput Romanian Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Ciprian Voicu <pictoru@autoportret.ro>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['ro'] = {
        fileSingle: 'fișier',
        filePlural: 'fișiere',
        browseLabel: 'Răsfoiește &hellip;',
        removeLabel: 'Șterge',
        removeTitle: 'Curăță fișierele selectate',
        cancelLabel: 'Renunță',
        cancelTitle: 'Anulează încărcarea curentă',
        uploadLabel: 'Încarcă',
        uploadTitle: 'Încarcă fișierele selectate',
        msgNo: 'Nu',
        msgNoFilesSelected: '',
        msgCancelled: 'Anulat',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Previzualizare detaliată',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Fișierul "{name}" (<b>{size} KB</b>) depășește limita maximă de încărcare de <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Trebuie să selectezi cel puțin <b>{n}</b> {files} pentru a încărca.',
        msgFilesTooMany: 'Numărul fișierelor pentru încărcare <b>({n})</b> depășește limita maximă de <b>{m}</b>.',
        msgFileNotFound: 'Fișierul "{name}" nu a fost găsit!',
        msgFileSecured: 'Restricții de securitate previn citirea fișierului "{name}".',
        msgFileNotReadable: 'Fișierul "{name}" nu se poate citi.',
        msgFilePreviewAborted: 'Fișierului "{name}" nu poate fi previzualizat.',
        msgFilePreviewError: 'A intervenit o eroare în încercarea de citire a fișierului "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'Tip de fișier incorect pentru "{name}". Sunt suportate doar fișiere de tipurile "{types}".',
        msgInvalidFileExtension: 'Extensie incorectă pentru "{name}". Sunt suportate doar extensiile "{extensions}".',
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
        msgUploadAborted: 'Fișierul Încărcarea a fost întrerupt',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Eroare de validare',
        msgLoading: 'Se încarcă fișierul {index} din {files} &hellip;',
        msgProgress: 'Se încarcă fișierul {index} din {files} - {name} - {percent}% încărcat.',
        msgSelected: '{n} {files} încărcate',
        msgFoldersNotAllowed: 'Se poate doar trăgând fișierele! Se renunță la {n} dosar(e).',
        msgImageWidthSmall: 'Lățimea de fișier de imagine "{name}" trebuie să fie de cel puțin {size} px.',
        msgImageHeightSmall: 'Înălțimea fișier imagine "{name}" trebuie să fie de cel puțin {size} px.',
        msgImageWidthLarge: 'Lățimea de fișier de imagine "{name}" nu poate depăși {size} px.',
        msgImageHeightLarge: 'Înălțimea fișier imagine "{name}" nu poate depăși {size} px.',
        msgImageResizeError: 'Nu a putut obține dimensiunile imaginii pentru a redimensiona.',
        msgImageResizeException: 'Eroare la redimensionarea imaginii.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Trage fișierele aici &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'Scoateți fișier',
            uploadTitle: 'Incarca fisier',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'Vezi detalii',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'Nu a încărcat încă',
            indicatorSuccessTitle: 'încărcat',
            indicatorErrorTitle: 'Încărcați eroare',
            indicatorLoadingTitle: 'Se încarcă ...'
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


    function relativeTimeWithPlural(number, withoutSuffix, key) {
        var format = {
                'ss': 'secunde',
                'mm': 'minute',
                'hh': 'ore',
                'dd': 'zile',
                'MM': 'luni',
                'yy': 'ani'
            },
            separator = ' ';
        if (number % 100 >= 20 || (number >= 100 && number % 100 === 0)) {
            separator = ' de ';
        }
        return number + separator + format[key];
    }

    var ro = moment.defineLocale('ro', {
        months : 'ianuarie_februarie_martie_aprilie_mai_iunie_iulie_august_septembrie_octombrie_noiembrie_decembrie'.split('_'),
        monthsShort : 'ian._febr._mart._apr._mai_iun._iul._aug._sept._oct._nov._dec.'.split('_'),
        monthsParseExact: true,
        weekdays : 'duminică_luni_marți_miercuri_joi_vineri_sâmbătă'.split('_'),
        weekdaysShort : 'Dum_Lun_Mar_Mie_Joi_Vin_Sâm'.split('_'),
        weekdaysMin : 'Du_Lu_Ma_Mi_Jo_Vi_Sâ'.split('_'),
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY H:mm',
            LLLL : 'dddd, D MMMM YYYY H:mm'
        },
        calendar : {
            sameDay: '[azi la] LT',
            nextDay: '[mâine la] LT',
            nextWeek: 'dddd [la] LT',
            lastDay: '[ieri la] LT',
            lastWeek: '[fosta] dddd [la] LT',
            sameElse: 'L'
        },
        relativeTime : {
            future : 'peste %s',
            past : '%s în urmă',
            s : 'câteva secunde',
            ss : relativeTimeWithPlural,
            m : 'un minut',
            mm : relativeTimeWithPlural,
            h : 'o oră',
            hh : relativeTimeWithPlural,
            d : 'o zi',
            dd : relativeTimeWithPlural,
            M : 'o lună',
            MM : relativeTimeWithPlural,
            y : 'un an',
            yy : relativeTimeWithPlural
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 7th is the first week of the year.
        }
    });

    return ro;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/ro",[],function(){return{errorLoading:function(){return"Rezultatele nu au putut fi incărcate."},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Vă rugăm să ștergeți"+t+" caracter";return t!==1&&(n+="e"),n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Vă rugăm să introduceți "+t+" sau mai multe caractere";return n},loadingMore:function(){return"Se încarcă mai multe rezultate…"},maximumSelected:function(e){var t="Aveți voie să selectați cel mult "+e.maximum;return t+=" element",e.maximum!==1&&(t+="e"),t},noResults:function(){return"Nu au fost găsite rezultate"},searching:function(){return"Căutare…"}}}),{define:e.define,require:e.require}})();
/*!
 * Bootstrap-select v1.13.9 (https://developer.snapappointments.com/bootstrap-select)
 *
 * Copyright 2012-2019 SnapAppointments, LLC
 * Licensed under MIT (https://github.com/snapappointments/bootstrap-select/blob/master/LICENSE)
 */

(function (root, factory) {
  if (root === undefined && window !== undefined) root = window;
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module unless amdModuleId is set
    define(["jquery"], function (a0) {
      return (factory(a0));
    });
  } else if (typeof module === 'object' && module.exports) {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like environments that support module.exports,
    // like Node.
    module.exports = factory(require("jquery"));
  } else {
    factory(root["jQuery"]);
  }
}(this, function (jQuery) {

(function ($) {
  $.fn.selectpicker.defaults = {
    doneButtonText: 'Închide',
    noneSelectedText: 'Nu a fost selectat nimic',
    noneResultsText: 'Nu există niciun rezultat {0}',
    countSelectedText: '{0} din {1} selectat(e)',
    maxOptionsText: ['Limita a fost atinsă ({n} {var} max)', 'Limita de grup a fost atinsă ({n} {var} max)', ['iteme', 'item']],
    selectAllText: 'Selectează toate',
    deselectAllText: 'Deselectează toate',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-ro_RO.js.map