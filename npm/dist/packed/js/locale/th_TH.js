/*!
 * FileInput Thai Translations
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

    $.fn.fileinputLocales['th'] = {
        fileSingle: 'ไฟล์',
        filePlural: 'ไฟล์',
        browseLabel: 'เลือกดู &hellip;',
        removeLabel: 'ลบทิ้ง',
        removeTitle: 'ลบไฟล์ที่เลือกทิ้ง',
        cancelLabel: 'ยกเลิก',
        cancelTitle: 'ยกเลิกการอัพโหลด',
        uploadLabel: 'อัพโหลด',
        uploadTitle: 'อัพโหลดไฟล์ที่เลือก',
        msgNo: 'ไม่',
        msgNoFilesSelected: '',
        msgCancelled: 'ยกเลิก',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'ตัวอย่างละเอียด',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'ไฟล์ "{name}" (<b>{size} KB</b>) มีขนาดเกินที่ระบบอนุญาตที่ <b>{maxSize} KB</b>, กรุณาลองใหม่อีกครั้ง!',
        msgFilesTooLess: 'คุณต้องเลือกไฟล์จำนวนอย่างน้อย <b>{n}</b> {files} เพื่ออัพโหลด, กรุณาลองใหม่อีกครั้ง!',
        msgFilesTooMany: 'ไฟล์ที่คุณเลือกมีจำนวน <b>({n})</b> ซึ่งเกินกว่าที่ระบบอนุญาตที่ <b>{m}</b>, กรุณาลองใหม่อีกครั้ง!',
        msgFileNotFound: 'ไม่พบไฟล์ "{name}" !',
        msgFileSecured: 'ระบบความปลอดภัยไม่อนุญาตให้อ่านไฟล์ "{name}".',
        msgFileNotReadable: 'ไม่สามารถอ่านไฟล์ "{name}" ได้',
        msgFilePreviewAborted: 'ไฟล์ "{name}" ไม่อนุญาตให้ดูตัวอย่าง',
        msgFilePreviewError: 'พบปัญหาในการดูตัวอย่างไฟล์ "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'ไฟล์ "{name}" เป็นประเภทไฟล์ที่ไม่ถูกต้อง, อนุญาตเฉพาะไฟล์ประเภท "{types}"',
        msgInvalidFileExtension: 'ไฟล์ "{name}" เป็น extension ที่ไมถูกต้อง, อนุญาตเฉพาะไฟล์ extension "{extensions}"',
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
        msgUploadAborted: 'อัปโหลดไฟล์ถูกยกเลิก',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'ข้อผิดพลาดในการตรวจสอบ',
        msgLoading: 'กำลังโหลดไฟล์ {index} จาก {files} &hellip;',
        msgProgress: 'กำลังโหลดไฟล์ {index} จาก {files} - {name} - {percent}%',
        msgSelected: '{n} {files} ถูกเลือก',
        msgFoldersNotAllowed: 'Drag & drop เฉพาะไฟล์เท่านั้น! ข้าม dropped folder จำนวน {n}',
        msgImageWidthSmall: 'ความกว้างของภาพไฟล์ "{name}" ต้องมีอย่างน้อย {size} px.',
        msgImageHeightSmall: 'ความสูงของภาพไฟล์ "{name}" ต้องมีอย่างน้อย {size} px.',
        msgImageWidthLarge: 'ความกว้างของภาพไฟล์ "{name}" ไม่เกิน {size} พิกเซล.',
        msgImageHeightLarge: 'ความสูงของไฟล์ภาพ "{name}" ไม่เกิน {size} พิกเซล.',
        msgImageResizeError: 'ไม่สามารถรับขนาดภาพเพื่อปรับขนาด',
        msgImageResizeException: 'ข้อผิดพลาดขณะปรับขนาดภาพ<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Drag & drop ไฟล์ตรงนี้ &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'ลบไฟล์',
            uploadTitle: 'อัปโหลดไฟล์',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'ดูรายละเอียด',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'ยังไม่ได้อัปโหลด',
            indicatorSuccessTitle: 'อัพโหลด',
            indicatorErrorTitle: 'อัปโหลดข้อผิดพลาด',
            indicatorLoadingTitle: 'อัพโหลด ...'
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
//! locale : Thai [th]
//! author : Kridsada Thanabulpong : https://github.com/sirn

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';

    //! moment.js locale configuration

    var th = moment.defineLocale('th', {
        months: 'มกราคม_กุมภาพันธ์_มีนาคม_เมษายน_พฤษภาคม_มิถุนายน_กรกฎาคม_สิงหาคม_กันยายน_ตุลาคม_พฤศจิกายน_ธันวาคม'.split(
            '_'
        ),
        monthsShort: 'ม.ค._ก.พ._มี.ค._เม.ย._พ.ค._มิ.ย._ก.ค._ส.ค._ก.ย._ต.ค._พ.ย._ธ.ค.'.split(
            '_'
        ),
        monthsParseExact: true,
        weekdays: 'อาทิตย์_จันทร์_อังคาร_พุธ_พฤหัสบดี_ศุกร์_เสาร์'.split('_'),
        weekdaysShort: 'อาทิตย์_จันทร์_อังคาร_พุธ_พฤหัส_ศุกร์_เสาร์'.split('_'), // yes, three characters difference
        weekdaysMin: 'อา._จ._อ._พ._พฤ._ศ._ส.'.split('_'),
        weekdaysParseExact: true,
        longDateFormat: {
            LT: 'H:mm',
            LTS: 'H:mm:ss',
            L: 'DD/MM/YYYY',
            LL: 'D MMMM YYYY',
            LLL: 'D MMMM YYYY เวลา H:mm',
            LLLL: 'วันddddที่ D MMMM YYYY เวลา H:mm',
        },
        meridiemParse: /ก่อนเที่ยง|หลังเที่ยง/,
        isPM: function (input) {
            return input === 'หลังเที่ยง';
        },
        meridiem: function (hour, minute, isLower) {
            if (hour < 12) {
                return 'ก่อนเที่ยง';
            } else {
                return 'หลังเที่ยง';
            }
        },
        calendar: {
            sameDay: '[วันนี้ เวลา] LT',
            nextDay: '[พรุ่งนี้ เวลา] LT',
            nextWeek: 'dddd[หน้า เวลา] LT',
            lastDay: '[เมื่อวานนี้ เวลา] LT',
            lastWeek: '[วัน]dddd[ที่แล้ว เวลา] LT',
            sameElse: 'L',
        },
        relativeTime: {
            future: 'อีก %s',
            past: '%sที่แล้ว',
            s: 'ไม่กี่วินาที',
            ss: '%d วินาที',
            m: '1 นาที',
            mm: '%d นาที',
            h: '1 ชั่วโมง',
            hh: '%d ชั่วโมง',
            d: '1 วัน',
            dd: '%d วัน',
            M: '1 เดือน',
            MM: '%d เดือน',
            y: '1 ปี',
            yy: '%d ปี',
        },
    });

    return th;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var n=jQuery.fn.select2.amd;n.define("select2/i18n/th",[],function(){return{errorLoading:function(){return"ไม่สามารถค้นข้อมูลได้"},inputTooLong:function(n){return"โปรดลบออก "+(n.input.length-n.maximum)+" ตัวอักษร"},inputTooShort:function(n){return"โปรดพิมพ์เพิ่มอีก "+(n.minimum-n.input.length)+" ตัวอักษร"},loadingMore:function(){return"กำลังค้นข้อมูลเพิ่ม…"},maximumSelected:function(n){return"คุณสามารถเลือกได้ไม่เกิน "+n.maximum+" รายการ"},noResults:function(){return"ไม่พบข้อมูล"},searching:function(){return"กำลังค้นข้อมูล…"},removeAllItems:function(){return"ลบรายการทั้งหมด"}}}),n.define,n.require}();