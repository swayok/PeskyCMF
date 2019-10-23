!function(i){"use strict";i.fn.fileinputLocales.lt={fileSingle:"failas",filePlural:"failai",browseLabel:"Naršyti &hellip;",removeLabel:"Šalinti",removeTitle:"Pašalinti pasirinktus failus",cancelLabel:"Atšaukti",cancelTitle:"Atšaukti vykstantį įkėlimą",uploadLabel:"Įkelti",uploadTitle:"Įkelti pasirinktus failus",msgNo:"Ne",msgNoFilesSelected:"Nepasirinkta jokių failų",msgCancelled:"Atšaukta",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"Detali Peržiūra",msgFileRequired:"Pasirinkite failą įkėlimui.",msgSizeTooSmall:'Failas "{name}" (<b>{size} KB</b>) yra per mažas ir turi būti didesnis nei <b>{minSize} KB</b>.',msgSizeTooLarge:'Failas "{name}" (<b>{size} KB</b>) viršija maksimalų leidžiamą įkeliamo failo dydį <b>{maxSize} KB</b>.',msgFilesTooLess:"Turite pasirinkti bent <b>{n}</b> failus įkėlimui.",msgFilesTooMany:"Įkėlimui pasirinktų failų skaičius <b>({n})</b> viršija maksimalų leidžiamą limitą <b>{m}</b>.",msgFileNotFound:'Failas "{name}" nerastas!',msgFileSecured:'Saugumo apribojimai neleidžia perskaityti failo "{name}".',msgFileNotReadable:'Failas "{name}" neperskaitomas.',msgFilePreviewAborted:'Failo peržiūra nutraukta "{name}".',msgFilePreviewError:'Įvyko klaida skaitant failą "{name}".',msgInvalidFileName:'Klaidingi arba nepalaikomi simboliai failo pavadinime "{name}".',msgInvalidFileType:'Klaidingas failo "{name}" tipas. Tik "{types}" tipai yra palaikomi.',msgInvalidFileExtension:'Klaidingas failo "{name}" plėtinys. Tik "{extensions}" plėtiniai yra palaikomi.',msgFileTypes:{image:"paveikslėlis",html:"HTML",text:"tekstas",video:"vaizdo įrašas",audio:"garso įrašas",flash:"flash",pdf:"PDF",object:"objektas"},msgUploadAborted:"Failo įkėlimas buvo nutrauktas",msgUploadThreshold:"Vykdoma...",msgUploadBegin:"Inicijuojama...",msgUploadEnd:"Baigta",msgUploadEmpty:"Nėra teisingų duomenų įkėlimui.",msgUploadError:"Klaida",msgValidationError:"Validacijos Klaida",msgLoading:"Keliamas failas {index} iš {files} &hellip;",msgProgress:"Keliamas failas {index} iš {files} - {name} - {percent}% baigta.",msgSelected:"Pasirinkti {n} {files}",msgFoldersNotAllowed:"Tempkite tik failus! Praleisti {n} nutempti aplankalas(-i).",msgImageWidthSmall:'Paveikslėlio "{name}" plotis turi būti bent {size} px.',msgImageHeightSmall:'Paveikslėlio "{name}" aukštis turi būti bent {size} px.',msgImageWidthLarge:'Paveikslėlio "{name}" plotis negali viršyti {size} px.',msgImageHeightLarge:'Paveikslėlio "{name}" aukštis negali viršyti {size} px.',msgImageResizeError:"Nepavyksta gauti paveikslėlio matmetų, kad pakeisti jo matmemis.",msgImageResizeException:"Klaida keičiant paveikslėlio matmenis.<pre>{errors}</pre>",msgAjaxError:"Kažkas nutiko vykdant {operation} operaciją. Prašome pabandyti vėliau!",msgAjaxProgressError:"{operation} operacija nesėkminga",ajaxOperations:{deleteThumb:"failo trynimo",uploadThumb:"failo įkėlimo",uploadBatch:"failų rinkinio įkėlimo",uploadExtra:"formos duomenų įkėlimo"},dropZoneTitle:"Tempkite failus čia &hellip;",dropZoneClickTitle:"<br>(arba paspauskite, kad pasirinktumėte failus)",fileActionSettings:{removeTitle:"Šalinti failą",uploadTitle:"Įkelti failą",uploadRetryTitle:"Bandyti įkelti vėl",zoomTitle:"Peržiūrėti detales",dragTitle:"Perstumti",indicatorNewTitle:"Dar neįkelta",indicatorSuccessTitle:"Įkelta",indicatorErrorTitle:"Įkėlimo Klaida",indicatorLoadingTitle:"Įkeliama ..."},previewZoomButtonTitles:{prev:"Peržiūrėti ankstesnį failą",next:"Peržiūrėti kitą failą",toggleheader:"Perjungti viršutinę juostą",fullscreen:"Perjungti pilno ekrano rėžimą",borderless:"Perjungti berėmį režimą",close:"Uždaryti detalią peržiūrą"}}}(window.jQuery),function(i,e){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?e(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],e):e(i.moment)}(this,function(i){"use strict";function e(i,e,a,t){return e?"kelios sekundės":t?"kelių sekundžių":"kelias sekundes"}function a(i,e,a,t){return e?n(a)[0]:t?n(a)[1]:n(a)[2]}function t(i){return i%10==0||i>10&&i<20}function n(i){return l[i].split("_")}function s(i,e,s,l){var r=i+" ";return 1===i?r+a(i,e,s[0],l):e?r+(t(i)?n(s)[1]:n(s)[0]):l?r+n(s)[1]:r+(t(i)?n(s)[1]:n(s)[2])}var l={ss:"sekundė_sekundžių_sekundes",m:"minutė_minutės_minutę",mm:"minutės_minučių_minutes",h:"valanda_valandos_valandą",hh:"valandos_valandų_valandas",d:"diena_dienos_dieną",dd:"dienos_dienų_dienas",M:"mėnuo_mėnesio_mėnesį",MM:"mėnesiai_mėnesių_mėnesius",y:"metai_metų_metus",yy:"metai_metų_metus"};return i.defineLocale("lt",{months:{format:"sausio_vasario_kovo_balandžio_gegužės_birželio_liepos_rugpjūčio_rugsėjo_spalio_lapkričio_gruodžio".split("_"),standalone:"sausis_vasaris_kovas_balandis_gegužė_birželis_liepa_rugpjūtis_rugsėjis_spalis_lapkritis_gruodis".split("_"),isFormat:/D[oD]?(\[[^\[\]]*\]|\s)+MMMM?|MMMM?(\[[^\[\]]*\]|\s)+D[oD]?/},monthsShort:"sau_vas_kov_bal_geg_bir_lie_rgp_rgs_spa_lap_grd".split("_"),weekdays:{format:"sekmadienį_pirmadienį_antradienį_trečiadienį_ketvirtadienį_penktadienį_šeštadienį".split("_"),standalone:"sekmadienis_pirmadienis_antradienis_trečiadienis_ketvirtadienis_penktadienis_šeštadienis".split("_"),isFormat:/dddd HH:mm/},weekdaysShort:"Sek_Pir_Ant_Tre_Ket_Pen_Šeš".split("_"),weekdaysMin:"S_P_A_T_K_Pn_Š".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"YYYY-MM-DD",LL:"YYYY [m.] MMMM D [d.]",LLL:"YYYY [m.] MMMM D [d.], HH:mm [val.]",LLLL:"YYYY [m.] MMMM D [d.], dddd, HH:mm [val.]",l:"YYYY-MM-DD",ll:"YYYY [m.] MMMM D [d.]",lll:"YYYY [m.] MMMM D [d.], HH:mm [val.]",llll:"YYYY [m.] MMMM D [d.], ddd, HH:mm [val.]"},calendar:{sameDay:"[Šiandien] LT",nextDay:"[Rytoj] LT",nextWeek:"dddd LT",lastDay:"[Vakar] LT",lastWeek:"[Praėjusį] dddd LT",sameElse:"L"},relativeTime:{future:"po %s",past:"prieš %s",s:e,ss:s,m:a,mm:s,h:a,hh:s,d:a,dd:s,M:a,MM:s,y:a,yy:s},dayOfMonthOrdinalParse:/\d{1,2}-oji/,ordinal:function(i){return i+"-oji"},week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var i=jQuery.fn.select2.amd;i.define("select2/i18n/lt",[],function(){function i(i,e,a,t){return i%10==1&&(i%100<11||i%100>19)?e:i%10>=2&&i%10<=9&&(i%100<11||i%100>19)?a:t}return{inputTooLong:function(e){var a=e.input.length-e.maximum,t="Pašalinkite "+a+" simbol";return t+=i(a,"į","ius","ių")},inputTooShort:function(e){var a=e.minimum-e.input.length,t="Įrašykite dar "+a+" simbol";return t+=i(a,"į","ius","ių")},loadingMore:function(){return"Kraunama daugiau rezultatų…"},maximumSelected:function(e){var a="Jūs galite pasirinkti tik "+e.maximum+" element";return a+=i(e.maximum,"ą","us","ų")},noResults:function(){return"Atitikmenų nerasta"},searching:function(){return"Ieškoma…"},removeAllItems:function(){return"Pašalinti visus elementus"}}}),i.define,i.require}(),function(i,e){void 0===i&&void 0!==window&&(i=window),"function"==typeof define&&define.amd?define(["jquery"],function(i){return e(i)}):"object"==typeof module&&module.exports?module.exports=e(require("jquery")):e(i.jQuery)}(this,function(i){!function(i){i.fn.selectpicker.defaults={noneSelectedText:"Niekas nepasirinkta",noneResultsText:"Niekas nesutapo su {0}",countSelectedText:function(i,e){return 1==i?"{0} elementas pasirinktas":"{0} elementai(-ų) pasirinkta"},maxOptionsText:function(i,e){return[1==i?"Pasiekta riba ({n} elementas daugiausiai)":"Riba pasiekta ({n} elementai(-ų) daugiausiai)",1==e?"Grupės riba pasiekta ({n} elementas daugiausiai)":"Grupės riba pasiekta ({n} elementai(-ų) daugiausiai)"]},selectAllText:"Pasirinkti visus",deselectAllText:"Atmesti visus",multipleSeparator:", "}}(i)});
