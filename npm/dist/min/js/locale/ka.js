!function(e){"use strict";e.fn.fileinputLocales.ka={fileSingle:"ფაილი",filePlural:"ფაილები",browseLabel:"არჩევა &hellip;",removeLabel:"წაშლა",removeTitle:"არჩეული ფაილების წაშლა",cancelLabel:"გაუქმება",cancelTitle:"მიმდინარე ატვირთვის გაუქმება",uploadLabel:"ატვირთვა",uploadTitle:"არჩეული ფაილების ატვირთვა",msgNo:"არა",msgNoFilesSelected:"ფაილები არ არის არჩეული",msgCancelled:"გაუქმებულია",msgPlaceholder:"აირჩიეთ {files}...",msgZoomModalHeading:"დეტალურად ნახვა",msgFileRequired:"ატვირთვისთვის აუცილებელია ფაილის არჩევა.",msgSizeTooSmall:'ფაილი "{name}" (<b>{size} KB</b>) არის ძალიან პატარა. მისი ზომა უნდა იყოს არანაკლებ <b>{minSize} KB</b>.',msgSizeTooLarge:'ფაილი "{name}" (<b>{size} KB</b>) აჭარბებს მაქსიმალურ დასაშვებ ზომას <b>{maxSize} KB</b>.',msgFilesTooLess:"უნდა აირჩიოთ მინიმუმ <b>{n}</b> {file} ატვირთვისთვის.",msgFilesTooMany:"არჩეული ფაილების რაოდენობა <b>({n})</b> აჭარბებს დასაშვებ ლიმიტს <b>{m}</b>.",msgFileNotFound:'ფაილი "{name}" არ მოიძებნა!',msgFileSecured:'უსაფრთხოებით გამოწვეული შეზღუდვები კრძალავს ფაილის "{name}" წაკითხვას.',msgFileNotReadable:'ფაილის "{name}" წაკითხვა შეუძლებელია.',msgFilePreviewAborted:'პრევიუ გაუქმებულია ფაილისათვის "{name}".',msgFilePreviewError:'დაფიქსირდა შეცდომა ფაილის "{name}" კითხვისას.',msgInvalidFileName:'ნაპოვნია დაუშვებელი სიმბოლოები ფაილის "{name}" სახელში.',msgInvalidFileType:'ფაილს "{name}" გააჩნია დაუშვებელი ტიპი. მხოლოდ "{types}" ტიპის ფაილები არის დაშვებული.',msgInvalidFileExtension:'ფაილს "{name}" გააჩნია დაუშვებელი გაფართოება. მხოლოდ "{extensions}" გაფართოების ფაილები არის დაშვებული.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"ფაილის ატვირთვა შეწყდა",msgUploadThreshold:"მუშავდება...",msgUploadBegin:"ინიციალიზაცია...",msgUploadEnd:"დასრულებულია",msgUploadEmpty:"ატვირთვისთვის დაუშვებელი მონაცემები.",msgUploadError:"ატვირთვის შეცდომა",msgValidationError:"ვალიდაციის შეცდომა",msgLoading:"ატვირთვა {index} / {files} &hellip;",msgProgress:"ფაილის ატვირთვა დასრულებულია {index} / {files} - {name} - {percent}%.",msgSelected:"არჩეულია {n} {file}",msgFoldersNotAllowed:"დაშვებულია მხოლოდ ფაილების გადმოთრევა! გამოტოვებულია {n} გადმოთრეული ფოლდერი.",msgImageWidthSmall:'სურათის "{name}" სიგანე უნდა იყოს არანაკლებ {size} px.',msgImageHeightSmall:'სურათის "{name}" სიმაღლე უნდა იყოს არანაკლებ {size} px.',msgImageWidthLarge:'სურათის "{name}" სიგანე არ უნდა აღემატებოდეს {size} px-ს.',msgImageHeightLarge:'სურათის "{name}" სიმაღლე არ უნდა აღემატებოდეს {size} px-ს.',msgImageResizeError:"ვერ მოხერხდა სურათის ზომის შეცვლისთვის საჭირო მონაცემების გარკვევა.",msgImageResizeException:"შეცდომა სურათის ზომის შეცვლისას.<pre>{errors}</pre>",msgAjaxError:"დაფიქსირდა შეცდომა ოპერაციის {operation} შესრულებისას. ცადეთ მოგვიანებით!",msgAjaxProgressError:"ვერ მოხერხდა ოპერაციის {operation} შესრულება",ajaxOperations:{deleteThumb:"ფაილის წაშლა",uploadThumb:"ფაილის ატვირთვა",uploadBatch:"ფაილების ატვირთვა",uploadExtra:"მონაცემების გაგზავნა ფორმიდან"},dropZoneTitle:"გადმოათრიეთ ფაილები აქ &hellip;",dropZoneClickTitle:"<br>(ან დააჭირეთ რათა აირჩიოთ {files})",fileActionSettings:{removeTitle:"ფაილის წაშლა",uploadTitle:"ფაილის ატვირთვა",uploadRetryTitle:"ატვირთვის გამეორება",downloadTitle:"ფაილის ჩამოტვირთვა",zoomTitle:"დეტალურად ნახვა",dragTitle:"გადაადგილება / მიმდევრობის შეცვლა",indicatorNewTitle:"ჯერ არ ატვირთულა",indicatorSuccessTitle:"ატვირთულია",indicatorErrorTitle:"ატვირთვის შეცდომა",indicatorLoadingTitle:"ატვირთვა ..."},previewZoomButtonTitles:{prev:"წინა ფაილის ნახვა",next:"შემდეგი ფაილის ნახვა",toggleheader:"სათაურის დამალვა",fullscreen:"მთელ ეკრანზე გაშლა",borderless:"მთელ გვერდზე გაშლა",close:"დახურვა"}}}(window.jQuery),function(e,i){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?i(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],i):i(e.moment)}(this,function(e){"use strict";return e.defineLocale("ka",{months:{standalone:"იანვარი_თებერვალი_მარტი_აპრილი_მაისი_ივნისი_ივლისი_აგვისტო_სექტემბერი_ოქტომბერი_ნოემბერი_დეკემბერი".split("_"),format:"იანვარს_თებერვალს_მარტს_აპრილის_მაისს_ივნისს_ივლისს_აგვისტს_სექტემბერს_ოქტომბერს_ნოემბერს_დეკემბერს".split("_")},monthsShort:"იან_თებ_მარ_აპრ_მაი_ივნ_ივლ_აგვ_სექ_ოქტ_ნოე_დეკ".split("_"),weekdays:{standalone:"კვირა_ორშაბათი_სამშაბათი_ოთხშაბათი_ხუთშაბათი_პარასკევი_შაბათი".split("_"),format:"კვირას_ორშაბათს_სამშაბათს_ოთხშაბათს_ხუთშაბათს_პარასკევს_შაბათს".split("_"),isFormat:/(წინა|შემდეგ)/},weekdaysShort:"კვი_ორშ_სამ_ოთხ_ხუთ_პარ_შაბ".split("_"),weekdaysMin:"კვ_ორ_სა_ოთ_ხუ_პა_შა".split("_"),longDateFormat:{LT:"h:mm A",LTS:"h:mm:ss A",L:"DD/MM/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY h:mm A",LLLL:"dddd, D MMMM YYYY h:mm A"},calendar:{sameDay:"[დღეს] LT[-ზე]",nextDay:"[ხვალ] LT[-ზე]",lastDay:"[გუშინ] LT[-ზე]",nextWeek:"[შემდეგ] dddd LT[-ზე]",lastWeek:"[წინა] dddd LT-ზე",sameElse:"L"},relativeTime:{future:function(e){return/(წამი|წუთი|საათი|წელი)/.test(e)?e.replace(/ი$/,"ში"):e+"ში"},past:function(e){return/(წამი|წუთი|საათი|დღე|თვე)/.test(e)?e.replace(/(ი|ე)$/,"ის წინ"):/წელი/.test(e)?e.replace(/წელი$/,"წლის წინ"):void 0},s:"რამდენიმე წამი",ss:"%d წამი",m:"წუთი",mm:"%d წუთი",h:"საათი",hh:"%d საათი",d:"დღე",dd:"%d დღე",M:"თვე",MM:"%d თვე",y:"წელი",yy:"%d წელი"},dayOfMonthOrdinalParse:/0|1-ლი|მე-\d{1,2}|\d{1,2}-ე/,ordinal:function(e){return 0===e?e:1===e?e+"-ლი":e<20||e<=100&&e%20==0||e%100==0?"მე-"+e:e+"-ე"},week:{dow:1,doy:7}})});