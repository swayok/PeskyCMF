!function(e){"use strict";e.fn.fileinputLocales.uk={fileSingle:"файл",filePlural:"файли",browseLabel:"Вибрати &hellip;",removeLabel:"Видалити",removeTitle:"Видалити вибрані файли",cancelLabel:"Скасувати",cancelTitle:"Скасувати поточне відвантаження",uploadLabel:"Відвантажити",uploadTitle:"Відвантажити обрані файли",msgNo:"Немає",msgNoFilesSelected:"",msgCancelled:"Cкасовано",msgPlaceholder:"Оберіть {files}...",msgZoomModalHeading:"Детальний превью",msgFileRequired:"Ви повинні обрати файл для завантаження.",msgSizeTooSmall:'Файл "{name}" (<b>{size} KB</b>) занадто малий і повинен бути більший, ніж <b>{minSize} KB</b>.',msgSizeTooLarge:'Файл "{name}" (<b>{size} KB</b>) перевищує максимальний розмір <b>{maxSize} KB</b>.',msgFilesTooLess:"Ви повинні обрати як мінімум <b>{n}</b> {files} для відвантаження.",msgFilesTooMany:"Кількість обраних файлів <b>({n})</b> перевищує максимально допустиму кількість <b>{m}</b>.",msgFileNotFound:'Файл "{name}" не знайдено!',msgFileSecured:'Обмеження безпеки перешкоджають читанню файла "{name}".',msgFileNotReadable:'Файл "{name}" неможливо прочитати.',msgFilePreviewAborted:'Перегляд скасований для файла "{name}".',msgFilePreviewError:'Сталася помилка під час читання файла "{name}".',msgInvalidFileName:'Недійсні чи непідтримувані символи в імені файлу "{name}".',msgInvalidFileType:'Заборонений тип файла для "{name}". Тільки "{types}" дозволені.',msgInvalidFileExtension:'Заборонене розширення для файла "{name}". Тільки "{extensions}" дозволені.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"Вивантаження файлу перервана",msgUploadThreshold:"Обробка...",msgUploadBegin:"Ініціалізація...",msgUploadEnd:"Готово",msgUploadEmpty:"Немає доступних даних для відвантаження.",msgUploadError:"Помилка",msgValidationError:"Помилка перевірки",msgLoading:"Відвантаження файла {index} із {files} &hellip;",msgProgress:"Відвантаження файла {index} із {files} - {name} - {percent}% завершено.",msgSelected:"{n} {files} обрано",msgFoldersNotAllowed:"Дозволено перетягувати тільки файли! Пропущено {n} папок.",msgImageWidthSmall:'Ширина зображення "{name}" повинна бути не менше {size} px.',msgImageHeightSmall:'Висота зображення "{name}" повинна бути не менше {size} px.',msgImageWidthLarge:'Ширина зображення "{name}" не може перевищувати {size} px.',msgImageHeightLarge:'Висота зображення "{name}" не може перевищувати {size} px.',msgImageResizeError:"Не вдалося розміри зображення, щоб змінити розмір.",msgImageResizeException:"Помилка при зміні розміру зображення.<pre>{errors}</pre>",msgAjaxError:"Щось не так із операцією {operation}. Будь ласка, спробуйте пізніше!",msgAjaxProgressError:"помилка {operation}",ajaxOperations:{deleteThumb:"видалити файл",uploadThumb:"відвантажити файл",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"Перетягніть файли сюди &hellip;",dropZoneClickTitle:"<br>(або клацність та оберіть {files})",fileActionSettings:{removeTitle:"Видалити файл",uploadTitle:"Відвантажити файл",uploadRetryTitle:"Повторити відвантаження",downloadTitle:"Завантажити файл",zoomTitle:"Подивитися деталі",dragTitle:"Перенести / Переставити",indicatorNewTitle:"Ще не відвантажено",indicatorSuccessTitle:"Відвантажено",indicatorErrorTitle:"Помилка при відвантаженні",indicatorLoadingTitle:"Завантаження ..."},previewZoomButtonTitles:{prev:"Переглянути попередній файл",next:"Переглянути наступний файл",toggleheader:"Перемкнути заголовок",fullscreen:"Перемкнути повноекранний режим",borderless:"Перемкнути режим без полів",close:"Закрити детальний перегляд"}}}(window.jQuery),function(e,i){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?i(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],i):i(e.moment)}(this,function(e){"use strict";function i(e,i){var n=e.split("_");return i%10==1&&i%100!=11?n[0]:i%10>=2&&i%10<=4&&(i%100<10||i%100>=20)?n[1]:n[2]}function n(e,n,t){var a={ss:n?"секунда_секунди_секунд":"секунду_секунди_секунд",mm:n?"хвилина_хвилини_хвилин":"хвилину_хвилини_хвилин",hh:n?"година_години_годин":"годину_години_годин",dd:"день_дні_днів",MM:"місяць_місяці_місяців",yy:"рік_роки_років"};return"m"===t?n?"хвилина":"хвилину":"h"===t?n?"година":"годину":e+" "+i(a[t],+e)}function t(e,i){var n={nominative:"неділя_понеділок_вівторок_середа_четвер_п’ятниця_субота".split("_"),accusative:"неділю_понеділок_вівторок_середу_четвер_п’ятницю_суботу".split("_"),genitive:"неділі_понеділка_вівторка_середи_четверга_п’ятниці_суботи".split("_")};return!0===e?n.nominative.slice(1,7).concat(n.nominative.slice(0,1)):e?n[/(\[[ВвУу]\]) ?dddd/.test(i)?"accusative":/\[?(?:минулої|наступної)? ?\] ?dddd/.test(i)?"genitive":"nominative"][e.day()]:n.nominative}function a(e){return function(){return e+"о"+(11===this.hours()?"б":"")+"] LT"}}return e.defineLocale("uk",{months:{format:"січня_лютого_березня_квітня_травня_червня_липня_серпня_вересня_жовтня_листопада_грудня".split("_"),standalone:"січень_лютий_березень_квітень_травень_червень_липень_серпень_вересень_жовтень_листопад_грудень".split("_")},monthsShort:"січ_лют_бер_квіт_трав_черв_лип_серп_вер_жовт_лист_груд".split("_"),weekdays:t,weekdaysShort:"нд_пн_вт_ср_чт_пт_сб".split("_"),weekdaysMin:"нд_пн_вт_ср_чт_пт_сб".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D MMMM YYYY р.",LLL:"D MMMM YYYY р., HH:mm",LLLL:"dddd, D MMMM YYYY р., HH:mm"},calendar:{sameDay:a("[Сьогодні "),nextDay:a("[Завтра "),lastDay:a("[Вчора "),nextWeek:a("[У] dddd ["),lastWeek:function(){switch(this.day()){case 0:case 3:case 5:case 6:return a("[Минулої] dddd [").call(this);case 1:case 2:case 4:return a("[Минулого] dddd [").call(this)}},sameElse:"L"},relativeTime:{future:"за %s",past:"%s тому",s:"декілька секунд",ss:n,m:n,mm:n,h:"годину",hh:n,d:"день",dd:n,M:"місяць",MM:n,y:"рік",yy:n},meridiemParse:/ночі|ранку|дня|вечора/,isPM:function(e){return/^(дня|вечора)$/.test(e)},meridiem:function(e,i,n){return e<4?"ночі":e<12?"ранку":e<17?"дня":"вечора"},dayOfMonthOrdinalParse:/\d{1,2}-(й|го)/,ordinal:function(e,i){switch(i){case"M":case"d":case"DDD":case"w":case"W":return e+"-й";case"D":return e+"-го";default:return e}},week:{dow:1,doy:7}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/uk",[],function(){function e(e,i,n,t){return e%100>10&&e%100<15?t:e%10==1?i:e%10>1&&e%10<5?n:t}return{errorLoading:function(){return"Неможливо завантажити результати"},inputTooLong:function(i){return"Будь ласка, видаліть "+(i.input.length-i.maximum)+" "+e(i.maximum,"літеру","літери","літер")},inputTooShort:function(e){return"Будь ласка, введіть "+(e.minimum-e.input.length)+" або більше літер"},loadingMore:function(){return"Завантаження інших результатів…"},maximumSelected:function(i){return"Ви можете вибрати лише "+i.maximum+" "+e(i.maximum,"пункт","пункти","пунктів")},noResults:function(){return"Нічого не знайдено"},searching:function(){return"Пошук…"},removeAllItems:function(){return"Видалити всі елементи"}}}),e.define,e.require}();
