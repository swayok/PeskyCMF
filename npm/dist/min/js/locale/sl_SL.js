!function(e,a){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return a(e)}):"object"==typeof module&&module.exports?module.exports=a(require("jquery")):a(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Nič izbranega",noneResultsText:"Ni zadetkov za {0}",countSelectedText:"{0} od {1} izbranih",maxOptionsText:function(e,a){return["Omejitev dosežena (max. izbranih: {n})","Omejitev skupine dosežena (max. izbranih: {n})"]},selectAllText:"Izberi vse",deselectAllText:"Počisti izbor",multipleSeparator:", "}}(e)}),function(e){"use strict";e.fn.fileinputLocales.sl={fileSingle:"datoteka",filePlural:"datotek",browseLabel:"Prebrskaj &hellip;",removeLabel:"Odstrani",removeTitle:"Počisti izbrane datoteke",cancelLabel:"Prekliči",cancelTitle:"Prekliči nalaganje",uploadLabel:"Naloži",uploadTitle:"Naloži izbrane datoteke",msgNo:"Ne",msgNoFilesSelected:"Nobena datoteka ni izbrana",msgCancelled:"Preklicano",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"Podroben predogled",msgSizeTooLarge:'Datoteka "{name}" (<b>{size} KB</b>) presega največjo dovoljeno velikost za nalaganje <b>{maxSize} KB</b>.',msgFilesTooLess:"Za nalaganje morate izbrati vsaj <b>{n}</b> {files}.",msgFilesTooMany:"Število datotek, izbranih za nalaganje <b>({n})</b> je prekoračilo največjo dovoljeno število <b>{m}</b>.",msgFileNotFound:'Datoteka "{name}" ni bila najdena!',msgFileSecured:'Zaradi varnostnih omejitev nisem mogel prebrati datoteko "{name}".',msgFileNotReadable:'Datoteka "{name}" ni berljiva.',msgFilePreviewAborted:'Predogled datoteke "{name}" preklican.',msgFilePreviewError:'Pri branju datoteke "{name}" je prišlo do napake.',msgInvalidFileType:'Napačen tip datoteke "{name}". Samo "{types}" datoteke so podprte.',msgInvalidFileExtension:'Napačna končnica datoteke "{name}". Samo "{extensions}" datoteke so podprte.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Nalaganje datoteke je bilo preklicano",msgUploadThreshold:"Procesiram...",msgUploadBegin:"Initializing...",msgUploadEnd:"Done",msgUploadEmpty:"No valid data available for upload.",msgUploadError:"Error",msgValidationError:"Napaki pri validiranju",msgLoading:"Nalaganje datoteke {index} od {files} &hellip;",msgProgress:"Nalaganje datoteke {index} od {files} - {name} - {percent}% dokončano.",msgSelected:"{n} {files} izbrano",msgFoldersNotAllowed:"Povlecite in spustite samo datoteke! Izpuščenih je bilo {n} map.",msgImageWidthSmall:'Širina slike "{name}" mora biti vsaj {size} px.',msgImageHeightSmall:'Višina slike "{name}" mora biti vsaj {size} px.',msgImageWidthLarge:'Širina slike "{name}" ne sme preseči {size} px.',msgImageHeightLarge:'Višina slike "{name}" ne sme preseči {size} px.',msgImageResizeError:"Nisem mogel pridobiti dimenzij slike za spreminjanje velikosti.",msgImageResizeException:"Napaka pri spreminjanju velikosti slike.<pre>{errors}</pre>",msgAjaxError:"Something went wrong with the {operation} operation. Please try again later!",msgAjaxProgressError:"{operation} failed",ajaxOperations:{deleteThumb:"file delete",uploadThumb:"file upload",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"Povlecite in spustite datoteke sem &hellip;",dropZoneClickTitle:"<br>(ali kliknite sem za izbiro {files})",fileActionSettings:{removeTitle:"Odstrani datoteko",uploadTitle:"Naloži datoteko",uploadRetryTitle:"Retry upload",downloadTitle:"Download file",zoomTitle:"Poglej podrobnosti",dragTitle:"Premaki / Razporedi",indicatorNewTitle:"Še ni naloženo",indicatorSuccessTitle:"Naloženo",indicatorErrorTitle:"Napaka pri nalaganju",indicatorLoadingTitle:"Nalagam ..."},previewZoomButtonTitles:{prev:"Poglej prejšno datoteko",next:"Poglej naslednjo datoteko",toggleheader:"Preklopi glavo",fullscreen:"Preklopi celozaslonski način",borderless:"Preklopi način brez robov",close:"Zapri predogled podrobnosti"}}}(window.jQuery),function(e,a){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?a(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],a):a(e.moment)}(this,function(e){"use strict";function a(e,a,i,o){var n=e+" ";switch(i){case"s":return a||o?"nekaj sekund":"nekaj sekundami";case"ss":return n+=1===e?a?"sekundo":"sekundi":2===e?a||o?"sekundi":"sekundah":e<5?a||o?"sekunde":"sekundah":"sekund";case"m":return a?"ena minuta":"eno minuto";case"mm":return n+=1===e?a?"minuta":"minuto":2===e?a||o?"minuti":"minutama":e<5?a||o?"minute":"minutami":a||o?"minut":"minutami";case"h":return a?"ena ura":"eno uro";case"hh":return n+=1===e?a?"ura":"uro":2===e?a||o?"uri":"urama":e<5?a||o?"ure":"urami":a||o?"ur":"urami";case"d":return a||o?"en dan":"enim dnem";case"dd":return n+=1===e?a||o?"dan":"dnem":2===e?a||o?"dni":"dnevoma":a||o?"dni":"dnevi";case"M":return a||o?"en mesec":"enim mesecem";case"MM":return n+=1===e?a||o?"mesec":"mesecem":2===e?a||o?"meseca":"mesecema":e<5?a||o?"mesece":"meseci":a||o?"mesecev":"meseci";case"y":return a||o?"eno leto":"enim letom";case"yy":return n+=1===e?a||o?"leto":"letom":2===e?a||o?"leti":"letoma":e<5?a||o?"leta":"leti":a||o?"let":"leti"}}return e.defineLocale("sl",{months:"januar_februar_marec_april_maj_junij_julij_avgust_september_oktober_november_december".split("_"),monthsShort:"jan._feb._mar._apr._maj._jun._jul._avg._sep._okt._nov._dec.".split("_"),monthsParseExact:!0,weekdays:"nedelja_ponedeljek_torek_sreda_četrtek_petek_sobota".split("_"),weekdaysShort:"ned._pon._tor._sre._čet._pet._sob.".split("_"),weekdaysMin:"ne_po_to_sr_če_pe_so".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY H:mm",LLLL:"dddd, D. MMMM YYYY H:mm"},calendar:{sameDay:"[danes ob] LT",nextDay:"[jutri ob] LT",nextWeek:function(){switch(this.day()){case 0:return"[v] [nedeljo] [ob] LT";case 3:return"[v] [sredo] [ob] LT";case 6:return"[v] [soboto] [ob] LT";case 1:case 2:case 4:case 5:return"[v] dddd [ob] LT"}},lastDay:"[včeraj ob] LT",lastWeek:function(){switch(this.day()){case 0:return"[prejšnjo] [nedeljo] [ob] LT";case 3:return"[prejšnjo] [sredo] [ob] LT";case 6:return"[prejšnjo] [soboto] [ob] LT";case 1:case 2:case 4:case 5:return"[prejšnji] dddd [ob] LT"}},sameElse:"L"},relativeTime:{future:"čez %s",past:"pred %s",s:a,ss:a,m:a,mm:a,h:a,hh:a,d:a,dd:a,M:a,MM:a,y:a,yy:a},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:7}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/sl",[],function(){return{errorLoading:function(){return"Zadetkov iskanja ni bilo mogoče naložiti."},inputTooLong:function(e){var a=e.input.length-e.maximum,i="Prosim zbrišite "+a+" znak";return 2==a?i+="a":1!=a&&(i+="e"),i},inputTooShort:function(e){var a=e.minimum-e.input.length,i="Prosim vpišite še "+a+" znak";return 2==a?i+="a":1!=a&&(i+="e"),i},loadingMore:function(){return"Nalagam več zadetkov…"},maximumSelected:function(e){var a="Označite lahko največ "+e.maximum+" predmet";return 2==e.maximum?a+="a":1!=e.maximum&&(a+="e"),a},noResults:function(){return"Ni zadetkov."},searching:function(){return"Iščem…"},removeAllItems:function(){return"Odstranite vse elemente"}}}),e.define,e.require}();
