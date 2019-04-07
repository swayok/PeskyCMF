/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Turkish (tr)
 * Author: Aykut Alpgiray Ateş
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

QueryBuilder.regional['tr'] = {
  "__locale": "Turkish (tr)",
  "__author": "Aykut Alpgiray Ateş",
  "add_rule": "Kural Ekle",
  "add_group": "Grup Ekle",
  "delete_rule": "Sil",
  "delete_group": "Sil",
  "conditions": {
    "AND": "Ve",
    "OR": "Veya"
  },
  "operators": {
    "equal": "eşit",
    "not_equal": "eşit değil",
    "in": "içinde",
    "not_in": "içinde değil",
    "less": "küçük",
    "less_or_equal": "küçük veya eşit",
    "greater": "büyük",
    "greater_or_equal": "büyük veya eşit",
    "between": "arasında",
    "not_between": "arasında değil",
    "begins_with": "ile başlayan",
    "not_begins_with": "ile başlamayan",
    "contains": "içeren",
    "not_contains": "içermeyen",
    "ends_with": "ile biten",
    "not_ends_with": "ile bitmeyen",
    "is_empty": "boş ise",
    "is_not_empty": "boş değil ise",
    "is_null": "var ise",
    "is_not_null": "yok ise"
  },
  "errors": {
    "no_filter": "Bir filtre seçili değil",
    "empty_group": "Grup bir eleman içermiyor",
    "radio_empty": "Seçim yapılmalı",
    "checkbox_empty": "Seçim yapılmalı",
    "select_empty": "Seçim yapılmalı",
    "string_empty": "Bir metin girilmeli",
    "string_exceed_min_length": "En az {0} karakter girilmeli",
    "string_exceed_max_length": "En fazla {0} karakter girilebilir",
    "string_invalid_format": "Uyumsuz format ({0})",
    "number_nan": "Sayı değil",
    "number_not_integer": "Tam sayı değil",
    "number_not_double": "Ondalıklı sayı değil",
    "number_exceed_min": "Sayı {0}'den/dan daha büyük olmalı",
    "number_exceed_max": "Sayı {0}'den/dan daha küçük olmalı",
    "number_wrong_step": "{0} veya katı olmalı",
    "number_between_invalid": "Geçersiz değerler, {0} değeri {1} değerinden büyük",
    "datetime_empty": "Tarih Seçilmemiş",
    "datetime_invalid": "Uygun olmayan tarih formatı ({0})",
    "datetime_exceed_min": "{0} Tarihinden daha sonrası olmalı.",
    "datetime_exceed_max": "{0} Tarihinden daha öncesi olmalı.",
    "datetime_between_invalid": "Geçersiz değerler, {0} değeri {1} değerinden büyük",
    "boolean_not_valid": "Değer Doğru/Yanlış(bool) olmalı",
    "operator_not_multiple": "Operatör \"{1}\" birden fazla değer kabul etmiyor"
  },
  "invert": "Ters Çevir"
};

QueryBuilder.defaults({ lang_code: 'tr' });
}));
/*!
 * FileInput Turkish Translations
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

    $.fn.fileinputLocales['tr'] = {
        fileSingle: 'dosya',
        filePlural: 'dosyalar',
        browseLabel: 'Gözat &hellip;',
        removeLabel: 'Sil',
        removeTitle: 'Seçilen dosyaları sil',
        cancelLabel: 'İptal',
        cancelTitle: 'Devam eden yüklemeyi iptal et',
        uploadLabel: 'Yükle',
        uploadTitle: 'Seçilen dosyaları yükle',
        msgNo: 'Hayır',
        msgNoFilesSelected: '',
        msgCancelled: 'İptal edildi',
        msgPlaceholder: 'Seçilen {files}...',
        msgZoomModalHeading: 'Detaylı Önizleme',
        msgFileRequired: 'Yüklemek için bir dosya seçmelisiniz.',
        msgSizeTooSmall: '"{name}"(<b>{size} KB</b>) dosyası çok küçük  ve <b>{minSize} KB</b> boyutundan büyük olmalıdır.',
        msgSizeTooLarge: '"{name}" dosyasının boyutu (<b>{size} KB</b>) izin verilen azami dosya boyutu olan <b>{maxSize} KB</b>\'tan büyük.',
        msgFilesTooLess: 'Yüklemek için en az <b>{n}</b> {files} dosya seçmelisiniz.',
        msgFilesTooMany: 'Yüklemek için seçtiğiniz dosya sayısı <b>({n})</b> azami limitin <b>({m})</b> altında olmalıdır.',
        msgFileNotFound: '"{name}" dosyası bulunamadı!',
        msgFileSecured: 'Güvenlik kısıtlamaları "{name}" dosyasının okunmasını engelliyor.',
        msgFileNotReadable: '"{name}" dosyası okunabilir değil.',
        msgFilePreviewAborted: '"{name}" dosyası için önizleme iptal edildi.',
        msgFilePreviewError: '"{name}" dosyası okunurken bir hata oluştu.',
        msgInvalidFileName: '"{name}" dosya adında geçersiz veya desteklenmeyen karakterler var.',
        msgInvalidFileType: '"{name}" dosyasının türü geçerli değil. Yalnızca "{types}" türünde dosyalara izin veriliyor.',
        msgInvalidFileExtension: '"{name}" dosyasının uzantısı geçersiz. Yalnızca "{extensions}" uzantılı dosyalara izin veriliyor.',
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
        msgUploadAborted: 'Dosya yükleme iptal edildi',
        msgUploadThreshold: 'İşlem yapılıyor...',
        msgUploadBegin: 'Başlıyor...',
        msgUploadEnd: 'Başarılı',
        msgUploadEmpty: 'Yüklemek için geçerli veri mevcut değil.',
        msgUploadError: 'Hata',
        msgValidationError: 'Doğrulama Hatası',
        msgLoading: 'Dosya yükleniyor {index} / {files} &hellip;',
        msgProgress: 'Dosya yükleniyor {index} / {files} - {name} - %{percent} tamamlandı.',
        msgSelected: '{n} {files} seçildi',
        msgFoldersNotAllowed: 'Yalnızca dosyaları sürükleyip bırakabilirsiniz! {n} dizin(ler) göz ardı edildi.',
        msgImageWidthSmall: '"{name}" adlı görüntü dosyasının genişliği en az {size} piksel olmalıdır.',
        msgImageHeightSmall: '"{name}" adlı görüntü dosyasının yüksekliği en az {size} piksel olmalıdır.',
        msgImageWidthLarge: '"{name}" adlı görüntü dosyasının genişliği {size} pikseli geçemez.',
        msgImageHeightLarge: '"{name}" adlı görüntü dosyasının yüksekliği {size} pikseli geçemez.',
        msgImageResizeError: 'Görüntü boyutlarını yeniden boyutlandıramadı.',
        msgImageResizeException: 'Görüntü boyutlandırma sırasında hata.<pre>{errors}</pre>',
        msgAjaxError: '{operation} işlemi ile ilgili bir şeyler ters gitti. Lütfen daha sonra tekrar deneyiniz!',
        msgAjaxProgressError: '{operation} işlemi başarısız oldu.',
        ajaxOperations: {
            deleteThumb: 'dosya silme',
            uploadThumb: 'dosya yükleme',
            uploadBatch: 'toplu dosya yükleme',
            uploadExtra: 'form verisi yükleme'
        },
        dropZoneTitle: 'Dosyaları buraya sürükleyip bırakın',
        dropZoneClickTitle: '<br>(ya da {files} seçmek için tıklayınız)',
        fileActionSettings: {
            removeTitle: 'Dosyayı kaldır',
            uploadTitle: 'Dosyayı yükle',
            uploadRetryTitle: 'Retry upload',
            zoomTitle: 'Ayrıntıları görüntüle',
            dragTitle: 'Taşı / Yeniden düzenle',
            indicatorNewTitle: 'Henüz yüklenmedi',
            indicatorSuccessTitle: 'Yüklendi',
            indicatorErrorTitle: 'Yükleme Hatası',
            indicatorLoadingTitle: 'Yükleniyor ...'
        },
        previewZoomButtonTitles: {
            prev: 'Önceki dosyayı göster',
            next: 'Sonraki dosyayı göster',
            toggleheader: 'Üst bilgi geçiş',
            fullscreen: 'Tam ekran geçiş',
            borderless: 'Çerçevesiz moda geçiş',
            close: 'Detaylı önizlemeyi kapat'
        }
    };
})(window.jQuery);


;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';

    var suffixes = {
        1: '\'inci',
        5: '\'inci',
        8: '\'inci',
        70: '\'inci',
        80: '\'inci',
        2: '\'nci',
        7: '\'nci',
        20: '\'nci',
        50: '\'nci',
        3: '\'üncü',
        4: '\'üncü',
        100: '\'üncü',
        6: '\'ncı',
        9: '\'uncu',
        10: '\'uncu',
        30: '\'uncu',
        60: '\'ıncı',
        90: '\'ıncı'
    };

    var tr = moment.defineLocale('tr', {
        months : 'Ocak_Şubat_Mart_Nisan_Mayıs_Haziran_Temmuz_Ağustos_Eylül_Ekim_Kasım_Aralık'.split('_'),
        monthsShort : 'Oca_Şub_Mar_Nis_May_Haz_Tem_Ağu_Eyl_Eki_Kas_Ara'.split('_'),
        weekdays : 'Pazar_Pazartesi_Salı_Çarşamba_Perşembe_Cuma_Cumartesi'.split('_'),
        weekdaysShort : 'Paz_Pts_Sal_Çar_Per_Cum_Cts'.split('_'),
        weekdaysMin : 'Pz_Pt_Sa_Ça_Pe_Cu_Ct'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd, D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay : '[bugün saat] LT',
            nextDay : '[yarın saat] LT',
            nextWeek : '[gelecek] dddd [saat] LT',
            lastDay : '[dün] LT',
            lastWeek : '[geçen] dddd [saat] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : '%s sonra',
            past : '%s önce',
            s : 'birkaç saniye',
            ss : '%d saniye',
            m : 'bir dakika',
            mm : '%d dakika',
            h : 'bir saat',
            hh : '%d saat',
            d : 'bir gün',
            dd : '%d gün',
            M : 'bir ay',
            MM : '%d ay',
            y : 'bir yıl',
            yy : '%d yıl'
        },
        ordinal: function (number, period) {
            switch (period) {
                case 'd':
                case 'D':
                case 'Do':
                case 'DD':
                    return number;
                default:
                    if (number === 0) {  // special case for zero
                        return number + '\'ıncı';
                    }
                    var a = number % 10,
                        b = number % 100 - a,
                        c = number >= 100 ? 100 : null;
                    return number + (suffixes[a] || suffixes[b] || suffixes[c]);
            }
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 7th is the first week of the year.
        }
    });

    return tr;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/tr",[],function(){return{errorLoading:function(){return"Sonuç yüklenemedi"},inputTooLong:function(e){var t=e.input.length-e.maximum,n=t+" karakter daha girmelisiniz";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="En az "+t+" karakter daha girmelisiniz";return n},loadingMore:function(){return"Daha fazla…"},maximumSelected:function(e){var t="Sadece "+e.maximum+" seçim yapabilirsiniz";return t},noResults:function(){return"Sonuç bulunamadı"},searching:function(){return"Aranıyor…"}}}),{define:e.define,require:e.require}})();
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
    noneSelectedText: 'Hiçbiri seçilmedi',
    noneResultsText: 'Hiçbir sonuç bulunamadı {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} öğe seçildi' : '{0} öğe seçildi';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Limit aşıldı (maksimum {n} sayıda öğe )' : 'Limit aşıldı (maksimum {n} sayıda öğe)',
        (numGroup == 1) ? 'Grup limiti aşıldı (maksimum {n} sayıda öğe)' : 'Grup limiti aşıldı (maksimum {n} sayıda öğe)'
      ];
    },
    selectAllText: 'Tümünü Seç',
    deselectAllText: 'Seçiniz',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-tr_TR.js.map
/*!
 * Ajax Bootstrap Select
 *
 * Extends existing [Bootstrap Select] implementations by adding the ability to search via AJAX requests as you type. Originally for CROSCON.
 *
 * @version 1.4.4
 * @author Adam Heim - https://github.com/truckingsim
 * @link https://github.com/truckingsim/Ajax-Bootstrap-Select
 * @copyright 2018 Adam Heim
 * @license Released under the MIT license.
 *
 * Contributors:
 *   Mark Carver - https://github.com/markcarver
 *
 * Last build: 2018-06-12 11:53:57 AM EDT
 */
!(function ($) {
/*!
 * Turkish translation for the "tr-TR" and "tr" language codes.
 * Burak Çakırel <burakcakirel@gmail.com>
 */
$.fn.ajaxSelectPicker.locale['tr-TR'] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * The text to use for the label of the option group when currently selected options are preserved.
     */
    currentlySelected: 'Seçili olanlar',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * The text to use as the title for the select element when there are no items to display.
     */
    emptyTitle: 'Seç ve yazmaya başla',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = ''Unable to retrieve results'
     * @markdown
     * The text to use in the status container when a request returns with an error.
     */
    errorText: 'Sonuçlar alınamadı',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * The text to use for the search input placeholder attribute.
     */
    searchPlaceholder: 'Ara...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * The text used in the status container when it is initialized.
     */
    statusInitialized: 'Arama için yazmaya başla',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusNoResults: 'Sonuç yok',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * The text to use in the status container when a request is being initiated.
     */
    statusSearching: 'Aranıyor...',
	
	/**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'Lütfen daha fazla karakter girin'
};
$.fn.ajaxSelectPicker.locale.tr = $.fn.ajaxSelectPicker.locale['tr-TR'];
})(jQuery);
