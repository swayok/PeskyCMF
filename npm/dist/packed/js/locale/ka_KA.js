/*!
 * FileInput Georgian Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['ka'] = {
        fileSingle: 'ფაილი',
        filePlural: 'ფაილები',
        browseLabel: 'არჩევა &hellip;',
        removeLabel: 'წაშლა',
        removeTitle: 'არჩეული ფაილების წაშლა',
        cancelLabel: 'გაუქმება',
        cancelTitle: 'მიმდინარე ატვირთვის გაუქმება',
        uploadLabel: 'ატვირთვა',
        uploadTitle: 'არჩეული ფაილების ატვირთვა',
        msgNo: 'არა',
        msgNoFilesSelected: 'ფაილები არ არის არჩეული',
        msgCancelled: 'გაუქმებულია',
        msgPlaceholder: 'აირჩიეთ {files}...',
        msgZoomModalHeading: 'დეტალურად ნახვა',
        msgFileRequired: 'ატვირთვისთვის აუცილებელია ფაილის არჩევა.',
        msgSizeTooSmall: 'ფაილი "{name}" (<b>{size} KB</b>) არის ძალიან პატარა. მისი ზომა უნდა იყოს არანაკლებ <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'ფაილი "{name}" (<b>{size} KB</b>) აჭარბებს მაქსიმალურ დასაშვებ ზომას <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'უნდა აირჩიოთ მინიმუმ <b>{n}</b> {file} ატვირთვისთვის.',
        msgFilesTooMany: 'არჩეული ფაილების რაოდენობა <b>({n})</b> აჭარბებს დასაშვებ ლიმიტს <b>{m}</b>.',
        msgFileNotFound: 'ფაილი "{name}" არ მოიძებნა!',
        msgFileSecured: 'უსაფრთხოებით გამოწვეული შეზღუდვები კრძალავს ფაილის "{name}" წაკითხვას.',
        msgFileNotReadable: 'ფაილის "{name}" წაკითხვა შეუძლებელია.',
        msgFilePreviewAborted: 'პრევიუ გაუქმებულია ფაილისათვის "{name}".',
        msgFilePreviewError: 'დაფიქსირდა შეცდომა ფაილის "{name}" კითხვისას.',
        msgInvalidFileName: 'ნაპოვნია დაუშვებელი სიმბოლოები ფაილის "{name}" სახელში.',
        msgInvalidFileType: 'ფაილს "{name}" გააჩნია დაუშვებელი ტიპი. მხოლოდ "{types}" ტიპის ფაილები არის დაშვებული.',
        msgInvalidFileExtension: 'ფაილს "{name}" გააჩნია დაუშვებელი გაფართოება. მხოლოდ "{extensions}" გაფართოების ფაილები არის დაშვებული.',
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
        msgUploadAborted: 'ფაილის ატვირთვა შეწყდა',
        msgUploadThreshold: 'მუშავდება...',
        msgUploadBegin: 'ინიციალიზაცია...',
        msgUploadEnd: 'დასრულებულია',
        msgUploadEmpty: 'ატვირთვისთვის დაუშვებელი მონაცემები.',
        msgUploadError: 'ატვირთვის შეცდომა',
        msgValidationError: 'ვალიდაციის შეცდომა',
        msgLoading: 'ატვირთვა {index} / {files} &hellip;',
        msgProgress: 'ფაილის ატვირთვა დასრულებულია {index} / {files} - {name} - {percent}%.',
        msgSelected: 'არჩეულია {n} {file}',
        msgFoldersNotAllowed: 'დაშვებულია მხოლოდ ფაილების გადმოთრევა! გამოტოვებულია {n} გადმოთრეული ფოლდერი.',
        msgImageWidthSmall: 'სურათის "{name}" სიგანე უნდა იყოს არანაკლებ {size} px.',
        msgImageHeightSmall: 'სურათის "{name}" სიმაღლე უნდა იყოს არანაკლებ {size} px.',
        msgImageWidthLarge: 'სურათის "{name}" სიგანე არ უნდა აღემატებოდეს {size} px-ს.',
        msgImageHeightLarge: 'სურათის "{name}" სიმაღლე არ უნდა აღემატებოდეს {size} px-ს.',
        msgImageResizeError: 'ვერ მოხერხდა სურათის ზომის შეცვლისთვის საჭირო მონაცემების გარკვევა.',
        msgImageResizeException: 'შეცდომა სურათის ზომის შეცვლისას.<pre>{errors}</pre>',
        msgAjaxError: 'დაფიქსირდა შეცდომა ოპერაციის {operation} შესრულებისას. ცადეთ მოგვიანებით!',
        msgAjaxProgressError: 'ვერ მოხერხდა ოპერაციის {operation} შესრულება',
        ajaxOperations: {
            deleteThumb: 'ფაილის წაშლა',
            uploadThumb: 'ფაილის ატვირთვა',
            uploadBatch: 'ფაილების ატვირთვა',
            uploadExtra: 'მონაცემების გაგზავნა ფორმიდან'
        },
        dropZoneTitle: 'გადმოათრიეთ ფაილები აქ &hellip;',
        dropZoneClickTitle: '<br>(ან დააჭირეთ რათა აირჩიოთ {files})',
        fileActionSettings: {
            removeTitle: 'ფაილის წაშლა',
            uploadTitle: 'ფაილის ატვირთვა',
            uploadRetryTitle: 'ატვირთვის გამეორება',
            downloadTitle: 'ფაილის ჩამოტვირთვა',
            zoomTitle: 'დეტალურად ნახვა',
            dragTitle: 'გადაადგილება / მიმდევრობის შეცვლა',
            indicatorNewTitle: 'ჯერ არ ატვირთულა',
            indicatorSuccessTitle: 'ატვირთულია',
            indicatorErrorTitle: 'ატვირთვის შეცდომა',
            indicatorLoadingTitle: 'ატვირთვა ...'
        },
        previewZoomButtonTitles: {
            prev: 'წინა ფაილის ნახვა',
            next: 'შემდეგი ფაილის ნახვა',
            toggleheader: 'სათაურის დამალვა',
            fullscreen: 'მთელ ეკრანზე გაშლა',
            borderless: 'მთელ გვერდზე გაშლა',
            close: 'დახურვა'
        }
    };
})(window.jQuery);

//! moment.js locale configuration
//! locale : Georgian [ka]
//! author : Irakli Janiashvili : https://github.com/IrakliJani

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';

    //! moment.js locale configuration

    var ka = moment.defineLocale('ka', {
        months: 'იანვარი_თებერვალი_მარტი_აპრილი_მაისი_ივნისი_ივლისი_აგვისტო_სექტემბერი_ოქტომბერი_ნოემბერი_დეკემბერი'.split(
            '_'
        ),
        monthsShort: 'იან_თებ_მარ_აპრ_მაი_ივნ_ივლ_აგვ_სექ_ოქტ_ნოე_დეკ'.split('_'),
        weekdays: {
            standalone: 'კვირა_ორშაბათი_სამშაბათი_ოთხშაბათი_ხუთშაბათი_პარასკევი_შაბათი'.split(
                '_'
            ),
            format: 'კვირას_ორშაბათს_სამშაბათს_ოთხშაბათს_ხუთშაბათს_პარასკევს_შაბათს'.split(
                '_'
            ),
            isFormat: /(წინა|შემდეგ)/,
        },
        weekdaysShort: 'კვი_ორშ_სამ_ოთხ_ხუთ_პარ_შაბ'.split('_'),
        weekdaysMin: 'კვ_ორ_სა_ოთ_ხუ_პა_შა'.split('_'),
        longDateFormat: {
            LT: 'HH:mm',
            LTS: 'HH:mm:ss',
            L: 'DD/MM/YYYY',
            LL: 'D MMMM YYYY',
            LLL: 'D MMMM YYYY HH:mm',
            LLLL: 'dddd, D MMMM YYYY HH:mm',
        },
        calendar: {
            sameDay: '[დღეს] LT[-ზე]',
            nextDay: '[ხვალ] LT[-ზე]',
            lastDay: '[გუშინ] LT[-ზე]',
            nextWeek: '[შემდეგ] dddd LT[-ზე]',
            lastWeek: '[წინა] dddd LT-ზე',
            sameElse: 'L',
        },
        relativeTime: {
            future: function (s) {
                return s.replace(/(წამ|წუთ|საათ|წელ|დღ|თვ)(ი|ე)/, function (
                    $0,
                    $1,
                    $2
                ) {
                    return $2 === 'ი' ? $1 + 'ში' : $1 + $2 + 'ში';
                });
            },
            past: function (s) {
                if (/(წამი|წუთი|საათი|დღე|თვე)/.test(s)) {
                    return s.replace(/(ი|ე)$/, 'ის წინ');
                }
                if (/წელი/.test(s)) {
                    return s.replace(/წელი$/, 'წლის წინ');
                }
                return s;
            },
            s: 'რამდენიმე წამი',
            ss: '%d წამი',
            m: 'წუთი',
            mm: '%d წუთი',
            h: 'საათი',
            hh: '%d საათი',
            d: 'დღე',
            dd: '%d დღე',
            M: 'თვე',
            MM: '%d თვე',
            y: 'წელი',
            yy: '%d წელი',
        },
        dayOfMonthOrdinalParse: /0|1-ლი|მე-\d{1,2}|\d{1,2}-ე/,
        ordinal: function (number) {
            if (number === 0) {
                return number;
            }
            if (number === 1) {
                return number + '-ლი';
            }
            if (
                number < 20 ||
                (number <= 100 && number % 20 === 0) ||
                number % 100 === 0
            ) {
                return 'მე-' + number;
            }
            return number + '-ე';
        },
        week: {
            dow: 1,
            doy: 7,
        },
    });

    return ka;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/ka",[],function(){return{errorLoading:function(){return"მონაცემების ჩატვირთვა შეუძლებელია."},inputTooLong:function(n){return"გთხოვთ აკრიფეთ "+(n.input.length-n.maximum)+" სიმბოლოთი ნაკლები"},inputTooShort:function(n){return"გთხოვთ აკრიფეთ "+(n.minimum-n.input.length)+" სიმბოლო ან მეტი"},loadingMore:function(){return"მონაცემების ჩატვირთვა…"},maximumSelected:function(n){return"თქვენ შეგიძლიათ აირჩიოთ არაუმეტეს "+n.maximum+" ელემენტი"},noResults:function(){return"რეზულტატი არ მოიძებნა"},searching:function(){return"ძიება…"},removeAllItems:function(){return"ამოიღე ყველა ელემენტი"}}}),n.define,n.require}();