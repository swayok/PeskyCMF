!function(e,a){"function"==typeof define&&define.amd?define(["jquery","query-builder"],a):a(e.jQuery)}(this,function(e){"use strict";var a=e.fn.queryBuilder;a.regional.tr={__locale:"Turkish (tr)",__author:"Aykut Alpgiray Ateş",add_rule:"Kural Ekle",add_group:"Grup Ekle",delete_rule:"Sil",delete_group:"Sil",conditions:{AND:"Ve",OR:"Veya"},operators:{equal:"eşit",not_equal:"eşit değil",in:"içinde",not_in:"içinde değil",less:"küçük",less_or_equal:"küçük veya eşit",greater:"büyük",greater_or_equal:"büyük veya eşit",between:"arasında",not_between:"arasında değil",begins_with:"ile başlayan",not_begins_with:"ile başlamayan",contains:"içeren",not_contains:"içermeyen",ends_with:"ile biten",not_ends_with:"ile bitmeyen",is_empty:"boş ise",is_not_empty:"boş değil ise",is_null:"var ise",is_not_null:"yok ise"},errors:{no_filter:"Bir filtre seçili değil",empty_group:"Grup bir eleman içermiyor",radio_empty:"Seçim yapılmalı",checkbox_empty:"Seçim yapılmalı",select_empty:"Seçim yapılmalı",string_empty:"Bir metin girilmeli",string_exceed_min_length:"En az {0} karakter girilmeli",string_exceed_max_length:"En fazla {0} karakter girilebilir",string_invalid_format:"Uyumsuz format ({0})",number_nan:"Sayı değil",number_not_integer:"Tam sayı değil",number_not_double:"Ondalıklı sayı değil",number_exceed_min:"Sayı {0}'den/dan daha büyük olmalı",number_exceed_max:"Sayı {0}'den/dan daha küçük olmalı",number_wrong_step:"{0} veya katı olmalı",number_between_invalid:"Geçersiz değerler, {0} değeri {1} değerinden büyük",datetime_empty:"Tarih Seçilmemiş",datetime_invalid:"Uygun olmayan tarih formatı ({0})",datetime_exceed_min:"{0} Tarihinden daha sonrası olmalı.",datetime_exceed_max:"{0} Tarihinden daha öncesi olmalı.",datetime_between_invalid:"Geçersiz değerler, {0} değeri {1} değerinden büyük",boolean_not_valid:"Değer Doğru/Yanlış(bool) olmalı",operator_not_multiple:'Operatör "{1}" birden fazla değer kabul etmiyor'},invert:"Ters Çevir"},a.defaults({lang_code:"tr"})}),function(e){"use strict";e.fn.fileinputLocales.tr={fileSingle:"dosya",filePlural:"dosyalar",browseLabel:"Gözat &hellip;",removeLabel:"Sil",removeTitle:"Seçilen dosyaları sil",cancelLabel:"İptal",cancelTitle:"Devam eden yüklemeyi iptal et",uploadLabel:"Yükle",uploadTitle:"Seçilen dosyaları yükle",msgNo:"Hayır",msgNoFilesSelected:"",msgCancelled:"İptal edildi",msgPlaceholder:"Seçilen {files}...",msgZoomModalHeading:"Detaylı Önizleme",msgFileRequired:"Yüklemek için bir dosya seçmelisiniz.",msgSizeTooSmall:'"{name}"(<b>{size} KB</b>) dosyası çok küçük  ve <b>{minSize} KB</b> boyutundan büyük olmalıdır.',msgSizeTooLarge:'"{name}" dosyasının boyutu (<b>{size} KB</b>) izin verilen azami dosya boyutu olan <b>{maxSize} KB</b>\'tan büyük.',msgFilesTooLess:"Yüklemek için en az <b>{n}</b> {files} dosya seçmelisiniz.",msgFilesTooMany:"Yüklemek için seçtiğiniz dosya sayısı <b>({n})</b> azami limitin <b>({m})</b> altında olmalıdır.",msgFileNotFound:'"{name}" dosyası bulunamadı!',msgFileSecured:'Güvenlik kısıtlamaları "{name}" dosyasının okunmasını engelliyor.',msgFileNotReadable:'"{name}" dosyası okunabilir değil.',msgFilePreviewAborted:'"{name}" dosyası için önizleme iptal edildi.',msgFilePreviewError:'"{name}" dosyası okunurken bir hata oluştu.',msgInvalidFileName:'"{name}" dosya adında geçersiz veya desteklenmeyen karakterler var.',msgInvalidFileType:'"{name}" dosyasının türü geçerli değil. Yalnızca "{types}" türünde dosyalara izin veriliyor.',msgInvalidFileExtension:'"{name}" dosyasının uzantısı geçersiz. Yalnızca "{extensions}" uzantılı dosyalara izin veriliyor.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Dosya yükleme iptal edildi",msgUploadThreshold:"İşlem yapılıyor...",msgUploadBegin:"Başlıyor...",msgUploadEnd:"Başarılı",msgUploadEmpty:"Yüklemek için geçerli veri mevcut değil.",msgUploadError:"Hata",msgValidationError:"Doğrulama Hatası",msgLoading:"Dosya yükleniyor {index} / {files} &hellip;",msgProgress:"Dosya yükleniyor {index} / {files} - {name} - %{percent} tamamlandı.",msgSelected:"{n} {files} seçildi",msgFoldersNotAllowed:"Yalnızca dosyaları sürükleyip bırakabilirsiniz! {n} dizin(ler) göz ardı edildi.",msgImageWidthSmall:'"{name}" adlı görüntü dosyasının genişliği en az {size} piksel olmalıdır.',msgImageHeightSmall:'"{name}" adlı görüntü dosyasının yüksekliği en az {size} piksel olmalıdır.',msgImageWidthLarge:'"{name}" adlı görüntü dosyasının genişliği {size} pikseli geçemez.',msgImageHeightLarge:'"{name}" adlı görüntü dosyasının yüksekliği {size} pikseli geçemez.',msgImageResizeError:"Görüntü boyutlarını yeniden boyutlandıramadı.",msgImageResizeException:"Görüntü boyutlandırma sırasında hata.<pre>{errors}</pre>",msgAjaxError:"{operation} işlemi ile ilgili bir şeyler ters gitti. Lütfen daha sonra tekrar deneyiniz!",msgAjaxProgressError:"{operation} işlemi başarısız oldu.",ajaxOperations:{deleteThumb:"dosya silme",uploadThumb:"dosya yükleme",uploadBatch:"toplu dosya yükleme",uploadExtra:"form verisi yükleme"},dropZoneTitle:"Dosyaları buraya sürükleyip bırakın",dropZoneClickTitle:"<br>(ya da {files} seçmek için tıklayınız)",fileActionSettings:{removeTitle:"Dosyayı kaldır",uploadTitle:"Dosyayı yükle",uploadRetryTitle:"Retry upload",zoomTitle:"Ayrıntıları görüntüle",dragTitle:"Taşı / Yeniden düzenle",indicatorNewTitle:"Henüz yüklenmedi",indicatorSuccessTitle:"Yüklendi",indicatorErrorTitle:"Yükleme Hatası",indicatorLoadingTitle:"Yükleniyor ..."},previewZoomButtonTitles:{prev:"Önceki dosyayı göster",next:"Sonraki dosyayı göster",toggleheader:"Üst bilgi geçiş",fullscreen:"Tam ekran geçiş",borderless:"Çerçevesiz moda geçiş",close:"Detaylı önizlemeyi kapat"}}}(window.jQuery),function(e,a){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?a(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],a):a(e.moment)}(this,function(e){"use strict";var a={1:"'inci",5:"'inci",8:"'inci",70:"'inci",80:"'inci",2:"'nci",7:"'nci",20:"'nci",50:"'nci",3:"'üncü",4:"'üncü",100:"'üncü",6:"'ncı",9:"'uncu",10:"'uncu",30:"'uncu",60:"'ıncı",90:"'ıncı"};return e.defineLocale("tr",{months:"Ocak_Şubat_Mart_Nisan_Mayıs_Haziran_Temmuz_Ağustos_Eylül_Ekim_Kasım_Aralık".split("_"),monthsShort:"Oca_Şub_Mar_Nis_May_Haz_Tem_Ağu_Eyl_Eki_Kas_Ara".split("_"),weekdays:"Pazar_Pazartesi_Salı_Çarşamba_Perşembe_Cuma_Cumartesi".split("_"),weekdaysShort:"Paz_Pts_Sal_Çar_Per_Cum_Cts".split("_"),weekdaysMin:"Pz_Pt_Sa_Ça_Pe_Cu_Ct".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd, D MMMM YYYY HH:mm"},calendar:{sameDay:"[bugün saat] LT",nextDay:"[yarın saat] LT",nextWeek:"[gelecek] dddd [saat] LT",lastDay:"[dün] LT",lastWeek:"[geçen] dddd [saat] LT",sameElse:"L"},relativeTime:{future:"%s sonra",past:"%s önce",s:"birkaç saniye",ss:"%d saniye",m:"bir dakika",mm:"%d dakika",h:"bir saat",hh:"%d saat",d:"bir gün",dd:"%d gün",M:"bir ay",MM:"%d ay",y:"bir yıl",yy:"%d yıl"},ordinal:function(e,i){switch(i){case"d":case"D":case"Do":case"DD":return e;default:if(0===e)return e+"'ıncı";var n=e%10,l=e%100-n,r=e>=100?100:null;return e+(a[n]||a[l]||a[r])}},week:{dow:1,doy:7}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/tr",[],function(){return{errorLoading:function(){return"Sonuç yüklenemedi"},inputTooLong:function(e){return e.input.length-e.maximum+" karakter daha girmelisiniz"},inputTooShort:function(e){return"En az "+(e.minimum-e.input.length)+" karakter daha girmelisiniz"},loadingMore:function(){return"Daha fazla…"},maximumSelected:function(e){return"Sadece "+e.maximum+" seçim yapabilirsiniz"},noResults:function(){return"Sonuç bulunamadı"},searching:function(){return"Aranıyor…"},removeAllItems:function(){return"Tüm öğeleri kaldır"}}}),e.define,e.require}(),function(e,a){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return a(e)}):"object"==typeof module&&module.exports?module.exports=a(require("jquery")):a(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Hiçbiri seçilmedi",noneResultsText:"Hiçbir sonuç bulunamadı {0}",countSelectedText:function(e,a){return"{0} öğe seçildi"},maxOptionsText:function(e,a){return[1==e?"Limit aşıldı (maksimum {n} sayıda öğe )":"Limit aşıldı (maksimum {n} sayıda öğe)","Grup limiti aşıldı (maksimum {n} sayıda öğe)"]},selectAllText:"Tümünü Seç",deselectAllText:"Seçiniz",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["tr-TR"]={currentlySelected:"Seçili olanlar",emptyTitle:"Seç ve yazmaya başla",errorText:"Sonuçlar alınamadı",searchPlaceholder:"Ara...",statusInitialized:"Arama için yazmaya başla",statusNoResults:"Sonuç yok",statusSearching:"Aranıyor...",statusTooShort:"Lütfen daha fazla karakter girin"},e.fn.ajaxSelectPicker.locale.tr=e.fn.ajaxSelectPicker.locale["tr-TR"]}(jQuery);
