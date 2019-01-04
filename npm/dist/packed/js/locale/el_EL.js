/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Greek (el)
 * Author: Stelios Patsatzis, https://www.linkedin.com/in/stelios-patsatzis-89841561
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

QueryBuilder.regional['el'] = {
  "__locale": "Greek (el)",
  "__author": "Stelios Patsatzis, https://www.linkedin.com/in/stelios-patsatzis-89841561",
  "add_rule": "Προσθήκη Συνθήκης",
  "add_group": "Προσθήκη Ομάδας",
  "delete_rule": "Διαγραφή",
  "delete_group": "Διαγραφή",
  "conditions": {
    "AND": "Λογικό ΚΑΙ",
    "OR": "Λογικό Η"
  },
  "operators": {
    "equal": "Ισούται με",
    "not_equal": "Διάφορο από ",
    "in": "Περιέχει",
    "not_in": "Δεν Περιέχει",
    "less": "Λιγότερο από",
    "less_or_equal": "Λιγότερο ή Ίσο",
    "greater": "Μεγαλύτερο από",
    "greater_or_equal": "Μεγαλύτερο ή Ίσο",
    "between": "Μεταξύ",
    "not_between": "Εκτός",
    "begins_with": "Αρχίζει με",
    "not_begins_with": "Δεν αρχίζει με",
    "contains": "Περιέχει",
    "not_contains": "Δεν περιέχει",
    "ends_with": "Τελειώνει σε",
    "not_ends_with": "Δεν τελειώνει σε",
    "is_empty": "Είναι άδειο",
    "is_not_empty": "Δεν είναι άδειο",
    "is_null": "Είναι NULL",
    "is_not_null": "Δεν είναι NULL"
  },
  "errors": {
    "no_filter": "Χωρίς φίλτρα",
    "empty_group": "Άδεια ομάδα",
    "radio_empty": "Χωρίς τιμή",
    "checkbox_empty": "Χωρίς τιμή",
    "select_empty": "Χωρίς τιμή",
    "string_empty": "Χωρίς τιμή",
    "string_exceed_min_length": "Ελάχιστο όριο {0} χαρακτήρων",
    "string_exceed_max_length": "Μέγιστο όριο {0} χαρακτήρων",
    "string_invalid_format": "Λανθασμένη μορφή ({0})",
    "number_nan": "Δεν είναι αριθμός",
    "number_not_integer": "Δεν είναι ακέραιος αριθμός",
    "number_not_double": "Δεν είναι πραγματικός αριθμός",
    "number_exceed_min": "Πρέπει να είναι μεγαλύτερο απο {0}",
    "number_exceed_max": "Πρέπει να είναι μικρότερο απο {0}",
    "number_wrong_step": "Πρέπει να είναι πολλαπλάσιο του {0}",
    "datetime_empty": "Χωρίς τιμή",
    "datetime_invalid": "Λανθασμένη μορφή ημερομηνίας ({0})",
    "datetime_exceed_min": "Νεότερο από {0}",
    "datetime_exceed_max": "Παλαιότερο από {0}",
    "boolean_not_valid": "Δεν είναι BOOLEAN",
    "operator_not_multiple": "Η συνθήκη \"{1}\" δεν μπορεί να δεχθεί πολλαπλές τιμές"
  },
  "invert": "Εναλλαγή"
};

QueryBuilder.defaults({ lang_code: 'el' });
}));
/*!
 * FileInput Greek Translations
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

    $.fn.fileinputLocales['el'] = {
        fileSingle: 'αρχείο',
        filePlural: 'αρχεία',
        browseLabel: 'Αναζήτηση &hellip;',
        removeLabel: 'Διαγραφή',
        removeTitle: 'Εκκαθάριση αρχείων',
        cancelLabel: 'Ακύρωση',
        cancelTitle: 'Ακύρωση μεταφόρτωσης',
        uploadLabel: 'Μεταφόρτωση',
        uploadTitle: 'Μεταφόρτωση επιλεγμένων αρχείων',
        msgNo: 'Όχι',
        msgNoFilesSelected: 'Δεν επιλέχθηκαν αρχεία',
        msgCancelled: 'Ακυρώθηκε',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Λεπτομερής Προεπισκόπηση',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'Το "{name}" (<b>{size} KB</b>) είναι πολύ μικρό, πρέπει να είναι μεγαλύτερο από <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Το αρχείο "{name}" (<b>{size} KB</b>) υπερβαίνει το μέγιστο επιτρεπόμενο μέγεθος μεταφόρτωσης <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Πρέπει να επιλέξετε τουλάχιστον <b>{n}</b> {files} για να ξεκινήσει η μεταφόρτωση.',
        msgFilesTooMany: 'Ο αριθμός των αρχείων που έχουν επιλεγεί για μεταφόρτωση <b>({n})</b> υπερβαίνει το μέγιστο επιτρεπόμενο αριθμό <b>{m}</b>.',
        msgFileNotFound: 'Το αρχείο "{name}" δεν βρέθηκε!',
        msgFileSecured: 'Περιορισμοί ασφαλείας εμπόδισαν την ανάγνωση του αρχείου "{name}".',
        msgFileNotReadable: 'Το αρχείο "{name}" δεν είναι αναγνώσιμο.',
        msgFilePreviewAborted: 'Η προεπισκόπηση του αρχείου "{name}" ακυρώθηκε.',
        msgFilePreviewError: 'Παρουσιάστηκε σφάλμα κατά την ανάγνωση του αρχείου "{name}".',
        msgInvalidFileName: 'Μη έγκυροι χαρακτήρες στο όνομα του αρχείου "{name}".',
        msgInvalidFileType: 'Μη έγκυρος ο τύπος του αρχείου "{name}". Οι τύποι αρχείων που υποστηρίζονται είναι : "{types}".',
        msgInvalidFileExtension: 'Μη έγκυρη η επέκταση του αρχείου "{name}". Οι επεκτάσεις που υποστηρίζονται είναι : "{extensions}".',
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
        msgUploadAborted: 'Η μεταφόρτωση του αρχείου ματαιώθηκε',
        msgUploadThreshold: 'Μεταφόρτωση ...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Σφάλμα Επικύρωσης',
        msgLoading: 'Φόρτωση αρχείου {index} από {files} &hellip;',
        msgProgress: 'Φόρτωση αρχείου {index} απο {files} - {name} - {percent}% ολοκληρώθηκε.',
        msgSelected: '{n} {files} επιλέχθηκαν',
        msgFoldersNotAllowed: 'Μπορείτε να σύρετε μόνο αρχεία! Παραβλέφθηκαν {n} φάκελος(οι).',
        msgImageWidthSmall: 'Το πλάτος του αρχείου εικόνας "{name}" πρέπει να είναι τουλάχιστον {size} px.',
        msgImageHeightSmall: 'Το ύψος του αρχείου εικόνας "{name}" πρέπει να είναι τουλάχιστον {size} px.',
        msgImageWidthLarge: 'Το πλάτος του αρχείου εικόνας "{name}" δεν μπορεί να υπερβαίνει το {size} px.',
        msgImageHeightLarge: 'Το ύψος του αρχείου εικόνας "{name}" δεν μπορεί να υπερβαίνει το {size} px.',
        msgImageResizeError: 'Δεν μπορούν να βρεθούν οι διαστάσεις της εικόνας για να αλλάγή μεγέθους.',
        msgImageResizeException: 'Σφάλμα κατά την αλλαγή μεγέθους της εικόνας. <pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Σύρετε τα αρχεία εδώ &hellip;',
        dropZoneClickTitle: '<br>(ή πατήστε για επιλογή {files})',
        fileActionSettings: {
            removeTitle: 'Αφαιρέστε το αρχείο',
            uploadTitle: 'Μεταφορτώστε το αρχείο',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'Δείτε λεπτομέρειες',
            dragTitle: 'Μετακίνηση/Προσπαρμογή',
            indicatorNewTitle: 'Δεν μεταφορτώθηκε ακόμα',
            indicatorSuccessTitle: 'Μεταφορτώθηκε',
            indicatorErrorTitle: 'Σφάλμα Μεταφόρτωσης',
            indicatorLoadingTitle: 'Μεταφόρτωση ...'
        },
        previewZoomButtonTitles: {
            prev: 'Προηγούμενο αρχείο',
            next: 'Επόμενο αρχείο',
            toggleheader: 'Εμφάνιση/Απόκρυψη τίτλου',
            fullscreen: 'Εναλλαγή πλήρους οθόνης',
            borderless: 'Με ή χωρίς πλαίσιο',
            close: 'Κλείσιμο προβολής'
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

    function isFunction(input) {
        return input instanceof Function || Object.prototype.toString.call(input) === '[object Function]';
    }


    var el = moment.defineLocale('el', {
        monthsNominativeEl : 'Ιανουάριος_Φεβρουάριος_Μάρτιος_Απρίλιος_Μάιος_Ιούνιος_Ιούλιος_Αύγουστος_Σεπτέμβριος_Οκτώβριος_Νοέμβριος_Δεκέμβριος'.split('_'),
        monthsGenitiveEl : 'Ιανουαρίου_Φεβρουαρίου_Μαρτίου_Απριλίου_Μαΐου_Ιουνίου_Ιουλίου_Αυγούστου_Σεπτεμβρίου_Οκτωβρίου_Νοεμβρίου_Δεκεμβρίου'.split('_'),
        months : function (momentToFormat, format) {
            if (!momentToFormat) {
                return this._monthsNominativeEl;
            } else if (typeof format === 'string' && /D/.test(format.substring(0, format.indexOf('MMMM')))) { // if there is a day number before 'MMMM'
                return this._monthsGenitiveEl[momentToFormat.month()];
            } else {
                return this._monthsNominativeEl[momentToFormat.month()];
            }
        },
        monthsShort : 'Ιαν_Φεβ_Μαρ_Απρ_Μαϊ_Ιουν_Ιουλ_Αυγ_Σεπ_Οκτ_Νοε_Δεκ'.split('_'),
        weekdays : 'Κυριακή_Δευτέρα_Τρίτη_Τετάρτη_Πέμπτη_Παρασκευή_Σάββατο'.split('_'),
        weekdaysShort : 'Κυρ_Δευ_Τρι_Τετ_Πεμ_Παρ_Σαβ'.split('_'),
        weekdaysMin : 'Κυ_Δε_Τρ_Τε_Πε_Πα_Σα'.split('_'),
        meridiem : function (hours, minutes, isLower) {
            if (hours > 11) {
                return isLower ? 'μμ' : 'ΜΜ';
            } else {
                return isLower ? 'πμ' : 'ΠΜ';
            }
        },
        isPM : function (input) {
            return ((input + '').toLowerCase()[0] === 'μ');
        },
        meridiemParse : /[ΠΜ]\.?Μ?\.?/i,
        longDateFormat : {
            LT : 'h:mm A',
            LTS : 'h:mm:ss A',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY h:mm A',
            LLLL : 'dddd, D MMMM YYYY h:mm A'
        },
        calendarEl : {
            sameDay : '[Σήμερα {}] LT',
            nextDay : '[Αύριο {}] LT',
            nextWeek : 'dddd [{}] LT',
            lastDay : '[Χθες {}] LT',
            lastWeek : function () {
                switch (this.day()) {
                    case 6:
                        return '[το προηγούμενο] dddd [{}] LT';
                    default:
                        return '[την προηγούμενη] dddd [{}] LT';
                }
            },
            sameElse : 'L'
        },
        calendar : function (key, mom) {
            var output = this._calendarEl[key],
                hours = mom && mom.hours();
            if (isFunction(output)) {
                output = output.apply(mom);
            }
            return output.replace('{}', (hours % 12 === 1 ? 'στη' : 'στις'));
        },
        relativeTime : {
            future : 'σε %s',
            past : '%s πριν',
            s : 'λίγα δευτερόλεπτα',
            ss : '%d δευτερόλεπτα',
            m : 'ένα λεπτό',
            mm : '%d λεπτά',
            h : 'μία ώρα',
            hh : '%d ώρες',
            d : 'μία μέρα',
            dd : '%d μέρες',
            M : 'ένας μήνας',
            MM : '%d μήνες',
            y : 'ένας χρόνος',
            yy : '%d χρόνια'
        },
        dayOfMonthOrdinalParse: /\d{1,2}η/,
        ordinal: '%dη',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4st is the first week of the year.
        }
    });

    return el;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/el",[],function(){return{errorLoading:function(){return"Τα αποτελέσματα δεν μπόρεσαν να φορτώσουν."},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Παρακαλώ διαγράψτε "+t+" χαρακτήρ";return t==1&&(n+="α"),t!=1&&(n+="ες"),n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="Παρακαλώ συμπληρώστε "+t+" ή περισσότερους χαρακτήρες";return n},loadingMore:function(){return"Φόρτωση περισσότερων αποτελεσμάτων…"},maximumSelected:function(e){var t="Μπορείτε να επιλέξετε μόνο "+e.maximum+" επιλογ";return e.maximum==1&&(t+="ή"),e.maximum!=1&&(t+="ές"),t},noResults:function(){return"Δεν βρέθηκαν αποτελέσματα"},searching:function(){return"Αναζήτηση…"}}}),{define:e.define,require:e.require}})();