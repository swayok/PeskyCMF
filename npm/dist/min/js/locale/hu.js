!function(e){"use strict";e.fn.fileinputLocales.hu={fileSingle:"fájl",filePlural:"fájlok",browseLabel:"Tallóz &hellip;",removeLabel:"Eltávolít",removeTitle:"Kijelölt fájlok törlése",cancelLabel:"Mégse",cancelTitle:"Feltöltés megszakítása",uploadLabel:"Feltöltés",uploadTitle:"Kijelölt fájlok feltöltése",msgNo:"Nem",msgNoFilesSelected:"Nincs fájl kiválasztva",msgCancelled:"Megszakítva",msgPlaceholder:"Válasz {files}...",msgZoomModalHeading:"Részletes Előnézet",msgFileRequired:"Kötelező fájlt kiválasztani a feltöltéshez.",msgSizeTooSmall:'A fájl: "{name}" (<b>{size} KB</b>) mérete túl kicsi, nagyobbnak kell lennie, mint <b>{minSize} KB</b>.',msgSizeTooLarge:'"{name}" fájl (<b>{size} KB</b>) mérete nagyobb a megengedettnél <b>{maxSize} KB</b>.',msgFilesTooLess:"Legalább <b>{n}</b> {files} ki kell választania a feltöltéshez.",msgFilesTooMany:"A feltölteni kívánt fájlok száma <b>({n})</b> elérte a megengedett maximumot <b>{m}</b>.",msgFileNotFound:'"{name}" fájl nem található!',msgFileSecured:'Biztonsági beállítások nem engedik olvasni a fájlt "{name}".',msgFileNotReadable:'"{name}" fájl nem olvasható.',msgFilePreviewAborted:'"{name}" fájl feltöltése megszakítva.',msgFilePreviewError:'Hiba lépett fel a "{name}" fájl olvasása közben.',msgInvalidFileName:'Hibás vagy nem támogatott karakterek a fájl nevében "{name}".',msgInvalidFileType:'Nem megengedett fájl "{name}". Csak a "{types}" fájl típusok támogatottak.',msgInvalidFileExtension:'Nem megengedett kiterjesztés / fájltípus "{name}". Csak a "{extensions}" kiterjesztés(ek) / fájltípus(ok) támogatottak.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"A fájl feltöltés megszakítva",msgUploadThreshold:"Folyamatban...",msgUploadBegin:"Inicializálás...",msgUploadEnd:"Kész",msgUploadEmpty:"Nincs érvényes adat a feltöltéshez.",msgUploadError:"Error",msgValidationError:"Érvényesítés hiba",msgLoading:"{index} / {files} töltése &hellip;",msgProgress:"Feltöltés: {index} / {files} - {name} - {percent}% kész.",msgSelected:"{n} {files} kiválasztva.",msgFoldersNotAllowed:"Csak fájlokat húzzon ide! Kihagyva {n} könyvtár.",msgImageWidthSmall:'A kép szélességének "{name}" legalább {size} pixelnek kell lennie.',msgImageHeightSmall:'A kép magasságának "{name}" legalább {size} pixelnek kell lennie.',msgImageWidthLarge:'A kép szélessége "{name}" nem haladhatja meg a {size} pixelt.',msgImageHeightLarge:'A kép magassága "{name}" nem haladhatja meg a {size} pixelt.',msgImageResizeError:"Nem lehet megállapítani a kép méreteit az átméretezéshez.",msgImageResizeException:"Hiba történt a méretezés közben.<pre>{errors}</pre>",msgAjaxError:"Hiba történt a művelet közben ({operation}). Kérjük, próbálja később!",msgAjaxProgressError:"Hiba! ({operation})",ajaxOperations:{deleteThumb:"fájl törlés",uploadThumb:"fájl feltöltés",uploadBatch:"csoportos fájl feltöltés",uploadExtra:"űrlap adat feltöltés"},dropZoneTitle:"Húzzon ide fájlokat &hellip;",dropZoneClickTitle:"<br>(vagy kattintson ide a {files} tallózásához...)",fileActionSettings:{removeTitle:"A fájl eltávolítása",uploadTitle:"fájl feltöltése",uploadRetryTitle:"Feltöltés újból",downloadTitle:"Fájl letöltése",zoomTitle:"Részletek megtekintése",dragTitle:"Mozgatás / Átrendezés",indicatorNewTitle:"Nem feltöltött",indicatorSuccessTitle:"Feltöltött",indicatorErrorTitle:"Feltöltés hiba",indicatorLoadingTitle:"Feltöltés ..."},previewZoomButtonTitles:{prev:"Elöző fájl megnézése",next:"Következő fájl megnézése",toggleheader:"Fejléc mutatása",fullscreen:"Teljes képernyős mód bekapcsolása",borderless:"Keret nélküli ablak mód bekapcsolása",close:"Részletes előnézet bezárása"}}}(window.jQuery),function(e,t){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?t(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],t):t(e.moment)}(this,function(e){"use strict";function t(e,t,l,a){var n=e;switch(l){case"s":return a||t?"néhány másodperc":"néhány másodperce";case"ss":return n+(a||t)?" másodperc":" másodperce";case"m":return"egy"+(a||t?" perc":" perce");case"mm":return n+(a||t?" perc":" perce");case"h":return"egy"+(a||t?" óra":" órája");case"hh":return n+(a||t?" óra":" órája");case"d":return"egy"+(a||t?" nap":" napja");case"dd":return n+(a||t?" nap":" napja");case"M":return"egy"+(a||t?" hónap":" hónapja");case"MM":return n+(a||t?" hónap":" hónapja");case"y":return"egy"+(a||t?" év":" éve");case"yy":return n+(a||t?" év":" éve")}return""}function l(e){return(e?"":"[múlt] ")+"["+a[this.day()]+"] LT[-kor]"}var a="vasárnap hétfőn kedden szerdán csütörtökön pénteken szombaton".split(" ");return e.defineLocale("hu",{months:"január_február_március_április_május_június_július_augusztus_szeptember_október_november_december".split("_"),monthsShort:"jan_feb_márc_ápr_máj_jún_júl_aug_szept_okt_nov_dec".split("_"),weekdays:"vasárnap_hétfő_kedd_szerda_csütörtök_péntek_szombat".split("_"),weekdaysShort:"vas_hét_kedd_sze_csüt_pén_szo".split("_"),weekdaysMin:"v_h_k_sze_cs_p_szo".split("_"),longDateFormat:{LT:"H:mm",LTS:"H:mm:ss",L:"YYYY.MM.DD.",LL:"YYYY. MMMM D.",LLL:"YYYY. MMMM D. H:mm",LLLL:"YYYY. MMMM D., dddd H:mm"},meridiemParse:/de|du/i,isPM:function(e){return"u"===e.charAt(1).toLowerCase()},meridiem:function(e,t,l){return e<12?!0===l?"de":"DE":!0===l?"du":"DU"},calendar:{sameDay:"[ma] LT[-kor]",nextDay:"[holnap] LT[-kor]",nextWeek:function(){return l.call(this,!0)},lastDay:"[tegnap] LT[-kor]",lastWeek:function(){return l.call(this,!1)},sameElse:"L"},relativeTime:{future:"%s múlva",past:"%s",s:t,ss:t,m:t,mm:t,h:t,hh:t,d:t,dd:t,M:t,MM:t,y:t,yy:t},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/hu",[],function(){return{errorLoading:function(){return"Az eredmények betöltése nem sikerült."},inputTooLong:function(e){return"Túl hosszú. "+(e.input.length-e.maximum)+" karakterrel több, mint kellene."},inputTooShort:function(e){return"Túl rövid. Még "+(e.minimum-e.input.length)+" karakter hiányzik."},loadingMore:function(){return"Töltés…"},maximumSelected:function(e){return"Csak "+e.maximum+" elemet lehet kiválasztani."},noResults:function(){return"Nincs találat."},searching:function(){return"Keresés…"},removeAllItems:function(){return"Távolítson el minden elemet"}}}),e.define,e.require}(),function(e,t){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return t(e)}):"object"==typeof module&&module.exports?module.exports=t(require("jquery")):t(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Válasszon!",noneResultsText:"Nincs találat {0}",countSelectedText:function(e,t){return"{0} elem kiválasztva"},maxOptionsText:function(e,t){return["Legfeljebb {n} elem választható","A csoportban legfeljebb {n} elem választható"]},selectAllText:"Mind",deselectAllText:"Egyik sem",multipleSeparator:", "}}(e)});
