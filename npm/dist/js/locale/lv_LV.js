!function(e,s){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?s(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],s):s(e.moment)}(this,function(e){"use strict";function s(e,s,t){return t?s%10==1&&s%100!=11?e[2]:e[3]:s%10==1&&s%100!=11?e[0]:e[1]}function t(e,t,n){return e+" "+s(a[n],e,t)}function n(e,t,n){return s(a[n],e,t)}function i(e,s){return s?"dažas sekundes":"dažām sekundēm"}var a={ss:"sekundes_sekundēm_sekunde_sekundes".split("_"),m:"minūtes_minūtēm_minūte_minūtes".split("_"),mm:"minūtes_minūtēm_minūte_minūtes".split("_"),h:"stundas_stundām_stunda_stundas".split("_"),hh:"stundas_stundām_stunda_stundas".split("_"),d:"dienas_dienām_diena_dienas".split("_"),dd:"dienas_dienām_diena_dienas".split("_"),M:"mēneša_mēnešiem_mēnesis_mēneši".split("_"),MM:"mēneša_mēnešiem_mēnesis_mēneši".split("_"),y:"gada_gadiem_gads_gadi".split("_"),yy:"gada_gadiem_gads_gadi".split("_")};return e.defineLocale("lv",{months:"janvāris_februāris_marts_aprīlis_maijs_jūnijs_jūlijs_augusts_septembris_oktobris_novembris_decembris".split("_"),monthsShort:"jan_feb_mar_apr_mai_jūn_jūl_aug_sep_okt_nov_dec".split("_"),weekdays:"svētdiena_pirmdiena_otrdiena_trešdiena_ceturtdiena_piektdiena_sestdiena".split("_"),weekdaysShort:"Sv_P_O_T_C_Pk_S".split("_"),weekdaysMin:"Sv_P_O_T_C_Pk_S".split("_"),weekdaysParseExact:!0,longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY.",LL:"YYYY. [gada] D. MMMM",LLL:"YYYY. [gada] D. MMMM, HH:mm",LLLL:"YYYY. [gada] D. MMMM, dddd, HH:mm"},calendar:{sameDay:"[Šodien pulksten] LT",nextDay:"[Rīt pulksten] LT",nextWeek:"dddd [pulksten] LT",lastDay:"[Vakar pulksten] LT",lastWeek:"[Pagājušā] dddd [pulksten] LT",sameElse:"L"},relativeTime:{future:"pēc %s",past:"pirms %s",s:i,ss:t,m:n,mm:t,h:n,hh:t,d:n,dd:t,M:n,MM:t,y:n,yy:t},dayOfMonthOrdinalParse:/\d{1,2}\./,ordinal:"%d.",week:{dow:1,doy:4}})}),function(e,s){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return s(e)}):"object"==typeof module&&module.exports?module.exports=s(require("jquery")):s(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Nekas nav atzīmēts",noneResultsText:"Nav neviena rezultāta {0}",countSelectedText:function(e,s){return 1==e?"{0} ieraksts atzīmēts":"{0} ieraksti atzīmēts"},maxOptionsText:function(e,s){return[1==e?"Sasniegts limits ({n} ieraksts maksimums)":"Sasniegts limits ({n} ieraksti maksimums)",1==s?"Sasniegts grupas limits ({n} ieraksts maksimums)":"Sasniegts grupas limits ({n} ieraksti maksimums)"]},selectAllText:"Atzīmēt visu",deselectAllText:"Neatzīmēt nevienu",multipleSeparator:", "}}(e)});
