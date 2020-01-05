/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Hebrew (he)
 * Author: Kfir Stri https://github.com/kfirstri
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

QueryBuilder.regional['he'] = {
  "__locale": "Hebrew (he)",
  "__author": "Kfir Stri https://github.com/kfirstri",
  "add_rule": "הוסף כלל",
  "add_group": "הוסף קבוצה",
  "delete_rule": "מחק",
  "delete_group": "מחק",
  "conditions": {
    "AND": "וגם",
    "OR": "או"
  },
  "operators": {
    "equal": "שווה ל",
    "not_equal": "שונה מ",
    "in": "חלק מ",
    "not_in": "לא חלק מ",
    "less": "פחות מ",
    "less_or_equal": "פחות או שווה ל",
    "greater": "גדול מ",
    "greater_or_equal": "גדול או שווה ל",
    "between": "בין",
    "not_between": "לא בין",
    "begins_with": "מתחיל ב",
    "not_begins_with": "לא מתחיל ב",
    "contains": "מכיל",
    "not_contains": "לא מכיל",
    "ends_with": "מסתיים ב",
    "not_ends_with": "לא מסתיים ב",
    "is_empty": "ריק",
    "is_not_empty": "לא ריק",
    "is_null": "חסר ערך",
    "is_not_null": "לא חסר ערך"
  },
  "errors": {
    "no_filter": "לא נבחרו מסננים",
    "empty_group": "הקבוצה רירקה",
    "radio_empty": "לא נבחר אף ערך",
    "checkbox_empty": "לא נבחר אף ערך",
    "select_empty": "לא נבחר אף ערך",
    "string_empty": "חסר ערך",
    "string_exceed_min_length": "המחרוזת חייבת להכיל לפחות {0} תווים",
    "string_exceed_max_length": "המחרוזת לא יכולה להכיל יותר מ{0} תווים",
    "string_invalid_format": "המחרוזת בפורמט שגוי ({0})",
    "number_nan": "זהו לא מספר",
    "number_not_integer": "המספר אינו מספר שלם",
    "number_not_double": "המספר אינו מספר עשרוני",
    "number_exceed_min": "המספר צריך להיות גדול מ {0}",
    "number_exceed_max": "המספר צריך להיות קטן מ{0}",
    "number_wrong_step": "המספר צריך להיות כפולה של {0}",
    "datetime_empty": "תאריך ריק",
    "datetime_invalid": "פורמט תאריך שגוי ({0})",
    "datetime_exceed_min": "התאריך חייב להיות אחרי {0}",
    "datetime_exceed_max": "התאריך חייב להיות לפני {0}",
    "boolean_not_valid": "זהו לא בוליאני",
    "operator_not_multiple": "האופרטור \"{1}\" לא יכול לקבל ערכים מרובים"
  },
  "invert": "הפוך שאילתא",
  "NOT": "לא"
};

QueryBuilder.defaults({ lang_code: 'he' });
}));
/*!
 * FileInput Hebrew Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Daniel Coryat <awq8002@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['he'] = {
        fileSingle: 'קובץ',
        filePlural: 'קבצים',
        browseLabel: 'העלאה &hellip;',
        removeLabel: 'הסרה',
        removeTitle: 'נקה קבצים נבחרים',
        cancelLabel: 'ביטול',
        cancelTitle: 'ביטול העלאה מתמשכת',
        uploadLabel: 'טעינה',
        uploadTitle: 'טעינת קבצים נבחרים',
        msgNo: 'לא',
        msgNoFilesSelected: 'לא נבחרו קבצים',
        msgCancelled: 'מבוטל',
        msgPlaceholder: 'בחר {files}...',
        msgZoomModalHeading: 'תצוגה מקדימה מפורטת',
        msgSizeTooSmall: 'קובץ "{name}" (<b>{size} KB</b>) קטן מדי וחייב להיות גדול מ <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'קובץ "{name}" (<b>{size} KB</b>) חורג מהגודל המרבי המותר להעלאה של <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'עליך לבחור לפחות <b>{n}</b> {files} להעלאה.',
        msgFilesTooMany: 'מספר הקבצים שנבחרו להעלאה <b>({n})</b> חורג מהמגבלה המרבית המותרת של <b>{m}</b>.',
        msgFileNotFound: 'קובץ "{name}" לא נמצא!',
        msgFileSecured: 'הגבלות אבטחה מונעות קריאת הקובץ "{name}".',
        msgFileNotReadable: 'קובץ "{name}" לא קריא.',
        msgFilePreviewAborted: 'תצוגה מקדימה של הקובץ בוטלה עבור "{name}".',
        msgFilePreviewError: 'אירעה שגיאה בעת קריאת הקובץ "{name}".',
        msgInvalidFileName: 'תווים לא חוקיים או לא נתמכים בשם הקובץ "{name}".',
        msgInvalidFileType: 'סוג קובץ לא חוקי "{name}". רק "{types}" קבצים נתמכים.',
        msgInvalidFileExtension: 'תוסף לא חוקי עבור הקובץ "{name}". רק "{extensions}" קבצים נתמכים.',
        msgFileTypes: {
            'image': 'תמונה',
            'html': 'HTML',
            'text': 'טקסט',
            'video': 'וידאו',
            'audio': 'שמע',
            'flash': 'פלאש',
            'pdf': 'PDF',
            'object': 'אובייקט'
        },
        msgUploadAborted: 'העלאת הקובץ בוטלה',
        msgUploadThreshold: 'מעבד...',
        msgUploadBegin: 'מאתחל ...',
        msgUploadEnd: 'בוצע',
        msgUploadEmpty: 'אין נתונים זמינים להעלאה.',
        msgValidationError: 'שגיאת אימות',
        msgLoading: 'טוען קובץ {index} של {files} &hellip;',
        msgProgress: 'טוען קובץ {index} של {files} - {name} - {percent}% הושלמה.',
        msgSelected: '{n} {files} נבחרו',
        msgFoldersNotAllowed: 'גרירת קבצים ושחרורם בלבד! דילוג {n} גרירת תיקיה(s).',
        msgImageWidthSmall: 'רוחב קובץ התמונה "{name}" חייב להיות לפחות {size} px.',
        msgImageHeightSmall: 'גובה קובץ התמונה "{name}" חייב להיות לפחות {size} px.',
        msgImageWidthLarge: 'רוחב קובץ התמונה "{name}" לא יעלה על {size} px.',
        msgImageHeightLarge: 'גובה קובץ התמונה "{name}" לא יעלה על {size} px.',
        msgImageResizeError: 'לא ניתן לשנות את גודל מידות התמונה.',
        msgImageResizeException: 'שגיאה בעת שינוי גודל התמונה.<pre>{errors}</pre>',
        msgAjaxError: 'משהו השתבש עם {operation} המערכת. יש לנסות מאוחר יותר!',
        msgAjaxProgressError: '{operation} נכשל',
        ajaxOperations: {
            deleteThumb: 'קובץ נמחק',
            uploadThumb: 'קובץ הועלה',
            uploadBatch: 'קובץ אצווה הועלה',
            uploadExtra: 'העלאת נתונים בטופס'
        },
        dropZoneTitle: 'גרירת קבצים ושחרורם כאן &hellip;',
        dropZoneClickTitle: '<br>(או לחץ /י כדי לבחור {files})',
        fileActionSettings: {
            removeTitle: 'הסרת קובץ',
            uploadTitle: 'טעינת קובץ',
            zoomTitle: 'הצגת פרטים',
            dragTitle: 'העברה / סידור מחדש',
            indicatorNewTitle: 'עדיין לא הועלה',
            indicatorSuccessTitle: 'הועלה',
            indicatorErrorTitle: 'שגיאת העלאה',
            indicatorLoadingTitle: 'מעלה...'
        },
        previewZoomButtonTitles: {
            prev: 'הצגת את הקובץ הקודם',
            next: 'הצגת את הקובץ הבא',
            toggleheader: 'שינוי כותרת',
            fullscreen: 'מעבר למסך מלא',
            borderless: 'שינוי המודל ללא שוליים',
            close: 'סגירת תצוגה מקדימה מפורטת'
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


    var he = moment.defineLocale('he', {
        months : 'ינואר_פברואר_מרץ_אפריל_מאי_יוני_יולי_אוגוסט_ספטמבר_אוקטובר_נובמבר_דצמבר'.split('_'),
        monthsShort : 'ינו׳_פבר׳_מרץ_אפר׳_מאי_יוני_יולי_אוג׳_ספט׳_אוק׳_נוב׳_דצמ׳'.split('_'),
        weekdays : 'ראשון_שני_שלישי_רביעי_חמישי_שישי_שבת'.split('_'),
        weekdaysShort : 'א׳_ב׳_ג׳_ד׳_ה׳_ו׳_ש׳'.split('_'),
        weekdaysMin : 'א_ב_ג_ד_ה_ו_ש'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D [ב]MMMM YYYY',
            LLL : 'D [ב]MMMM YYYY HH:mm',
            LLLL : 'dddd, D [ב]MMMM YYYY HH:mm',
            l : 'D/M/YYYY',
            ll : 'D MMM YYYY',
            lll : 'D MMM YYYY HH:mm',
            llll : 'ddd, D MMM YYYY HH:mm'
        },
        calendar : {
            sameDay : '[היום ב־]LT',
            nextDay : '[מחר ב־]LT',
            nextWeek : 'dddd [בשעה] LT',
            lastDay : '[אתמול ב־]LT',
            lastWeek : '[ביום] dddd [האחרון בשעה] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'בעוד %s',
            past : 'לפני %s',
            s : 'מספר שניות',
            ss : '%d שניות',
            m : 'דקה',
            mm : '%d דקות',
            h : 'שעה',
            hh : function (number) {
                if (number === 2) {
                    return 'שעתיים';
                }
                return number + ' שעות';
            },
            d : 'יום',
            dd : function (number) {
                if (number === 2) {
                    return 'יומיים';
                }
                return number + ' ימים';
            },
            M : 'חודש',
            MM : function (number) {
                if (number === 2) {
                    return 'חודשיים';
                }
                return number + ' חודשים';
            },
            y : 'שנה',
            yy : function (number) {
                if (number === 2) {
                    return 'שנתיים';
                } else if (number % 10 === 0 && number !== 10) {
                    return number + ' שנה';
                }
                return number + ' שנים';
            }
        },
        meridiemParse: /אחה"צ|לפנה"צ|אחרי הצהריים|לפני הצהריים|לפנות בוקר|בבוקר|בערב/i,
        isPM : function (input) {
            return /^(אחה"צ|אחרי הצהריים|בערב)$/.test(input);
        },
        meridiem : function (hour, minute, isLower) {
            if (hour < 5) {
                return 'לפנות בוקר';
            } else if (hour < 10) {
                return 'בבוקר';
            } else if (hour < 12) {
                return isLower ? 'לפנה"צ' : 'לפני הצהריים';
            } else if (hour < 18) {
                return isLower ? 'אחה"צ' : 'אחרי הצהריים';
            } else {
                return 'בערב';
            }
        }
    });

    return he;

})));

/*! Select2 4.0.12 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/he",[],function(){return{errorLoading:function(){return"שגיאה בטעינת התוצאות"},inputTooLong:function(n){var e=n.input.length-n.maximum,r="נא למחוק ";return r+=1===e?"תו אחד":e+" תווים"},inputTooShort:function(n){var e=n.minimum-n.input.length,r="נא להכניס ";return r+=1===e?"תו אחד":e+" תווים",r+=" או יותר"},loadingMore:function(){return"טוען תוצאות נוספות…"},maximumSelected:function(n){var e="באפשרותך לבחור עד ";return 1===n.maximum?e+="פריט אחד":e+=n.maximum+" פריטים",e},noResults:function(){return"לא נמצאו תוצאות"},searching:function(){return"מחפש…"},removeAllItems:function(){return"הסר את כל הפריטים"}}}),n.define,n.require}();