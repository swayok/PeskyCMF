!function(e,n){"function"==typeof define&&define.amd?define(["jquery","query-builder"],n):n(e.jQuery)}(this,function(e){"use strict";var n=e.fn.queryBuilder;n.regional.de={__locale:"German (de)",__author:'"raimu"',add_rule:"neue Regel",add_group:"neue Gruppe",delete_rule:"löschen",delete_group:"löschen",conditions:{AND:"UND",OR:"ODER"},operators:{equal:"gleich",not_equal:"ungleich",in:"in",not_in:"nicht in",less:"kleiner",less_or_equal:"kleiner gleich",greater:"größer",greater_or_equal:"größer gleich",between:"zwischen",not_between:"nicht zwischen",begins_with:"beginnt mit",not_begins_with:"beginnt nicht mit",contains:"enthält",not_contains:"enthält nicht",ends_with:"endet mit",not_ends_with:"endet nicht mit",is_empty:"ist leer",is_not_empty:"ist nicht leer",is_null:"ist null",is_not_null:"ist nicht null"},errors:{no_filter:"Kein Filter ausgewählt",empty_group:"Die Gruppe ist leer",radio_empty:"Kein Wert ausgewählt",checkbox_empty:"Kein Wert ausgewählt",select_empty:"Kein Wert ausgewählt",string_empty:"Leerer Wert",string_exceed_min_length:"Muss mindestens {0} Zeichen enthalten",string_exceed_max_length:"Darf nicht mehr als {0} Zeichen enthalten",string_invalid_format:"Ungültiges Format ({0})",number_nan:"Keine Zahl",number_not_integer:"Keine Ganzzahl",number_not_double:"Keine Dezimalzahl",number_exceed_min:"Muss größer als {0} sein",number_exceed_max:"Muss kleiner als {0} sein",number_wrong_step:"Muss ein Vielfaches von {0} sein",datetime_invalid:"Ungültiges Datumsformat ({0})",datetime_exceed_min:"Muss nach dem {0} sein",datetime_exceed_max:"Muss vor dem {0} sein"}},n.defaults({lang_code:"de"})}),function(e){"use strict";e.fn.fileinputLocales.de={fileSingle:"Datei",filePlural:"Dateien",browseLabel:"Auswählen &hellip;",removeLabel:"Löschen",removeTitle:"Ausgewählte löschen",cancelLabel:"Abbrechen",cancelTitle:"Hochladen abbrechen",uploadLabel:"Hochladen",uploadTitle:"Hochladen der ausgewählten Dateien",msgNo:"Keine",msgNoFilesSelected:"Keine Dateien ausgewählt",msgCancelled:"Abgebrochen",msgPlaceholder:"{files} auswählen...",msgZoomModalHeading:"ausführliche Vorschau",msgFileRequired:"Sie müssen eine Datei zum Hochladen auswählen.",msgSizeTooSmall:'Datei "{name}" (<b>{size} KB</b>) unterschreitet mindestens notwendige Upload-Größe von <b>{minSize} KB</b>.',msgSizeTooLarge:'Datei "{name}" (<b>{size} KB</b>) überschreitet maximal zulässige Upload-Größe von <b>{maxSize} KB</b>.',msgFilesTooLess:"Sie müssen mindestens <b>{n}</b> {files} zum Hochladen auswählen.",msgFilesTooMany:"Anzahl der zum Hochladen ausgewählten Dateien <b>({n})</b>, überschreitet maximal zulässige Grenze von <b>{m}</b> Stück.",msgFileNotFound:'Datei "{name}" wurde nicht gefunden!',msgFileSecured:'Sicherheitseinstellungen verhindern das Lesen der Datei "{name}".',msgFileNotReadable:'Die Datei "{name}" ist nicht lesbar.',msgFilePreviewAborted:'Dateivorschau abgebrochen für "{name}".',msgFilePreviewError:'Beim Lesen der Datei "{name}" ein Fehler aufgetreten.',msgInvalidFileName:'Ungültige oder nicht unterstützte Zeichen im Dateinamen "{name}".',msgInvalidFileType:'Ungültiger Typ für Datei "{name}". Nur Dateien der Typen "{types}" werden unterstützt.',msgInvalidFileExtension:'Ungültige Erweiterung für Datei "{name}". Nur Dateien mit der Endung "{extensions}" werden unterstützt.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Der Datei-Upload wurde abgebrochen",msgUploadThreshold:"Wird bearbeitet ...",msgUploadBegin:"Wird initialisiert ...",msgUploadEnd:"Erledigt",msgUploadEmpty:"Keine gültigen Daten zum Hochladen verfügbar.",msgUploadError:"Fehler",msgValidationError:"Validierungsfehler",msgLoading:"Lade Datei {index} von {files} hoch&hellip;",msgProgress:"Datei {index} von {files} - {name} - zu {percent}% fertiggestellt.",msgSelected:"{n} {files} ausgewählt",msgFoldersNotAllowed:"Drag & Drop funktioniert nur bei Dateien! {n} Ordner übersprungen.",msgImageWidthSmall:'Breite der Bilddatei "{name}" muss mindestens {size} px betragen.',msgImageHeightSmall:'Höhe der Bilddatei "{name}" muss mindestens {size} px betragen.',msgImageWidthLarge:'Breite der Bilddatei "{name}" nicht überschreiten {size} px.',msgImageHeightLarge:'Höhe der Bilddatei "{name}" nicht überschreiten {size} px.',msgImageResizeError:"Konnte nicht die Bildabmessungen zu ändern.",msgImageResizeException:"Fehler beim Ändern der Größe des Bildes.<pre>{errors}</pre>",msgAjaxError:"Bei der Aktion {operation} ist ein Fehler aufgetreten. Bitte versuche es später noch einmal!",msgAjaxProgressError:"{operation} fehlgeschlagen",ajaxOperations:{deleteThumb:"Datei löschen",uploadThumb:"Datei hochladen",uploadBatch:"Batch-Datei-Upload",uploadExtra:"Formular-Datei-Upload"},dropZoneTitle:"Dateien hierher ziehen &hellip;",dropZoneClickTitle:"<br>(oder klicken um {files} auszuwählen)",fileActionSettings:{removeTitle:"Datei entfernen",uploadTitle:"Datei hochladen",uploadRetryTitle:"Upload erneut versuchen",downloadTitle:"Datei herunterladen",zoomTitle:"Details anzeigen",dragTitle:"Verschieben / Neuordnen",indicatorNewTitle:"Noch nicht hochgeladen",indicatorSuccessTitle:"Hochgeladen",indicatorErrorTitle:"Upload Fehler",indicatorLoadingTitle:"Hochladen ..."},previewZoomButtonTitles:{prev:"Vorherige Datei anzeigen",next:"Nächste Datei anzeigen",toggleheader:"Header umschalten",fullscreen:"Vollbildmodus umschalten",borderless:"Randlosen Modus umschalten",close:"Detailansicht schließen"}}}(window.jQuery),function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";function n(e,n,i,t){var r={m:["eine Minute","einer Minute"],h:["eine Stunde","einer Stunde"],d:["ein Tag","einem Tag"],dd:[e+" Tage",e+" Tagen"],M:["ein Monat","einem Monat"],MM:[e+" Monate",e+" Monaten"],y:["ein Jahr","einem Jahr"],yy:[e+" Jahre",e+" Jahren"]};return n?r[i][0]:r[i][1]}return e.defineLocale("de-ch",{months:"Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember".split("_"),monthsShort:"Jan._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.".split("_"),monthsParseExact:!0,weekdays:"Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag".split("_"),weekdaysShort:"So_Mo_Di_Mi_Do_Fr_Sa".split("_"),weekdaysMin:"So_Mo_Di_Mi_Do_Fr_Sa".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY HH:mm",LLLL:"dddd, D. MMMM YYYY HH:mm"},calendar:{sameDay:"[heute um] LT [Uhr]",sameElse:"L",nextDay:"[morgen um] LT [Uhr]",nextWeek:"dddd [um] LT [Uhr]",lastDay:"[gestern um] LT [Uhr]",lastWeek:"[letzten] dddd [um] LT [Uhr]"},relativeTime:{future:"in %s",past:"vor %s",s:"ein paar Sekunden",ss:"%d Sekunden",m:n,mm:"%d Minuten",h:n,hh:"%d Stunden",d:n,dd:n,M:n,MM:n,y:n,yy:n},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/de",[],function(){return{errorLoading:function(){return"Die Ergebnisse konnten nicht geladen werden."},inputTooLong:function(e){return"Bitte "+(e.input.length-e.maximum)+" Zeichen weniger eingeben"},inputTooShort:function(e){return"Bitte "+(e.minimum-e.input.length)+" Zeichen mehr eingeben"},loadingMore:function(){return"Lade mehr Ergebnisse…"},maximumSelected:function(e){var n="Sie können nur "+e.maximum+" Eintr";return 1===e.maximum?n+="ag":n+="äge",n+=" auswählen"},noResults:function(){return"Keine Übereinstimmungen gefunden"},searching:function(){return"Suche…"},removeAllItems:function(){return"Entferne alle Gegenstände"}}}),e.define,e.require}(),function(e,n){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return n(e)}):"object"==typeof module&&module.exports?module.exports=n(require("jquery")):n(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Bitte wählen...",noneResultsText:"Keine Ergebnisse für {0}",countSelectedText:function(e,n){return 1==e?"{0} Element ausgewählt":"{0} Elemente ausgewählt"},maxOptionsText:function(e,n){return[1==e?"Limit erreicht ({n} Element max.)":"Limit erreicht ({n} Elemente max.)",1==n?"Gruppen-Limit erreicht ({n} Element max.)":"Gruppen-Limit erreicht ({n} Elemente max.)"]},selectAllText:"Alles auswählen",deselectAllText:"Nichts auswählen",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["de-DE"]={currentlySelected:"Momentan ausgewählt",emptyTitle:"Hier klicken und eingeben",errorText:"Ergebnisse konnten nicht abgerufen wurden",searchPlaceholder:"Suche...",statusInitialized:"Suchbegriff eingeben",statusNoResults:"Keine Ergebnisse",statusSearching:"Suche...",statusTooShort:"Der Suchbegriff ist nicht lang genug"},e.fn.ajaxSelectPicker.locale.de=e.fn.ajaxSelectPicker.locale["de-DE"]}(jQuery);
