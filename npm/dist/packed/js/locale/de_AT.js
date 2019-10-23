/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: German (de)
 * Author: "raimu"
 * Licensed under MIT (https://opensource.org/licenses/MIT)
 */

(function(root, factory) {
    if (typeof define == 'function' && define.amd) {
        define(['jquery', 'query-builder'], factory);
    }
    else {
        factory(root.jQuery);
    }
}(this, function($) {
"use strict";

var QueryBuilder = $.fn.queryBuilder;

QueryBuilder.regional['de'] = {
  "__locale": "German (de)",
  "__author": "\"raimu\"",
  "add_rule": "neue Regel",
  "add_group": "neue Gruppe",
  "delete_rule": "löschen",
  "delete_group": "löschen",
  "conditions": {
    "AND": "UND",
    "OR": "ODER"
  },
  "operators": {
    "equal": "gleich",
    "not_equal": "ungleich",
    "in": "in",
    "not_in": "nicht in",
    "less": "kleiner",
    "less_or_equal": "kleiner gleich",
    "greater": "größer",
    "greater_or_equal": "größer gleich",
    "between": "zwischen",
    "not_between": "nicht zwischen",
    "begins_with": "beginnt mit",
    "not_begins_with": "beginnt nicht mit",
    "contains": "enthält",
    "not_contains": "enthält nicht",
    "ends_with": "endet mit",
    "not_ends_with": "endet nicht mit",
    "is_empty": "ist leer",
    "is_not_empty": "ist nicht leer",
    "is_null": "ist null",
    "is_not_null": "ist nicht null"
  },
  "errors": {
    "no_filter": "Kein Filter ausgewählt",
    "empty_group": "Die Gruppe ist leer",
    "radio_empty": "Kein Wert ausgewählt",
    "checkbox_empty": "Kein Wert ausgewählt",
    "select_empty": "Kein Wert ausgewählt",
    "string_empty": "Leerer Wert",
    "string_exceed_min_length": "Muss mindestens {0} Zeichen enthalten",
    "string_exceed_max_length": "Darf nicht mehr als {0} Zeichen enthalten",
    "string_invalid_format": "Ungültiges Format ({0})",
    "number_nan": "Keine Zahl",
    "number_not_integer": "Keine Ganzzahl",
    "number_not_double": "Keine Dezimalzahl",
    "number_exceed_min": "Muss größer als {0} sein",
    "number_exceed_max": "Muss kleiner als {0} sein",
    "number_wrong_step": "Muss ein Vielfaches von {0} sein",
    "datetime_invalid": "Ungültiges Datumsformat ({0})",
    "datetime_exceed_min": "Muss nach dem {0} sein",
    "datetime_exceed_max": "Muss vor dem {0} sein"
  }
};

QueryBuilder.defaults({ lang_code: 'de' });
}));
/*!
 * FileInput German Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['de'] = {
        fileSingle: 'Datei',
        filePlural: 'Dateien',
        browseLabel: 'Auswählen &hellip;',
        removeLabel: 'Löschen',
        removeTitle: 'Ausgewählte löschen',
        cancelLabel: 'Abbrechen',
        cancelTitle: 'Hochladen abbrechen',
        uploadLabel: 'Hochladen',
        uploadTitle: 'Hochladen der ausgewählten Dateien',
        msgNo: 'Keine',
        msgNoFilesSelected: 'Keine Dateien ausgewählt',
        msgCancelled: 'Abgebrochen',
        msgPlaceholder: '{files} auswählen...',
        msgZoomModalHeading: 'ausführliche Vorschau',
        msgFileRequired: 'Sie müssen eine Datei zum Hochladen auswählen.',
        msgSizeTooSmall: 'Datei "{name}" (<b>{size} KB</b>) unterschreitet mindestens notwendige Upload-Größe von <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Datei "{name}" (<b>{size} KB</b>) überschreitet maximal zulässige Upload-Größe von <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Sie müssen mindestens <b>{n}</b> {files} zum Hochladen auswählen.',
        msgFilesTooMany: 'Anzahl der zum Hochladen ausgewählten Dateien <b>({n})</b>, überschreitet maximal zulässige Grenze von <b>{m}</b> Stück.',
        msgFileNotFound: 'Datei "{name}" wurde nicht gefunden!',
        msgFileSecured: 'Sicherheitseinstellungen verhindern das Lesen der Datei "{name}".',
        msgFileNotReadable: 'Die Datei "{name}" ist nicht lesbar.',
        msgFilePreviewAborted: 'Dateivorschau abgebrochen für "{name}".',
        msgFilePreviewError: 'Beim Lesen der Datei "{name}" ein Fehler aufgetreten.',
        msgInvalidFileName: 'Ungültige oder nicht unterstützte Zeichen im Dateinamen "{name}".',
        msgInvalidFileType: 'Ungültiger Typ für Datei "{name}". Nur Dateien der Typen "{types}" werden unterstützt.',
        msgInvalidFileExtension: 'Ungültige Erweiterung für Datei "{name}". Nur Dateien mit der Endung "{extensions}" werden unterstützt.',
        msgFileTypes: {
            'image': 'image',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'audio',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'Der Datei-Upload wurde abgebrochen',
        msgUploadThreshold: 'Wird bearbeitet ...',
        msgUploadBegin: 'Wird initialisiert ...',
        msgUploadEnd: 'Erledigt',
        msgUploadEmpty: 'Keine gültigen Daten zum Hochladen verfügbar.',
        msgUploadError: 'Fehler',
        msgValidationError: 'Validierungsfehler',
        msgLoading: 'Lade Datei {index} von {files} hoch&hellip;',
        msgProgress: 'Datei {index} von {files} - {name} - zu {percent}% fertiggestellt.',
        msgSelected: '{n} {files} ausgewählt',
        msgFoldersNotAllowed: 'Drag & Drop funktioniert nur bei Dateien! {n} Ordner übersprungen.',
        msgImageWidthSmall: 'Breite der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageHeightSmall: 'Höhe der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageWidthLarge: 'Breite der Bilddatei "{name}" nicht überschreiten {size} px.',
        msgImageHeightLarge: 'Höhe der Bilddatei "{name}" nicht überschreiten {size} px.',
        msgImageResizeError: 'Konnte nicht die Bildabmessungen zu ändern.',
        msgImageResizeException: 'Fehler beim Ändern der Größe des Bildes.<pre>{errors}</pre>',
        msgAjaxError: 'Bei der Aktion {operation} ist ein Fehler aufgetreten. Bitte versuche es später noch einmal!',
        msgAjaxProgressError: '{operation} fehlgeschlagen',
        ajaxOperations: {
            deleteThumb: 'Datei löschen',
            uploadThumb: 'Datei hochladen',
            uploadBatch: 'Batch-Datei-Upload',
            uploadExtra: 'Formular-Datei-Upload'
        },
        dropZoneTitle: 'Dateien hierher ziehen &hellip;',
        dropZoneClickTitle: '<br>(oder klicken um {files} auszuwählen)',
        fileActionSettings: {
            removeTitle: 'Datei entfernen',
            uploadTitle: 'Datei hochladen',
            uploadRetryTitle: 'Upload erneut versuchen',
            downloadTitle: 'Datei herunterladen',
            zoomTitle: 'Details anzeigen',
            dragTitle: 'Verschieben / Neuordnen',
            indicatorNewTitle: 'Noch nicht hochgeladen',
            indicatorSuccessTitle: 'Hochgeladen',
            indicatorErrorTitle: 'Upload Fehler',
            indicatorLoadingTitle: 'Hochladen ...'
        },
        previewZoomButtonTitles: {
            prev: 'Vorherige Datei anzeigen',
            next: 'Nächste Datei anzeigen',
            toggleheader: 'Header umschalten',
            fullscreen: 'Vollbildmodus umschalten',
            borderless: 'Randlosen Modus umschalten',
            close: 'Detailansicht schließen'
        }
    };
})(window.jQuery);

//! moment.js locale configuration

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';


    function processRelativeTime(number, withoutSuffix, key, isFuture) {
        var format = {
            'm': ['eine Minute', 'einer Minute'],
            'h': ['eine Stunde', 'einer Stunde'],
            'd': ['ein Tag', 'einem Tag'],
            'dd': [number + ' Tage', number + ' Tagen'],
            'M': ['ein Monat', 'einem Monat'],
            'MM': [number + ' Monate', number + ' Monaten'],
            'y': ['ein Jahr', 'einem Jahr'],
            'yy': [number + ' Jahre', number + ' Jahren']
        };
        return withoutSuffix ? format[key][0] : format[key][1];
    }

    var deAt = moment.defineLocale('de-at', {
        months : 'Jänner_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'.split('_'),
        monthsShort : 'Jän._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'.split('_'),
        monthsParseExact : true,
        weekdays : 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'.split('_'),
        weekdaysShort : 'So._Mo._Di._Mi._Do._Fr._Sa.'.split('_'),
        weekdaysMin : 'So_Mo_Di_Mi_Do_Fr_Sa'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT: 'HH:mm',
            LTS: 'HH:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D. MMMM YYYY',
            LLL : 'D. MMMM YYYY HH:mm',
            LLLL : 'dddd, D. MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay: '[heute um] LT [Uhr]',
            sameElse: 'L',
            nextDay: '[morgen um] LT [Uhr]',
            nextWeek: 'dddd [um] LT [Uhr]',
            lastDay: '[gestern um] LT [Uhr]',
            lastWeek: '[letzten] dddd [um] LT [Uhr]'
        },
        relativeTime : {
            future : 'in %s',
            past : 'vor %s',
            s : 'ein paar Sekunden',
            ss : '%d Sekunden',
            m : processRelativeTime,
            mm : '%d Minuten',
            h : processRelativeTime,
            hh : '%d Stunden',
            d : processRelativeTime,
            dd : processRelativeTime,
            M : processRelativeTime,
            MM : processRelativeTime,
            y : processRelativeTime,
            yy : processRelativeTime
        },
        dayOfMonthOrdinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return deAt;

})));

/*! Select2 4.0.11 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/de",[],function(){return{errorLoading:function(){return"Die Ergebnisse konnten nicht geladen werden."},inputTooLong:function(e){return"Bitte "+(e.input.length-e.maximum)+" Zeichen weniger eingeben"},inputTooShort:function(e){return"Bitte "+(e.minimum-e.input.length)+" Zeichen mehr eingeben"},loadingMore:function(){return"Lade mehr Ergebnisse…"},maximumSelected:function(e){var n="Sie können nur "+e.maximum+" Element";return 1!=e.maximum&&(n+="e"),n+=" auswählen"},noResults:function(){return"Keine Übereinstimmungen gefunden"},searching:function(){return"Suche…"},removeAllItems:function(){return"Entferne alle Elemente"}}}),e.define,e.require}();
/*!
 * Bootstrap-select v1.13.12 (https://developer.snapappointments.com/bootstrap-select)
 *
 * Copyright 2012-2019 SnapAppointments, LLC
 * Licensed under MIT (https://github.com/snapappointments/bootstrap-select/blob/master/LICENSE)
 */

(function (root, factory) {
  if (root === undefined && window !== undefined) root = window;
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module unless amdModuleId is set
    define(["jquery"], function (a0) {
      return (factory(a0));
    });
  } else if (typeof module === 'object' && module.exports) {
    // Node. Does not work with strict CommonJS, but
    // only CommonJS-like environments that support module.exports,
    // like Node.
    module.exports = factory(require("jquery"));
  } else {
    factory(root["jQuery"]);
  }
}(this, function (jQuery) {

(function ($) {
  $.fn.selectpicker.defaults = {
    noneSelectedText: 'Bitte wählen...',
    noneResultsText: 'Keine Ergebnisse für {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? '{0} Element ausgewählt' : '{0} Elemente ausgewählt';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Limit erreicht ({n} Element max.)' : 'Limit erreicht ({n} Elemente max.)',
        (numGroup == 1) ? 'Gruppen-Limit erreicht ({n} Element max.)' : 'Gruppen-Limit erreicht ({n} Elemente max.)'
      ];
    },
    selectAllText: 'Alles auswählen',
    deselectAllText: 'Nichts auswählen',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-de_DE.js.map
/*!
 * Ajax Bootstrap Select
 *
 * Extends existing [Bootstrap Select] implementations by adding the ability to search via AJAX requests as you type. Originally for CROSCON.
 *
 * @version 1.4.5
 * @author Adam Heim - https://github.com/truckingsim
 * @link https://github.com/truckingsim/Ajax-Bootstrap-Select
 * @copyright 2019 Adam Heim
 * @license Released under the MIT license.
 *
 * Contributors:
 *   Mark Carver - https://github.com/markcarver
 *
 * Last build: 2019-04-23 12:18:55 PM EDT
 */
!(function ($) {
/*!
 * English translation for the "en-US" and "en" language codes.
 * Tobias Weichart <tobias.weichart@gmail.com>
 */
$.fn.ajaxSelectPicker.locale['de-DE'] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * The text to use for the label of the option group when currently selected options are preserved.
     */
    currentlySelected: 'Momentan ausgewählt',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * The text to use as the title for the select element when there are no items to display.
     */
    emptyTitle: 'Hier klicken und eingeben',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = ''Unable to retrieve results'
     * @markdown
     * The text to use in the status container when a request returns with an error.
     */
    errorText: 'Ergebnisse konnten nicht abgerufen wurden',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * The text to use for the search input placeholder attribute.
     */
    searchPlaceholder: 'Suche...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * The text used in the status container when it is initialized.
     */
    statusInitialized: 'Suchbegriff eingeben',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusNoResults: 'Keine Ergebnisse',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * The text to use in the status container when a request is being initiated.
     */
    statusSearching: 'Suche...',

     /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'Der Suchbegriff ist nicht lang genug'
};
$.fn.ajaxSelectPicker.locale.de = $.fn.ajaxSelectPicker.locale['de-DE'];
})(jQuery);
