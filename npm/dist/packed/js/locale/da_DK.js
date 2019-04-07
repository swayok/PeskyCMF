/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Danish (da)
 * Author: Jna Borup Coyle, github@coyle.dk
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

QueryBuilder.regional['da'] = {
  "__locale": "Danish (da)",
  "__author": "Jna Borup Coyle, github@coyle.dk",
  "add_rule": "Tilføj regel",
  "add_group": "Tilføj gruppe",
  "delete_rule": "Slet regel",
  "delete_group": "Slet gruppe",
  "conditions": {
    "AND": "OG",
    "OR": "ELLER"
  },
  "condition_and": "OG",
  "condition_or": "ELLER",
  "operators": {
    "equal": "lig med",
    "not_equal": "ikke lige med",
    "in": "i",
    "not_in": "ikke i",
    "less": "mindre",
    "less_or_equal": "mindre eller lig med",
    "greater": "større",
    "greater_or_equal": "større eller lig med",
    "begins_with": "begynder med",
    "not_begins_with": "begynder ikke med",
    "contains": "indeholder",
    "not_contains": "indeholder ikke",
    "ends_with": "slutter med",
    "not_ends_with": "slutter ikke med",
    "is_empty": "er tom",
    "is_not_empty": "er ikke tom",
    "is_null": "er null",
    "is_not_null": "er ikke null"
  }
};

QueryBuilder.defaults({ lang_code: 'da' });
}));
/*!
 * FileInput Danish Translations
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

    $.fn.fileinputLocales['da'] = {
        fileSingle: 'fil',
        filePlural: 'filer',
        browseLabel: 'Browse &hellip;',
        removeLabel: 'Fjern',
        removeTitle: 'Fjern valgte filer',
        cancelLabel: 'Fortryd',
        cancelTitle: 'Afbryd nuv&aelig;rende upload',
        uploadLabel: 'Upload',
        uploadTitle: 'Upload valgte filer',
        msgNo: 'Ingen',
        msgNoFilesSelected: '',
        msgCancelled: 'aflyst',
        msgPlaceholder: 'V&aelig;lg {files}...',
        msgZoomModalHeading: 'Detaljeret visning',
        msgFileRequired: 'Du skal v&aelig;lge en fil at uploade.',
        msgSizeTooSmall: 'Fil "{name}" (<b>{size} KB</b>) er for lille og skal v&aelig;re st&oslash;rre end <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Fil "{name}" (<b>{size} KB</b>) er st&oslash;rre end de tilladte <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Du skal mindst v&aelig;lge <b>{n}</b> {files} til upload.',
        msgFilesTooMany: '<b>({n})</b> filer valgt til upload, men maks. <b>{m}</b> er tilladt.',
        msgFileNotFound: 'Filen "{name}" blev ikke fundet!',
        msgFileSecured: 'Sikkerhedsrestriktioner forhindrer l&aelig;sning af "{name}".',
        msgFileNotReadable: 'Filen "{name}" kan ikke indl&aelig;ses.',
        msgFilePreviewAborted: 'Filgennemsyn annulleret for "{name}".',
        msgFilePreviewError: 'Der skete en fejl under l&aelig;sningen af filen "{name}".',
        msgInvalidFileName: 'Ugyldige eller ikke-underst&oslash;ttede tegn i filnavn "{name}".',
        msgInvalidFileType: 'Ukendt type for filen "{name}". Kun "{types}" kan bruges.',
        msgInvalidFileExtension: 'Ukendt filtype for filen "{name}". Kun "{extensions}" filer kan bruges.',
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
        msgUploadAborted: 'Filupload annulleret',
        msgUploadThreshold: 'Arbejder...',
        msgUploadBegin: 'Initialiserer...',
        msgUploadEnd: 'Udf&oslash;rt',
        msgUploadEmpty: 'Ingen gyldig data tilg&aelig;ngelig til upload.',
        msgUploadError: 'Fejl',
        msgValidationError: 'Valideringsfejl',
        msgLoading: 'Henter fil {index} af {files} &hellip;',
        msgProgress: 'Henter fil {index} af {files} - {name} - {percent}% f&aelig;rdiggjort.',
        msgSelected: '{n} {files} valgt',
        msgFoldersNotAllowed: 'Drag & drop kun filer! {n} mappe(r) sprunget over.',
        msgImageWidthSmall: 'Bredden af billedet "{name}" skal v&aelig;re p&aring; mindst {size} px.',
        msgImageHeightSmall: 'H&oslash;jden af billedet "{name}" skal v&aelig;re p&aring; mindst {size} px.',
        msgImageWidthLarge: 'Bredden af billedet "{name}" m&aring; ikke v&aelig;re over {size} px.',
        msgImageHeightLarge: 'H&oslash;jden af billedet "{name}" m&aring; ikke v&aelig;re over {size} px.',
        msgImageResizeError: 'Kunne ikke f&aring; billedets dimensioner for at &aelig;ndre st&oslash;rrelsen.',
        msgImageResizeException: 'Fejl ved at &aelig;ndre st&oslash;rrelsen p&aring; billedet.<pre>{errors}</pre>',
        msgAjaxError: 'Noget gik galt med {operation} operationen. Fors&oslash;g venligst senere!',
        msgAjaxProgressError: '{operation} fejlede',
        ajaxOperations: {
            deleteThumb: 'fil slet',
            uploadThumb: 'fil upload',
            uploadBatch: 'batchfil upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Drag & drop filer her &hellip;',
        dropZoneClickTitle: '<br>(eller klik for at v&aelig;lge {files})',
        fileActionSettings: {
            removeTitle: 'Fjern fil',
            uploadTitle: 'Upload fil',
            uploadRetryTitle: 'Fors&aring;g upload igen',
            downloadTitle: 'Download fil',
            zoomTitle: 'Se detaljer',
            dragTitle: 'Flyt / Omarranger',
            indicatorNewTitle: 'Ikke uploadet endnu',
            indicatorSuccessTitle: 'Uploadet',
            indicatorErrorTitle: 'Upload fejl',
            indicatorLoadingTitle: 'Uploader ...'
        },
        previewZoomButtonTitles: {
            prev: 'Se forrige fil',
            next: 'Se n&aelig;ste fil',
            toggleheader: 'Skift header',
            fullscreen: 'Skift fuld sk&aelig;rm',
            borderless: 'Skift gr&aelig;nsel&oslash;s mode',
            close: 'Luk detaljeret visning'
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


    var da = moment.defineLocale('da', {
        months : 'januar_februar_marts_april_maj_juni_juli_august_september_oktober_november_december'.split('_'),
        monthsShort : 'jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec'.split('_'),
        weekdays : 'søndag_mandag_tirsdag_onsdag_torsdag_fredag_lørdag'.split('_'),
        weekdaysShort : 'søn_man_tir_ons_tor_fre_lør'.split('_'),
        weekdaysMin : 'sø_ma_ti_on_to_fr_lø'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D. MMMM YYYY',
            LLL : 'D. MMMM YYYY HH:mm',
            LLLL : 'dddd [d.] D. MMMM YYYY [kl.] HH:mm'
        },
        calendar : {
            sameDay : '[i dag kl.] LT',
            nextDay : '[i morgen kl.] LT',
            nextWeek : 'på dddd [kl.] LT',
            lastDay : '[i går kl.] LT',
            lastWeek : '[i] dddd[s kl.] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'om %s',
            past : '%s siden',
            s : 'få sekunder',
            ss : '%d sekunder',
            m : 'et minut',
            mm : '%d minutter',
            h : 'en time',
            hh : '%d timer',
            d : 'en dag',
            dd : '%d dage',
            M : 'en måned',
            MM : '%d måneder',
            y : 'et år',
            yy : '%d år'
        },
        dayOfMonthOrdinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return da;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/da",[],function(){return{errorLoading:function(){return"Resultaterne kunne ikke indlæses."},inputTooLong:function(e){var t=e.input.length-e.maximum;return"Angiv venligst "+t+" tegn mindre"},inputTooShort:function(e){var t=e.minimum-e.input.length;return"Angiv venligst "+t+" tegn mere"},loadingMore:function(){return"Indlæser flere resultater…"},maximumSelected:function(e){var t="Du kan kun vælge "+e.maximum+" emne";return e.maximum!=1&&(t+="r"),t},noResults:function(){return"Ingen resultater fundet"},searching:function(){return"Søger…"}}}),{define:e.define,require:e.require}})();
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
    noneSelectedText: 'Intet valgt',
    noneResultsText: 'Ingen resultater fundet {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} valgt' : '{0} valgt';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Begrænsning nået (max {n} valgt)' : 'Begrænsning nået (max {n} valgte)',
        (numGroup == 1) ? 'Gruppe-begrænsning nået (max {n} valgt)' : 'Gruppe-begrænsning nået (max {n} valgte)'
      ];
    },
    selectAllText: 'Markér alle',
    deselectAllText: 'Afmarkér alle',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-da_DK.js.map