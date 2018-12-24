!function(e,i){"function"==typeof define&&define.amd?define(["jquery","query-builder"],i):i(e.jQuery)}(this,function(e){"use strict";var i=e.fn.queryBuilder;i.regional.ro={__locale:"Romanian (ro)",__author:"ArianServ",add_rule:"Adaugă regulă",add_group:"Adaugă grup",delete_rule:"Şterge",delete_group:"Şterge",conditions:{AND:"ŞI",OR:"SAU"},operators:{equal:"egal",not_equal:"diferit",in:"în",not_in:"nu în",less:"mai puţin",less_or_equal:"mai puţin sau egal",greater:"mai mare",greater_or_equal:"mai mare sau egal",begins_with:"începe cu",not_begins_with:"nu începe cu",contains:"conţine",not_contains:"nu conţine",ends_with:"se termină cu",not_ends_with:"nu se termină cu",is_empty:"este gol",is_not_empty:"nu este gol",is_null:"e nul",is_not_null:"nu e nul"}},i.defaults({lang_code:"ro"})}),function(e){"use strict";e.fn.fileinputLocales.ro={fileSingle:"fișier",filePlural:"fișiere",browseLabel:"Răsfoiește &hellip;",removeLabel:"Șterge",removeTitle:"Curăță fișierele selectate",cancelLabel:"Renunță",cancelTitle:"Anulează încărcarea curentă",uploadLabel:"Încarcă",uploadTitle:"Încarcă fișierele selectate",msgNo:"Nu",msgNoFilesSelected:"",msgCancelled:"Anulat",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"Previzualizare detaliată",msgFileRequired:"You must select a file to upload.",msgSizeTooSmall:'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',msgSizeTooLarge:'Fișierul "{name}" (<b>{size} KB</b>) depășește limita maximă de încărcare de <b>{maxSize} KB</b>.',msgFilesTooLess:"Trebuie să selectezi cel puțin <b>{n}</b> {files} pentru a încărca.",msgFilesTooMany:"Numărul fișierelor pentru încărcare <b>({n})</b> depășește limita maximă de <b>{m}</b>.",msgFileNotFound:'Fișierul "{name}" nu a fost găsit!',msgFileSecured:'Restricții de securitate previn citirea fișierului "{name}".',msgFileNotReadable:'Fișierul "{name}" nu se poate citi.',msgFilePreviewAborted:'Fișierului "{name}" nu poate fi previzualizat.',msgFilePreviewError:'A intervenit o eroare în încercarea de citire a fișierului "{name}".',msgInvalidFileName:'Invalid or unsupported characters in file name "{name}".',msgInvalidFileType:'Tip de fișier incorect pentru "{name}". Sunt suportate doar fișiere de tipurile "{types}".',msgInvalidFileExtension:'Extensie incorectă pentru "{name}". Sunt suportate doar extensiile "{extensions}".',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Fișierul Încărcarea a fost întrerupt",msgUploadThreshold:"Processing...",msgUploadBegin:"Initializing...",msgUploadEnd:"Done",msgUploadEmpty:"No valid data available for upload.",msgUploadError:"Error",msgValidationError:"Eroare de validare",msgLoading:"Se încarcă fișierul {index} din {files} &hellip;",msgProgress:"Se încarcă fișierul {index} din {files} - {name} - {percent}% încărcat.",msgSelected:"{n} {files} încărcate",msgFoldersNotAllowed:"Se poate doar trăgând fișierele! Se renunță la {n} dosar(e).",msgImageWidthSmall:'Lățimea de fișier de imagine "{name}" trebuie să fie de cel puțin {size} px.',msgImageHeightSmall:'Înălțimea fișier imagine "{name}" trebuie să fie de cel puțin {size} px.',msgImageWidthLarge:'Lățimea de fișier de imagine "{name}" nu poate depăși {size} px.',msgImageHeightLarge:'Înălțimea fișier imagine "{name}" nu poate depăși {size} px.',msgImageResizeError:"Nu a putut obține dimensiunile imaginii pentru a redimensiona.",msgImageResizeException:"Eroare la redimensionarea imaginii.<pre>{errors}</pre>",msgAjaxError:"Something went wrong with the {operation} operation. Please try again later!",msgAjaxProgressError:"{operation} failed",ajaxOperations:{deleteThumb:"file delete",uploadThumb:"file upload",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"Trage fișierele aici &hellip;",dropZoneClickTitle:"<br>(or click to select {files})",fileActionSettings:{removeTitle:"Scoateți fișier",uploadTitle:"Incarca fisier",uploadRetryTitle:"Retry upload",downloadTitle:"Download file",zoomTitle:"Vezi detalii",dragTitle:"Move / Rearrange",indicatorNewTitle:"Nu a încărcat încă",indicatorSuccessTitle:"încărcat",indicatorErrorTitle:"Încărcați eroare",indicatorLoadingTitle:"Se încarcă ..."},previewZoomButtonTitles:{prev:"View previous file",next:"View next file",toggleheader:"Toggle header",fullscreen:"Toggle full screen",borderless:"Toggle borderless mode",close:"Close detailed preview"}}}(window.jQuery),function(e,i){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?i(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],i):i(e.moment)}(this,function(e){"use strict";function i(e,i,t){var n={ss:"secunde",mm:"minute",hh:"ore",dd:"zile",MM:"luni",yy:"ani"},a=" ";return(e%100>=20||e>=100&&e%100==0)&&(a=" de "),e+a+n[t]}return e.defineLocale("ro",{months:"ianuarie_februarie_martie_aprilie_mai_iunie_iulie_august_septembrie_octombrie_noiembrie_decembrie".split("_"),monthsShort:"ian._febr._mart._apr._mai_iun._iul._aug._sept._oct._nov._dec.".split("_"),monthsParseExact:!0,weekdays:"duminică_luni_marți_miercuri_joi_vineri_sâmbătă".split("_"),weekdaysShort:"Dum_Lun_Mar_Mie_Joi_Vin_Sâm".split("_"),weekdaysMin:"Du_Lu_Ma_Mi_Jo_Vi_Sâ".split("_"),longDateFormat:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY H:mm",LLLL:"dddd, D MMMM YYYY H:mm"},calendar:{sameDay:"[azi la] LT",nextDay:"[mâine la] LT",nextWeek:"dddd [la] LT",lastDay:"[ieri la] LT",lastWeek:"[fosta] dddd [la] LT",sameElse:"L"},relativeTime:{future:"peste %s",past:"%s în urmă",s:"câteva secunde",ss:i,m:"un minut",mm:i,h:"o oră",hh:i,d:"o zi",dd:i,M:"o lună",MM:i,y:"un an",yy:i},week:{dow:1,doy:7}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/ro",[],function(){return{errorLoading:function(){return"Rezultatele nu au putut fi incărcate."},inputTooLong:function(e){var i=e.input.length-e.maximum,t="Vă rugăm să ștergeți"+i+" caracter";return 1!==i&&(t+="e"),t},inputTooShort:function(e){return"Vă rugăm să introduceți "+(e.minimum-e.input.length)+" sau mai multe caractere"},loadingMore:function(){return"Se încarcă mai multe rezultate…"},maximumSelected:function(e){var i="Aveți voie să selectați cel mult "+e.maximum;return i+=" element",1!==e.maximum&&(i+="e"),i},noResults:function(){return"Nu au fost găsite rezultate"},searching:function(){return"Căutare…"}}}),e.define,e.require}(),function(e,i){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return i(e)}):"object"==typeof module&&module.exports?module.exports=i(require("jquery")):i(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={doneButtonText:"Închide",noneSelectedText:"Nu a fost selectat nimic",noneResultsText:"Nu există niciun rezultat {0}",countSelectedText:"{0} din {1} selectat(e)",maxOptionsText:["Limita a fost atinsă ({n} {var} max)","Limita de grup a fost atinsă ({n} {var} max)",["iteme","item"]],selectAllText:"Selectează toate",deselectAllText:"Deselectează toate",multipleSeparator:", "}}(e)});
