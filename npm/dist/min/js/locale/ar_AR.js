!function(e,n){"function"==typeof define&&define.amd?define(["jquery","query-builder"],n):n(e.jQuery)}(this,function(e){"use strict";var n=e.fn.queryBuilder;n.regional.ar={__locale:"Arabic (ar)",__author:"Mohamed YOUNES, https://github.com/MedYOUNES",add_rule:"إضافة حُكم",add_group:"إضافة زُمْرَة",delete_rule:"حذف",delete_group:"حذف",conditions:{AND:"و",OR:"أو"},operators:{equal:"يساوي",not_equal:"غير مساوٍ",in:"في",not_in:"ليس في",less:"أقل من",less_or_equal:"أصغر أو مساو",greater:"أكبر",greater_or_equal:"أكبر أو مساو",between:"محصور بين",not_between:"غير محصور بين",begins_with:"يبدأ بـ",not_begins_with:"لا يبدأ بـ",contains:"يحتوي على",not_contains:"لا يحتوي على",ends_with:"ينتهي بـ",not_ends_with:"لا ينتهي بـ",is_empty:"فارغ",is_not_empty:"غير فارغ",is_null:"صفر",is_not_null:"ليس صفرا"},errors:{no_filter:"لم تحدد أي مرشح",empty_group:"الزمرة فارغة",radio_empty:"لم تحدد أي قيمة",checkbox_empty:"لم تحدد أي قيمة",select_empty:"لم تحدد أي قيمة",string_empty:"النص فارغ",string_exceed_min_length:"النص دون الأدنى المسموح به",string_exceed_max_length:"النص فوق الأقصى المسموح به",string_invalid_format:"تركيبة غير صحيحة",number_nan:"ليس عددا",number_not_integer:"ليس عددا صحيحا",number_not_double:"ليس عددا كسريا",number_exceed_min:"العدد أصغر من الأدنى المسموح به",number_exceed_max:"العدد أكبر من الأقصى المسموح به",number_wrong_step:"أخطأت في حساب مضاعفات العدد",datetime_empty:"لم تحدد التاريخ",datetime_invalid:"صيغة التاريخ غير صحيحة",datetime_exceed_min:"التاريخ دون الأدنى المسموح به",datetime_exceed_max:"التاريخ أكبر من الأقصى المسموح به",boolean_not_valid:"ليست قيمة منطقية ثنائية",operator_not_multiple:"العامل ليس متعدد القيَم"},invert:"قَلْبُ"},n.defaults({lang_code:"ar"})}),function(e){"use strict";e.fn.fileinputLocales.ar={fileSingle:"ملف",filePlural:"ملفات",browseLabel:"تصفح &hellip;",removeLabel:"إزالة",removeTitle:"إزالة الملفات المختارة",cancelLabel:"إلغاء",cancelTitle:"إنهاء الرفع الحالي",uploadLabel:"رفع",uploadTitle:"رفع الملفات المختارة",msgNo:"لا",msgNoFilesSelected:"",msgCancelled:"ألغيت",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"معاينة تفصيلية",msgFileRequired:"You must select a file to upload.",msgSizeTooSmall:'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',msgSizeTooLarge:'الملف "{name}" (<b>{size} ك.ب</b>) تعدى الحد الأقصى المسموح للرفع <b>{maxSize} ك.ب</b>.',msgFilesTooLess:"يجب عليك اختيار <b>{n}</b> {files} على الأقل للرفع.",msgFilesTooMany:"عدد الملفات المختارة للرفع <b>({n})</b> تعدت الحد الأقصى المسموح به لعدد <b>{m}</b>.",msgFileNotFound:'الملف "{name}" غير موجود!',msgFileSecured:'قيود أمنية تمنع قراءة الملف "{name}".',msgFileNotReadable:'الملف "{name}" غير قابل للقراءة.',msgFilePreviewAborted:'تم إلغاء معاينة الملف "{name}".',msgFilePreviewError:'حدث خطأ أثناء قراءة الملف "{name}".',msgInvalidFileName:'Invalid or unsupported characters in file name "{name}".',msgInvalidFileType:'نوعية غير صالحة للملف "{name}". فقط هذه النوعيات مدعومة "{types}".',msgInvalidFileExtension:'امتداد غير صالح للملف "{name}". فقط هذه الملفات مدعومة "{extensions}".',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"تم إلغاء رفع الملف",msgUploadThreshold:"Processing...",msgUploadBegin:"Initializing...",msgUploadEnd:"Done",msgUploadEmpty:"No valid data available for upload.",msgUploadError:"Error",msgValidationError:"خطأ التحقق من صحة",msgLoading:"تحميل ملف {index} من {files} &hellip;",msgProgress:"تحميل ملف {index} من {files} - {name} - {percent}% منتهي.",msgSelected:"{n} {files} مختار(ة)",msgFoldersNotAllowed:"اسحب وأفلت الملفات فقط! تم تخطي {n} مجلد(ات).",msgImageWidthSmall:'عرض ملف الصورة "{name}" يجب أن يكون على الأقل {size} px.',msgImageHeightSmall:'طول ملف الصورة "{name}" يجب أن يكون على الأقل {size} px.',msgImageWidthLarge:'عرض ملف الصورة "{name}" لا يمكن أن يتعدى {size} px.',msgImageHeightLarge:'طول ملف الصورة "{name}" لا يمكن أن يتعدى {size} px.',msgImageResizeError:"لم يتمكن من معرفة أبعاد الصورة لتغييرها.",msgImageResizeException:"حدث خطأ أثناء تغيير أبعاد الصورة.<pre>{errors}</pre>",msgAjaxError:"Something went wrong with the {operation} operation. Please try again later!",msgAjaxProgressError:"{operation} failed",ajaxOperations:{deleteThumb:"file delete",uploadThumb:"file upload",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"اسحب وأفلت الملفات هنا &hellip;",dropZoneClickTitle:"<br>(or click to select {files})",fileActionSettings:{removeTitle:"إزالة الملف",uploadTitle:"رفع الملف",uploadRetryTitle:"Retry upload",downloadTitle:"Download file",zoomTitle:"مشاهدة التفاصيل",dragTitle:"Move / Rearrange",indicatorNewTitle:"لم يتم الرفع بعد",indicatorSuccessTitle:"تم الرفع",indicatorErrorTitle:"خطأ بالرفع",indicatorLoadingTitle:"جارٍ الرفع ..."},previewZoomButtonTitles:{prev:"View previous file",next:"View next file",toggleheader:"Toggle header",fullscreen:"Toggle full screen",borderless:"Toggle borderless mode",close:"Close detailed preview"}}}(window.jQuery),function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";var n={1:"١",2:"٢",3:"٣",4:"٤",5:"٥",6:"٦",7:"٧",8:"٨",9:"٩",0:"٠"},t={"١":"1","٢":"2","٣":"3","٤":"4","٥":"5","٦":"6","٧":"7","٨":"8","٩":"9","٠":"0"},i=function(e){return 0===e?0:1===e?1:2===e?2:e%100>=3&&e%100<=10?3:e%100>=11?4:5},o={s:["أقل من ثانية","ثانية واحدة",["ثانيتان","ثانيتين"],"%d ثوان","%d ثانية","%d ثانية"],m:["أقل من دقيقة","دقيقة واحدة",["دقيقتان","دقيقتين"],"%d دقائق","%d دقيقة","%d دقيقة"],h:["أقل من ساعة","ساعة واحدة",["ساعتان","ساعتين"],"%d ساعات","%d ساعة","%d ساعة"],d:["أقل من يوم","يوم واحد",["يومان","يومين"],"%d أيام","%d يومًا","%d يوم"],M:["أقل من شهر","شهر واحد",["شهران","شهرين"],"%d أشهر","%d شهرا","%d شهر"],y:["أقل من عام","عام واحد",["عامان","عامين"],"%d أعوام","%d عامًا","%d عام"]},r=function(e){return function(n,t,r,l){var a=i(n),d=o[e][i(n)];return 2===a&&(d=d[t?0:1]),d.replace(/%d/i,n)}},l=["يناير","فبراير","مارس","أبريل","مايو","يونيو","يوليو","أغسطس","سبتمبر","أكتوبر","نوفمبر","ديسمبر"];return e.defineLocale("ar",{months:l,monthsShort:l,weekdays:"الأحد_الإثنين_الثلاثاء_الأربعاء_الخميس_الجمعة_السبت".split("_"),weekdaysShort:"أحد_إثنين_ثلاثاء_أربعاء_خميس_جمعة_سبت".split("_"),weekdaysMin:"ح_ن_ث_ر_خ_ج_س".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"D/‏M/‏YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd D MMMM YYYY HH:mm"},meridiemParse:/ص|م/,isPM:function(e){return"م"===e},meridiem:function(e,n,t){return e<12?"ص":"م"},calendar:{sameDay:"[اليوم عند الساعة] LT",nextDay:"[غدًا عند الساعة] LT",nextWeek:"dddd [عند الساعة] LT",lastDay:"[أمس عند الساعة] LT",lastWeek:"dddd [عند الساعة] LT",sameElse:"L"},relativeTime:{future:"بعد %s",past:"منذ %s",s:r("s"),ss:r("s"),m:r("m"),mm:r("m"),h:r("h"),hh:r("h"),d:r("d"),dd:r("d"),M:r("M"),MM:r("M"),y:r("y"),yy:r("y")},preparse:function(e){return e.replace(/[١٢٣٤٥٦٧٨٩٠]/g,function(e){return t[e]}).replace(/،/g,",")},postformat:function(e){return e.replace(/\d/g,function(e){return n[e]}).replace(/,/g,"،")},week:{dow:6,doy:12}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/ar",[],function(){return{errorLoading:function(){return"لا يمكن تحميل النتائج"},inputTooLong:function(e){return"الرجاء حذف "+(e.input.length-e.maximum)+" عناصر"},inputTooShort:function(e){return"الرجاء إضافة "+(e.minimum-e.input.length)+" عناصر"},loadingMore:function(){return"جاري تحميل نتائج إضافية..."},maximumSelected:function(e){return"تستطيع إختيار "+e.maximum+" بنود فقط"},noResults:function(){return"لم يتم العثور على أي نتائج"},searching:function(){return"جاري البحث…"},removeAllItems:function(){return"قم بإزالة كل العناصر"}}}),e.define,e.require}(),function(e,n){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return n(e)}):"object"==typeof module&&module.exports?module.exports=n(require("jquery")):n(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"لم يتم إختيار شئ",noneResultsText:"لا توجد نتائج مطابقة لـ {0}",countSelectedText:function(e,n){return 1==e?"{0} خيار تم إختياره":"{0} خيارات تمت إختيارها"},maxOptionsText:function(e,n){return[1==e?"تخطى الحد المسموح ({n} خيار بحد أقصى)":"تخطى الحد المسموح ({n} خيارات بحد أقصى)",1==n?"تخطى الحد المسموح للمجموعة ({n} خيار بحد أقصى)":"تخطى الحد المسموح للمجموعة ({n} خيارات بحد أقصى)"]},selectAllText:"إختيار الجميع",deselectAllText:"إلغاء إختيار الجميع",multipleSeparator:"، "}}(e)});
