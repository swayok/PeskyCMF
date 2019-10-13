!function(e,n){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return n(e)}):"object"==typeof module&&module.exports?module.exports=n(require("jquery")):n(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Inget valt",noneResultsText:"Inget sökresultat matchar {0}",countSelectedText:function(e,n){return 1===e?"{0} alternativ valt":"{0} alternativ valda"},maxOptionsText:function(e,n){return["Gräns uppnåd (max {n} alternativ)","Gräns uppnåd (max {n} gruppalternativ)"]},selectAllText:"Markera alla",deselectAllText:"Avmarkera alla",multipleSeparator:", "}}(e)}),function(e){"use strict";e.fn.fileinputLocales.sv={fileSingle:"fil",filePlural:"filer",browseLabel:"Bläddra &hellip;",removeLabel:"Ta bort",removeTitle:"Rensa valda filer",cancelLabel:"Avbryt",cancelTitle:"Avbryt pågående uppladdning",uploadLabel:"Ladda upp",uploadTitle:"Ladda upp valda filer",msgNo:"Nej",msgNoFilesSelected:"Inga filer valda",msgCancelled:"Avbruten",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"detaljerad förhandsgranskning",msgFileRequired:"You must select a file to upload.",msgSizeTooSmall:'Filen "{name}" (<b>{size} KB</b>) är för liten och måste vara större än <b>{minSize} KB</b>.',msgSizeTooLarge:'File "{name}" (<b>{size} KB</b>) överstiger högsta tillåtna uppladdningsstorlek <b>{maxSize} KB</b>.',msgFilesTooLess:"Du måste välja minst <b>{n}</b> {files} för att ladda upp.",msgFilesTooMany:"Antal filer valda för uppladdning <b>({n})</b> överstiger högsta tillåtna gränsen <b>{m}</b>.",msgFileNotFound:'Filen "{name}" kunde inte hittas!',msgFileSecured:'Säkerhetsbegränsningar förhindrar att läsa filen "{name}".',msgFileNotReadable:'Filen "{name}" är inte läsbar.',msgFilePreviewAborted:'Filförhandsvisning avbröts för "{name}".',msgFilePreviewError:'Ett fel uppstod vid inläsning av filen "{name}".',msgInvalidFileName:'Ogiltiga eller tecken som inte stöds i filnamnet "{name}".',msgInvalidFileType:'Ogiltig typ för filen "{name}". Endast "{types}" filtyper stöds.',msgInvalidFileExtension:'Ogiltigt filtillägg för filen "{name}". Endast "{extensions}" filer stöds.',msgFileTypes:{image:"bild",html:"HTML",text:"text",video:"video",audio:"ljud",flash:"flash",pdf:"PDF",object:"objekt"},msgUploadAborted:"Filöverföringen avbröts",msgUploadThreshold:"Bearbetar...",msgUploadBegin:"Påbörjar...",msgUploadEnd:"Färdig",msgUploadEmpty:"Ingen giltig data tillgänglig för uppladdning.",msgUploadError:"Error",msgValidationError:"Valideringsfel",msgLoading:"Laddar fil {index} av {files} &hellip;",msgProgress:"Laddar fil {index} av {files} - {name} - {percent}% färdig.",msgSelected:"{n} {files} valda",msgFoldersNotAllowed:"Endast drag & släppfiler! Skippade {n} släpta mappar.",msgImageWidthSmall:'Bredd på bildfilen "{name}" måste minst vara {size} pixlar.',msgImageHeightSmall:'Höjden på bildfilen "{name}" måste minst vara {size} pixlar.',msgImageWidthLarge:'Bredd på bildfil "{name}" kan inte överstiga {size} pixlar.',msgImageHeightLarge:'Höjden på bildfilen "{name}" kan inte överstiga {size} pixlar.',msgImageResizeError:"Det gick inte att hämta bildens dimensioner för att ändra storlek.",msgImageResizeException:"Fel vid storleksändring av bilden.<pre>{errors}</pre>",msgAjaxError:"Något gick fel med {operation} operationen. Försök igen senare!",msgAjaxProgressError:"{operation} misslyckades",ajaxOperations:{deleteThumb:"file delete",uploadThumb:"file upload",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"Drag & släpp filer här &hellip;",dropZoneClickTitle:"<br>(eller klicka för att markera {files})",fileActionSettings:{removeTitle:"Ta bort fil",uploadTitle:"Ladda upp fil",uploadRetryTitle:"Retry upload",zoomTitle:"Visa detaljer",dragTitle:"Flytta / Ändra ordning",indicatorNewTitle:"Inte uppladdat ännu",indicatorSuccessTitle:"Uppladdad",indicatorErrorTitle:"Uppladdningsfel",indicatorLoadingTitle:"Laddar upp..."},previewZoomButtonTitles:{prev:"Visa föregående fil",next:"Visa nästa fil",toggleheader:"Rubrik",fullscreen:"Fullskärm",borderless:"Gränslös",close:"Stäng detaljerad förhandsgranskning"}}}(window.jQuery),function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";return e.defineLocale("sv",{months:"januari_februari_mars_april_maj_juni_juli_augusti_september_oktober_november_december".split("_"),monthsShort:"jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec".split("_"),weekdays:"söndag_måndag_tisdag_onsdag_torsdag_fredag_lördag".split("_"),weekdaysShort:"sön_mån_tis_ons_tor_fre_lör".split("_"),weekdaysMin:"sö_må_ti_on_to_fr_lö".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"YYYY-MM-DD",LL:"D MMMM YYYY",LLL:"D MMMM YYYY [kl.] HH:mm",LLLL:"dddd D MMMM YYYY [kl.] HH:mm",lll:"D MMM YYYY HH:mm",llll:"ddd D MMM YYYY HH:mm"},calendar:{sameDay:"[Idag] LT",nextDay:"[Imorgon] LT",lastDay:"[Igår] LT",nextWeek:"[På] dddd LT",lastWeek:"[I] dddd[s] LT",sameElse:"L"},relativeTime:{future:"om %s",past:"för %s sedan",s:"några sekunder",ss:"%d sekunder",m:"en minut",mm:"%d minuter",h:"en timme",hh:"%d timmar",d:"en dag",dd:"%d dagar",M:"en månad",MM:"%d månader",y:"ett år",yy:"%d år"},dayOfMonthOrdinalParse:/\d{1,2}(e|a)/,ordinal:function(e){var n=e%10;return e+(1==~~(e%100/10)?"e":1===n?"a":2===n?"a":"e")},week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/sv",[],function(){return{errorLoading:function(){return"Resultat kunde inte laddas."},inputTooLong:function(e){return"Vänligen sudda ut "+(e.input.length-e.maximum)+" tecken"},inputTooShort:function(e){return"Vänligen skriv in "+(e.minimum-e.input.length)+" eller fler tecken"},loadingMore:function(){return"Laddar fler resultat…"},maximumSelected:function(e){return"Du kan max välja "+e.maximum+" element"},noResults:function(){return"Inga träffar"},searching:function(){return"Söker…"},removeAllItems:function(){return"Ta bort alla objekt"}}}),e.define,e.require}();
