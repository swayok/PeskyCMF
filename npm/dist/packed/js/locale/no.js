/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Norwegian (no)
 * Author: Jna Borup Coyle, github@coyle.dk
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

QueryBuilder.regional['no'] = {
  "__locale": "Norwegian (no)",
  "__author": "Jna Borup Coyle, github@coyle.dk",
  "add_rule": "Legg til regel",
  "add_group": "Legg til gruppe",
  "delete_rule": "Slett regel",
  "delete_group": "Slett gruppe",
  "conditions": {
    "AND": "OG",
    "OR": "ELLER"
  },
  "operators": {
    "equal": "er lik",
    "not_equal": "er ikke lik",
    "in": "finnes i",
    "not_in": "finnes ikke i",
    "less": "er mindre enn",
    "less_or_equal": "er mindre eller lik",
    "greater": "er større enn",
    "greater_or_equal": "er større eller lik",
    "begins_with": "begynner med",
    "not_begins_with": "begynner ikke med",
    "contains": "inneholder",
    "not_contains": "inneholder ikke",
    "ends_with": "slutter med",
    "not_ends_with": "slutter ikke med",
    "is_empty": "er tom",
    "is_not_empty": "er ikke tom",
    "is_null": "er null",
    "is_not_null": "er ikke null"
  }
};

QueryBuilder.defaults({ lang_code: 'no' });
}));
/*!
 * FileInput Norwegian Translations
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

    $.fn.fileinputLocales['no'] = {
        fileSingle: 'fil',
        filePlural: 'filer',
        browseLabel: 'Bla gjennom &hellip;',
        removeLabel: 'Fjern',
        removeTitle: 'Fjern valgte filer',
        cancelLabel: 'Avbryt',
        cancelTitle: 'Stopp pågående opplastninger',
        uploadLabel: 'Last opp',
        uploadTitle: 'Last opp valgte filer',
        msgNo: 'Nei',
        msgNoFilesSelected: 'Ingen filer er valgt',
        msgCancelled: 'Avbrutt',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Detaljert visning',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'Filen "{name}" (<b>{size} KB</b>) er for liten og må være større enn <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Filen "{name}" (<b>{size} KB</b>) er for stor, maksimal filstørrelse er <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Du må velge minst <b>{n}</b> {files} for opplastning.',
        msgFilesTooMany: 'For mange filer til opplastning, <b>({n})</b> overstiger maksantallet som er <b>{m}</b>.',
        msgFileNotFound: 'Fant ikke filen "{name}"!',
        msgFileSecured: 'Sikkerhetsrestriksjoner hindrer lesing av filen "{name}".',
        msgFileNotReadable: 'Filen "{name}" er ikke lesbar.',
        msgFilePreviewAborted: 'Filvisning avbrutt for "{name}".',
        msgFilePreviewError: 'En feil oppstod under lesing av filen "{name}".',
        msgInvalidFileName: 'Ugyldige tegn i filen "{name}".',
        msgInvalidFileType: 'Ugyldig type for filen "{name}". Kun "{types}" filer er tillatt.',
        msgInvalidFileExtension: 'Ugyldig endelse for filen "{name}". Kun "{extensions}" filer støttes.',
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
        msgUploadAborted: 'Filopplastningen ble avbrutt',
        msgUploadThreshold: 'Prosesserer...',
        msgUploadBegin: 'Initialiserer...',
        msgUploadEnd: 'Ferdig',
        msgUploadEmpty: 'Ingen gyldige data tilgjengelig for opplastning.',
        msgUploadError: 'Error',
        msgValidationError: 'Valideringsfeil',
        msgLoading: 'Laster fil {index} av {files} &hellip;',
        msgProgress: 'Laster fil {index} av {files} - {name} - {percent}% fullført.',
        msgSelected: '{n} {files} valgt',
        msgFoldersNotAllowed: 'Kun Dra & slipp filer! Hoppet over {n} mappe(r).',
        msgImageWidthSmall: 'Bredde på bildefilen "{name}" må være minst {size} px.',
        msgImageHeightSmall: 'Høyde på bildefilen "{name}" må være minst {size} px.',
        msgImageWidthLarge: 'Bredde på bildefilen "{name}" kan ikke overstige {size} px.',
        msgImageHeightLarge: 'Høyde på bildefilen "{name}" kan ikke overstige {size} px.',
        msgImageResizeError: 'Fant ikke dimensjonene som skulle resizes.',
        msgImageResizeException: 'En feil oppstod under endring av størrelse .<pre>{errors}</pre>',
        msgAjaxError: 'Noe gikk galt med {operation} operasjonen. Vennligst prøv igjen senere!',
        msgAjaxProgressError: '{operation} feilet',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Dra & slipp filer her &hellip;',
        dropZoneClickTitle: '<br>(eller klikk for å velge {files})',
        fileActionSettings: {
            removeTitle: 'Fjern fil',
            uploadTitle: 'Last opp fil',
            uploadRetryTitle: 'Retry upload',
            zoomTitle: 'Vis detaljer',
            dragTitle: 'Flytt / endre rekkefølge',
            indicatorNewTitle: 'Opplastning ikke fullført',
            indicatorSuccessTitle: 'Opplastet',
            indicatorErrorTitle: 'Opplastningsfeil',
            indicatorLoadingTitle: 'Laster opp ...'
        },
        previewZoomButtonTitles: {
            prev: 'Vis forrige fil',
            next: 'Vis neste fil',
            toggleheader: 'Vis header',
            fullscreen: 'Åpne fullskjerm',
            borderless: 'Åpne uten kanter',
            close: 'Lukk detaljer'
        }
    };
})(window.jQuery);
