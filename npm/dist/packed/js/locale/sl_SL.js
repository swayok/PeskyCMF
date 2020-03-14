/*!
 * Bootstrap-select v1.13.12 (https://developer.snapappointments.com/bootstrap-select)
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
    noneSelectedText: 'Nič izbranega',
    noneResultsText: 'Ni zadetkov za {0}',
    countSelectedText: '{0} od {1} izbranih',
    maxOptionsText: function (numAll, numGroup) {
      return [
        'Omejitev dosežena (max. izbranih: {n})',
        'Omejitev skupine dosežena (max. izbranih: {n})'
      ];
    },
    selectAllText: 'Izberi vse',
    deselectAllText: 'Počisti izbor',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-sl_SI.js.map
/*!
 * FileInput Slovenian Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author kv1dr <kv1dr.android@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['sl'] = {
        fileSingle: 'datoteka',
        filePlural: 'datotek',
        browseLabel: 'Prebrskaj &hellip;',
        removeLabel: 'Odstrani',
        removeTitle: 'Počisti izbrane datoteke',
        cancelLabel: 'Prekliči',
        cancelTitle: 'Prekliči nalaganje',
        uploadLabel: 'Naloži',
        uploadTitle: 'Naloži izbrane datoteke',
        msgNo: 'Ne',
        msgNoFilesSelected: 'Nobena datoteka ni izbrana',
        msgCancelled: 'Preklicano',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Podroben predogled',
        msgSizeTooLarge: 'Datoteka "{name}" (<b>{size} KB</b>) presega največjo dovoljeno velikost za nalaganje <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Za nalaganje morate izbrati vsaj <b>{n}</b> {files}.',
        msgFilesTooMany: 'Število datotek, izbranih za nalaganje <b>({n})</b> je prekoračilo največjo dovoljeno število <b>{m}</b>.',
        msgFileNotFound: 'Datoteka "{name}" ni bila najdena!',
        msgFileSecured: 'Zaradi varnostnih omejitev nisem mogel prebrati datoteko "{name}".',
        msgFileNotReadable: 'Datoteka "{name}" ni berljiva.',
        msgFilePreviewAborted: 'Predogled datoteke "{name}" preklican.',
        msgFilePreviewError: 'Pri branju datoteke "{name}" je prišlo do napake.',
        msgInvalidFileType: 'Napačen tip datoteke "{name}". Samo "{types}" datoteke so podprte.',
        msgInvalidFileExtension: 'Napačna končnica datoteke "{name}". Samo "{extensions}" datoteke so podprte.',
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
        msgUploadAborted: 'Nalaganje datoteke je bilo preklicano',
        msgUploadThreshold: 'Procesiram...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Napaki pri validiranju',
        msgLoading: 'Nalaganje datoteke {index} od {files} &hellip;',
        msgProgress: 'Nalaganje datoteke {index} od {files} - {name} - {percent}% dokončano.',
        msgSelected: '{n} {files} izbrano',
        msgFoldersNotAllowed: 'Povlecite in spustite samo datoteke! Izpuščenih je bilo {n} map.',
        msgImageWidthSmall: 'Širina slike "{name}" mora biti vsaj {size} px.',
        msgImageHeightSmall: 'Višina slike "{name}" mora biti vsaj {size} px.',
        msgImageWidthLarge: 'Širina slike "{name}" ne sme preseči {size} px.',
        msgImageHeightLarge: 'Višina slike "{name}" ne sme preseči {size} px.',
        msgImageResizeError: 'Nisem mogel pridobiti dimenzij slike za spreminjanje velikosti.',
        msgImageResizeException: 'Napaka pri spreminjanju velikosti slike.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Povlecite in spustite datoteke sem &hellip;',
        dropZoneClickTitle: '<br>(ali kliknite sem za izbiro {files})',
        fileActionSettings: {
            removeTitle: 'Odstrani datoteko',
            uploadTitle: 'Naloži datoteko',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'Poglej podrobnosti',
            dragTitle: 'Premaki / Razporedi',
            indicatorNewTitle: 'Še ni naloženo',
            indicatorSuccessTitle: 'Naloženo',
            indicatorErrorTitle: 'Napaka pri nalaganju',
            indicatorLoadingTitle: 'Nalagam ...'
        },
        previewZoomButtonTitles: {
            prev: 'Poglej prejšno datoteko',
            next: 'Poglej naslednjo datoteko',
            toggleheader: 'Preklopi glavo',
            fullscreen: 'Preklopi celozaslonski način',
            borderless: 'Preklopi način brez robov',
            close: 'Zapri predogled podrobnosti'
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


    function processRelativeTime(number, withoutSuffix, key, isFuture) {
        var result = number + ' ';
        switch (key) {
            case 's':
                return withoutSuffix || isFuture ? 'nekaj sekund' : 'nekaj sekundami';
            case 'ss':
                if (number === 1) {
                    result += withoutSuffix ? 'sekundo' : 'sekundi';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'sekundi' : 'sekundah';
                } else if (number < 5) {
                    result += withoutSuffix || isFuture ? 'sekunde' : 'sekundah';
                } else {
                    result += 'sekund';
                }
                return result;
            case 'm':
                return withoutSuffix ? 'ena minuta' : 'eno minuto';
            case 'mm':
                if (number === 1) {
                    result += withoutSuffix ? 'minuta' : 'minuto';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'minuti' : 'minutama';
                } else if (number < 5) {
                    result += withoutSuffix || isFuture ? 'minute' : 'minutami';
                } else {
                    result += withoutSuffix || isFuture ? 'minut' : 'minutami';
                }
                return result;
            case 'h':
                return withoutSuffix ? 'ena ura' : 'eno uro';
            case 'hh':
                if (number === 1) {
                    result += withoutSuffix ? 'ura' : 'uro';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'uri' : 'urama';
                } else if (number < 5) {
                    result += withoutSuffix || isFuture ? 'ure' : 'urami';
                } else {
                    result += withoutSuffix || isFuture ? 'ur' : 'urami';
                }
                return result;
            case 'd':
                return withoutSuffix || isFuture ? 'en dan' : 'enim dnem';
            case 'dd':
                if (number === 1) {
                    result += withoutSuffix || isFuture ? 'dan' : 'dnem';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'dni' : 'dnevoma';
                } else {
                    result += withoutSuffix || isFuture ? 'dni' : 'dnevi';
                }
                return result;
            case 'M':
                return withoutSuffix || isFuture ? 'en mesec' : 'enim mesecem';
            case 'MM':
                if (number === 1) {
                    result += withoutSuffix || isFuture ? 'mesec' : 'mesecem';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'meseca' : 'mesecema';
                } else if (number < 5) {
                    result += withoutSuffix || isFuture ? 'mesece' : 'meseci';
                } else {
                    result += withoutSuffix || isFuture ? 'mesecev' : 'meseci';
                }
                return result;
            case 'y':
                return withoutSuffix || isFuture ? 'eno leto' : 'enim letom';
            case 'yy':
                if (number === 1) {
                    result += withoutSuffix || isFuture ? 'leto' : 'letom';
                } else if (number === 2) {
                    result += withoutSuffix || isFuture ? 'leti' : 'letoma';
                } else if (number < 5) {
                    result += withoutSuffix || isFuture ? 'leta' : 'leti';
                } else {
                    result += withoutSuffix || isFuture ? 'let' : 'leti';
                }
                return result;
        }
    }

    var sl = moment.defineLocale('sl', {
        months : 'januar_februar_marec_april_maj_junij_julij_avgust_september_oktober_november_december'.split('_'),
        monthsShort : 'jan._feb._mar._apr._maj._jun._jul._avg._sep._okt._nov._dec.'.split('_'),
        monthsParseExact: true,
        weekdays : 'nedelja_ponedeljek_torek_sreda_četrtek_petek_sobota'.split('_'),
        weekdaysShort : 'ned._pon._tor._sre._čet._pet._sob.'.split('_'),
        weekdaysMin : 'ne_po_to_sr_če_pe_so'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D. MMMM YYYY',
            LLL : 'D. MMMM YYYY H:mm',
            LLLL : 'dddd, D. MMMM YYYY H:mm'
        },
        calendar : {
            sameDay  : '[danes ob] LT',
            nextDay  : '[jutri ob] LT',

            nextWeek : function () {
                switch (this.day()) {
                    case 0:
                        return '[v] [nedeljo] [ob] LT';
                    case 3:
                        return '[v] [sredo] [ob] LT';
                    case 6:
                        return '[v] [soboto] [ob] LT';
                    case 1:
                    case 2:
                    case 4:
                    case 5:
                        return '[v] dddd [ob] LT';
                }
            },
            lastDay  : '[včeraj ob] LT',
            lastWeek : function () {
                switch (this.day()) {
                    case 0:
                        return '[prejšnjo] [nedeljo] [ob] LT';
                    case 3:
                        return '[prejšnjo] [sredo] [ob] LT';
                    case 6:
                        return '[prejšnjo] [soboto] [ob] LT';
                    case 1:
                    case 2:
                    case 4:
                    case 5:
                        return '[prejšnji] dddd [ob] LT';
                }
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : 'čez %s',
            past   : 'pred %s',
            s      : processRelativeTime,
            ss     : processRelativeTime,
            m      : processRelativeTime,
            mm     : processRelativeTime,
            h      : processRelativeTime,
            hh     : processRelativeTime,
            d      : processRelativeTime,
            dd     : processRelativeTime,
            M      : processRelativeTime,
            MM     : processRelativeTime,
            y      : processRelativeTime,
            yy     : processRelativeTime
        },
        dayOfMonthOrdinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 7th is the first week of the year.
        }
    });

    return sl;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/sl",[],function(){return{errorLoading:function(){return"Zadetkov iskanja ni bilo mogoče naložiti."},inputTooLong:function(e){var n=e.input.length-e.maximum,t="Prosim zbrišite "+n+" znak";return 2==n?t+="a":1!=n&&(t+="e"),t},inputTooShort:function(e){var n=e.minimum-e.input.length,t="Prosim vpišite še "+n+" znak";return 2==n?t+="a":1!=n&&(t+="e"),t},loadingMore:function(){return"Nalagam več zadetkov…"},maximumSelected:function(e){var n="Označite lahko največ "+e.maximum+" predmet";return 2==e.maximum?n+="a":1!=e.maximum&&(n+="e"),n},noResults:function(){return"Ni zadetkov."},searching:function(){return"Iščem…"},removeAllItems:function(){return"Odstranite vse elemente"}}}),e.define,e.require}();