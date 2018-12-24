!function(e,i){"function"==typeof define&&define.amd?define(["jquery","query-builder"],i):i(e.jQuery)}(this,function(e){"use strict";var i=e.fn.queryBuilder;i.regional.pl={__locale:"Polish (pl)",__author:"Artur Smolarek",add_rule:"Dodaj regułę",add_group:"Dodaj grupę",delete_rule:"Usuń",delete_group:"Usuń",conditions:{AND:"ORAZ",OR:"LUB"},operators:{equal:"równa się",not_equal:"jest różne od",in:"zawiera",not_in:"nie zawiera",less:"mniejsze",less_or_equal:"mniejsze lub równe",greater:"większe",greater_or_equal:"większe lub równe",between:"pomiędzy",not_between:"nie jest pomiędzy",begins_with:"rozpoczyna się od",not_begins_with:"nie rozpoczyna się od",contains:"zawiera",not_contains:"nie zawiera",ends_with:"kończy się na",not_ends_with:"nie kończy się na",is_empty:"jest puste",is_not_empty:"nie jest puste",is_null:"jest niezdefiniowane",is_not_null:"nie jest niezdefiniowane"},errors:{no_filter:"Nie wybrano żadnego filtra",empty_group:"Grupa jest pusta",radio_empty:"Nie wybrano wartości",checkbox_empty:"Nie wybrano wartości",select_empty:"Nie wybrano wartości",string_empty:"Nie wpisano wartości",string_exceed_min_length:"Minimalna długość to {0} znaków",string_exceed_max_length:"Maksymalna długość to {0} znaków",string_invalid_format:"Nieprawidłowy format ({0})",number_nan:"To nie jest liczba",number_not_integer:"To nie jest liczba całkowita",number_not_double:"To nie jest liczba rzeczywista",number_exceed_min:"Musi być większe niż {0}",number_exceed_max:"Musi być mniejsze niż {0}",number_wrong_step:"Musi być wielokrotnością {0}",datetime_empty:"Nie wybrano wartości",datetime_invalid:"Nieprawidłowy format daty ({0})",datetime_exceed_min:"Musi być po {0}",datetime_exceed_max:"Musi być przed {0}",boolean_not_valid:"Niepoprawna wartość logiczna",operator_not_multiple:'Operator "{1}" nie przyjmuje wielu wartości'},invert:"Odwróć"},i.defaults({lang_code:"pl"})}),function(e){"use strict";e.fn.fileinputLocales.pl={fileSingle:"plik",filePlural:"pliki",browseLabel:"Przeglądaj &hellip;",removeLabel:"Usuń",removeTitle:"Usuń zaznaczone pliki",cancelLabel:"Przerwij",cancelTitle:"Anuluj wysyłanie",uploadLabel:"Wgraj",uploadTitle:"Wgraj zaznaczone pliki",msgNo:"Nie",msgNoFilesSelected:"Brak zaznaczonych plików",msgCancelled:"Odwołany",msgPlaceholder:"Wybierz {files}...",msgZoomModalHeading:"Szczegółowy podgląd",msgFileRequired:"Musisz wybrać plik do wgrania.",msgSizeTooSmall:'Plik "{name}" (<b>{size} KB</b>) jest zbyt mały i musi być większy niż <b>{minSize} KB</b>.',msgSizeTooLarge:'Plik o nazwie "{name}" (<b>{size} KB</b>) przekroczył maksymalną dopuszczalną wielkość pliku wynoszącą <b>{maxSize} KB</b>.',msgFilesTooLess:"Minimalna liczba plików do wgrania: <b>{n}</b>.",msgFilesTooMany:"Liczba plików wybranych do wgrania w liczbie <b>({n})</b>, przekracza maksymalny dozwolony limit wynoszący <b>{m}</b>.",msgFileNotFound:'Plik "{name}" nie istnieje!',msgFileSecured:'Ustawienia zabezpieczeń uniemożliwiają odczyt pliku "{name}".',msgFileNotReadable:'Plik "{name}" nie jest plikiem do odczytu.',msgFilePreviewAborted:'Podgląd pliku "{name}" został przerwany.',msgFilePreviewError:'Wystąpił błąd w czasie odczytu pliku "{name}".',msgInvalidFileName:'Nieprawidłowe lub nieobsługiwane znaki w nazwie pliku "{name}".',msgInvalidFileType:'Nieznany typ pliku "{name}". Tylko następujące rodzaje plików są dozwolone: "{types}".',msgInvalidFileExtension:'Złe rozszerzenie dla pliku "{name}". Tylko następujące rozszerzenia plików są dozwolone: "{extensions}".',msgUploadAborted:"Przesyłanie pliku zostało przerwane",msgUploadThreshold:"Przetwarzanie...",msgUploadBegin:"Rozpoczynanie...",msgUploadEnd:"Gotowe!",msgUploadEmpty:"Brak poprawnych danych do przesłania.",msgUploadError:"Błąd",msgValidationError:"Błąd walidacji",msgLoading:"Wczytywanie pliku {index} z {files} &hellip;",msgProgress:"Wczytywanie pliku {index} z {files} - {name} - {percent}% zakończone.",msgSelected:"{n} Plików zaznaczonych",msgFoldersNotAllowed:"Metodą przeciągnij i upuść, można przenosić tylko pliki. Pominięto {n} katalogów.",msgImageWidthSmall:'Szerokość pliku obrazu "{name}" musi być co najmniej {size} px.',msgImageHeightSmall:'Wysokość pliku obrazu "{name}" musi być co najmniej {size} px.',msgImageWidthLarge:'Szerokość pliku obrazu "{name}" nie może przekraczać {size} px.',msgImageHeightLarge:'Wysokość pliku obrazu "{name}" nie może przekraczać {size} px.',msgImageResizeError:"Nie udało się uzyskać wymiaru obrazu, aby zmienić rozmiar.",msgImageResizeException:"Błąd podczas zmiany rozmiaru obrazu.<pre>{errors}</pre>",msgAjaxError:"Coś poczło nie tak podczas {operation}. Spróbuj ponownie!",msgAjaxProgressError:"{operation} nie powiodło się",ajaxOperations:{deleteThumb:"usuwanie pliku",uploadThumb:"przesyłanie pliku",uploadBatch:"masowe przesyłanie plików",uploadExtra:"przesyłanie danych formularza"},dropZoneTitle:"Przeciągnij i upuść pliki tutaj &hellip;",dropZoneClickTitle:"<br>(lub kliknij tutaj i wybierz {files} z komputera)",fileActionSettings:{removeTitle:"Usuń plik",uploadTitle:"Przesyłanie pliku",uploadRetryTitle:"Ponów",downloadTitle:"Pobierz plik",zoomTitle:"Pokaż szczegóły",dragTitle:"Przenies / Ponownie zaaranżuj",indicatorNewTitle:"Jeszcze nie przesłany",indicatorSuccessTitle:"Dodane",indicatorErrorTitle:"Błąd",indicatorLoadingTitle:"Przesyłanie ..."},previewZoomButtonTitles:{prev:"Pokaż poprzedni plik",next:"Pokaż następny plik",toggleheader:"Włącz / wyłącz nagłówek",fullscreen:"Włącz / wyłącz pełny ekran",borderless:"Włącz / wyłącz tryb bez ramek",close:"Zamknij szczegółowy widok"}}}(window.jQuery),function(e,i){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?i(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],i):i(e.moment)}(this,function(e){"use strict";function i(e){return e%10<5&&e%10>1&&~~(e/10)%10!=1}function n(e,n,a){var o=e+" ";switch(a){case"ss":return o+(i(e)?"sekundy":"sekund");case"m":return n?"minuta":"minutę";case"mm":return o+(i(e)?"minuty":"minut");case"h":return n?"godzina":"godzinę";case"hh":return o+(i(e)?"godziny":"godzin");case"MM":return o+(i(e)?"miesiące":"miesięcy");case"yy":return o+(i(e)?"lata":"lat")}}var a="styczeń_luty_marzec_kwiecień_maj_czerwiec_lipiec_sierpień_wrzesień_październik_listopad_grudzień".split("_"),o="stycznia_lutego_marca_kwietnia_maja_czerwca_lipca_sierpnia_września_października_listopada_grudnia".split("_");return e.defineLocale("pl",{months:function(e,i){return e?""===i?"("+o[e.month()]+"|"+a[e.month()]+")":/D MMMM/.test(i)?o[e.month()]:a[e.month()]:a},monthsShort:"sty_lut_mar_kwi_maj_cze_lip_sie_wrz_paź_lis_gru".split("_"),weekdays:"niedziela_poniedziałek_wtorek_środa_czwartek_piątek_sobota".split("_"),weekdaysShort:"ndz_pon_wt_śr_czw_pt_sob".split("_"),weekdaysMin:"Nd_Pn_Wt_Śr_Cz_Pt_So".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd, D MMMM YYYY HH:mm"},calendar:{sameDay:"[Dziś o] LT",nextDay:"[Jutro o] LT",nextWeek:function(){switch(this.day()){case 0:return"[W niedzielę o] LT";case 2:return"[We wtorek o] LT";case 3:return"[W środę o] LT";case 6:return"[W sobotę o] LT";default:return"[W] dddd [o] LT"}},lastDay:"[Wczoraj o] LT",lastWeek:function(){switch(this.day()){case 0:return"[W zeszłą niedzielę o] LT";case 3:return"[W zeszłą środę o] LT";case 6:return"[W zeszłą sobotę o] LT";default:return"[W zeszły] dddd [o] LT"}},sameElse:"L"},relativeTime:{future:"za %s",past:"%s temu",s:"kilka sekund",ss:n,m:n,mm:n,h:n,hh:n,d:"1 dzień",dd:"%d dni",M:"miesiąc",MM:n,y:"rok",yy:n},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/pl",[],function(){var e=["znak","znaki","znaków"],i=["element","elementy","elementów"],n=function(e,i){return 1===e?i[0]:e>1&&e<=4?i[1]:e>=5?i[2]:void 0};return{errorLoading:function(){return"Nie można załadować wyników."},inputTooLong:function(i){var a=i.input.length-i.maximum;return"Usuń "+a+" "+n(a,e)},inputTooShort:function(i){var a=i.minimum-i.input.length;return"Podaj przynajmniej "+a+" "+n(a,e)},loadingMore:function(){return"Trwa ładowanie…"},maximumSelected:function(e){return"Możesz zaznaczyć tylko "+e.maximum+" "+n(e.maximum,i)},noResults:function(){return"Brak wyników"},searching:function(){return"Trwa wyszukiwanie…"}}}),e.define,e.require}(),function(e,i){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return i(e)}):"object"==typeof module&&module.exports?module.exports=i(require("jquery")):i(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Nic nie zaznaczono",noneResultsText:"Brak wyników wyszukiwania {0}",countSelectedText:"Zaznaczono {0} z {1}",maxOptionsText:["Osiągnięto limit ({n} {var} max)","Limit grupy osiągnięty ({n} {var} max)",["elementy","element"]],selectAllText:"Zaznacz wszystkie",deselectAllText:"Odznacz wszystkie",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["pl-PL"]={currentlySelected:"Aktualny wybór",emptyTitle:"Wybierz i zacznij pisać",errorText:"Nie można pobrać wyników",searchPlaceholder:"Szukaj...",statusInitialized:"Zacznij pisać warunek wyszukiwania",statusNoResults:"Brak wyników",statusSearching:"Szukam...",statusTooShort:"Wprowadź więcej znaków"},e.fn.ajaxSelectPicker.locale.pl=e.fn.ajaxSelectPicker.locale["pl-PL"]}(jQuery);
