/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Arabic (ar)
 * Author: Mohamed YOUNES, https://github.com/MedYOUNES
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

QueryBuilder.regional['ar'] = {
  "__locale": "Arabic (ar)",
  "__author": "Mohamed YOUNES, https://github.com/MedYOUNES",
  "add_rule": "إضافة حُكم",
  "add_group": "إضافة زُمْرَة",
  "delete_rule": "حذف",
  "delete_group": "حذف",
  "conditions": {
    "AND": "و",
    "OR": "أو"
  },
  "operators": {
    "equal": "يساوي",
    "not_equal": "غير مساوٍ",
    "in": "في",
    "not_in": "ليس في",
    "less": "أقل من",
    "less_or_equal": "أصغر أو مساو",
    "greater": "أكبر",
    "greater_or_equal": "أكبر أو مساو",
    "between": "محصور بين",
    "not_between": "غير محصور بين",
    "begins_with": "يبدأ بـ",
    "not_begins_with": "لا يبدأ بـ",
    "contains": "يحتوي على",
    "not_contains": "لا يحتوي على",
    "ends_with": "ينتهي بـ",
    "not_ends_with": "لا ينتهي بـ",
    "is_empty": "فارغ",
    "is_not_empty": "غير فارغ",
    "is_null": "صفر",
    "is_not_null": "ليس صفرا"
  },
  "errors": {
    "no_filter": "لم تحدد أي مرشح",
    "empty_group": "الزمرة فارغة",
    "radio_empty": "لم تحدد أي قيمة",
    "checkbox_empty": "لم تحدد أي قيمة",
    "select_empty": "لم تحدد أي قيمة",
    "string_empty": "النص فارغ",
    "string_exceed_min_length": "النص دون الأدنى المسموح به",
    "string_exceed_max_length": "النص فوق الأقصى المسموح به",
    "string_invalid_format": "تركيبة غير صحيحة",
    "number_nan": "ليس عددا",
    "number_not_integer": "ليس عددا صحيحا",
    "number_not_double": "ليس عددا كسريا",
    "number_exceed_min": "العدد أصغر من الأدنى المسموح به",
    "number_exceed_max": "العدد أكبر من الأقصى المسموح به",
    "number_wrong_step": "أخطأت في حساب مضاعفات العدد",
    "datetime_empty": "لم تحدد التاريخ",
    "datetime_invalid": "صيغة التاريخ غير صحيحة",
    "datetime_exceed_min": "التاريخ دون الأدنى المسموح به",
    "datetime_exceed_max": "التاريخ أكبر من الأقصى المسموح به",
    "boolean_not_valid": "ليست قيمة منطقية ثنائية",
    "operator_not_multiple": "العامل ليس متعدد القيَم"
  },
  "invert": "قَلْبُ"
};

QueryBuilder.defaults({ lang_code: 'ar' });
}));
/*!
 * FileInput Arabic Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Yasser Lotfy <y_l@live.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['ar'] = {
        fileSingle: 'ملف',
        filePlural: 'ملفات',
        browseLabel: 'تصفح &hellip;',
        removeLabel: 'إزالة',
        removeTitle: 'إزالة الملفات المختارة',
        cancelLabel: 'إلغاء',
        cancelTitle: 'إنهاء الرفع الحالي',
        uploadLabel: 'رفع',
        uploadTitle: 'رفع الملفات المختارة',
        msgNo: 'لا',
        msgNoFilesSelected: '',
        msgCancelled: 'ألغيت',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'معاينة تفصيلية',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'الملف "{name}" (<b>{size} ك.ب</b>) تعدى الحد الأقصى المسموح للرفع <b>{maxSize} ك.ب</b>.',
        msgFilesTooLess: 'يجب عليك اختيار <b>{n}</b> {files} على الأقل للرفع.',
        msgFilesTooMany: 'عدد الملفات المختارة للرفع <b>({n})</b> تعدت الحد الأقصى المسموح به لعدد <b>{m}</b>.',
        msgFileNotFound: 'الملف "{name}" غير موجود!',
        msgFileSecured: 'قيود أمنية تمنع قراءة الملف "{name}".',
        msgFileNotReadable: 'الملف "{name}" غير قابل للقراءة.',
        msgFilePreviewAborted: 'تم إلغاء معاينة الملف "{name}".',
        msgFilePreviewError: 'حدث خطأ أثناء قراءة الملف "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'نوعية غير صالحة للملف "{name}". فقط هذه النوعيات مدعومة "{types}".',
        msgInvalidFileExtension: 'امتداد غير صالح للملف "{name}". فقط هذه الملفات مدعومة "{extensions}".',
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
        msgUploadAborted: 'تم إلغاء رفع الملف',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'خطأ التحقق من صحة',
        msgLoading: 'تحميل ملف {index} من {files} &hellip;',
        msgProgress: 'تحميل ملف {index} من {files} - {name} - {percent}% منتهي.',
        msgSelected: '{n} {files} مختار(ة)',
        msgFoldersNotAllowed: 'اسحب وأفلت الملفات فقط! تم تخطي {n} مجلد(ات).',
        msgImageWidthSmall: 'عرض ملف الصورة "{name}" يجب أن يكون على الأقل {size} px.',
        msgImageHeightSmall: 'طول ملف الصورة "{name}" يجب أن يكون على الأقل {size} px.',
        msgImageWidthLarge: 'عرض ملف الصورة "{name}" لا يمكن أن يتعدى {size} px.',
        msgImageHeightLarge: 'طول ملف الصورة "{name}" لا يمكن أن يتعدى {size} px.',
        msgImageResizeError: 'لم يتمكن من معرفة أبعاد الصورة لتغييرها.',
        msgImageResizeException: 'حدث خطأ أثناء تغيير أبعاد الصورة.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'اسحب وأفلت الملفات هنا &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'إزالة الملف',
            uploadTitle: 'رفع الملف',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'مشاهدة التفاصيل',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'لم يتم الرفع بعد',
            indicatorSuccessTitle: 'تم الرفع',
            indicatorErrorTitle: 'خطأ بالرفع',
            indicatorLoadingTitle: 'جارٍ الرفع ...'
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


    var arKw = moment.defineLocale('ar-kw', {
        months : 'يناير_فبراير_مارس_أبريل_ماي_يونيو_يوليوز_غشت_شتنبر_أكتوبر_نونبر_دجنبر'.split('_'),
        monthsShort : 'يناير_فبراير_مارس_أبريل_ماي_يونيو_يوليوز_غشت_شتنبر_أكتوبر_نونبر_دجنبر'.split('_'),
        weekdays : 'الأحد_الإتنين_الثلاثاء_الأربعاء_الخميس_الجمعة_السبت'.split('_'),
        weekdaysShort : 'احد_اتنين_ثلاثاء_اربعاء_خميس_جمعة_سبت'.split('_'),
        weekdaysMin : 'ح_ن_ث_ر_خ_ج_س'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay: '[اليوم على الساعة] LT',
            nextDay: '[غدا على الساعة] LT',
            nextWeek: 'dddd [على الساعة] LT',
            lastDay: '[أمس على الساعة] LT',
            lastWeek: 'dddd [على الساعة] LT',
            sameElse: 'L'
        },
        relativeTime : {
            future : 'في %s',
            past : 'منذ %s',
            s : 'ثوان',
            ss : '%d ثانية',
            m : 'دقيقة',
            mm : '%d دقائق',
            h : 'ساعة',
            hh : '%d ساعات',
            d : 'يوم',
            dd : '%d أيام',
            M : 'شهر',
            MM : '%d أشهر',
            y : 'سنة',
            yy : '%d سنوات'
        },
        week : {
            dow : 0, // Sunday is the first day of the week.
            doy : 12  // The week that contains Jan 12th is the first week of the year.
        }
    });

    return arKw;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/ar",[],function(){return{errorLoading:function(){return"لا يمكن تحميل النتائج"},inputTooLong:function(n){return"الرجاء حذف "+(n.input.length-n.maximum)+" عناصر"},inputTooShort:function(n){return"الرجاء إضافة "+(n.minimum-n.input.length)+" عناصر"},loadingMore:function(){return"جاري تحميل نتائج إضافية..."},maximumSelected:function(n){return"تستطيع إختيار "+n.maximum+" بنود فقط"},noResults:function(){return"لم يتم العثور على أي نتائج"},searching:function(){return"جاري البحث…"},removeAllItems:function(){return"قم بإزالة كل العناصر"}}}),n.define,n.require}();
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

/*!
 * Translated default messages for bootstrap-select.
 * Locale: AR (Arabic)
 * Author: Yasser Lotfy <y_l@alive.com>
 */
(function ($) {
  $.fn.selectpicker.defaults = {
    noneSelectedText: 'لم يتم إختيار شئ',
    noneResultsText: 'لا توجد نتائج مطابقة لـ {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} خيار تم إختياره' : '{0} خيارات تمت إختيارها';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'تخطى الحد المسموح ({n} خيار بحد أقصى)' : 'تخطى الحد المسموح ({n} خيارات بحد أقصى)',
        (numGroup == 1) ? 'تخطى الحد المسموح للمجموعة ({n} خيار بحد أقصى)' : 'تخطى الحد المسموح للمجموعة ({n} خيارات بحد أقصى)'
      ];
    },
    selectAllText: 'إختيار الجميع',
    deselectAllText: 'إلغاء إختيار الجميع',
    multipleSeparator: '، '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-ar_AR.js.map