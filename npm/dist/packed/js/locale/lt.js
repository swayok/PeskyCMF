/*!
 * FileInput <_LANG_> Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Mindaugas Varkalys <varkalys.mindaugas@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['lt'] = {
        fileSingle: 'failas',
        filePlural: 'failai',
        browseLabel: 'Naršyti &hellip;',
        removeLabel: 'Šalinti',
        removeTitle: 'Pašalinti pasirinktus failus',
        cancelLabel: 'Atšaukti',
        cancelTitle: 'Atšaukti vykstantį įkėlimą',
        uploadLabel: 'Įkelti',
        uploadTitle: 'Įkelti pasirinktus failus',
        msgNo: 'Ne',
        msgNoFilesSelected: 'Nepasirinkta jokių failų',
        msgCancelled: 'Atšaukta',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Detali Peržiūra',
        msgFileRequired: 'Pasirinkite failą įkėlimui.',
        msgSizeTooSmall: 'Failas "{name}" (<b>{size} KB</b>) yra per mažas ir turi būti didesnis nei <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Failas "{name}" (<b>{size} KB</b>) viršija maksimalų leidžiamą įkeliamo failo dydį <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Turite pasirinkti bent <b>{n}</b> failus įkėlimui.',
        msgFilesTooMany: 'Įkėlimui pasirinktų failų skaičius <b>({n})</b> viršija maksimalų leidžiamą limitą <b>{m}</b>.',
        msgFileNotFound: 'Failas "{name}" nerastas!',
        msgFileSecured: 'Saugumo apribojimai neleidžia perskaityti failo "{name}".',
        msgFileNotReadable: 'Failas "{name}" neperskaitomas.',
        msgFilePreviewAborted: 'Failo peržiūra nutraukta "{name}".',
        msgFilePreviewError: 'Įvyko klaida skaitant failą "{name}".',
        msgInvalidFileName: 'Klaidingi arba nepalaikomi simboliai failo pavadinime "{name}".',
        msgInvalidFileType: 'Klaidingas failo "{name}" tipas. Tik "{types}" tipai yra palaikomi.',
        msgInvalidFileExtension: 'Klaidingas failo "{name}" plėtinys. Tik "{extensions}" plėtiniai yra palaikomi.',
        msgFileTypes: {
            'image': 'paveikslėlis',
            'html': 'HTML',
            'text': 'tekstas',
            'video': 'vaizdo įrašas',
            'audio': 'garso įrašas',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'objektas'
        },
        msgUploadAborted: 'Failo įkėlimas buvo nutrauktas',
        msgUploadThreshold: 'Vykdoma...',
        msgUploadBegin: 'Inicijuojama...',
        msgUploadEnd: 'Baigta',
        msgUploadEmpty: 'Nėra teisingų duomenų įkėlimui.',
        msgUploadError: 'Klaida',
        msgValidationError: 'Validacijos Klaida',
        msgLoading: 'Keliamas failas {index} iš {files} &hellip;',
        msgProgress: 'Keliamas failas {index} iš {files} - {name} - {percent}% baigta.',
        msgSelected: 'Pasirinkti {n} {files}',
        msgFoldersNotAllowed: 'Tempkite tik failus! Praleisti {n} nutempti aplankalas(-i).',
        msgImageWidthSmall: 'Paveikslėlio "{name}" plotis turi būti bent {size} px.',
        msgImageHeightSmall: 'Paveikslėlio "{name}" aukštis turi būti bent {size} px.',
        msgImageWidthLarge: 'Paveikslėlio "{name}" plotis negali viršyti {size} px.',
        msgImageHeightLarge: 'Paveikslėlio "{name}" aukštis negali viršyti {size} px.',
        msgImageResizeError: 'Nepavyksta gauti paveikslėlio matmetų, kad pakeisti jo matmemis.',
        msgImageResizeException: 'Klaida keičiant paveikslėlio matmenis.<pre>{errors}</pre>',
        msgAjaxError: 'Kažkas nutiko vykdant {operation} operaciją. Prašome pabandyti vėliau!',
        msgAjaxProgressError: '{operation} operacija nesėkminga',
        ajaxOperations: {
            deleteThumb: 'failo trynimo',
            uploadThumb: 'failo įkėlimo',
            uploadBatch: 'failų rinkinio įkėlimo',
            uploadExtra: 'formos duomenų įkėlimo'
        },
        dropZoneTitle: 'Tempkite failus čia &hellip;',
        dropZoneClickTitle: '<br>(arba paspauskite, kad pasirinktumėte failus)',
        fileActionSettings: {
            removeTitle: 'Šalinti failą',
            uploadTitle: 'Įkelti failą',
            uploadRetryTitle: 'Bandyti įkelti vėl',
            zoomTitle: 'Peržiūrėti detales',
            dragTitle: 'Perstumti',
            indicatorNewTitle: 'Dar neįkelta',
            indicatorSuccessTitle: 'Įkelta',
            indicatorErrorTitle: 'Įkėlimo Klaida',
            indicatorLoadingTitle: 'Įkeliama ...'
        },
        previewZoomButtonTitles: {
            prev: 'Peržiūrėti ankstesnį failą',
            next: 'Peržiūrėti kitą failą',
            toggleheader: 'Perjungti viršutinę juostą',
            fullscreen: 'Perjungti pilno ekrano rėžimą',
            borderless: 'Perjungti berėmį režimą',
            close: 'Uždaryti detalią peržiūrą'
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


    var units = {
        'ss' : 'sekundė_sekundžių_sekundes',
        'm' : 'minutė_minutės_minutę',
        'mm': 'minutės_minučių_minutes',
        'h' : 'valanda_valandos_valandą',
        'hh': 'valandos_valandų_valandas',
        'd' : 'diena_dienos_dieną',
        'dd': 'dienos_dienų_dienas',
        'M' : 'mėnuo_mėnesio_mėnesį',
        'MM': 'mėnesiai_mėnesių_mėnesius',
        'y' : 'metai_metų_metus',
        'yy': 'metai_metų_metus'
    };
    function translateSeconds(number, withoutSuffix, key, isFuture) {
        if (withoutSuffix) {
            return 'kelios sekundės';
        } else {
            return isFuture ? 'kelių sekundžių' : 'kelias sekundes';
        }
    }
    function translateSingular(number, withoutSuffix, key, isFuture) {
        return withoutSuffix ? forms(key)[0] : (isFuture ? forms(key)[1] : forms(key)[2]);
    }
    function special(number) {
        return number % 10 === 0 || (number > 10 && number < 20);
    }
    function forms(key) {
        return units[key].split('_');
    }
    function translate(number, withoutSuffix, key, isFuture) {
        var result = number + ' ';
        if (number === 1) {
            return result + translateSingular(number, withoutSuffix, key[0], isFuture);
        } else if (withoutSuffix) {
            return result + (special(number) ? forms(key)[1] : forms(key)[0]);
        } else {
            if (isFuture) {
                return result + forms(key)[1];
            } else {
                return result + (special(number) ? forms(key)[1] : forms(key)[2]);
            }
        }
    }
    var lt = moment.defineLocale('lt', {
        months : {
            format: 'sausio_vasario_kovo_balandžio_gegužės_birželio_liepos_rugpjūčio_rugsėjo_spalio_lapkričio_gruodžio'.split('_'),
            standalone: 'sausis_vasaris_kovas_balandis_gegužė_birželis_liepa_rugpjūtis_rugsėjis_spalis_lapkritis_gruodis'.split('_'),
            isFormat: /D[oD]?(\[[^\[\]]*\]|\s)+MMMM?|MMMM?(\[[^\[\]]*\]|\s)+D[oD]?/
        },
        monthsShort : 'sau_vas_kov_bal_geg_bir_lie_rgp_rgs_spa_lap_grd'.split('_'),
        weekdays : {
            format: 'sekmadienį_pirmadienį_antradienį_trečiadienį_ketvirtadienį_penktadienį_šeštadienį'.split('_'),
            standalone: 'sekmadienis_pirmadienis_antradienis_trečiadienis_ketvirtadienis_penktadienis_šeštadienis'.split('_'),
            isFormat: /dddd HH:mm/
        },
        weekdaysShort : 'Sek_Pir_Ant_Tre_Ket_Pen_Šeš'.split('_'),
        weekdaysMin : 'S_P_A_T_K_Pn_Š'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'YYYY-MM-DD',
            LL : 'YYYY [m.] MMMM D [d.]',
            LLL : 'YYYY [m.] MMMM D [d.], HH:mm [val.]',
            LLLL : 'YYYY [m.] MMMM D [d.], dddd, HH:mm [val.]',
            l : 'YYYY-MM-DD',
            ll : 'YYYY [m.] MMMM D [d.]',
            lll : 'YYYY [m.] MMMM D [d.], HH:mm [val.]',
            llll : 'YYYY [m.] MMMM D [d.], ddd, HH:mm [val.]'
        },
        calendar : {
            sameDay : '[Šiandien] LT',
            nextDay : '[Rytoj] LT',
            nextWeek : 'dddd LT',
            lastDay : '[Vakar] LT',
            lastWeek : '[Praėjusį] dddd LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'po %s',
            past : 'prieš %s',
            s : translateSeconds,
            ss : translate,
            m : translateSingular,
            mm : translate,
            h : translateSingular,
            hh : translate,
            d : translateSingular,
            dd : translate,
            M : translateSingular,
            MM : translate,
            y : translateSingular,
            yy : translate
        },
        dayOfMonthOrdinalParse: /\d{1,2}-oji/,
        ordinal : function (number) {
            return number + '-oji';
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return lt;

})));

/*! Select2 4.0.11 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/lt",[],function(){function n(n,e,i,t){return n%10==1&&(n%100<11||n%100>19)?e:n%10>=2&&n%10<=9&&(n%100<11||n%100>19)?i:t}return{inputTooLong:function(e){var i=e.input.length-e.maximum,t="Pašalinkite "+i+" simbol";return t+=n(i,"į","ius","ių")},inputTooShort:function(e){var i=e.minimum-e.input.length,t="Įrašykite dar "+i+" simbol";return t+=n(i,"į","ius","ių")},loadingMore:function(){return"Kraunama daugiau rezultatų…"},maximumSelected:function(e){var i="Jūs galite pasirinkti tik "+e.maximum+" element";return i+=n(e.maximum,"ą","us","ų")},noResults:function(){return"Atitikmenų nerasta"},searching:function(){return"Ieškoma…"},removeAllItems:function(){return"Pašalinti visus elementus"}}}),n.define,n.require}();
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
    noneSelectedText: 'Niekas nepasirinkta',
    noneResultsText: 'Niekas nesutapo su {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} elementas pasirinktas' : '{0} elementai(-ų) pasirinkta';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Pasiekta riba ({n} elementas daugiausiai)' : 'Riba pasiekta ({n} elementai(-ų) daugiausiai)',
        (numGroup == 1) ? 'Grupės riba pasiekta ({n} elementas daugiausiai)' : 'Grupės riba pasiekta ({n} elementai(-ų) daugiausiai)'
      ];
    },
    selectAllText: 'Pasirinkti visus',
    deselectAllText: 'Atmesti visus',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-lt_LT.js.map