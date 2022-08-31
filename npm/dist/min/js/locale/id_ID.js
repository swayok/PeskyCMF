!function(e){"use strict";e.fn.fileinputLocales.id={fileSingle:"berkas",filePlural:"berkas",browseLabel:"Pilih berkas &hellip;",removeLabel:"Hapus",removeTitle:"Hapus berkas terpilih",cancelLabel:"Batal",cancelTitle:"Batalkan proses pengunggahan",uploadLabel:"Unggah",uploadTitle:"Unggah berkas terpilih",msgNo:"Tidak",msgNoFilesSelected:"",msgCancelled:"Dibatalkan",msgPlaceholder:"Pilih {files}...",msgZoomModalHeading:"Pratinjau terperinci",msgFileRequired:"Anda harus memilih berkas untuk diunggah.",msgSizeTooSmall:'Berkas "{name}" (<b>{size} KB</b>) terlalu kecil dan harus lebih besar dari <b>{minSize} KB</b>.',msgSizeTooLarge:'Berkas "{name}" (<b>{size} KB</b>) melebihi ukuran unggah maksimal yaitu <b>{maxSize} KB</b>.',msgFilesTooLess:"Anda harus memilih setidaknya <b>{n}</b> {files} untuk diunggah.",msgFilesTooMany:"<b>({n})</b> berkas yang dipilih untuk diunggah melebihi ukuran unggah maksimal yaitu <b>{m}</b>.",msgFileNotFound:'Berkas "{name}" tak ditemukan!',msgFileSecured:'Sistem keamanan mencegah untuk membaca berkas "{name}".',msgFileNotReadable:'Berkas "{name}" tak dapat dibaca.',msgFilePreviewAborted:'Pratinjau untuk berkas "{name}" dibatalkan.',msgFilePreviewError:'Kesalahan saat membaca berkas "{name}".',msgInvalidFileName:'Karakter tidak dikenali atau tidak didukung untuk nama berkas "{name}".',msgInvalidFileType:'Jenis berkas "{name}" tidak sah. Hanya berkas "{types}" yang didukung.',msgInvalidFileExtension:'Ekstensi berkas "{name}" tidak sah. Hanya ekstensi "{extensions}" yang didukung.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Proses Unggah berkas dibatalkan",msgUploadThreshold:"Memproses...",msgUploadBegin:"Menyiapkan...",msgUploadEnd:"Selesai",msgUploadEmpty:"Tidak ada data valid yang tersedia untuk diunggah.",msgUploadError:"Kesalahan",msgValidationError:"Kesalahan saat memvalidasi",msgLoading:"Memuat {index} dari {files} berkas &hellip;",msgProgress:"Memuat {index} dari {files} berkas - {name} - {percent}% selesai.",msgSelected:"{n} {files} dipilih",msgFoldersNotAllowed:"Hanya tahan dan lepas file saja! {n} folder diabaikan.",msgImageWidthSmall:'Lebar dari gambar "{name}" harus sekurangnya {size} px.',msgImageHeightSmall:'Tinggi dari gambar "{name}" harus sekurangnya {size} px.',msgImageWidthLarge:'Lebar dari gambar "{name}" tak boleh melebihi {size} px.',msgImageHeightLarge:'Tinggi dari gambar "{name}" tak boleh melebihi {size} px.',msgImageResizeError:"Tidak dapat menentukan dimensi gambar untuk mengubah ukuran.",msgImageResizeException:"Kesalahan saat mengubah ukuran gambar.<pre>{errors}</pre>",msgAjaxError:"Terjadi kesalahan ketika melakukan operasi {operation}. Silahkan coba lagi nanti!",msgAjaxProgressError:"{operation} gagal",ajaxOperations:{deleteThumb:"Hapus berkas",uploadThumb:"Unggah berkas",uploadBatch:"Unggah banyak berkas",uploadExtra:"Unggah form ekstra"},dropZoneTitle:"Tarik dan lepaskan berkas disini &hellip;",dropZoneClickTitle:"<br>(atau klik untuk memilih {files})",fileActionSettings:{removeTitle:"Hapus Berkas",uploadTitle:"Unggah Berkas",uploadRetryTitle:"Unggah Ulang",downloadTitle:"Unduh Berkas",zoomTitle:"Tampilkan Rincian",dragTitle:"Pindah atau Atur Ulang",indicatorNewTitle:"Belum diunggah",indicatorSuccessTitle:"Sudah diunggah",indicatorErrorTitle:"Kesalahan dalam mengungah",indicatorLoadingTitle:"Mengunggah ..."},previewZoomButtonTitles:{prev:"Lihat berkas sebelumnya",next:"Lihat berkas selanjutnya",toggleheader:"Beralih ke tajuk",fullscreen:"Beralih ke mode penuh",borderless:"Beralih ke mode tanpa tepi",close:"Tutup pratinjau terperinci"}}}(window.jQuery),function(e,a){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?a(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],a):a(e.moment)}(this,function(e){"use strict";return e.defineLocale("id",{months:"Januari_Februari_Maret_April_Mei_Juni_Juli_Agustus_September_Oktober_November_Desember".split("_"),monthsShort:"Jan_Feb_Mar_Apr_Mei_Jun_Jul_Agt_Sep_Okt_Nov_Des".split("_"),weekdays:"Minggu_Senin_Selasa_Rabu_Kamis_Jumat_Sabtu".split("_"),weekdaysShort:"Min_Sen_Sel_Rab_Kam_Jum_Sab".split("_"),weekdaysMin:"Mg_Sn_Sl_Rb_Km_Jm_Sb".split("_"),longDateFormat:{LT:"HH.mm",LTS:"HH.mm.ss",L:"DD/MM/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY [pukul] HH.mm",LLLL:"dddd, D MMMM YYYY [pukul] HH.mm"},meridiemParse:/pagi|siang|sore|malam/,meridiemHour:function(e,a){return 12===e&&(e=0),"pagi"===a?e:"siang"===a?e>=11?e:e+12:"sore"===a||"malam"===a?e+12:void 0},meridiem:function(e,a,i){return e<11?"pagi":e<15?"siang":e<19?"sore":"malam"},calendar:{sameDay:"[Hari ini pukul] LT",nextDay:"[Besok pukul] LT",nextWeek:"dddd [pukul] LT",lastDay:"[Kemarin pukul] LT",lastWeek:"dddd [lalu pukul] LT",sameElse:"L"},relativeTime:{future:"dalam %s",past:"%s yang lalu",s:"beberapa detik",ss:"%d detik",m:"semenit",mm:"%d menit",h:"sejam",hh:"%d jam",d:"sehari",dd:"%d hari",M:"sebulan",MM:"%d bulan",y:"setahun",yy:"%d tahun"},week:{dow:0,doy:6}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/id",[],function(){return{errorLoading:function(){return"Data tidak boleh diambil."},inputTooLong:function(e){return"Hapuskan "+(e.input.length-e.maximum)+" huruf"},inputTooShort:function(e){return"Masukkan "+(e.minimum-e.input.length)+" huruf lagi"},loadingMore:function(){return"Mengambil data…"},maximumSelected:function(e){return"Anda hanya dapat memilih "+e.maximum+" pilihan"},noResults:function(){return"Tidak ada data yang sesuai"},searching:function(){return"Mencari…"},removeAllItems:function(){return"Hapus semua item"}}}),e.define,e.require}(),function(e,a){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return a(e)}):"object"==typeof module&&module.exports?module.exports=a(require("jquery")):a(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Tidak ada yang dipilih",noneResultsText:"Tidak ada yang cocok {0}",countSelectedText:"{0} terpilih",maxOptionsText:["Mencapai batas (maksimum {n})","Mencapai batas grup (maksimum {n})"],selectAllText:"Pilih Semua",deselectAllText:"Hapus Semua",multipleSeparator:", "}}(e)});
