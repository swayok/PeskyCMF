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
    noneSelectedText: 'Inget valt',
    noneResultsText: 'Inget sökresultat matchar {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected === 1) ? '{0} alternativ valt' : '{0} alternativ valda';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        'Gräns uppnåd (max {n} alternativ)',
        'Gräns uppnåd (max {n} gruppalternativ)'
      ];
    },
    selectAllText: 'Markera alla',
    deselectAllText: 'Avmarkera alla',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-sv_SE.js.map
/*!
 * FileInput <_LANG_> Translations
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

    $.fn.fileinputLocales['sv'] = {
        fileSingle: 'fil',
        filePlural: 'filer',
        browseLabel: 'Bläddra &hellip;',
        removeLabel: 'Ta bort',
        removeTitle: 'Rensa valda filer',
        cancelLabel: 'Avbryt',
        cancelTitle: 'Avbryt pågående uppladdning',
        uploadLabel: 'Ladda upp',
        uploadTitle: 'Ladda upp valda filer',
        msgNo: 'Nej',
        msgNoFilesSelected: 'Inga filer valda',
        msgCancelled: 'Avbruten',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'detaljerad förhandsgranskning',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'Filen "{name}" (<b>{size} KB</b>) är för liten och måste vara större än <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'File "{name}" (<b>{size} KB</b>) överstiger högsta tillåtna uppladdningsstorlek <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Du måste välja minst <b>{n}</b> {files} för att ladda upp.',
        msgFilesTooMany: 'Antal filer valda för uppladdning <b>({n})</b> överstiger högsta tillåtna gränsen <b>{m}</b>.',
        msgFileNotFound: 'Filen "{name}" kunde inte hittas!',
        msgFileSecured: 'Säkerhetsbegränsningar förhindrar att läsa filen "{name}".',
        msgFileNotReadable: 'Filen "{name}" är inte läsbar.',
        msgFilePreviewAborted: 'Filförhandsvisning avbröts för "{name}".',
        msgFilePreviewError: 'Ett fel uppstod vid inläsning av filen "{name}".',
        msgInvalidFileName: 'Ogiltiga eller tecken som inte stöds i filnamnet "{name}".',
        msgInvalidFileType: 'Ogiltig typ för filen "{name}". Endast "{types}" filtyper stöds.',
        msgInvalidFileExtension: 'Ogiltigt filtillägg för filen "{name}". Endast "{extensions}" filer stöds.',
        msgFileTypes: {
            'image': 'bild',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'ljud',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'objekt'
        },
        msgUploadAborted: 'Filöverföringen avbröts',
        msgUploadThreshold: 'Bearbetar...',
        msgUploadBegin: 'Påbörjar...',
        msgUploadEnd: 'Färdig',
        msgUploadEmpty: 'Ingen giltig data tillgänglig för uppladdning.',
        msgUploadError: 'Error',
        msgValidationError: 'Valideringsfel',
        msgLoading: 'Laddar fil {index} av {files} &hellip;',
        msgProgress: 'Laddar fil {index} av {files} - {name} - {percent}% färdig.',
        msgSelected: '{n} {files} valda',
        msgFoldersNotAllowed: 'Endast drag & släppfiler! Skippade {n} släpta mappar.',
        msgImageWidthSmall: 'Bredd på bildfilen "{name}" måste minst vara {size} pixlar.',
        msgImageHeightSmall: 'Höjden på bildfilen "{name}" måste minst vara {size} pixlar.',
        msgImageWidthLarge: 'Bredd på bildfil "{name}" kan inte överstiga {size} pixlar.',
        msgImageHeightLarge: 'Höjden på bildfilen "{name}" kan inte överstiga {size} pixlar.',
        msgImageResizeError: 'Det gick inte att hämta bildens dimensioner för att ändra storlek.',
        msgImageResizeException: 'Fel vid storleksändring av bilden.<pre>{errors}</pre>',
        msgAjaxError: 'Något gick fel med {operation} operationen. Försök igen senare!',
        msgAjaxProgressError: '{operation} misslyckades',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Drag & släpp filer här &hellip;',
        dropZoneClickTitle: '<br>(eller klicka för att markera {files})',
        fileActionSettings: {
            removeTitle: 'Ta bort fil',
            uploadTitle: 'Ladda upp fil',
            uploadRetryTitle: 'Retry upload',
            zoomTitle: 'Visa detaljer',
            dragTitle: 'Flytta / Ändra ordning',
            indicatorNewTitle: 'Inte uppladdat ännu',
            indicatorSuccessTitle: 'Uppladdad',
            indicatorErrorTitle: 'Uppladdningsfel',
            indicatorLoadingTitle: 'Laddar upp...'
        },
        previewZoomButtonTitles: {
            prev: 'Visa föregående fil',
            next: 'Visa nästa fil',
            toggleheader: 'Rubrik',
            fullscreen: 'Fullskärm',
            borderless: 'Gränslös',
            close: 'Stäng detaljerad förhandsgranskning'
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


    var sv = moment.defineLocale('sv', {
        months : 'januari_februari_mars_april_maj_juni_juli_augusti_september_oktober_november_december'.split('_'),
        monthsShort : 'jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec'.split('_'),
        weekdays : 'söndag_måndag_tisdag_onsdag_torsdag_fredag_lördag'.split('_'),
        weekdaysShort : 'sön_mån_tis_ons_tor_fre_lör'.split('_'),
        weekdaysMin : 'sö_må_ti_on_to_fr_lö'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'YYYY-MM-DD',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY [kl.] HH:mm',
            LLLL : 'dddd D MMMM YYYY [kl.] HH:mm',
            lll : 'D MMM YYYY HH:mm',
            llll : 'ddd D MMM YYYY HH:mm'
        },
        calendar : {
            sameDay: '[Idag] LT',
            nextDay: '[Imorgon] LT',
            lastDay: '[Igår] LT',
            nextWeek: '[På] dddd LT',
            lastWeek: '[I] dddd[s] LT',
            sameElse: 'L'
        },
        relativeTime : {
            future : 'om %s',
            past : 'för %s sedan',
            s : 'några sekunder',
            ss : '%d sekunder',
            m : 'en minut',
            mm : '%d minuter',
            h : 'en timme',
            hh : '%d timmar',
            d : 'en dag',
            dd : '%d dagar',
            M : 'en månad',
            MM : '%d månader',
            y : 'ett år',
            yy : '%d år'
        },
        dayOfMonthOrdinalParse: /\d{1,2}(e|a)/,
        ordinal : function (number) {
            var b = number % 10,
                output = (~~(number % 100 / 10) === 1) ? 'e' :
                (b === 1) ? 'a' :
                (b === 2) ? 'a' :
                (b === 3) ? 'e' : 'e';
            return number + output;
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return sv;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/sv",[],function(){return{errorLoading:function(){return"Resultat kunde inte laddas."},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Vänligen sudda ut "+t+" tecken";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Vänligen skriv in "+t+" eller fler tecken";return n},loadingMore:function(){return"Laddar fler resultat…"},maximumSelected:function(e){var t="Du kan max välja "+e.maximum+" element";return t},noResults:function(){return"Inga träffar"},searching:function(){return"Söker…"}}}),{define:e.define,require:e.require}})();