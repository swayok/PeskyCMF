!function(e,o){"function"==typeof define&&define.amd?define(["jquery","query-builder"],o):o(e.jQuery)}(this,function(e){"use strict";var o=e.fn.queryBuilder;o.regional.es={__locale:"Spanish (es)",__author:'"pyarza", "kddlb"',add_rule:"Añadir regla",add_group:"Añadir grupo",delete_rule:"Borrar",delete_group:"Borrar",conditions:{AND:"Y",OR:"O"},operators:{equal:"igual",not_equal:"distinto",in:"en",not_in:"no en",less:"menor",less_or_equal:"menor o igual",greater:"mayor",greater_or_equal:"mayor o igual",between:"entre",not_between:"no está entre",begins_with:"empieza por",not_begins_with:"no empieza por",contains:"contiene",not_contains:"no contiene",ends_with:"acaba con",not_ends_with:"no acaba con",is_empty:"está vacío",is_not_empty:"no está vacío",is_null:"es nulo",is_not_null:"no es nulo"},errors:{no_filter:"No se ha seleccionado ningún filtro",empty_group:"El grupo está vacío",radio_empty:"Ningún valor seleccionado",checkbox_empty:"Ningún valor seleccionado",select_empty:"Ningún valor seleccionado",string_empty:"Cadena vacía",string_exceed_min_length:"Debe contener al menos {0} caracteres",string_exceed_max_length:"No debe contener más de {0} caracteres",string_invalid_format:"Formato inválido ({0})",number_nan:"No es un número",number_not_integer:"No es un número entero",number_not_double:"No es un número real",number_exceed_min:"Debe ser mayor que {0}",number_exceed_max:"Debe ser menor que {0}",number_wrong_step:"Debe ser múltiplo de {0}",datetime_invalid:"Formato de fecha inválido ({0})",datetime_exceed_min:"Debe ser posterior a {0}",datetime_exceed_max:"Debe ser anterior a {0}",number_between_invalid:"Valores Inválidos, {0} es mayor que {1}",datetime_empty:"Campo vacio",datetime_between_invalid:"Valores Inválidos, {0} es mayor que {1}",boolean_not_valid:"No es booleano",operator_not_multiple:'El operador "{1}" no puede aceptar valores multiples'}},o.defaults({lang_code:"es"})}),function(e){"use strict";e.fn.fileinputLocales.es={fileSingle:"archivo",filePlural:"archivos",browseLabel:"Examinar &hellip;",removeLabel:"Quitar",removeTitle:"Quitar archivos seleccionados",cancelLabel:"Cancelar",cancelTitle:"Abortar la subida en curso",uploadLabel:"Subir archivo",uploadTitle:"Subir archivos seleccionados",msgNo:"No",msgNoFilesSelected:"No hay archivos seleccionados",msgCancelled:"Cancelado",msgPlaceholder:"Seleccionar {files}...",msgZoomModalHeading:"Vista previa detallada",msgFileRequired:"Debes seleccionar un archivo para subir.",msgSizeTooSmall:'El archivo "{name}" (<b>{size} KB</b>) es demasiado pequeño y debe ser mayor de <b>{minSize} KB</b>.',msgSizeTooLarge:'El archivo "{name}" (<b>{size} KB</b>) excede el tamaño máximo permitido de <b>{maxSize} KB</b>.',msgFilesTooLess:"Debe seleccionar al menos <b>{n}</b> {files} a cargar.",msgFilesTooMany:"El número de archivos seleccionados a cargar <b>({n})</b> excede el límite máximo permitido de <b>{m}</b>.",msgFileNotFound:'Archivo "{name}" no encontrado.',msgFileSecured:'No es posible acceder al archivo "{name}" porque está siendo usado por otra aplicación o no tiene permisos de lectura.',msgFileNotReadable:'No es posible acceder al archivo "{name}".',msgFilePreviewAborted:'Previsualización del archivo "{name}" cancelada.',msgFilePreviewError:'Ocurrió un error mientras se leía el archivo "{name}".',msgInvalidFileName:'Caracteres no válidos o no soportados en el nombre del archivo "{name}".',msgInvalidFileType:'Tipo de archivo no válido para "{name}". Sólo se permiten archivos de tipo "{types}".',msgInvalidFileExtension:'Extensión de archivo no válida para "{name}". Sólo se permiten archivos "{extensions}".',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"La carga de archivos se ha cancelado",msgUploadThreshold:"Procesando...",msgUploadBegin:"Inicializando...",msgUploadEnd:"Hecho",msgUploadEmpty:"No existen datos válidos para el envío.",msgUploadError:"Error",msgValidationError:"Error de validación",msgLoading:"Subiendo archivo {index} de {files} &hellip;",msgProgress:"Subiendo archivo {index} de {files} - {name} - {percent}% completado.",msgSelected:"{n} {files} seleccionado(s)",msgFoldersNotAllowed:"Arrastre y suelte únicamente archivos. Omitida(s) {n} carpeta(s).",msgImageWidthSmall:'El ancho de la imagen "{name}" debe ser de al menos {size} px.',msgImageHeightSmall:'La altura de la imagen "{name}" debe ser de al menos {size} px.',msgImageWidthLarge:'El ancho de la imagen "{name}" no puede exceder de {size} px.',msgImageHeightLarge:'La altura de la imagen "{name}" no puede exceder de {size} px.',msgImageResizeError:"No se pudieron obtener las dimensiones de la imagen para cambiar el tamaño.",msgImageResizeException:"Error al cambiar el tamaño de la imagen.<pre>{errors}</pre>",msgAjaxError:"Algo ha ido mal con la operación {operation}. Por favor, inténtelo de nuevo mas tarde.",msgAjaxProgressError:"La operación {operation} ha fallado",ajaxOperations:{deleteThumb:"Archivo borrado",uploadThumb:"Archivo subido",uploadBatch:"Datos subidos en lote",uploadExtra:"Datos del formulario subidos "},dropZoneTitle:"Arrastre y suelte aquí los archivos &hellip;",dropZoneClickTitle:"<br>(o haga clic para seleccionar {files})",fileActionSettings:{removeTitle:"Eliminar archivo",uploadTitle:"Subir archivo",uploadRetryTitle:"Reintentar subir",downloadTitle:"Descargar archivo",zoomTitle:"Ver detalles",dragTitle:"Mover / Reordenar",indicatorNewTitle:"No subido todavía",indicatorSuccessTitle:"Subido",indicatorErrorTitle:"Error al subir",indicatorLoadingTitle:"Subiendo..."},previewZoomButtonTitles:{prev:"Anterior",next:"Siguiente",toggleheader:"Mostrar encabezado",fullscreen:"Pantalla completa",borderless:"Modo sin bordes",close:"Cerrar vista detallada"}}}(window.jQuery),function(e,o){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?o(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],o):o(e.moment)}(this,function(e){"use strict";var o="ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.".split("_"),a="ene_feb_mar_abr_may_jun_jul_ago_sep_oct_nov_dic".split("_");return e.defineLocale("es-us",{months:"enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre".split("_"),monthsShort:function(e,r){return e?/-MMM-/.test(r)?a[e.month()]:o[e.month()]:o},monthsParseExact:!0,weekdays:"domingo_lunes_martes_miércoles_jueves_viernes_sábado".split("_"),weekdaysShort:"dom._lun._mar._mié._jue._vie._sáb.".split("_"),weekdaysMin:"do_lu_ma_mi_ju_vi_sá".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"h:mm A",LTS:"h:mm:ss A",L:"MM/DD/YYYY",LL:"MMMM [de] D [de] YYYY",LLL:"MMMM [de] D [de] YYYY h:mm A",LLLL:"dddd, MMMM [de] D [de] YYYY h:mm A"},calendar:{sameDay:function(){return"[hoy a la"+(1!==this.hours()?"s":"")+"] LT"},nextDay:function(){return"[mañana a la"+(1!==this.hours()?"s":"")+"] LT"},nextWeek:function(){return"dddd [a la"+(1!==this.hours()?"s":"")+"] LT"},lastDay:function(){return"[ayer a la"+(1!==this.hours()?"s":"")+"] LT"},lastWeek:function(){return"[el] dddd [pasado a la"+(1!==this.hours()?"s":"")+"] LT"},sameElse:"L"},relativeTime:{future:"en %s",past:"hace %s",s:"unos segundos",ss:"%d segundos",m:"un minuto",mm:"%d minutos",h:"una hora",hh:"%d horas",d:"un día",dd:"%d días",M:"un mes",MM:"%d meses",y:"un año",yy:"%d años"},dayOfMonthOrdinalParse:/\d{1,2}º/,ordinal:"%dº",week:{dow:0,doy:6}})}),function(e,o){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return o(e)}):"object"==typeof module&&module.exports?module.exports=o(require("jquery")):o(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"No hay selección",noneResultsText:"No hay resultados {0}",countSelectedText:"Seleccionados {0} de {1}",maxOptionsText:["Límite alcanzado ({n} {var} max)","Límite del grupo alcanzado({n} {var} max)",["elementos","element"]],multipleSeparator:", ",selectAllText:"Seleccionar Todos",deselectAllText:"Desmarcar Todos"}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["es-ES"]={currentlySelected:"Seleccionado",emptyTitle:"Seleccione y comience a escribir",errorText:"No se puede recuperar resultados",searchPlaceholder:"Buscar...",statusInitialized:"Empieza a escribir una consulta de búsqueda",statusNoResults:"Sin Resultados",statusSearching:"Buscando...",statusTooShort:"Introduzca más caracteres"},e.fn.ajaxSelectPicker.locale.es=e.fn.ajaxSelectPicker.locale["es-ES"]}(jQuery);
