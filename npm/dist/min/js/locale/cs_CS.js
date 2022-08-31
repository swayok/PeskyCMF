!function(e,n){"function"==typeof define&&define.amd?define(["jquery","query-builder"],n):n(e.jQuery)}(this,function(e){"use strict";var n=e.fn.queryBuilder;n.regional.cs={__locale:"Čeština (cs)",__author:"Megaplan, mborisv <bm@megaplan.ru>",add_rule:"Přidat",add_group:"Přidat skupinu",delete_rule:"Odstranit",delete_group:"Odstranit skupinu",conditions:{AND:"I",OR:"NEBO"},operators:{equal:"stejně",not_equal:"liší se",in:"z uvedených",not_in:"ne z uvedených",less:"méně",less_or_equal:"méně nebo stejně",greater:"více",greater_or_equal:"více nebo stejně",between:"mezi",begins_with:"začíná z",not_begins_with:"nezačíná z",contains:"obsahuje",not_contains:"neobsahuje",ends_with:"končí na",not_ends_with:"nekončí na",is_empty:"prázdný řádek",is_not_empty:"neprázdný řádek",is_null:"prázdno",is_not_null:"plno"},errors:{no_filter:"není vybraný filtr",empty_group:"prázdná skupina",radio_empty:"Není udaná hodnota",checkbox_empty:"Není udaná hodnota",select_empty:"Není udaná hodnota",string_empty:"Nevyplněno",string_exceed_min_length:"Musí obsahovat více {0} symbolů",string_exceed_max_length:"Musí obsahovat méně {0} symbolů",string_invalid_format:"Nesprávný formát ({0})",number_nan:"Žádné číslo",number_not_integer:"Žádné číslo",number_not_double:"Žádné číslo",number_exceed_min:"Musí být více {0}",number_exceed_max:"Musí být méně {0}",number_wrong_step:"Musí být násobkem {0}",datetime_empty:"Nevyplněno",datetime_invalid:"Nesprávný formát datumu ({0})",datetime_exceed_min:"Musí být po {0}",datetime_exceed_max:"Musí být do {0}",boolean_not_valid:"Nelogické",operator_not_multiple:'Operátor "{1}" nepodporuje mnoho hodnot'},invert:"invertní"},n.defaults({lang_code:"cs"})}),function(e){"use strict";e.fn.fileinputLocales.cs={fileSingle:"soubor",filePlural:"soubory",browseLabel:"Vybrat &hellip;",removeLabel:"Odstranit",removeTitle:"Vyčistit vybrané soubory",cancelLabel:"Storno",cancelTitle:"Přerušit  nahrávání",uploadLabel:"Nahrát",uploadTitle:"Nahrát vybrané soubory",msgNo:"Ne",msgNoFilesSelected:"Nevybrány žádné soubory",msgCancelled:"Zrušeno",msgPlaceholder:"Vybrat {files}...",msgZoomModalHeading:"Detailní náhled",msgFileRequired:"Musíte vybrat soubor, který chcete nahrát.",msgSizeTooSmall:'Soubor "{name}" (<b>{size} KB</b>) je příliš malý, musí mít velikost nejméně <b>{minSize} KB</b>.',msgSizeTooLarge:'Soubor "{name}" (<b>{size} KB</b>) je příliš velký, maximální povolená velikost <b>{maxSize} KB</b>.',msgFilesTooLess:"Musíte vybrat nejméně <b>{n}</b> {files} souborů.",msgFilesTooMany:"Počet vybraných souborů <b>({n})</b> překročil maximální povolený limit <b>{m}</b>.",msgFileNotFound:'Soubor "{name}" nebyl nalezen!',msgFileSecured:'Zabezpečení souboru znemožnilo číst soubor "{name}".',msgFileNotReadable:'Soubor "{name}" není čitelný.',msgFilePreviewAborted:'Náhled souboru byl přerušen pro "{name}".',msgFilePreviewError:'Nastala chyba při načtení souboru "{name}".',msgInvalidFileName:'Neplatné nebo nepovolené znaky ve jménu souboru "{name}".',msgInvalidFileType:'Neplatný typ souboru "{name}". Pouze "{types}" souborů jsou podporovány.',msgInvalidFileExtension:'Neplatná extenze souboru "{name}". Pouze "{extensions}" souborů jsou podporovány.',msgFileTypes:{image:"obrázek",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Nahrávání souboru bylo přerušeno",msgUploadThreshold:"Zpracovávám...",msgUploadBegin:"Inicializujem...",msgUploadEnd:"Hotovo",msgUploadEmpty:"Pro nahrávání nejsou k dispozici žádné platné údaje.",msgUploadError:"Chyba",msgValidationError:"Chyba ověření",msgLoading:"Nahrávání souboru {index} z {files} &hellip;",msgProgress:"Nahrávání souboru {index} z {files} - {name} - {percent}% dokončeno.",msgSelected:"{n} {files} vybráno",msgFoldersNotAllowed:"Táhni a pusť pouze soubory! Vynechané {n} pustěné složk(y).",msgImageWidthSmall:'Šířka obrázku "{name}", musí být alespoň {size} px.',msgImageHeightSmall:'Výška obrázku "{name}", musí být alespoň {size} px.',msgImageWidthLarge:'Šířka obrázku "{name}" nesmí být větší než {size} px.',msgImageHeightLarge:'Výška obrázku "{name}" nesmí být větší než {size} px.',msgImageResizeError:"Nelze získat rozměry obrázku pro změnu velikosti.",msgImageResizeException:"Chyba při změně velikosti obrázku.<pre>{errors}</pre>",msgAjaxError:"Došlo k chybě v {operation}. Prosím zkuste to znovu později!",msgAjaxProgressError:"{operation} - neúspěšné",ajaxOperations:{deleteThumb:"odstranit soubor",uploadThumb:"nahrát soubor",uploadBatch:"nahrát várku souborů",uploadExtra:"odesílání dat formuláře"},dropZoneTitle:"Přetáhni soubory sem &hellip;",dropZoneClickTitle:"<br>(nebo klikni sem a vyber je)",fileActionSettings:{removeTitle:"Odstranit soubor",uploadTitle:"Nahrát soubor",uploadRetryTitle:"Opakovat nahrávání",downloadTitle:"Stáhnout soubor",zoomTitle:"Zobrazit podrobnosti",dragTitle:"Posunout / Přeskládat",indicatorNewTitle:"Ještě nenahrál",indicatorSuccessTitle:"Nahraný",indicatorErrorTitle:"Chyba nahrávání",indicatorLoadingTitle:"Nahrávání ..."},previewZoomButtonTitles:{prev:"Zobrazit předchozí soubor",next:"Zobrazit následující soubor",toggleheader:"Přepnout záhlaví",fullscreen:"Přepnout celoobrazovkové zobrazení",borderless:"Přepnout bezrámečkové zobrazení",close:"Zavřít detailní náhled"}}}(window.jQuery),function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";function n(e){return e>1&&e<5&&1!=~~(e/10)}function o(e,o,t,r){var s=e+" ";switch(t){case"s":return o||r?"pár sekund":"pár sekundami";case"ss":return o||r?s+(n(e)?"sekundy":"sekund"):s+"sekundami";case"m":return o?"minuta":r?"minutu":"minutou";case"mm":return o||r?s+(n(e)?"minuty":"minut"):s+"minutami";case"h":return o?"hodina":r?"hodinu":"hodinou";case"hh":return o||r?s+(n(e)?"hodiny":"hodin"):s+"hodinami";case"d":return o||r?"den":"dnem";case"dd":return o||r?s+(n(e)?"dny":"dní"):s+"dny";case"M":return o||r?"měsíc":"měsícem";case"MM":return o||r?s+(n(e)?"měsíce":"měsíců"):s+"měsíci";case"y":return o||r?"rok":"rokem";case"yy":return o||r?s+(n(e)?"roky":"let"):s+"lety"}}var t="leden_únor_březen_duben_květen_červen_červenec_srpen_září_říjen_listopad_prosinec".split("_"),r="led_úno_bře_dub_kvě_čvn_čvc_srp_zář_říj_lis_pro".split("_"),s=[/^led/i,/^úno/i,/^bře/i,/^dub/i,/^kvě/i,/^(čvn|červen$|června)/i,/^(čvc|červenec|července)/i,/^srp/i,/^zář/i,/^říj/i,/^lis/i,/^pro/i],a=/^(leden|únor|březen|duben|květen|červenec|července|červen|června|srpen|září|říjen|listopad|prosinec|led|úno|bře|dub|kvě|čvn|čvc|srp|zář|říj|lis|pro)/i;return e.defineLocale("cs",{months:t,monthsShort:r,monthsRegex:a,monthsShortRegex:a,monthsStrictRegex:/^(leden|ledna|února|únor|březen|března|duben|dubna|květen|května|červenec|července|červen|června|srpen|srpna|září|říjen|října|listopadu|listopad|prosinec|prosince)/i,monthsShortStrictRegex:/^(led|úno|bře|dub|kvě|čvn|čvc|srp|zář|říj|lis|pro)/i,monthsParse:s,longMonthsParse:s,shortMonthsParse:s,weekdays:"neděle_pondělí_úterý_středa_čtvrtek_pátek_sobota".split("_"),weekdaysShort:"ne_po_út_st_čt_pá_so".split("_"),weekdaysMin:"ne_po_út_st_čt_pá_so".split("_"),longDateFormat:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY H:mm",LLLL:"dddd D. MMMM YYYY H:mm",l:"D. M. YYYY"},calendar:{sameDay:"[dnes v] LT",nextDay:"[zítra v] LT",nextWeek:function(){switch(this.day()){case 0:return"[v neděli v] LT";case 1:case 2:return"[v] dddd [v] LT";case 3:return"[ve středu v] LT";case 4:return"[ve čtvrtek v] LT";case 5:return"[v pátek v] LT";case 6:return"[v sobotu v] LT"}},lastDay:"[včera v] LT",lastWeek:function(){switch(this.day()){case 0:return"[minulou neděli v] LT";case 1:case 2:return"[minulé] dddd [v] LT";case 3:return"[minulou středu v] LT";case 4:case 5:return"[minulý] dddd [v] LT";case 6:return"[minulou sobotu v] LT"}},sameElse:"L"},relativeTime:{future:"za %s",past:"před %s",s:o,ss:o,m:o,mm:o,h:o,hh:o,d:o,dd:o,M:o,MM:o,y:o,yy:o},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/cs",[],function(){function e(e,n){switch(e){case 2:return n?"dva":"dvě";case 3:return"tři";case 4:return"čtyři"}return""}return{errorLoading:function(){return"Výsledky nemohly být načteny."},inputTooLong:function(n){var o=n.input.length-n.maximum;return 1==o?"Prosím, zadejte o jeden znak méně.":o<=4?"Prosím, zadejte o "+e(o,!0)+" znaky méně.":"Prosím, zadejte o "+o+" znaků méně."},inputTooShort:function(n){var o=n.minimum-n.input.length;return 1==o?"Prosím, zadejte ještě jeden znak.":o<=4?"Prosím, zadejte ještě další "+e(o,!0)+" znaky.":"Prosím, zadejte ještě dalších "+o+" znaků."},loadingMore:function(){return"Načítají se další výsledky…"},maximumSelected:function(n){var o=n.maximum;return 1==o?"Můžete zvolit jen jednu položku.":o<=4?"Můžete zvolit maximálně "+e(o,!1)+" položky.":"Můžete zvolit maximálně "+o+" položek."},noResults:function(){return"Nenalezeny žádné položky."},searching:function(){return"Vyhledávání…"},removeAllItems:function(){return"Odstraňte všechny položky"}}}),e.define,e.require}();
