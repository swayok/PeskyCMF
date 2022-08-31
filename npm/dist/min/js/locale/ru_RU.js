!function(e,t){"function"==typeof define&&define.amd?define(["jquery","query-builder"],t):t(e.jQuery)}(this,function(e){"use strict";var t=e.fn.queryBuilder;t.regional.ru={__locale:"Russian (ru)",add_rule:"Добавить",add_group:"Добавить группу",delete_rule:"Удалить",delete_group:"Удалить",conditions:{AND:"И",OR:"ИЛИ"},operators:{equal:"равно",not_equal:"не равно",in:"из указанных",not_in:"не из указанных",less:"меньше",less_or_equal:"меньше или равно",greater:"больше",greater_or_equal:"больше или равно",between:"между",begins_with:"начинается с",not_begins_with:"не начинается с",contains:"содержит",not_contains:"не содержит",ends_with:"оканчивается на",not_ends_with:"не оканчивается на",is_empty:"пустая строка",is_not_empty:"не пустая строка",is_null:"пусто",is_not_null:"не пусто"},errors:{no_filter:"Фильтр не выбран",empty_group:"Группа пуста",radio_empty:"Не выбранно значение",checkbox_empty:"Не выбранно значение",select_empty:"Не выбранно значение",string_empty:"Не заполненно",string_exceed_min_length:"Должен содержать больше {0} символов",string_exceed_max_length:"Должен содержать меньше {0} символов",string_invalid_format:"Неверный формат ({0})",number_nan:"Не число",number_not_integer:"Не число",number_not_double:"Не число",number_exceed_min:"Должно быть больше {0}",number_exceed_max:"Должно быть меньше, чем {0}",number_wrong_step:"Должно быть кратно {0}",datetime_empty:"Не заполненно",datetime_invalid:"Неверный формат даты ({0})",datetime_exceed_min:"Должно быть, после {0}",datetime_exceed_max:"Должно быть, до {0}",boolean_not_valid:"Не логическое",operator_not_multiple:'Оператор "{1}" не поддерживает много значений'},invert:"Инвертировать"},t.defaults({lang_code:"ru"})}),function(e){"use strict";e.fn.fileinputLocales.ru={fileSingle:"файл",filePlural:"файлы",browseLabel:"Выбрать &hellip;",removeLabel:"Удалить",removeTitle:"Очистить выбранные файлы",cancelLabel:"Отмена",cancelTitle:"Отменить текущую загрузку",uploadLabel:"Загрузить",uploadTitle:"Загрузить выбранные файлы",msgNo:"нет",msgNoFilesSelected:"",msgCancelled:"Отменено",msgPlaceholder:"Выбрать {files}...",msgZoomModalHeading:"Подробное превью",msgFileRequired:"Необходимо выбрать файл для загрузки.",msgSizeTooSmall:'Файл "{name}" (<b>{size} KB</b>) имеет слишком маленький размер и должен быть больше <b>{minSize} KB</b>.',msgSizeTooLarge:'Файл "{name}" (<b>{size} KB</b>) превышает максимальный размер <b>{maxSize} KB</b>.',msgFilesTooLess:"Вы должны выбрать как минимум <b>{n}</b> {files} для загрузки.",msgFilesTooMany:"Количество выбранных файлов <b>({n})</b> превышает максимально допустимое количество <b>{m}</b>.",msgFileNotFound:'Файл "{name}" не найден!',msgFileSecured:'Ограничения безопасности запрещают читать файл "{name}".',msgFileNotReadable:'Файл "{name}" невозможно прочитать.',msgFilePreviewAborted:'Предпросмотр отменен для файла "{name}".',msgFilePreviewError:'Произошла ошибка при чтении файла "{name}".',msgInvalidFileName:'Неверные или неподдерживаемые символы в названии файла "{name}".',msgInvalidFileType:'Запрещенный тип файла для "{name}". Только "{types}" разрешены.',msgInvalidFileExtension:'Запрещенное расширение для файла "{name}". Только "{extensions}" разрешены.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Выгрузка файла прервана",msgUploadThreshold:"Обработка...",msgUploadBegin:"Инициализация...",msgUploadEnd:"Готово",msgUploadEmpty:"Недопустимые данные для загрузки",msgUploadError:"Ошибка загрузки",msgValidationError:"Ошибка проверки",msgLoading:"Загрузка файла {index} из {files} &hellip;",msgProgress:"Загрузка файла {index} из {files} - {name} - {percent}% завершено.",msgSelected:"Выбрано файлов: {n}",msgFoldersNotAllowed:"Разрешено перетаскивание только файлов! Пропущено {n} папок.",msgImageWidthSmall:"Ширина изображения {name} должна быть не меньше {size} px.",msgImageHeightSmall:"Высота изображения {name} должна быть не меньше {size} px.",msgImageWidthLarge:'Ширина изображения "{name}" не может превышать {size} px.',msgImageHeightLarge:'Высота изображения "{name}" не может превышать {size} px.',msgImageResizeError:"Не удалось получить размеры изображения, чтобы изменить размер.",msgImageResizeException:"Ошибка при изменении размера изображения.<pre>{errors}</pre>",msgAjaxError:"Произошла ошибка при выполнении операции {operation}. Повторите попытку позже!",msgAjaxProgressError:"Не удалось выполнить {operation}",ajaxOperations:{deleteThumb:"удалить файл",uploadThumb:"загрузить файл",uploadBatch:"загрузить пакет файлов",uploadExtra:"загрузка данных с формы"},dropZoneTitle:"Перетащите файлы сюда &hellip;",dropZoneClickTitle:"<br>(Или щёлкните, чтобы выбрать {files})",fileActionSettings:{removeTitle:"Удалить файл",uploadTitle:"Загрузить файл",uploadRetryTitle:"Повторить загрузку",downloadTitle:"Загрузить файл",zoomTitle:"Посмотреть детали",dragTitle:"Переместить / Изменить порядок",indicatorNewTitle:"Еще не загружен",indicatorSuccessTitle:"Загружен",indicatorErrorTitle:"Ошибка загрузки",indicatorLoadingTitle:"Загрузка ..."},previewZoomButtonTitles:{prev:"Посмотреть предыдущий файл",next:"Посмотреть следующий файл",toggleheader:"Переключить заголовок",fullscreen:"Переключить полноэкранный режим",borderless:"Переключить режим без полей",close:"Закрыть подробный предпросмотр"}}}(window.jQuery),function(e,t){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?t(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],t):t(e.moment)}(this,function(e){"use strict";function t(e,t){var n=e.split("_");return t%10==1&&t%100!=11?n[0]:t%10>=2&&t%10<=4&&(t%100<10||t%100>=20)?n[1]:n[2]}function n(e,n,i){var r={ss:n?"секунда_секунды_секунд":"секунду_секунды_секунд",mm:n?"минута_минуты_минут":"минуту_минуты_минут",hh:"час_часа_часов",dd:"день_дня_дней",MM:"месяц_месяца_месяцев",yy:"год_года_лет"};return"m"===i?n?"минута":"минуту":e+" "+t(r[i],+e)}var i=[/^янв/i,/^фев/i,/^мар/i,/^апр/i,/^ма[йя]/i,/^июн/i,/^июл/i,/^авг/i,/^сен/i,/^окт/i,/^ноя/i,/^дек/i];return e.defineLocale("ru",{months:{format:"января_февраля_марта_апреля_мая_июня_июля_августа_сентября_октября_ноября_декабря".split("_"),standalone:"январь_февраль_март_апрель_май_июнь_июль_август_сентябрь_октябрь_ноябрь_декабрь".split("_")},monthsShort:{format:"янв._февр._мар._апр._мая_июня_июля_авг._сент._окт._нояб._дек.".split("_"),standalone:"янв._февр._март_апр._май_июнь_июль_авг._сент._окт._нояб._дек.".split("_")},weekdays:{standalone:"воскресенье_понедельник_вторник_среда_четверг_пятница_суббота".split("_"),format:"воскресенье_понедельник_вторник_среду_четверг_пятницу_субботу".split("_"),isFormat:/\[ ?[Вв] ?(?:прошлую|следующую|эту)? ?] ?dddd/},weekdaysShort:"вс_пн_вт_ср_чт_пт_сб".split("_"),weekdaysMin:"вс_пн_вт_ср_чт_пт_сб".split("_"),monthsParse:i,longMonthsParse:i,shortMonthsParse:i,monthsRegex:/^(январ[ья]|янв\.?|феврал[ья]|февр?\.?|марта?|мар\.?|апрел[ья]|апр\.?|ма[йя]|июн[ья]|июн\.?|июл[ья]|июл\.?|августа?|авг\.?|сентябр[ья]|сент?\.?|октябр[ья]|окт\.?|ноябр[ья]|нояб?\.?|декабр[ья]|дек\.?)/i,monthsShortRegex:/^(январ[ья]|янв\.?|феврал[ья]|февр?\.?|марта?|мар\.?|апрел[ья]|апр\.?|ма[йя]|июн[ья]|июн\.?|июл[ья]|июл\.?|августа?|авг\.?|сентябр[ья]|сент?\.?|октябр[ья]|окт\.?|ноябр[ья]|нояб?\.?|декабр[ья]|дек\.?)/i,monthsStrictRegex:/^(январ[яь]|феврал[яь]|марта?|апрел[яь]|ма[яй]|июн[яь]|июл[яь]|августа?|сентябр[яь]|октябр[яь]|ноябр[яь]|декабр[яь])/i,monthsShortStrictRegex:/^(янв\.|февр?\.|мар[т.]|апр\.|ма[яй]|июн[ья.]|июл[ья.]|авг\.|сент?\.|окт\.|нояб?\.|дек\.)/i,longDateFormat:{LT:"H:mm",LTS:"H:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY г.",LLL:"D MMMM YYYY г., H:mm",LLLL:"dddd, D MMMM YYYY г., H:mm"},calendar:{sameDay:"[Сегодня, в] LT",nextDay:"[Завтра, в] LT",lastDay:"[Вчера, в] LT",nextWeek:function(e){if(e.week()===this.week())return 2===this.day()?"[Во] dddd, [в] LT":"[В] dddd, [в] LT";switch(this.day()){case 0:return"[В следующее] dddd, [в] LT";case 1:case 2:case 4:return"[В следующий] dddd, [в] LT";case 3:case 5:case 6:return"[В следующую] dddd, [в] LT"}},lastWeek:function(e){if(e.week()===this.week())return 2===this.day()?"[Во] dddd, [в] LT":"[В] dddd, [в] LT";switch(this.day()){case 0:return"[В прошлое] dddd, [в] LT";case 1:case 2:case 4:return"[В прошлый] dddd, [в] LT";case 3:case 5:case 6:return"[В прошлую] dddd, [в] LT"}},sameElse:"L"},relativeTime:{future:"через %s",past:"%s назад",s:"несколько секунд",ss:n,m:n,mm:n,h:"час",hh:n,d:"день",dd:n,M:"месяц",MM:n,y:"год",yy:n},meridiemParse:/ночи|утра|дня|вечера/i,isPM:function(e){return/^(дня|вечера)$/.test(e)},meridiem:function(e,t,n){return e<4?"ночи":e<12?"утра":e<17?"дня":"вечера"},dayOfMonthOrdinalParse:/\d{1,2}-(й|го|я)/,ordinal:function(e,t){switch(t){case"M":case"d":case"DDD":return e+"-й";case"D":return e+"-го";case"w":case"W":return e+"-я";default:return e}},week:{dow:1,doy:4}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/ru",[],function(){function e(e,t,n,i){return e%10<5&&e%10>0&&e%100<5||e%100>20?e%10>1?n:t:i}return{errorLoading:function(){return"Невозможно загрузить результаты"},inputTooLong:function(t){var n=t.input.length-t.maximum,i="Пожалуйста, введите на "+n+" символ";return i+=e(n,"","a","ов"),i+=" меньше"},inputTooShort:function(t){var n=t.minimum-t.input.length,i="Пожалуйста, введите ещё хотя бы "+n+" символ";return i+=e(n,"","a","ов")},loadingMore:function(){return"Загрузка данных…"},maximumSelected:function(t){var n="Вы можете выбрать не более "+t.maximum+" элемент";return n+=e(t.maximum,"","a","ов")},noResults:function(){return"Совпадений не найдено"},searching:function(){return"Поиск…"},removeAllItems:function(){return"Удалить все элементы"}}}),e.define,e.require}(),function(e,t){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return t(e)}):"object"==typeof module&&module.exports?module.exports=t(require("jquery")):t(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Ничего не выбрано",noneResultsText:"Совпадений не найдено {0}",countSelectedText:"Выбрано {0} из {1}",maxOptionsText:["Достигнут предел ({n} {var} максимум)","Достигнут предел в группе ({n} {var} максимум)",["шт.","шт."]],doneButtonText:"Закрыть",selectAllText:"Выбрать все",deselectAllText:"Отменить все",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["ru-RU"]={currentlySelected:"Выбрано",emptyTitle:"Выделите и начните печатать",errorText:"Невозможно получить результат",searchPlaceholder:"Искать...",statusInitialized:"Начните печатать запрос для поиска",statusNoResults:"Нет результатов",statusSearching:"Поиск...",statusTooShort:"Введите еще несколько символов"},e.fn.ajaxSelectPicker.locale.ru=e.fn.ajaxSelectPicker.locale["ru-RU"]}(jQuery);
