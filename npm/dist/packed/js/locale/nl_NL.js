/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Dutch (nl)
 * Author: "Roywcm"
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

QueryBuilder.regional['nl'] = {
  "__locale": "Dutch (nl)",
  "__author": "\"Roywcm\"",
  "add_rule": "Nieuwe regel",
  "add_group": "Nieuwe groep",
  "delete_rule": "Verwijder",
  "delete_group": "Verwijder",
  "conditions": {
    "AND": "EN",
    "OR": "OF"
  },
  "operators": {
    "equal": "gelijk",
    "not_equal": "niet gelijk",
    "in": "in",
    "not_in": "niet in",
    "less": "minder",
    "less_or_equal": "minder of gelijk",
    "greater": "groter",
    "greater_or_equal": "groter of gelijk",
    "between": "tussen",
    "not_between": "niet tussen",
    "begins_with": "begint met",
    "not_begins_with": "begint niet met",
    "contains": "bevat",
    "not_contains": "bevat niet",
    "ends_with": "eindigt met",
    "not_ends_with": "eindigt niet met",
    "is_empty": "is leeg",
    "is_not_empty": "is niet leeg",
    "is_null": "is null",
    "is_not_null": "is niet null"
  },
  "errors": {
    "no_filter": "Geen filter geselecteerd",
    "empty_group": "De groep is leeg",
    "radio_empty": "Geen waarde geselecteerd",
    "checkbox_empty": "Geen waarde geselecteerd",
    "select_empty": "Geen waarde geselecteerd",
    "string_empty": "Lege waarde",
    "string_exceed_min_length": "Dient minstens {0} karakters te bevatten",
    "string_exceed_max_length": "Dient niet meer dan {0} karakters te bevatten",
    "string_invalid_format": "Ongeldig format ({0})",
    "number_nan": "Niet een nummer",
    "number_not_integer": "Geen geheel getal",
    "number_not_double": "Geen echt nummer",
    "number_exceed_min": "Dient groter te zijn dan {0}",
    "number_exceed_max": "Dient lager te zijn dan {0}",
    "number_wrong_step": "Dient een veelvoud te zijn van {0}",
    "datetime_invalid": "Ongeldige datumformat ({0})",
    "datetime_exceed_min": "Dient na {0}",
    "datetime_exceed_max": "Dient voor {0}"
  }
};

QueryBuilder.defaults({ lang_code: 'nl' });
}));
/*!
 * FileInput Dutch Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['nl'] = {
        fileSingle: 'bestand',
        filePlural: 'bestanden',
        browseLabel: 'Zoek &hellip;',
        removeLabel: 'Verwijder',
        removeTitle: 'Verwijder geselecteerde bestanden',
        cancelLabel: 'Annuleren',
        cancelTitle: 'Annuleer upload',
        uploadLabel: 'Upload',
        uploadTitle: 'Upload geselecteerde bestanden',
        msgNo: 'Nee',
        msgNoFilesSelected: '',
        msgCancelled: 'Geannuleerd',
        msgPlaceholder: 'Selecteer {files}...',
        msgZoomModalHeading: 'Gedetailleerd voorbeeld',
        msgFileRequired: 'U moet een bestand kiezen om te uploaden.',
        msgSizeTooSmall: 'Bestand "{name}" (<b>{size} KB</b>) is te klein en moet groter zijn dan <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Bestand "{name}" (<b>{size} KB</b>) is groter dan de toegestane <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'U moet minstens <b>{n}</b> {files} selecteren om te uploaden.',
        msgFilesTooMany: 'Aantal geselecteerde bestanden <b>({n})</b> is meer dan de toegestane <b>{m}</b>.',
        msgFileNotFound: 'Bestand "{name}" niet gevonden!',
        msgFileSecured: 'Bestand kan niet gelezen worden in verband met beveiligings redenen "{name}".',
        msgFileNotReadable: 'Bestand "{name}" is niet leesbaar.',
        msgFilePreviewAborted: 'Bestand weergaven geannuleerd voor "{name}".',
        msgFilePreviewError: 'Er is een fout opgetreden met het lezen van "{name}".',
        msgInvalidFileName: 'Ongeldige of niet ondersteunde karakters in bestandsnaam "{name}".',
        msgInvalidFileType: 'Geen geldig bestand "{name}". Alleen "{types}" zijn toegestaan.',
        msgInvalidFileExtension: 'Geen geldige extensie "{name}". Alleen "{extensions}" zijn toegestaan.',
        msgFileTypes: {
            'image': 'afbeelding',
            'html': 'HTML',
            'text': 'tekst',
            'video': 'video',
            'audio': 'geluid',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'Het uploaden van bestanden is afgebroken',
        msgUploadThreshold: 'Verwerken...',
        msgUploadBegin: 'Initialiseren...',
        msgUploadEnd: 'Gedaan',
        msgUploadEmpty: 'Geen geldige data beschikbaar voor upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Bevestiging fout',
        msgLoading: 'Bestanden laden {index} van de {files} &hellip;',
        msgProgress: 'Bestanden laden {index} van de {files} - {name} - {percent}% compleet.',
        msgSelected: '{n} {files} geselecteerd',
        msgFoldersNotAllowed: 'Drag & drop alleen bestanden! {n} overgeslagen map(pen).',
        msgImageWidthSmall: 'Breedte van het foto-bestand "{name}" moet minstens {size} px zijn.',
        msgImageHeightSmall: 'Hoogte van het foto-bestand "{name}" moet minstens {size} px zijn.',
        msgImageWidthLarge: 'Breedte van het foto-bestand "{name}" kan niet hoger zijn dan {size} px.',
        msgImageHeightLarge: 'Hoogte van het foto bestand "{name}" kan niet hoger zijn dan {size} px.',
        msgImageResizeError: 'Kon de foto afmetingen niet lezen om te verkleinen.',
        msgImageResizeException: 'Fout bij het verkleinen van de foto.<pre>{errors}</pre>',
        msgAjaxError: 'Er ging iets mis met de {operation} actie. Gelieve later opnieuw te proberen!',
        msgAjaxProgressError: '{operation} mislukt',
        ajaxOperations: {
            deleteThumb: 'bestand verwijderen',
            uploadThumb: 'bestand uploaden',
            uploadBatch: 'alle bestanden uploaden',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Drag & drop bestanden hier &hellip;',
        dropZoneClickTitle: '<br>(of klik hier om {files} te selecteren)',
        fileActionSettings: {
            removeTitle: 'Verwijder bestand',
            uploadTitle: 'bestand uploaden',
            uploadRetryTitle: 'Opnieuw uploaden',
            downloadTitle: 'Download file',
            zoomTitle: 'Bekijk details',
            dragTitle: 'Verplaatsen / herindelen',
            indicatorNewTitle: 'Nog niet geupload',
            indicatorSuccessTitle: 'geupload',
            indicatorErrorTitle: 'fout uploaden',
            indicatorLoadingTitle: 'uploaden ...'
        },
        previewZoomButtonTitles: {
            prev: 'Toon vorig bestand',
            next: 'Toon volgend bestand',
            toggleheader: 'Toggle header',
            fullscreen: 'Toggle volledig scherm',
            borderless: 'Toggle randloze modus',
            close: 'Sluit gedetailleerde weergave'
        }
    };
})(window.jQuery);

//! moment.js locale configuration
//! locale : Dutch [nl]
//! author : Joris Röling : https://github.com/jorisroling
//! author : Jacob Middag : https://github.com/middagj

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';

    //! moment.js locale configuration

    var monthsShortWithDots = 'jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.'.split(
            '_'
        ),
        monthsShortWithoutDots = 'jan_feb_mrt_apr_mei_jun_jul_aug_sep_okt_nov_dec'.split(
            '_'
        ),
        monthsParse = [
            /^jan/i,
            /^feb/i,
            /^maart|mrt.?$/i,
            /^apr/i,
            /^mei$/i,
            /^jun[i.]?$/i,
            /^jul[i.]?$/i,
            /^aug/i,
            /^sep/i,
            /^okt/i,
            /^nov/i,
            /^dec/i,
        ],
        monthsRegex = /^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december|jan\.?|feb\.?|mrt\.?|apr\.?|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i;

    var nl = moment.defineLocale('nl', {
        months: 'januari_februari_maart_april_mei_juni_juli_augustus_september_oktober_november_december'.split(
            '_'
        ),
        monthsShort: function (m, format) {
            if (!m) {
                return monthsShortWithDots;
            } else if (/-MMM-/.test(format)) {
                return monthsShortWithoutDots[m.month()];
            } else {
                return monthsShortWithDots[m.month()];
            }
        },

        monthsRegex: monthsRegex,
        monthsShortRegex: monthsRegex,
        monthsStrictRegex: /^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december)/i,
        monthsShortStrictRegex: /^(jan\.?|feb\.?|mrt\.?|apr\.?|mei|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i,

        monthsParse: monthsParse,
        longMonthsParse: monthsParse,
        shortMonthsParse: monthsParse,

        weekdays: 'zondag_maandag_dinsdag_woensdag_donderdag_vrijdag_zaterdag'.split(
            '_'
        ),
        weekdaysShort: 'zo._ma._di._wo._do._vr._za.'.split('_'),
        weekdaysMin: 'zo_ma_di_wo_do_vr_za'.split('_'),
        weekdaysParseExact: true,
        longDateFormat: {
            LT: 'HH:mm',
            LTS: 'HH:mm:ss',
            L: 'DD-MM-YYYY',
            LL: 'D MMMM YYYY',
            LLL: 'D MMMM YYYY HH:mm',
            LLLL: 'dddd D MMMM YYYY HH:mm',
        },
        calendar: {
            sameDay: '[vandaag om] LT',
            nextDay: '[morgen om] LT',
            nextWeek: 'dddd [om] LT',
            lastDay: '[gisteren om] LT',
            lastWeek: '[afgelopen] dddd [om] LT',
            sameElse: 'L',
        },
        relativeTime: {
            future: 'over %s',
            past: '%s geleden',
            s: 'een paar seconden',
            ss: '%d seconden',
            m: 'één minuut',
            mm: '%d minuten',
            h: 'één uur',
            hh: '%d uur',
            d: 'één dag',
            dd: '%d dagen',
            M: 'één maand',
            MM: '%d maanden',
            y: 'één jaar',
            yy: '%d jaar',
        },
        dayOfMonthOrdinalParse: /\d{1,2}(ste|de)/,
        ordinal: function (number) {
            return (
                number +
                (number === 1 || number === 8 || number >= 20 ? 'ste' : 'de')
            );
        },
        week: {
            dow: 1, // Monday is the first day of the week.
            doy: 4, // The week that contains Jan 4th is the first week of the year.
        },
    });

    return nl;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/nl",[],function(){return{errorLoading:function(){return"De resultaten konden niet worden geladen."},inputTooLong:function(e){return"Gelieve "+(e.input.length-e.maximum)+" karakters te verwijderen"},inputTooShort:function(e){return"Gelieve "+(e.minimum-e.input.length)+" of meer karakters in te voeren"},loadingMore:function(){return"Meer resultaten laden…"},maximumSelected:function(e){var n=1==e.maximum?"kan":"kunnen",r="Er "+n+" maar "+e.maximum+" item";return 1!=e.maximum&&(r+="s"),r+=" worden geselecteerd"},noResults:function(){return"Geen resultaten gevonden…"},searching:function(){return"Zoeken…"},removeAllItems:function(){return"Verwijder alle items"}}}),e.define,e.require}();
/*!
 * Bootstrap-select v1.13.17 (https://developer.snapappointments.com/bootstrap-select)
 *
 * Copyright 2012-2020 SnapAppointments, LLC
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
    noneSelectedText: 'Niets geselecteerd',
    noneResultsText: 'Geen resultaten gevonden voor {0}',
    countSelectedText: '{0} van {1} geselecteerd',
    maxOptionsText: ['Limiet bereikt ({n} {var} max)', 'Groep limiet bereikt ({n} {var} max)', ['items', 'item']],
    selectAllText: 'Alles selecteren',
    deselectAllText: 'Alles deselecteren',
    multipleSeparator: ', '
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-nl_NL.js.map
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
 * Dutch translation for the "nl-NL" and "nl" language codes.
 * Arjen Ruiterkamp <arjenruiterkamp@gmail.com>
 */
$.fn.ajaxSelectPicker.locale['nl-NL'] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * The text to use for the label of the option group when currently selected options are preserved.
     */
    currentlySelected: 'Momenteel geselecteerd',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * The text to use as the title for the select element when there are no items to display.
     */
    emptyTitle: 'Selecteer en begin met typen',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = ''Unable to retrieve results'
     * @markdown
     * The text to use in the status container when a request returns with an error.
     */
    errorText: 'Kon geen resultaten ophalen',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * The text to use for the search input placeholder attribute.
     */
    searchPlaceholder: 'Zoeken...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * The text used in the status container when it is initialized.
     */
    statusInitialized: 'Begin met typen om te zoeken',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusNoResults: 'Geen resultaten',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * The text to use in the status container when a request is being initiated.
     */
    statusSearching: 'Zoeken...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'U dient meer karakters in te voeren'
};
$.fn.ajaxSelectPicker.locale.nl = $.fn.ajaxSelectPicker.locale['nl-NL'];
})(jQuery);
