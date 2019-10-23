/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Spanish (es)
 * Author: "pyarza", "kddlb"
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

QueryBuilder.regional['es'] = {
  "__locale": "Spanish (es)",
  "__author": "\"pyarza\", \"kddlb\"",
  "add_rule": "Añadir regla",
  "add_group": "Añadir grupo",
  "delete_rule": "Borrar",
  "delete_group": "Borrar",
  "conditions": {
    "AND": "Y",
    "OR": "O"
  },
  "operators": {
    "equal": "igual",
    "not_equal": "distinto",
    "in": "en",
    "not_in": "no en",
    "less": "menor",
    "less_or_equal": "menor o igual",
    "greater": "mayor",
    "greater_or_equal": "mayor o igual",
    "between": "entre",
    "not_between": "no está entre",
    "begins_with": "empieza por",
    "not_begins_with": "no empieza por",
    "contains": "contiene",
    "not_contains": "no contiene",
    "ends_with": "acaba con",
    "not_ends_with": "no acaba con",
    "is_empty": "está vacío",
    "is_not_empty": "no está vacío",
    "is_null": "es nulo",
    "is_not_null": "no es nulo"
  },
  "errors": {
    "no_filter": "No se ha seleccionado ningún filtro",
    "empty_group": "El grupo está vacío",
    "radio_empty": "Ningún valor seleccionado",
    "checkbox_empty": "Ningún valor seleccionado",
    "select_empty": "Ningún valor seleccionado",
    "string_empty": "Cadena vacía",
    "string_exceed_min_length": "Debe contener al menos {0} caracteres",
    "string_exceed_max_length": "No debe contener más de {0} caracteres",
    "string_invalid_format": "Formato inválido ({0})",
    "number_nan": "No es un número",
    "number_not_integer": "No es un número entero",
    "number_not_double": "No es un número real",
    "number_exceed_min": "Debe ser mayor que {0}",
    "number_exceed_max": "Debe ser menor que {0}",
    "number_wrong_step": "Debe ser múltiplo de {0}",
    "datetime_invalid": "Formato de fecha inválido ({0})",
    "datetime_exceed_min": "Debe ser posterior a {0}",
    "datetime_exceed_max": "Debe ser anterior a {0}",
    "number_between_invalid": "Valores Inválidos, {0} es mayor que {1}",
    "datetime_empty": "Campo vacio",
    "datetime_between_invalid": "Valores Inválidos, {0} es mayor que {1}",
    "boolean_not_valid": "No es booleano",
    "operator_not_multiple": "El operador \"{1}\" no puede aceptar valores multiples"
  }
};

QueryBuilder.defaults({ lang_code: 'es' });
}));
/*!
 * FileInput Spanish Translations
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

    $.fn.fileinputLocales['es'] = {
        fileSingle: 'archivo',
        filePlural: 'archivos',
        browseLabel: 'Examinar &hellip;',
        removeLabel: 'Quitar',
        removeTitle: 'Quitar archivos seleccionados',
        cancelLabel: 'Cancelar',
        cancelTitle: 'Abortar la subida en curso',
        uploadLabel: 'Subir archivo',
        uploadTitle: 'Subir archivos seleccionados',
        msgNo: 'No',
        msgNoFilesSelected: 'No hay archivos seleccionados',
        msgCancelled: 'Cancelado',
        msgPlaceholder: 'Seleccionar {files}...',
        msgZoomModalHeading: 'Vista previa detallada',
        msgFileRequired: 'Debes seleccionar un archivo para subir.',
        msgSizeTooSmall: 'El archivo "{name}" (<b>{size} KB</b>) es demasiado pequeño y debe ser mayor de <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'El archivo "{name}" (<b>{size} KB</b>) excede el tamaño máximo permitido de <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Debe seleccionar al menos <b>{n}</b> {files} a cargar.',
        msgFilesTooMany: 'El número de archivos seleccionados a cargar <b>({n})</b> excede el límite máximo permitido de <b>{m}</b>.',
        msgFileNotFound: 'Archivo "{name}" no encontrado.',
        msgFileSecured: 'No es posible acceder al archivo "{name}" porque está siendo usado por otra aplicación o no tiene permisos de lectura.',
        msgFileNotReadable: 'No es posible acceder al archivo "{name}".',
        msgFilePreviewAborted: 'Previsualización del archivo "{name}" cancelada.',
        msgFilePreviewError: 'Ocurrió un error mientras se leía el archivo "{name}".',
        msgInvalidFileName: 'Caracteres no válidos o no soportados en el nombre del archivo "{name}".',
        msgInvalidFileType: 'Tipo de archivo no válido para "{name}". Sólo se permiten archivos de tipo "{types}".',
        msgInvalidFileExtension: 'Extensión de archivo no válida para "{name}". Sólo se permiten archivos "{extensions}".',
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
        msgUploadAborted: 'La carga de archivos se ha cancelado',
        msgUploadThreshold: 'Procesando...',
        msgUploadBegin: 'Inicializando...',
        msgUploadEnd: 'Hecho',
        msgUploadEmpty: 'No existen datos válidos para el envío.',
        msgUploadError: 'Error',
        msgValidationError: 'Error de validación',
        msgLoading: 'Subiendo archivo {index} de {files} &hellip;',
        msgProgress: 'Subiendo archivo {index} de {files} - {name} - {percent}% completado.',
        msgSelected: '{n} {files} seleccionado(s)',
        msgFoldersNotAllowed: 'Arrastre y suelte únicamente archivos. Omitida(s) {n} carpeta(s).',
        msgImageWidthSmall: 'El ancho de la imagen "{name}" debe ser de al menos {size} px.',
        msgImageHeightSmall: 'La altura de la imagen "{name}" debe ser de al menos {size} px.',
        msgImageWidthLarge: 'El ancho de la imagen "{name}" no puede exceder de {size} px.',
        msgImageHeightLarge: 'La altura de la imagen "{name}" no puede exceder de {size} px.',
        msgImageResizeError: 'No se pudieron obtener las dimensiones de la imagen para cambiar el tamaño.',
        msgImageResizeException: 'Error al cambiar el tamaño de la imagen.<pre>{errors}</pre>',
        msgAjaxError: 'Algo ha ido mal con la operación {operation}. Por favor, inténtelo de nuevo mas tarde.',
        msgAjaxProgressError: 'La operación {operation} ha fallado',
        ajaxOperations: {
            deleteThumb: 'Archivo borrado',
            uploadThumb: 'Archivo subido',
            uploadBatch: 'Datos subidos en lote',
            uploadExtra: 'Datos del formulario subidos '
        },
        dropZoneTitle: 'Arrastre y suelte aquí los archivos &hellip;',
        dropZoneClickTitle: '<br>(o haga clic para seleccionar {files})',
        fileActionSettings: {
            removeTitle: 'Eliminar archivo',
            uploadTitle: 'Subir archivo',
            uploadRetryTitle: 'Reintentar subir',
            downloadTitle: 'Descargar archivo',
            zoomTitle: 'Ver detalles',
            dragTitle: 'Mover / Reordenar',
            indicatorNewTitle: 'No subido todavía',
            indicatorSuccessTitle: 'Subido',
            indicatorErrorTitle: 'Error al subir',
            indicatorLoadingTitle: 'Subiendo...'
        },
        previewZoomButtonTitles: {
            prev: 'Anterior',
            next: 'Siguiente',
            toggleheader: 'Mostrar encabezado',
            fullscreen: 'Pantalla completa',
            borderless: 'Modo sin bordes',
            close: 'Cerrar vista detallada'
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


    var monthsShortDot = 'ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.'.split('_'),
        monthsShort = 'ene_feb_mar_abr_may_jun_jul_ago_sep_oct_nov_dic'.split('_');

    var monthsParse = [/^ene/i, /^feb/i, /^mar/i, /^abr/i, /^may/i, /^jun/i, /^jul/i, /^ago/i, /^sep/i, /^oct/i, /^nov/i, /^dic/i];
    var monthsRegex = /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre|ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i;

    var esUs = moment.defineLocale('es-us', {
        months : 'enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre'.split('_'),
        monthsShort : function (m, format) {
            if (!m) {
                return monthsShortDot;
            } else if (/-MMM-/.test(format)) {
                return monthsShort[m.month()];
            } else {
                return monthsShortDot[m.month()];
            }
        },
        monthsRegex: monthsRegex,
        monthsShortRegex: monthsRegex,
        monthsStrictRegex: /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)/i,
        monthsShortStrictRegex: /^(ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i,
        monthsParse: monthsParse,
        longMonthsParse: monthsParse,
        shortMonthsParse: monthsParse,
        weekdays : 'domingo_lunes_martes_miércoles_jueves_viernes_sábado'.split('_'),
        weekdaysShort : 'dom._lun._mar._mié._jue._vie._sáb.'.split('_'),
        weekdaysMin : 'do_lu_ma_mi_ju_vi_sá'.split('_'),
        weekdaysParseExact : true,
        longDateFormat : {
            LT : 'h:mm A',
            LTS : 'h:mm:ss A',
            L : 'MM/DD/YYYY',
            LL : 'D [de] MMMM [de] YYYY',
            LLL : 'D [de] MMMM [de] YYYY h:mm A',
            LLLL : 'dddd, D [de] MMMM [de] YYYY h:mm A'
        },
        calendar : {
            sameDay : function () {
                return '[hoy a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
            },
            nextDay : function () {
                return '[mañana a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
            },
            nextWeek : function () {
                return 'dddd [a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
            },
            lastDay : function () {
                return '[ayer a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
            },
            lastWeek : function () {
                return '[el] dddd [pasado a la' + ((this.hours() !== 1) ? 's' : '') + '] LT';
            },
            sameElse : 'L'
        },
        relativeTime : {
            future : 'en %s',
            past : 'hace %s',
            s : 'unos segundos',
            ss : '%d segundos',
            m : 'un minuto',
            mm : '%d minutos',
            h : 'una hora',
            hh : '%d horas',
            d : 'un día',
            dd : '%d días',
            M : 'un mes',
            MM : '%d meses',
            y : 'un año',
            yy : '%d años'
        },
        dayOfMonthOrdinalParse : /\d{1,2}º/,
        ordinal : '%dº',
        week : {
            dow : 0, // Sunday is the first day of the week.
            doy : 6  // The week that contains Jan 6th is the first week of the year.
        }
    });

    return esUs;

})));

/*! Select2 4.0.11 | https://github.com/select2/select2/blob/master/LICENSE.md */

!function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/es",[],function(){return{errorLoading:function(){return"No se pudieron cargar los resultados"},inputTooLong:function(e){var n=e.input.length-e.maximum,r="Por favor, elimine "+n+" car";return r+=1==n?"ácter":"acteres"},inputTooShort:function(e){var n=e.minimum-e.input.length,r="Por favor, introduzca "+n+" car";return r+=1==n?"ácter":"acteres"},loadingMore:function(){return"Cargando más resultados…"},maximumSelected:function(e){var n="Sólo puede seleccionar "+e.maximum+" elemento";return 1!=e.maximum&&(n+="s"),n},noResults:function(){return"No se encontraron resultados"},searching:function(){return"Buscando…"},removeAllItems:function(){return"Eliminar todos los elementos"}}}),e.define,e.require}();
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
    noneSelectedText: 'No hay selección',
    noneResultsText: 'No hay resultados {0}',
    countSelectedText: 'Seleccionados {0} de {1}',
    maxOptionsText: ['Límite alcanzado ({n} {var} max)', 'Límite del grupo alcanzado({n} {var} max)', ['elementos', 'element']],
    multipleSeparator: ', ',
    selectAllText: 'Seleccionar Todos',
    deselectAllText: 'Desmarcar Todos'
  };
})(jQuery);


}));
//# sourceMappingURL=defaults-es_ES.js.map
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
 * Spanish translation for the "es-ES" and "es" language codes.
 * Diomedes Domínguez <diomedes.domimnguez@gmail.com>
 */
$.fn.ajaxSelectPicker.locale["es-ES"] = {
    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} currentlySelected = 'Currently Selected'
     * @markdown
     * El texto que se utilizará para la etiqueta del grupo de opciones cuando se conservan las opciones seleccionadas.
     */
    currentlySelected: "Seleccionado",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} emptyTitle = 'Select and begin typing'
     * @markdown
     * El texto que se utilizará como título para el elemento de selección cuando no hay elementos para mostrar.
     */
    emptyTitle: "Seleccione y comience a escribir",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} errorText = ''Unable to retrieve results'
     * @markdown
     * El texto que se utilizan en el contenedor de estado cuando una solicitud devuelve con un error.
     */
    errorText: "No se puede recuperar resultados",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} searchPlaceholder = 'Search...'
     * @markdown
     * El texto que se utilizará para el atributo marcador de posición de entrada de búsqueda.
     */
    searchPlaceholder: "Buscar...",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusInitialized = 'Start typing a search query'
     * @markdown
     * El texto utilizado en el contenedor de estado cuando se inicializa.
     */
    statusInitialized: "Empieza a escribir una consulta de búsqueda",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusNoResults = 'No Results'
     * @markdown
     * El texto utilizado en el contenedor de estado cuando la solicitud no devolvió resultados.
     */
    statusNoResults: "Sin Resultados",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusSearching = 'Searching...'
     * @markdown
     * El texto que se utilizan en el contenedor de estado cuando se está iniciando una solicitud.
     */
    statusSearching: "Buscando...",

    /**
     * @member $.fn.ajaxSelectPicker.locale
     * @cfg {String} statusTooShort = 'Please enter more characters'
     * @markdown
     * The text used in the status container when the request returns no results.
     */
    statusTooShort: 'Introduzca más caracteres'
};
$.fn.ajaxSelectPicker.locale.es = $.fn.ajaxSelectPicker.locale["es-ES"];
})(jQuery);
