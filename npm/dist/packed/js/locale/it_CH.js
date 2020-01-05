/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Italian (it)
 * Author: davegraziosi
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

QueryBuilder.regional['it'] = {
  "__locale": "Italian (it)",
  "__author": "davegraziosi",
  "add_rule": "Aggiungi regola",
  "add_group": "Aggiungi gruppo",
  "delete_rule": "Elimina",
  "delete_group": "Elimina",
  "conditions": {
    "AND": "E",
    "OR": "O"
  },
  "operators": {
    "equal": "uguale",
    "not_equal": "non uguale",
    "in": "in",
    "not_in": "non in",
    "less": "minore",
    "less_or_equal": "minore o uguale",
    "greater": "maggiore",
    "greater_or_equal": "maggiore o uguale",
    "between": "compreso tra",
    "not_between": "non compreso tra",
    "begins_with": "inizia con",
    "not_begins_with": "non inizia con",
    "contains": "contiene",
    "not_contains": "non contiene",
    "ends_with": "finisce con",
    "not_ends_with": "non finisce con",
    "is_empty": "è vuoto",
    "is_not_empty": "non è vuoto",
    "is_null": "è nullo",
    "is_not_null": "non è nullo"
  },
  "errors": {
    "no_filter": "Nessun filtro selezionato",
    "empty_group": "Il gruppo è vuoto",
    "radio_empty": "No value selected",
    "checkbox_empty": "Nessun valore selezionato",
    "select_empty": "Nessun valore selezionato",
    "string_empty": "Valore vuoto",
    "string_exceed_min_length": "Deve contenere almeno {0} caratteri",
    "string_exceed_max_length": "Non deve contenere più di {0} caratteri",
    "string_invalid_format": "Formato non valido ({0})",
    "number_nan": "Non è un numero",
    "number_not_integer": "Non è un intero",
    "number_not_double": "Non è un numero con la virgola",
    "number_exceed_min": "Deve essere maggiore di {0}",
    "number_exceed_max": "Deve essere minore di {0}",
    "number_wrong_step": "Deve essere multiplo di {0}",
    "datetime_empty": "Valore vuoto",
    "datetime_invalid": "Formato data non valido ({0})",
    "datetime_exceed_min": "Deve essere successivo a {0}",
    "datetime_exceed_max": "Deve essere precedente a {0}",
    "boolean_not_valid": "Non è un booleano",
    "operator_not_multiple": "L'Operatore {0} non può accettare valori multipli"
  }
};

QueryBuilder.defaults({ lang_code: 'it' });
}));
/*!
 * FileInput Italian Translation
 * 
 * Author: Lorenzo Milesi <maxxer@yetopen.it>
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

    $.fn.fileinputLocales['it'] = {
        fileSingle: 'file',
        filePlural: 'file',
        browseLabel: 'Sfoglia&hellip;',
        removeLabel: 'Rimuovi',
        removeTitle: 'Rimuovi i file selezionati',
        cancelLabel: 'Annulla',
        cancelTitle: 'Annulla i caricamenti in corso',
        uploadLabel: 'Carica',
        uploadTitle: 'Carica i file selezionati',
        msgNo: 'No',
        msgNoFilesSelected: 'Nessun file selezionato',
        msgCancelled: 'Annullato',
        msgPlaceholder: 'Seleziona {files}...',
        msgZoomModalHeading: 'Anteprima dettagliata',
        msgFileRequired: 'Devi selezionare un file da caricare.',
        msgSizeTooSmall: 'Il file "{name}" (<b>{size} KB</b>) è troppo piccolo, deve essere almeno di <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Il file "{name}" (<b>{size} KB</b>) eccede la dimensione massima di caricamento di <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Devi selezionare almeno <b>{n}</b> {files} da caricare.',
        msgFilesTooMany: 'Il numero di file selezionati per il caricamento <b>({n})</b> eccede il numero massimo di file accettati <b>{m}</b>.',
        msgFileNotFound: 'File "{name}" non trovato!',
        msgFileSecured: 'Restrizioni di sicurezza impediscono la lettura del file "{name}".',
        msgFileNotReadable: 'Il file "{name}" non è leggibile.',
        msgFilePreviewAborted: 'Generazione anteprima per "{name}" annullata.',
        msgFilePreviewError: 'Errore durante la lettura del file "{name}".',
        msgInvalidFileName: 'Carattere non valido o non supportato nel file "{name}".',
        msgInvalidFileType: 'Tipo non valido per il file "{name}". Sono ammessi solo file di tipo "{types}".',
        msgInvalidFileExtension: 'Estensione non valida per il file "{name}". Sono ammessi solo file con estensione "{extensions}".',
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
        msgUploadAborted: 'Il caricamento del file è stato interrotto',
        msgUploadThreshold: 'In lavorazione...',
        msgUploadBegin: 'Inizializzazione...',
        msgUploadEnd: 'Fatto',
        msgUploadEmpty: 'Dati non disponibili',
        msgUploadError: 'Errore',
        msgValidationError: 'Errore di convalida',
        msgLoading: 'Caricamento file {index} di {files}&hellip;',
        msgProgress: 'Caricamento file {index} di {files} - {name} - {percent}% completato.',
        msgSelected: '{n} {files} selezionati',
        msgFoldersNotAllowed: 'Trascina solo file! Ignorata/e {n} cartella/e.',
        msgImageWidthSmall: 'La larghezza dell\'immagine "{name}" deve essere di almeno {size} px.',
        msgImageHeightSmall: 'L\'altezza dell\'immagine "{name}" deve essere di almeno {size} px.',
        msgImageWidthLarge: 'La larghezza dell\'immagine "{name}" non può superare {size} px.',
        msgImageHeightLarge: 'L\'altezza dell\'immagine "{name}" non può superare {size} px.',
        msgImageResizeError: 'Impossibile ottenere le dimensioni dell\'immagine per ridimensionare.',
        msgImageResizeException: 'Errore durante il ridimensionamento dell\'immagine.<pre>{errors}</pre>',
        msgAjaxError: 'Qualcosa non ha funzionato con l\'operazione {operation}. Per favore riprova più tardi!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'eliminazione file',
            uploadThumb: 'caricamento file',
            uploadBatch: 'caricamento file in batch',
            uploadExtra: 'upload dati del form'
        },
        dropZoneTitle: 'Trascina i file qui&hellip;',
        dropZoneClickTitle: '<br>(o clicca per selezionare {files})',
        fileActionSettings: {
            removeTitle: 'Rimuovere il file',
            uploadTitle: 'Caricare un file',
            uploadRetryTitle: 'Riprova il caricamento',
            downloadTitle: 'Scarica file',
            zoomTitle: 'Guarda i dettagli',
            dragTitle: 'Muovi / Riordina',
            indicatorNewTitle: 'Non ancora caricato',
            indicatorSuccessTitle: 'Caricati',
            indicatorErrorTitle: 'Carica Errore',
            indicatorLoadingTitle: 'Caricamento ...'
        },
        previewZoomButtonTitles: {
            prev: 'Vedi il file precedente',
            next: 'Vedi il file seguente',
            toggleheader: 'Attiva header',
            fullscreen: 'Attiva full screen',
            borderless: 'Abilita modalità senza bordi',
            close: 'Chiudi'
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


    var itCh = moment.defineLocale('it-ch', {
        months : 'gennaio_febbraio_marzo_aprile_maggio_giugno_luglio_agosto_settembre_ottobre_novembre_dicembre'.split('_'),
        monthsShort : 'gen_feb_mar_apr_mag_giu_lug_ago_set_ott_nov_dic'.split('_'),
        weekdays : 'domenica_lunedì_martedì_mercoledì_giovedì_venerdì_sabato'.split('_'),
        weekdaysShort : 'dom_lun_mar_mer_gio_ven_sab'.split('_'),
        weekdaysMin : 'do_lu_ma_me_gi_ve_sa'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay: '[Oggi alle] LT',
            nextDay: '[Domani alle] LT',
            nextWeek: 'dddd [alle] LT',
            lastDay: '[Ieri alle] LT',
            lastWeek: function () {
                switch (this.day()) {
                    case 0:
                        return '[la scorsa] dddd [alle] LT';
                    default:
                        return '[lo scorso] dddd [alle] LT';
                }
            },
            sameElse: 'L'
        },
        relativeTime : {
            future : function (s) {
                return ((/^[0-9].+$/).test(s) ? 'tra' : 'in') + ' ' + s;
            },
            past : '%s fa',
            s : 'alcuni secondi',
            ss : '%d secondi',
            m : 'un minuto',
            mm : '%d minuti',
            h : 'un\'ora',
            hh : '%d ore',
            d : 'un giorno',
            dd : '%d giorni',
            M : 'un mese',
            MM : '%d mesi',
            y : 'un anno',
            yy : '%d anni'
        },
        dayOfMonthOrdinalParse : /\d{1,2}º/,
        ordinal: '%dº',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return itCh;

})));

/*! Select2 4.0.12 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/it",[],function(){return{errorLoading:function(){return"I risultati non possono essere caricati."},inputTooLong:function(e){var n=e.input.length-e.maximum,t="Per favore cancella "+n+" caratter";return t+=1!==n?"i":"e"},inputTooShort:function(e){return"Per favore inserisci "+(e.minimum-e.input.length)+" o più caratteri"},loadingMore:function(){return"Caricando più risultati…"},maximumSelected:function(e){var n="Puoi selezionare solo "+e.maximum+" element";return 1!==e.maximum?n+="i":n+="o",n},noResults:function(){return"Nessun risultato trovato"},searching:function(){return"Sto cercando…"},removeAllItems:function(){return"Rimuovi tutti gli oggetti"}}}),e.define,e.require}();
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
    noneSelectedText: 'Nessuna selezione',
    noneResultsText: 'Nessun risultato per {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? 'Selezionato {0} di {1}' : 'Selezionati {0} di {1}';
    },
    maxOptionsText: ['Limite raggiunto ({n} {var} max)', 'Limite del gruppo raggiunto ({n} {var} max)', ['elementi', 'elemento']],
    multipleSeparator: ', ',
    selectAllText: 'Seleziona Tutto',
    deselectAllText: 'Deseleziona Tutto'
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-it_IT.js.map
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
 * Italian translation for the "it-IT" and "it" language codes.
 * Luca Longo <l.longo@ambita.it>
 */
$.fn.ajaxSelectPicker.locale['it-IT'] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * The text to use for the label of the option group when currently selected options are preserved.
     */
    currentlySelected: 'Selezionati',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * The text to use as the title for the select element when there are no items to display.
     */
    emptyTitle: 'Clicca qui e scrivi...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = ''Unable to retrieve results'
     * @markdown
     * The text to use in the status container when a request returns with an error.
     */
    errorText: 'Impossibile recuperare dei risultati',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * The text to use for the search input placeholder attribute.
     */
    searchPlaceholder: 'Cerca...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * The text used in the status container when it is initialized.
     */
    statusInitialized: 'Inizia a digitare...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusNoResults: 'Non ci sono risultati',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * The text to use in the status container when a request is being initiated.
     */
    statusSearching: 'Ricerca in corso...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'Inserisci altri caratteri'
};
$.fn.ajaxSelectPicker.locale.it = $.fn.ajaxSelectPicker.locale['it-IT'];
})(jQuery);
