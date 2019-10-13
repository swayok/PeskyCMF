!function(e,n){"function"==typeof define&&define.amd?define(["jquery","query-builder"],n):n(e.jQuery)}(this,function(e){"use strict";var n=e.fn.queryBuilder;n.regional.nl={__locale:"Dutch (nl)",__author:'"Roywcm"',add_rule:"Nieuwe regel",add_group:"Nieuwe groep",delete_rule:"Verwijder",delete_group:"Verwijder",conditions:{AND:"EN",OR:"OF"},operators:{equal:"gelijk",not_equal:"niet gelijk",in:"in",not_in:"niet in",less:"minder",less_or_equal:"minder of gelijk",greater:"groter",greater_or_equal:"groter of gelijk",between:"tussen",not_between:"niet tussen",begins_with:"begint met",not_begins_with:"begint niet met",contains:"bevat",not_contains:"bevat niet",ends_with:"eindigt met",not_ends_with:"eindigt niet met",is_empty:"is leeg",is_not_empty:"is niet leeg",is_null:"is null",is_not_null:"is niet null"},errors:{no_filter:"Geen filter geselecteerd",empty_group:"De groep is leeg",radio_empty:"Geen waarde geselecteerd",checkbox_empty:"Geen waarde geselecteerd",select_empty:"Geen waarde geselecteerd",string_empty:"Lege waarde",string_exceed_min_length:"Dient minstens {0} karakters te bevatten",string_exceed_max_length:"Dient niet meer dan {0} karakters te bevatten",string_invalid_format:"Ongeldig format ({0})",number_nan:"Niet een nummer",number_not_integer:"Geen geheel getal",number_not_double:"Geen echt nummer",number_exceed_min:"Dient groter te zijn dan {0}",number_exceed_max:"Dient lager te zijn dan {0}",number_wrong_step:"Dient een veelvoud te zijn van {0}",datetime_invalid:"Ongeldige datumformat ({0})",datetime_exceed_min:"Dient na {0}",datetime_exceed_max:"Dient voor {0}"}},n.defaults({lang_code:"nl"})}),function(e){"use strict";e.fn.fileinputLocales.nl={fileSingle:"bestand",filePlural:"bestanden",browseLabel:"Zoek &hellip;",removeLabel:"Verwijder",removeTitle:"Verwijder geselecteerde bestanden",cancelLabel:"Annuleren",cancelTitle:"Annuleer upload",uploadLabel:"Upload",uploadTitle:"Upload geselecteerde bestanden",msgNo:"Nee",msgNoFilesSelected:"",msgCancelled:"Geannuleerd",msgPlaceholder:"Selecteer {files}...",msgZoomModalHeading:"Gedetailleerd voorbeeld",msgFileRequired:"U moet een bestand kiezen om te uploaden.",msgSizeTooSmall:'Bestand "{name}" (<b>{size} KB</b>) is te klein en moet groter zijn dan <b>{minSize} KB</b>.',msgSizeTooLarge:'Bestand "{name}" (<b>{size} KB</b>) is groter dan de toegestane <b>{maxSize} KB</b>.',msgFilesTooLess:"U moet minstens <b>{n}</b> {files} selecteren om te uploaden.",msgFilesTooMany:"Aantal geselecteerde bestanden <b>({n})</b> is meer dan de toegestane <b>{m}</b>.",msgFileNotFound:'Bestand "{name}" niet gevonden!',msgFileSecured:'Bestand kan niet gelezen worden in verband met beveiligings redenen "{name}".',msgFileNotReadable:'Bestand "{name}" is niet leesbaar.',msgFilePreviewAborted:'Bestand weergaven geannuleerd voor "{name}".',msgFilePreviewError:'Er is een fout opgetreden met het lezen van "{name}".',msgInvalidFileName:'Ongeldige of niet ondersteunde karakters in bestandsnaam "{name}".',msgInvalidFileType:'Geen geldig bestand "{name}". Alleen "{types}" zijn toegestaan.',msgInvalidFileExtension:'Geen geldige extensie "{name}". Alleen "{extensions}" zijn toegestaan.',msgFileTypes:{image:"afbeelding",html:"HTML",text:"tekst",video:"video",audio:"geluid",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Het uploaden van bestanden is afgebroken",msgUploadThreshold:"Verwerken...",msgUploadBegin:"Initialiseren...",msgUploadEnd:"Gedaan",msgUploadEmpty:"Geen geldige data beschikbaar voor upload.",msgUploadError:"Error",msgValidationError:"Bevestiging fout",msgLoading:"Bestanden laden {index} van de {files} &hellip;",msgProgress:"Bestanden laden {index} van de {files} - {name} - {percent}% compleet.",msgSelected:"{n} {files} geselecteerd",msgFoldersNotAllowed:"Drag & drop alleen bestanden! {n} overgeslagen map(pen).",msgImageWidthSmall:'Breedte van het foto-bestand "{name}" moet minstens {size} px zijn.',msgImageHeightSmall:'Hoogte van het foto-bestand "{name}" moet minstens {size} px zijn.',msgImageWidthLarge:'Breedte van het foto-bestand "{name}" kan niet hoger zijn dan {size} px.',msgImageHeightLarge:'Hoogte van het foto bestand "{name}" kan niet hoger zijn dan {size} px.',msgImageResizeError:"Kon de foto afmetingen niet lezen om te verkleinen.",msgImageResizeException:"Fout bij het verkleinen van de foto.<pre>{errors}</pre>",msgAjaxError:"Er ging iets mis met de {operation} actie. Gelieve later opnieuw te proberen!",msgAjaxProgressError:"{operation} mislukt",ajaxOperations:{deleteThumb:"bestand verwijderen",uploadThumb:"bestand uploaden",uploadBatch:"alle bestanden uploaden",uploadExtra:"form data upload"},dropZoneTitle:"Drag & drop bestanden hier &hellip;",dropZoneClickTitle:"<br>(of klik hier om {files} te selecteren)",fileActionSettings:{removeTitle:"Verwijder bestand",uploadTitle:"bestand uploaden",uploadRetryTitle:"Opnieuw uploaden",downloadTitle:"Download file",zoomTitle:"Bekijk details",dragTitle:"Verplaatsen / herindelen",indicatorNewTitle:"Nog niet geupload",indicatorSuccessTitle:"geupload",indicatorErrorTitle:"fout uploaden",indicatorLoadingTitle:"uploaden ..."},previewZoomButtonTitles:{prev:"Toon vorig bestand",next:"Toon volgend bestand",toggleheader:"Toggle header",fullscreen:"Toggle volledig scherm",borderless:"Toggle randloze modus",close:"Sluit gedetailleerde weergave"}}}(window.jQuery),function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";var n="jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.".split("_"),t="jan_feb_mrt_apr_mei_jun_jul_aug_sep_okt_nov_dec".split("_"),i=[/^jan/i,/^feb/i,/^maart|mrt.?$/i,/^apr/i,/^mei$/i,/^jun[i.]?$/i,/^jul[i.]?$/i,/^aug/i,/^sep/i,/^okt/i,/^nov/i,/^dec/i],a=/^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december|jan\.?|feb\.?|mrt\.?|apr\.?|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i;return e.defineLocale("nl-be",{months:"januari_februari_maart_april_mei_juni_juli_augustus_september_oktober_november_december".split("_"),monthsShort:function(e,i){return e?/-MMM-/.test(i)?t[e.month()]:n[e.month()]:n},monthsRegex:a,monthsShortRegex:a,monthsStrictRegex:/^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december)/i,monthsShortStrictRegex:/^(jan\.?|feb\.?|mrt\.?|apr\.?|mei|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i,monthsParse:i,longMonthsParse:i,shortMonthsParse:i,weekdays:"zondag_maandag_dinsdag_woensdag_donderdag_vrijdag_zaterdag".split("_"),weekdaysShort:"zo._ma._di._wo._do._vr._za.".split("_"),weekdaysMin:"zo_ma_di_wo_do_vr_za".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD/MM/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd D MMMM YYYY HH:mm"},calendar:{sameDay:"[vandaag om] LT",nextDay:"[morgen om] LT",nextWeek:"dddd [om] LT",lastDay:"[gisteren om] LT",lastWeek:"[afgelopen] dddd [om] LT",sameElse:"L"},relativeTime:{future:"over %s",past:"%s geleden",s:"een paar seconden",ss:"%d seconden",m:"één minuut",mm:"%d minuten",h:"één uur",hh:"%d uur",d:"één dag",dd:"%d dagen",M:"één maand",MM:"%d maanden",y:"één jaar",yy:"%d jaar"},dayOfMonthOrdinalParse:/\d{1,2}(ste|de)/,ordinal:function(e){return e+(1===e||8===e||e>=20?"ste":"de")},week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/nl",[],function(){return{errorLoading:function(){return"De resultaten konden niet worden geladen."},inputTooLong:function(e){return"Gelieve "+(e.input.length-e.maximum)+" karakters te verwijderen"},inputTooShort:function(e){return"Gelieve "+(e.minimum-e.input.length)+" of meer karakters in te voeren"},loadingMore:function(){return"Meer resultaten laden…"},maximumSelected:function(e){var n=1==e.maximum?"kan":"kunnen",t="Er "+n+" maar "+e.maximum+" item";return 1!=e.maximum&&(t+="s"),t+=" worden geselecteerd"},noResults:function(){return"Geen resultaten gevonden…"},searching:function(){return"Zoeken…"},removeAllItems:function(){return"Verwijder alle items"}}}),e.define,e.require}(),function(e,n){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return n(e)}):"object"==typeof module&&module.exports?module.exports=n(require("jquery")):n(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Niets geselecteerd",noneResultsText:"Geen resultaten gevonden voor {0}",countSelectedText:"{0} van {1} geselecteerd",maxOptionsText:["Limiet bereikt ({n} {var} max)","Groep limiet bereikt ({n} {var} max)",["items","item"]],selectAllText:"Alles selecteren",deselectAllText:"Alles deselecteren",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["nl-NL"]={currentlySelected:"Momenteel geselecteerd",emptyTitle:"Selecteer en begin met typen",errorText:"Kon geen resultaten ophalen",searchPlaceholder:"Zoeken...",statusInitialized:"Begin met typen om te zoeken",statusNoResults:"Geen resultaten",statusSearching:"Zoeken...",statusTooShort:"U dient meer karakters in te voeren"},e.fn.ajaxSelectPicker.locale.nl=e.fn.ajaxSelectPicker.locale["nl-NL"]}(jQuery);
