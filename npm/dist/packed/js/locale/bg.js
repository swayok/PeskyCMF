/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Bulgarian (bg)
 * Author: Valentin Hristov
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

QueryBuilder.regional['bg'] = {
  "__locale": "Bulgarian (bg)",
  "__author": "Valentin Hristov",
  "add_rule": "Добави правило",
  "add_group": "Добави група",
  "delete_rule": "Изтрий",
  "delete_group": "Изтрий",
  "conditions": {
    "AND": "И",
    "OR": "ИЛИ"
  },
  "operators": {
    "equal": "равно",
    "not_equal": "различно",
    "in": "в",
    "not_in": "не е в",
    "less": "по-малко",
    "less_or_equal": "по-малко или равно",
    "greater": "по-голям",
    "greater_or_equal": "по-голям или равно",
    "between": "между",
    "not_between": "не е между",
    "begins_with": "започва с",
    "not_begins_with": "не започва с",
    "contains": "съдържа",
    "not_contains": "не съдържа",
    "ends_with": "завършва с",
    "not_ends_with": "не завършва с",
    "is_empty": "е празно",
    "is_not_empty": "не е празно",
    "is_null": "е нищо",
    "is_not_null": "различно от нищо"
  },
  "errors": {
    "no_filter": "Не е избран филтър",
    "empty_group": "Групата е празна",
    "radio_empty": "Не е селектирана стойност",
    "checkbox_empty": "Не е селектирана стойност",
    "select_empty": "Не е селектирана стойност",
    "string_empty": "Празна стойност",
    "string_exceed_min_length": "Необходимо е да съдържа поне {0} символа",
    "string_exceed_max_length": "Необходимо е да съдържа повече от {0} символа",
    "string_invalid_format": "Невалиден формат ({0})",
    "number_nan": "Не е число",
    "number_not_integer": "Не е цяло число",
    "number_not_double": "Не е реално число",
    "number_exceed_min": "Трябва да е по-голямо от {0}",
    "number_exceed_max": "Трябва да е по-малко от {0}",
    "number_wrong_step": "Трябва да е кратно на {0}",
    "datetime_empty": "Празна стойност",
    "datetime_invalid": "Невалиден формат на дата ({0})",
    "datetime_exceed_min": "Трябва да е след {0}",
    "datetime_exceed_max": "Трябва да е преди {0}",
    "boolean_not_valid": "Не е булева",
    "operator_not_multiple": "Оператора \"{1}\" не може да приеме множество стойности"
  }
};

QueryBuilder.defaults({ lang_code: 'bg' });
}));
/*!
 * FileInput Bulgarian Translations
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

    $.fn.fileinputLocales['bg'] = {
        fileSingle: 'файл',
        filePlural: 'файла',
        browseLabel: 'Избери &hellip;',
        removeLabel: 'Премахни',
        removeTitle: 'Изчисти избраните',
        cancelLabel: 'Откажи',
        cancelTitle: 'Откажи качването',
        uploadLabel: 'Качи',
        uploadTitle: 'Качи избраните файлове',
        msgNo: 'Не',
        msgNoFilesSelected: '',
        msgCancelled: 'Отменен',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Детайлен преглед',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Файла "{name}" (<b>{size} KB</b>) надвишава максималните разрешени <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Трябва да изберете поне <b>{n}</b> {files} файла.',
        msgFilesTooMany: 'Броя файлове избрани за качване <b>({n})</b> надвишава ограниченито от максимум <b>{m}</b>.',
        msgFileNotFound: 'Файлът "{name}" не може да бъде намерен!',
        msgFileSecured: 'От съображения за сигурност не може да прочетем файла "{name}".',
        msgFileNotReadable: 'Файлът "{name}" не е четим.',
        msgFilePreviewAborted: 'Прегледа на файла е прекратен за "{name}".',
        msgFilePreviewError: 'Грешка при опит за четене на файла "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'Невалиден тип на файла "{name}". Разрешени са само "{types}".',
        msgInvalidFileExtension: 'Невалидно разрешение на "{name}". Разрешени са само "{extensions}".',
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
        msgUploadAborted: 'Качите файла, бе прекратена',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'утвърждаване грешка',
        msgLoading: 'Зареждане на файл {index} от общо {files} &hellip;',
        msgProgress: 'Зареждане на файл {index} от общо {files} - {name} - {percent}% завършени.',
        msgSelected: '{n} {files} избрани',
        msgFoldersNotAllowed: 'Само пуснати файлове! Пропуснати {n} пуснати папки.',
        msgImageWidthSmall: 'Широчината на изображението "{name}" трябва да е поне {size} px.',
        msgImageHeightSmall: 'Височината на изображението "{name}" трябва да е поне {size} px.',
        msgImageWidthLarge: 'Широчината на изображението "{name}" не може да е по-голяма от {size} px.',
        msgImageHeightLarge: 'Височината на изображението "{name}" нее може да е по-голяма от {size} px.',
        msgImageResizeError: 'Не може да размерите на изображението, за да промените размера.',
        msgImageResizeException: 'Грешка при промяна на размера на изображението.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Пуснете файловете тук &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'Махни файл',
            uploadTitle: 'Качване на файл',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'Вижте детайли',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'Все още не е качил',
            indicatorSuccessTitle: 'Качено',
            indicatorErrorTitle: 'Качи Error',
            indicatorLoadingTitle: 'Качва се ...'
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


    var bg = moment.defineLocale('bg', {
        months : 'януари_февруари_март_април_май_юни_юли_август_септември_октомври_ноември_декември'.split('_'),
        monthsShort : 'янр_фев_мар_апр_май_юни_юли_авг_сеп_окт_ное_дек'.split('_'),
        weekdays : 'неделя_понеделник_вторник_сряда_четвъртък_петък_събота'.split('_'),
        weekdaysShort : 'нед_пон_вто_сря_чет_пет_съб'.split('_'),
        weekdaysMin : 'нд_пн_вт_ср_чт_пт_сб'.split('_'),
        longDateFormat : {
            LT : 'H:mm',
            LTS : 'H:mm:ss',
            L : 'D.MM.YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY H:mm',
            LLLL : 'dddd, D MMMM YYYY H:mm'
        },
        calendar : {
            sameDay : '[Днес в] LT',
            nextDay : '[Утре в] LT',
            nextWeek : 'dddd [в] LT',
            lastDay : '[Вчера в] LT',
            lastWeek : function () {
                switch (this.day()) {
                    case 0:
                    case 3:
                    case 6:
                        return '[В изминалата] dddd [в] LT';
                    case 1:
                    case 2:
                    case 4:
                    case 5:
                        return '[В изминалия] dddd [в] LT';
                }
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : 'след %s',
            past : 'преди %s',
            s : 'няколко секунди',
            ss : '%d секунди',
            m : 'минута',
            mm : '%d минути',
            h : 'час',
            hh : '%d часа',
            d : 'ден',
            dd : '%d дни',
            M : 'месец',
            MM : '%d месеца',
            y : 'година',
            yy : '%d години'
        },
        dayOfMonthOrdinalParse: /\d{1,2}-(ев|ен|ти|ви|ри|ми)/,
        ordinal : function (number) {
            var lastDigit = number % 10,
                last2Digits = number % 100;
            if (number === 0) {
                return number + '-ев';
            } else if (last2Digits === 0) {
                return number + '-ен';
            } else if (last2Digits > 10 && last2Digits < 20) {
                return number + '-ти';
            } else if (lastDigit === 1) {
                return number + '-ви';
            } else if (lastDigit === 2) {
                return number + '-ри';
            } else if (lastDigit === 7 || lastDigit === 8) {
                return number + '-ми';
            } else {
                return number + '-ти';
            }
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 7th is the first week of the year.
        }
    });

    return bg;

})));

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
    noneSelectedText: 'Нищо избрано',
    noneResultsText: 'Няма резултат за {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} избран елемент' : '{0} избрани елемента';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Лимита е достигнат ({n} елемент максимум)' : 'Лимита е достигнат ({n} елемента максимум)',
        (numGroup == 1) ? 'Груповия лимит е достигнат ({n} елемент максимум)' : 'Груповия лимит е достигнат ({n} елемента максимум)'
      ];
    },
    selectAllText: 'Избери всички',
    deselectAllText: 'Размаркирай всички',
    multipleSeparator: ', '
  };
})(jQuery);


}));
