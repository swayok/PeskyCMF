/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: French (fr)
 * Author: Damien "Mistic" Sorel, http://www.strangeplanet.fr
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

QueryBuilder.regional['fr'] = {
  "__locale": "French (fr)",
  "__author": "Damien \"Mistic\" Sorel, http://www.strangeplanet.fr",
  "add_rule": "Ajouter une règle",
  "add_group": "Ajouter un groupe",
  "delete_rule": "Supprimer",
  "delete_group": "Supprimer",
  "conditions": {
    "AND": "ET",
    "OR": "OU"
  },
  "operators": {
    "equal": "est égal à",
    "not_equal": "n'est pas égal à",
    "in": "est compris dans",
    "not_in": "n'est pas compris dans",
    "less": "est inférieur à",
    "less_or_equal": "est inférieur ou égal à",
    "greater": "est supérieur à",
    "greater_or_equal": "est supérieur ou égal à",
    "between": "est entre",
    "not_between": "n'est pas entre",
    "begins_with": "commence par",
    "not_begins_with": "ne commence pas par",
    "contains": "contient",
    "not_contains": "ne contient pas",
    "ends_with": "finit par",
    "not_ends_with": "ne finit pas par",
    "is_empty": "est vide",
    "is_not_empty": "n'est pas vide",
    "is_null": "est nul",
    "is_not_null": "n'est pas nul"
  },
  "errors": {
    "no_filter": "Aucun filtre sélectionné",
    "empty_group": "Le groupe est vide",
    "radio_empty": "Pas de valeur selectionnée",
    "checkbox_empty": "Pas de valeur selectionnée",
    "select_empty": "Pas de valeur selectionnée",
    "string_empty": "Valeur vide",
    "string_exceed_min_length": "Doit contenir au moins {0} caractères",
    "string_exceed_max_length": "Ne doit pas contenir plus de {0} caractères",
    "string_invalid_format": "Format invalide ({0})",
    "number_nan": "N'est pas un nombre",
    "number_not_integer": "N'est pas un entier",
    "number_not_double": "N'est pas un nombre réel",
    "number_exceed_min": "Doit être plus grand que {0}",
    "number_exceed_max": "Doit être plus petit que {0}",
    "number_wrong_step": "Doit être un multiple de {0}",
    "number_between_invalid": "Valeurs invalides, {0} est plus grand que {1}",
    "datetime_empty": "Valeur vide",
    "datetime_invalid": "Fomat de date invalide ({0})",
    "datetime_exceed_min": "Doit être après {0}",
    "datetime_exceed_max": "Doit être avant {0}",
    "datetime_between_invalid": "Valeurs invalides, {0} est plus grand que {1}",
    "boolean_not_valid": "N'est pas un booléen",
    "operator_not_multiple": "L'opérateur \"{1}\" ne peut utiliser plusieurs valeurs"
  },
  "invert": "Inverser",
  "NOT": "NON"
};

QueryBuilder.defaults({ lang_code: 'fr' });
}));
/*!
 * FileInput French Translations
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

    $.fn.fileinputLocales['fr'] = {
        fileSingle: 'fichier',
        filePlural: 'fichiers',
        browseLabel: 'Parcourir&hellip;',
        removeLabel: 'Retirer',
        removeTitle: 'Retirer les fichiers sélectionnés',
        cancelLabel: 'Annuler',
        cancelTitle: "Annuler l'envoi en cours",
        uploadLabel: 'Transférer',
        uploadTitle: 'Transférer les fichiers sélectionnés',
        msgNo: 'Non',
        msgNoFilesSelected: '',
        msgCancelled: 'Annulé',
        msgPlaceholder: 'Sélectionner le(s) {files}...',
        msgZoomModalHeading: 'Aperçu détaillé',
        msgFileRequired: 'Vous devez sélectionner un fichier à uploader.',
        msgSizeTooSmall: 'Le fichier "{name}" (<b>{size} KB</b>) est inférieur à la taille minimale de <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'Le fichier "{name}" (<b>{size} Ko</b>) dépasse la taille maximale autorisée qui est de <b>{maxSize} Ko</b>.',
        msgFilesTooLess: 'Vous devez sélectionner au moins <b>{n}</b> {files} à transmettre.',
        msgFilesTooMany: 'Le nombre de fichier sélectionné <b>({n})</b> dépasse la quantité maximale autorisée qui est de <b>{m}</b>.',
        msgFileNotFound: 'Le fichier "{name}" est introuvable !',
        msgFileSecured: "Des restrictions de sécurité vous empêchent d'accéder au fichier \"{name}\".",
        msgFileNotReadable: 'Le fichier "{name}" est illisible.',
        msgFilePreviewAborted: 'Prévisualisation du fichier "{name}" annulée.',
        msgFilePreviewError: 'Une erreur est survenue lors de la lecture du fichier "{name}".',
        msgInvalidFileName: 'Caractères invalides ou non supportés dans le nom de fichier "{name}".',
        msgInvalidFileType: 'Type de document invalide pour "{name}". Seulement les documents de type "{types}" sont autorisés.',
        msgInvalidFileExtension: 'Extension invalide pour le fichier "{name}". Seules les extensions "{extensions}" sont autorisées.',
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
        msgUploadAborted: 'Le transfert du fichier a été interrompu',
        msgUploadThreshold: 'En cours...',
        msgUploadBegin: 'Initialisation...',
        msgUploadEnd: 'Terminé',
        msgUploadEmpty: 'Aucune donnée valide disponible pour transmission.',
        msgUploadError: 'Erreur',
        msgValidationError: 'Erreur de validation',
        msgLoading: 'Transmission du fichier {index} sur {files}&hellip;',
        msgProgress: 'Transmission du fichier {index} sur {files} - {name} - {percent}%.',
        msgSelected: '{n} {files} sélectionné(s)',
        msgFoldersNotAllowed: 'Glissez et déposez uniquement des fichiers ! {n} répertoire(s) exclu(s).',
        msgImageWidthSmall: 'La largeur de l\'image "{name}" doit être d\'au moins {size} px.',
        msgImageHeightSmall: 'La hauteur de l\'image "{name}" doit être d\'au moins {size} px.',
        msgImageWidthLarge: 'La largeur de l\'image "{name}" ne peut pas dépasser {size} px.',
        msgImageHeightLarge: 'La hauteur de l\'image "{name}" ne peut pas dépasser {size} px.',
        msgImageResizeError: "Impossible d'obtenir les dimensions de l'image à redimensionner.",
        msgImageResizeException: "Erreur lors du redimensionnement de l'image.<pre>{errors}</pre>",
        msgAjaxError: "Une erreur s'est produite pendant l'opération de {operation}. Veuillez réessayer plus tard.",
        msgAjaxProgressError: 'L\'opération "{operation}" a échoué',
        ajaxOperations: {
            deleteThumb: 'suppression du fichier',
            uploadThumb: 'transfert du fichier',
            uploadBatch: 'transfert des fichiers',
            uploadExtra: 'soumission des données de formulaire'
        },
        dropZoneTitle: 'Glissez et déposez les fichiers ici&hellip;',
        dropZoneClickTitle: '<br>(ou cliquez pour sélectionner manuellement)',
        fileActionSettings: {
            removeTitle: 'Supprimer le fichier',
            uploadTitle: 'Transférer le fichier',
            uploadRetryTitle: 'Relancer le transfert',
            zoomTitle: 'Voir les détails',
            dragTitle: 'Déplacer / Réarranger',
            indicatorNewTitle: 'Pas encore transféré',
            indicatorSuccessTitle: 'Posté',
            indicatorErrorTitle: 'Ajouter erreur',
            indicatorLoadingTitle: 'En cours...'
        },
        previewZoomButtonTitles: {
            prev: 'Voir le fichier précédent',
            next: 'Voir le fichier suivant',
            toggleheader: 'Masquer le titre',
            fullscreen: 'Mode plein écran',
            borderless: 'Mode cinéma',
            close: "Fermer l'aperçu"
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


    var frCh = moment.defineLocale('fr-ch', {
        months : 'janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'.split('_'),
        monthsShort : 'janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'.split('_'),
        monthsParseExact : true,
        weekdays : 'dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'.split('_'),
        weekdaysShort : 'dim._lun._mar._mer._jeu._ven._sam.'.split('_'),
        weekdaysMin : 'di_lu_ma_me_je_ve_sa'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD.MM.YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay : '[Aujourd’hui à] LT',
            nextDay : '[Demain à] LT',
            nextWeek : 'dddd [à] LT',
            lastDay : '[Hier à] LT',
            lastWeek : 'dddd [dernier à] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'dans %s',
            past : 'il y a %s',
            s : 'quelques secondes',
            ss : '%d secondes',
            m : 'une minute',
            mm : '%d minutes',
            h : 'une heure',
            hh : '%d heures',
            d : 'un jour',
            dd : '%d jours',
            M : 'un mois',
            MM : '%d mois',
            y : 'un an',
            yy : '%d ans'
        },
        dayOfMonthOrdinalParse: /\d{1,2}(er|e)/,
        ordinal : function (number, period) {
            switch (period) {
                // Words with masculine grammatical gender: mois, trimestre, jour
                default:
                case 'M':
                case 'Q':
                case 'D':
                case 'DDD':
                case 'd':
                    return number + (number === 1 ? 'er' : 'e');

                // Words with feminine grammatical gender: semaine
                case 'w':
                case 'W':
                    return number + (number === 1 ? 're' : 'e');
            }
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return frCh;

})));

/*! Select2 4.0.13 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/fr",[],function(){return{errorLoading:function(){return"Les résultats ne peuvent pas être chargés."},inputTooLong:function(e){var n=e.input.length-e.maximum;return"Supprimez "+n+" caractère"+(n>1?"s":"")},inputTooShort:function(e){var n=e.minimum-e.input.length;return"Saisissez au moins "+n+" caractère"+(n>1?"s":"")},loadingMore:function(){return"Chargement de résultats supplémentaires…"},maximumSelected:function(e){return"Vous pouvez seulement sélectionner "+e.maximum+" élément"+(e.maximum>1?"s":"")},noResults:function(){return"Aucun résultat trouvé"},searching:function(){return"Recherche en cours…"},removeAllItems:function(){return"Supprimer tous les éléments"}}}),e.define,e.require}();
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
    noneSelectedText: 'Aucune sélection',
    noneResultsText: 'Aucun résultat pour {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected > 1) ? '{0} éléments sélectionnés' : '{0} élément sélectionné';
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll > 1) ? 'Limite atteinte ({n} éléments max)' : 'Limite atteinte ({n} élément max)',
        (numGroup > 1) ? 'Limite du groupe atteinte ({n} éléments max)' : 'Limite du groupe atteinte ({n} élément max)'
      ];
    },
    multipleSeparator: ', ',
    selectAllText: 'Tout sélectionner',
    deselectAllText: 'Tout désélectionner'
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-fr_FR.js.map
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
 * French translation for the "fr-FR" and "fr" language codes.
 * Bastien (https://github.com/lisurc)
 */
$.fn.ajaxSelectPicker.locale['fr-FR'] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * The text to use for the label of the option group when currently selected options are preserved.
     */
    currentlySelected: 'Actuellement sélectionné',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * The text to use as the title for the select element when there are no items to display.
     */
    emptyTitle: 'Sélectionner et commencer à taper',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = 'Unable to retrieve results'
     * @markdown
     * The text to use in the status container when a request returns with an error.
     */
    errorText: 'Impossible de récupérer les résultats',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * The text to use for the search input placeholder attribute.
     */
    searchPlaceholder: 'Rechercher...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * The text used in the status container when it is initialized.
     */
    statusInitialized: 'Commencer à taper une recherche',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusNoResults: 'Aucun résultat',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * The text to use in the status container when a request is being initiated.
     */
    statusSearching: 'Recherche en cours...',

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'Entrez plus de caractères'
};
$.fn.ajaxSelectPicker.locale.fr = $.fn.ajaxSelectPicker.locale['fr-FR'];
})(jQuery);
