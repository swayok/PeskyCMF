/*!
 * FileInput Indonesian Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 * @author Bambang Riswanto <bamz3r@gmail.com>
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['id'] = {
        fileSingle: 'berkas',
        filePlural: 'berkas',
        browseLabel: 'Pilih berkas &hellip;',
        removeLabel: 'Hapus',
        removeTitle: 'Hapus berkas terpilih',
        cancelLabel: 'Batal',
        cancelTitle: 'Batalkan proses pengunggahan',
        uploadLabel: 'Unggah',
        uploadTitle: 'Unggah berkas terpilih',
        msgNo: 'Tidak',
        msgNoFilesSelected: '',
        msgCancelled: 'Dibatalkan',
        msgPlaceholder: 'Pilih {files}...',
        msgZoomModalHeading: 'Pratinjau terperinci',
        msgFileRequired: 'Anda harus memilih berkas untuk diunggah.',
        msgSizeTooSmall: 'Berkas "{name}" (<b>{size} KB</b>) terlalu kecil dan harus lebih besar dari <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Berkas "{name}" (<b>{size} KB</b>) melebihi ukuran unggah maksimal yaitu <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Anda harus memilih setidaknya <b>{n}</b> {files} untuk diunggah.',
        msgFilesTooMany: '<b>({n})</b> berkas yang dipilih untuk diunggah melebihi ukuran unggah maksimal yaitu <b>{m}</b>.',
        msgFileNotFound: 'Berkas "{name}" tak ditemukan!',
        msgFileSecured: 'Sistem keamanan mencegah untuk membaca berkas "{name}".',
        msgFileNotReadable: 'Berkas "{name}" tak dapat dibaca.',
        msgFilePreviewAborted: 'Pratinjau untuk berkas "{name}" dibatalkan.',
        msgFilePreviewError: 'Kesalahan saat membaca berkas "{name}".',
        msgInvalidFileName: 'Karakter tidak dikenali atau tidak didukung untuk nama berkas "{name}".',
        msgInvalidFileType: 'Jenis berkas "{name}" tidak sah. Hanya berkas "{types}" yang didukung.',
        msgInvalidFileExtension: 'Ekstensi berkas "{name}" tidak sah. Hanya ekstensi "{extensions}" yang didukung.',
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
        msgUploadAborted: 'Proses Unggah berkas dibatalkan',
        msgUploadThreshold: 'Memproses...',
        msgUploadBegin: 'Menyiapkan...',
        msgUploadEnd: 'Selesai',
        msgUploadEmpty: 'Tidak ada data valid yang tersedia untuk diunggah.',
        msgUploadError: 'Kesalahan',
        msgValidationError: 'Kesalahan saat memvalidasi',
        msgLoading: 'Memuat {index} dari {files} berkas &hellip;',
        msgProgress: 'Memuat {index} dari {files} berkas - {name} - {percent}% selesai.',
        msgSelected: '{n} {files} dipilih',
        msgFoldersNotAllowed: 'Hanya tahan dan lepas file saja! {n} folder diabaikan.',
        msgImageWidthSmall: 'Lebar dari gambar "{name}" harus sekurangnya {size} px.',
        msgImageHeightSmall: 'Tinggi dari gambar "{name}" harus sekurangnya {size} px.',
        msgImageWidthLarge: 'Lebar dari gambar "{name}" tak boleh melebihi {size} px.',
        msgImageHeightLarge: 'Tinggi dari gambar "{name}" tak boleh melebihi {size} px.',
        msgImageResizeError: 'Tidak dapat menentukan dimensi gambar untuk mengubah ukuran.',
        msgImageResizeException: 'Kesalahan saat mengubah ukuran gambar.<pre>{errors}</pre>',
        msgAjaxError: 'Terjadi kesalahan ketika melakukan operasi {operation}. Silahkan coba lagi nanti!',
        msgAjaxProgressError: '{operation} gagal',
        ajaxOperations: {
            deleteThumb: 'Hapus berkas',
            uploadThumb: 'Unggah berkas',
            uploadBatch: 'Unggah banyak berkas',
            uploadExtra: 'Unggah form ekstra'
        },
        dropZoneTitle: 'Tarik dan lepaskan berkas disini &hellip;',
        dropZoneClickTitle: '<br>(atau klik untuk memilih {files})',
        fileActionSettings: {
            removeTitle: 'Hapus Berkas',
            uploadTitle: 'Unggah Berkas',
            uploadRetryTitle: 'Unggah Ulang',
            downloadTitle: 'Unduh Berkas',
            zoomTitle: 'Tampilkan Rincian',
            dragTitle: 'Pindah atau Atur Ulang',
            indicatorNewTitle: 'Belum diunggah',
            indicatorSuccessTitle: 'Sudah diunggah',
            indicatorErrorTitle: 'Kesalahan dalam mengungah',
            indicatorLoadingTitle: 'Mengunggah ...'
        },
        previewZoomButtonTitles: {
            prev: 'Lihat berkas sebelumnya',
            next: 'Lihat berkas selanjutnya',
            toggleheader: 'Beralih ke tajuk',
            fullscreen: 'Beralih ke mode penuh',
            borderless: 'Beralih ke mode tanpa tepi',
            close: 'Tutup pratinjau terperinci'
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


    var id = moment.defineLocale('id', {
        months : 'Januari_Februari_Maret_April_Mei_Juni_Juli_Agustus_September_Oktober_November_Desember'.split('_'),
        monthsShort : 'Jan_Feb_Mar_Apr_Mei_Jun_Jul_Agt_Sep_Okt_Nov_Des'.split('_'),
        weekdays : 'Minggu_Senin_Selasa_Rabu_Kamis_Jumat_Sabtu'.split('_'),
        weekdaysShort : 'Min_Sen_Sel_Rab_Kam_Jum_Sab'.split('_'),
        weekdaysMin : 'Mg_Sn_Sl_Rb_Km_Jm_Sb'.split('_'),
        longDateFormat : {
            LT : 'HH.mm',
            LTS : 'HH.mm.ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY [pukul] HH.mm',
            LLLL : 'dddd, D MMMM YYYY [pukul] HH.mm'
        },
        meridiemParse: /pagi|siang|sore|malam/,
        meridiemHour : function (hour, meridiem) {
            if (hour === 12) {
                hour = 0;
            }
            if (meridiem === 'pagi') {
                return hour;
            } else if (meridiem === 'siang') {
                return hour >= 11 ? hour : hour + 12;
            } else if (meridiem === 'sore' || meridiem === 'malam') {
                return hour + 12;
            }
        },
        meridiem : function (hours, minutes, isLower) {
            if (hours < 11) {
                return 'pagi';
            } else if (hours < 15) {
                return 'siang';
            } else if (hours < 19) {
                return 'sore';
            } else {
                return 'malam';
            }
        },
        calendar : {
            sameDay : '[Hari ini pukul] LT',
            nextDay : '[Besok pukul] LT',
            nextWeek : 'dddd [pukul] LT',
            lastDay : '[Kemarin pukul] LT',
            lastWeek : 'dddd [lalu pukul] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'dalam %s',
            past : '%s yang lalu',
            s : 'beberapa detik',
            ss : '%d detik',
            m : 'semenit',
            mm : '%d menit',
            h : 'sejam',
            hh : '%d jam',
            d : 'sehari',
            dd : '%d hari',
            M : 'sebulan',
            MM : '%d bulan',
            y : 'setahun',
            yy : '%d tahun'
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 7  // The week that contains Jan 7th is the first week of the year.
        }
    });

    return id;

})));

/*! Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/id",[],function(){return{errorLoading:function(){return"Data tidak boleh diambil."},inputTooLong:function(e){var t=e.input.length-e.maximum;return"Hapuskan "+t+" huruf"},inputTooShort:function(e){var t=e.minimum-e.input.length;return"Masukkan "+t+" huruf lagi"},loadingMore:function(){return"Mengambil data…"},maximumSelected:function(e){return"Anda hanya dapat memilih "+e.maximum+" pilihan"},noResults:function(){return"Tidak ada data yang sesuai"},searching:function(){return"Mencari…"}}}),{define:e.define,require:e.require}})();
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
    noneSelectedText: 'Tidak ada yang dipilih',
    noneResultsText: 'Tidak ada yang cocok {0}',
    countSelectedText: '{0} terpilih',
    maxOptionsText: ['Mencapai batas (maksimum {n})', 'Mencapai batas grup (maksimum {n})'],
    selectAllText: 'Pilih Semua',
    deselectAllText: 'Hapus Semua',
    multipleSeparator: ', '
  };
})(jQuery);


}));
