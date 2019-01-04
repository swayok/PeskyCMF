/*!
 * FileInput Hungarian Translations
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

    $.fn.fileinputLocales['hu'] = {
        fileSingle: 'fájl',
        filePlural: 'fájlok',
        browseLabel: 'Tallóz &hellip;',
        removeLabel: 'Eltávolít',
        removeTitle: 'Kijelölt fájlok törlése',
        cancelLabel: 'Mégse',
        cancelTitle: 'Feltöltés megszakítása',
        uploadLabel: 'Feltöltés',
        uploadTitle: 'Kijelölt fájlok feltöltése',
        msgNo: 'Nem',
        msgNoFilesSelected: 'Nincs fájl kiválasztva',
        msgCancelled: 'Megszakítva',
        msgPlaceholder: 'Válasz {files}...',
        msgZoomModalHeading: 'Részletes Előnézet',
        msgFileRequired: 'Kötelező fájlt kiválasztani a feltöltéshez.',
        msgSizeTooSmall: 'A fájl: "{name}" (<b>{size} KB</b>) mérete túl kicsi, nagyobbnak kell lennie, mint <b>{minSize} KB</b>.',
        msgSizeTooLarge: '"{name}" fájl (<b>{size} KB</b>) mérete nagyobb a megengedettnél <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Legalább <b>{n}</b> {files} ki kell választania a feltöltéshez.',
        msgFilesTooMany: 'A feltölteni kívánt fájlok száma <b>({n})</b> elérte a megengedett maximumot <b>{m}</b>.',
        msgFileNotFound: '"{name}" fájl nem található!',
        msgFileSecured: 'Biztonsági beállítások nem engedik olvasni a fájlt "{name}".',
        msgFileNotReadable: '"{name}" fájl nem olvasható.',
        msgFilePreviewAborted: '"{name}" fájl feltöltése megszakítva.',
        msgFilePreviewError: 'Hiba lépett fel a "{name}" fájl olvasása közben.',
        msgInvalidFileName: 'Hibás vagy nem támogatott karakterek a fájl nevében "{name}".',
        msgInvalidFileType: 'Nem megengedett fájl "{name}". Csak a "{types}" fájl típusok támogatottak.',
        msgInvalidFileExtension: 'Nem megengedett kiterjesztés / fájltípus "{name}". Csak a "{extensions}" kiterjesztés(ek) / fájltípus(ok) támogatottak.',
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
        msgUploadAborted: 'A fájl feltöltés megszakítva',
        msgUploadThreshold: 'Folyamatban...',
        msgUploadBegin: 'Inicializálás...',
        msgUploadEnd: 'Kész',
        msgUploadEmpty: 'Nincs érvényes adat a feltöltéshez.',
        msgUploadError: 'Error',
        msgValidationError: 'Érvényesítés hiba',
        msgLoading: '{index} / {files} töltése &hellip;',
        msgProgress: 'Feltöltés: {index} / {files} - {name} - {percent}% kész.',
        msgSelected: '{n} {files} kiválasztva.',
        msgFoldersNotAllowed: 'Csak fájlokat húzzon ide! Kihagyva {n} könyvtár.',
        msgImageWidthSmall: 'A kép szélességének "{name}" legalább {size} pixelnek kell lennie.',
        msgImageHeightSmall: 'A kép magasságának "{name}" legalább {size} pixelnek kell lennie.',
        msgImageWidthLarge: 'A kép szélessége "{name}" nem haladhatja meg a {size} pixelt.',
        msgImageHeightLarge: 'A kép magassága "{name}" nem haladhatja meg a {size} pixelt.',
        msgImageResizeError: 'Nem lehet megállapítani a kép méreteit az átméretezéshez.',
        msgImageResizeException: 'Hiba történt a méretezés közben.<pre>{errors}</pre>',
        msgAjaxError: 'Hiba történt a művelet közben ({operation}). Kérjük, próbálja később!',
        msgAjaxProgressError: 'Hiba! ({operation})',
        ajaxOperations: {
            deleteThumb: 'fájl törlés',
            uploadThumb: 'fájl feltöltés',
            uploadBatch: 'csoportos fájl feltöltés',
            uploadExtra: 'űrlap adat feltöltés'
        },
        dropZoneTitle: 'Húzzon ide fájlokat &hellip;',
        dropZoneClickTitle: '<br>(vagy kattintson ide a {files} tallózásához...)',
        fileActionSettings: {
            removeTitle: 'A fájl eltávolítása',
            uploadTitle: 'fájl feltöltése',
            uploadRetryTitle: 'Feltöltés újból',
            downloadTitle: 'Fájl letöltése',
            zoomTitle: 'Részletek megtekintése',
            dragTitle: 'Mozgatás / Átrendezés',
            indicatorNewTitle: 'Nem feltöltött',
            indicatorSuccessTitle: 'Feltöltött',
            indicatorErrorTitle: 'Feltöltés hiba',
            indicatorLoadingTitle: 'Feltöltés ...'
        },
        previewZoomButtonTitles: {
            prev: 'Elöző fájl megnézése',
            next: 'Következő fájl megnézése',
            toggleheader: 'Fejléc mutatása',
            fullscreen: 'Teljes képernyős mód bekapcsolása',
            borderless: 'Keret nélküli ablak mód bekapcsolása',
            close: 'Részletes előnézet bezárása'
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


    var weekEndings = 'vasárnap hétfőn kedden szerdán csütörtökön pénteken szombaton'.split(' ');
    function translate(number, withoutSuffix, key, isFuture) {
        var num = number;
        switch (key) {
            case 's':
                return (isFuture || withoutSuffix) ? 'néhány másodperc' : 'néhány másodperce';
            case 'ss':
                return num + (isFuture || withoutSuffix) ? ' másodperc' : ' másodperce';
            case 'm':
                return 'egy' + (isFuture || withoutSuffix ? ' perc' : ' perce');
            case 'mm':
                return num + (isFuture || withoutSuffix ? ' perc' : ' perce');
            case 'h':
                return 'egy' + (isFuture || withoutSuffix ? ' óra' : ' órája');
            case 'hh':
                return num + (isFuture || withoutSuffix ? ' óra' : ' órája');
            case 'd':
                return 'egy' + (isFuture || withoutSuffix ? ' nap' : ' napja');
            case 'dd':
                return num + (isFuture || withoutSuffix ? ' nap' : ' napja');
            case 'M':
                return 'egy' + (isFuture || withoutSuffix ? ' hónap' : ' hónapja');
            case 'MM':
                return num + (isFuture || withoutSuffix ? ' hónap' : ' hónapja');
            case 'y':
                return 'egy' + (isFuture || withoutSuffix ? ' év' : ' éve');
            case 'yy':
                return num + (isFuture || withoutSuffix ? ' év' : ' éve');
        }
        return '';
    }
    function week(isFuture) {
        return (isFuture ? '' : '[múlt] ') + '[' + weekEndings[this.day()] + '] LT[-kor]';
    }

    var hu = moment.defineLocale('hu', {
        months : 'január_február_március_április_május_június_július_augusztus_szeptember_október_november_december'.split('_'),
        monthsShort : 'jan_feb_márc_ápr_máj_jún_júl_aug_szept_okt_nov_dec'.split('_'),
        weekdays : 'vasárnap_hétfő_kedd_szerda_csütörtök_péntek_szombat'.split('_'),
        weekdaysShort : 'vas_hét_kedd_sze_csüt_pén_szo'.split('_'),
        weekdaysMin : 'v_h_k_sze_cs_p_szo'.split('_'),
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'YYYY.MM.DD.',
            LL : 'YYYY. MMMM D.',
            LLL : 'YYYY. MMMM D. H:mm',
            LLLL : 'YYYY. MMMM D., dddd H:mm'
        },
        meridiemParse: /de|du/i,
        isPM: function (input) {
            return input.charAt(1).toLowerCase() === 'u';
        },
        meridiem : function (hours, minutes, isLower) {
            if (hours < 12) {
                return isLower === true ? 'de' : 'DE';
            } else {
                return isLower === true ? 'du' : 'DU';
            }
        },
        calendar : {
            sameDay : '[ma] LT[-kor]',
            nextDay : '[holnap] LT[-kor]',
            nextWeek : function () {
                return week.call(this, true);
            },
            lastDay : '[tegnap] LT[-kor]',
            lastWeek : function () {
                return week.call(this, false);
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : '%s múlva',
            past : '%s',
            s : translate,
            ss : translate,
            m : translate,
            mm : translate,
            h : translate,
            hh : translate,
            d : translate,
            dd : translate,
            M : translate,
            MM : translate,
            y : translate,
            yy : translate
        },
        dayOfMonthOrdinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return hu;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/hu",[],function(){return{errorLoading:function(){return"Az eredmények betöltése nem sikerült."},inputTooLong:function(e){var t=e.input.length-e.maximum;return"Túl hosszú. "+t+" karakterrel több, mint kellene."},inputTooShort:function(e){var t=e.minimum-e.input.length;return"Túl rövid. Még "+t+" karakter hiányzik."},loadingMore:function(){return"Töltés…"},maximumSelected:function(e){return"Csak "+e.maximum+" elemet lehet kiválasztani."},noResults:function(){return"Nincs találat."},searching:function(){return"Keresés…"}}}),{define:e.define,require:e.require}})();
/*!
 * Bootstrap-select v1.13.5 (https://developer.snapappointments.com/bootstrap-select)
 *
 * Copyright 2012-2018 SnapAppointments, LLC
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
    noneSelectedText: 'Válasszon!',
    noneResultsText: 'Nincs találat {0}',
    countSelectedText: function (numSelected, numTotal) {
      return '{0} elem kiválasztva';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        'Legfeljebb {n} elem választható',
        'A csoportban legfeljebb {n} elem választható'
      ];
    },
    selectAllText: 'Mind',
    deselectAllText: 'Egyik sem',
    multipleSeparator: ', '
  };
})(jQuery);


}));
