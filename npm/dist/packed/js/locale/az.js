/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Azerbaijan (az)
 * Author: Megaplan, mborisv <bm@megaplan.ru>
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

QueryBuilder.regional['az'] = {
  "__locale": "Azerbaijan (az)",
  "__author": "Megaplan, mborisv <bm@megaplan.ru>",
  "add_rule": "Əlavə etmək",
  "add_group": "Qrup əlavə etmək",
  "delete_rule": "Silmək",
  "delete_group": "Silmək",
  "conditions": {
    "AND": "VƏ",
    "OR": "VƏ YA"
  },
  "operators": {
    "equal": "bərabərdir",
    "not_equal": "bərabər deyil",
    "in": "qeyd edilmişlərdən",
    "not_in": "qeyd olunmamışlardan",
    "less": "daha az",
    "less_or_equal": "daha az və ya bərabər",
    "greater": "daha çox",
    "greater_or_equal": "daha çox və ya bərabər",
    "between": "arasında",
    "begins_with": "başlayır",
    "not_begins_with": "başlamır",
    "contains": "ibarətdir",
    "not_contains": "yoxdur",
    "ends_with": "başa çatır",
    "not_ends_with": "başa çatmır",
    "is_empty": "boş sətir",
    "is_not_empty": "boş olmayan sətir",
    "is_null": "boşdur",
    "is_not_null": "boş deyil"
  },
  "errors": {
    "no_filter": "Filterlər seçilməyib",
    "empty_group": "Qrup boşdur",
    "radio_empty": "Məna seçilməyib",
    "checkbox_empty": "Məna seçilməyib",
    "select_empty": "Məna seçilməyib",
    "string_empty": "Doldurulmayıb",
    "string_exceed_min_length": "{0} daha çox simvol olmalıdır",
    "string_exceed_max_length": "{0} daha az simvol olmalıdır",
    "string_invalid_format": "Yanlış format ({0})",
    "number_nan": "Rəqəm deyil",
    "number_not_integer": "Rəqəm deyil",
    "number_not_double": "Rəqəm deyil",
    "number_exceed_min": "{0} daha çox olmalıdır",
    "number_exceed_max": "{0} daha az olmalıdır",
    "number_wrong_step": "{0} bölünən olmalıdır",
    "datetime_empty": "Doldurulmayıb",
    "datetime_invalid": "Yanlış tarix formatı ({0})",
    "datetime_exceed_min": "{0} sonra olmalıdır",
    "datetime_exceed_max": "{0} əvvəl olmalıdır",
    "boolean_not_valid": "Loqik olmayan",
    "operator_not_multiple": "\"{1}\" operatoru çoxlu məna daşımır"
  },
  "invert": "invert"
};

QueryBuilder.defaults({ lang_code: 'az' });
}));
/*!
 * FileInput Azerbaijan Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Elbrus <elbrusnt@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['az'] = {
        fileSingle: 'fayl',
        filePlural: 'fayl',
        browseLabel: 'Seç &hellip;',
        removeLabel: 'Sil',
        removeTitle: 'Seçilmiş faylları təmizlə',
        cancelLabel: 'İmtina et',
        cancelTitle: 'Cari yükləməni dayandır',
        uploadLabel: 'Yüklə',
        uploadTitle: 'Seçilmiş faylları yüklə',
        msgNo: 'xeyir',
        msgNoFilesSelected: 'Heç bir fayl seçilməmişdir',
        msgCancelled: 'İmtina edildi',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'İlkin baxış',
        msgFileRequired: 'Yükləmə üçün fayl seçməlisiniz.',
        msgSizeTooSmall: 'Seçdiyiniz "{name}" faylının həcmi (<b>{size} KB</b>)-dır,  minimum <b>{minSize} KB</b> olmalıdır.',
        msgSizeTooLarge: 'Seçdiyiniz "{name}" faylının həcmi (<b>{size} KB</b>)-dır,  maksimum <b>{maxSize} KB</b> olmalıdır.',
        msgFilesTooLess: 'Yükləmə üçün minimum <b>{n}</b> {files} seçməlisiniz.',
        msgFilesTooMany: 'Seçilmiş fayl sayı <b>({n})</b>. Maksimum <b>{m}</b> fayl seçmək mümkündür.',
        msgFileNotFound: 'Fayl "{name}" tapılmadı!',
        msgFileSecured: '"{name}" faylının istifadəsinə yetginiz yoxdur.',
        msgFileNotReadable: '"{name}" faylının istifadəsi mümkün deyil.',
        msgFilePreviewAborted: '"{name}" faylı üçün ilkin baxış ləğv olunub.',
        msgFilePreviewError: '"{name}" faylının oxunması mümkün olmadı.',
        msgInvalidFileName: '"{name}" faylının adında qadağan olunmuş simvollardan istifadə olunmuşdur.',
        msgInvalidFileType: '"{name}" faylının tipi dəstəklənmir. Yalnız "{types}" tipli faylları yükləmək mümkündür.',
        msgInvalidFileExtension: '"{name}" faylının genişlənməsi yanlışdır. Yalnız "{extensions}" fayl genişlənmə(si / ləri) qəbul olunur.',
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
        msgUploadAborted: 'Yükləmə dayandırılmışdır',
        msgUploadThreshold: 'Yükləmə...',
        msgUploadBegin: 'Yoxlama...',
        msgUploadEnd: 'Fayl(lar) yükləndi',
        msgUploadEmpty: 'Yükləmə üçün verilmiş məlumatlar yanlışdır',
        msgUploadError: 'Error',
        msgValidationError: 'Yoxlama nəticəsi səhvir',
        msgLoading: '{files} fayldan {index} yüklənir &hellip;',
        msgProgress: '{files} fayldan {index} - {name} - {percent}% yükləndi.',
        msgSelected: 'Faylların sayı: {n}',
        msgFoldersNotAllowed: 'Ancaq faylların daşınmasına icazə verilir! {n} qovluq yüklənmədi.',
        msgImageWidthSmall: '{name} faylının eni {size} px -dən kiçik olmamalıdır.',
        msgImageHeightSmall: '{name} faylının hündürlüyü {size} px -dən kiçik olmamalıdır.',
        msgImageWidthLarge: '"{name}" faylının eni {size} px -dən böyük olmamalıdır.',
        msgImageHeightLarge: '"{name}" faylının hündürlüyü {size} px -dən böyük olmamalıdır.',
        msgImageResizeError: 'Faylın ölçülərini dəyişmək üçün ölçüləri hesablamaq mümkün olmadı.',
        msgImageResizeException: 'Faylın ölçülərini dəyişmək mümkün olmadı.<pre>{errors}</pre>',
        msgAjaxError: '{operation} əməliyyatı zamanı səhv baş verdi. Təkrar yoxlayın!',
        msgAjaxProgressError: '{operation} əməliyyatı yerinə yetirmək mümkün olmadı.',
        ajaxOperations: {
            deleteThumb: 'faylı sil',
            uploadThumb: 'faylı yüklə',
            uploadBatch: 'bir neçə faylı yüklə',
            uploadExtra: 'məlumatların yüklənməsi'
        },
        dropZoneTitle: 'Faylları bura daşıyın &hellip;',
        dropZoneClickTitle: '<br>(Və ya seçin {files})',
        fileActionSettings: {
            removeTitle: 'Faylı sil',
            uploadTitle: 'Faylı yüklə',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'məlumatlara bax',
            dragTitle: 'Yerini dəyiş və ya sırala',
            indicatorNewTitle: 'Davam edir',
            indicatorSuccessTitle: 'Tamamlandı',
            indicatorErrorTitle: 'Yükləmə xətası',
            indicatorLoadingTitle: 'Yükləmə ...'
        },
        previewZoomButtonTitles: {
            prev: 'Əvvəlki fayla bax',
            next: 'Növbəti fayla bax',
            toggleheader: 'Başlığı dəyiş',
            fullscreen: 'Tam ekranı dəyiş',
            borderless: 'Bölmələrsiz rejimi dəyiş',
            close: 'Ətraflı baxışı bağla'
        }
    };
})(window.jQuery);

//! moment.js locale configuration
//! locale : Azerbaijani [az]
//! author : topchiyev : https://github.com/topchiyev

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';

    //! moment.js locale configuration

    var suffixes = {
        1: '-inci',
        5: '-inci',
        8: '-inci',
        70: '-inci',
        80: '-inci',
        2: '-nci',
        7: '-nci',
        20: '-nci',
        50: '-nci',
        3: '-üncü',
        4: '-üncü',
        100: '-üncü',
        6: '-ncı',
        9: '-uncu',
        10: '-uncu',
        30: '-uncu',
        60: '-ıncı',
        90: '-ıncı',
    };

    var az = moment.defineLocale('az', {
        months: 'yanvar_fevral_mart_aprel_may_iyun_iyul_avqust_sentyabr_oktyabr_noyabr_dekabr'.split(
            '_'
        ),
        monthsShort: 'yan_fev_mar_apr_may_iyn_iyl_avq_sen_okt_noy_dek'.split('_'),
        weekdays: 'Bazar_Bazar ertəsi_Çərşənbə axşamı_Çərşənbə_Cümə axşamı_Cümə_Şənbə'.split(
            '_'
        ),
        weekdaysShort: 'Baz_BzE_ÇAx_Çər_CAx_Cüm_Şən'.split('_'),
        weekdaysMin: 'Bz_BE_ÇA_Çə_CA_Cü_Şə'.split('_'),
        weekdaysParseExact: true,
        longDateFormat: {
            LT: 'HH:mm',
            LTS: 'HH:mm:ss',
            L: 'DD.MM.YYYY',
            LL: 'D MMMM YYYY',
            LLL: 'D MMMM YYYY HH:mm',
            LLLL: 'dddd, D MMMM YYYY HH:mm',
        },
        calendar: {
            sameDay: '[bugün saat] LT',
            nextDay: '[sabah saat] LT',
            nextWeek: '[gələn həftə] dddd [saat] LT',
            lastDay: '[dünən] LT',
            lastWeek: '[keçən həftə] dddd [saat] LT',
            sameElse: 'L',
        },
        relativeTime: {
            future: '%s sonra',
            past: '%s əvvəl',
            s: 'birneçə saniyə',
            ss: '%d saniyə',
            m: 'bir dəqiqə',
            mm: '%d dəqiqə',
            h: 'bir saat',
            hh: '%d saat',
            d: 'bir gün',
            dd: '%d gün',
            M: 'bir ay',
            MM: '%d ay',
            y: 'bir il',
            yy: '%d il',
        },
        meridiemParse: /gecə|səhər|gündüz|axşam/,
        isPM: function (input) {
            return /^(gündüz|axşam)$/.test(input);
        },
        meridiem: function (hour, minute, isLower) {
            if (hour < 4) {
                return 'gecə';
            } else if (hour < 12) {
                return 'səhər';
            } else if (hour < 17) {
                return 'gündüz';
            } else {
                return 'axşam';
            }
        },
        dayOfMonthOrdinalParse: /\d{1,2}-(ıncı|inci|nci|üncü|ncı|uncu)/,
        ordinal: function (number) {
            if (number === 0) {
                // special case for zero
                return number + '-ıncı';
            }
            var a = number % 10,
                b = (number % 100) - a,
                c = number >= 100 ? 100 : null;
            return number + (suffixes[a] || suffixes[b] || suffixes[c]);
        },
        week: {
            dow: 1, // Monday is the first day of the week.
            doy: 7, // The week that contains Jan 7th is the first week of the year.
        },
    });

    return az;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/az",[],function(){return{inputTooLong:function(n){return n.input.length-n.maximum+" simvol silin"},inputTooShort:function(n){return n.minimum-n.input.length+" simvol daxil edin"},loadingMore:function(){return"Daha çox nəticə yüklənir…"},maximumSelected:function(n){return"Sadəcə "+n.maximum+" element seçə bilərsiniz"},noResults:function(){return"Nəticə tapılmadı"},searching:function(){return"Axtarılır…"},removeAllItems:function(){return"Bütün elementləri sil"}}}),n.define,n.require}();