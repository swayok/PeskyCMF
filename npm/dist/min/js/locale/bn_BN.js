!function(e,n){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?n(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],n):n(e.moment)}(this,function(e){"use strict";var n={1:"১",2:"২",3:"৩",4:"৪",5:"৫",6:"৬",7:"৭",8:"৮",9:"৯",0:"০"},t={"১":"1","২":"2","৩":"3","৪":"4","৫":"5","৬":"6","৭":"7","৮":"8","৯":"9","০":"0"};return e.defineLocale("bn",{months:"জানুয়ারী_ফেব্রুয়ারি_মার্চ_এপ্রিল_মে_জুন_জুলাই_আগস্ট_সেপ্টেম্বর_অক্টোবর_নভেম্বর_ডিসেম্বর".split("_"),monthsShort:"জানু_ফেব_মার্চ_এপ্র_মে_জুন_জুল_আগ_সেপ্ট_অক্টো_নভে_ডিসে".split("_"),weekdays:"রবিবার_সোমবার_মঙ্গলবার_বুধবার_বৃহস্পতিবার_শুক্রবার_শনিবার".split("_"),weekdaysShort:"রবি_সোম_মঙ্গল_বুধ_বৃহস্পতি_শুক্র_শনি".split("_"),weekdaysMin:"রবি_সোম_মঙ্গ_বুধ_বৃহঃ_শুক্র_শনি".split("_"),longDateFormat:{LT:"A h:mm সময়",LTS:"A h:mm:ss সময়",L:"DD/MM/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY, A h:mm সময়",LLLL:"dddd, D MMMM YYYY, A h:mm সময়"},calendar:{sameDay:"[আজ] LT",nextDay:"[আগামীকাল] LT",nextWeek:"dddd, LT",lastDay:"[গতকাল] LT",lastWeek:"[গত] dddd, LT",sameElse:"L"},relativeTime:{future:"%s পরে",past:"%s আগে",s:"কয়েক সেকেন্ড",ss:"%d সেকেন্ড",m:"এক মিনিট",mm:"%d মিনিট",h:"এক ঘন্টা",hh:"%d ঘন্টা",d:"এক দিন",dd:"%d দিন",M:"এক মাস",MM:"%d মাস",y:"এক বছর",yy:"%d বছর"},preparse:function(e){return e.replace(/[১২৩৪৫৬৭৮৯০]/g,function(e){return t[e]})},postformat:function(e){return e.replace(/\d/g,function(e){return n[e]})},meridiemParse:/রাত|সকাল|দুপুর|বিকাল|রাত/,meridiemHour:function(e,n){return 12===e&&(e=0),"রাত"===n&&e>=4||"দুপুর"===n&&e<5||"বিকাল"===n?e+12:e},meridiem:function(e,n,t){return e<4?"রাত":e<10?"সকাল":e<17?"দুপুর":e<20?"বিকাল":"রাত"},week:{dow:0,doy:6}})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/bn",[],function(){return{errorLoading:function(){return"ফলাফলগুলি লোড করা যায়নি।"},inputTooLong:function(e){var n=e.input.length-e.maximum,t="অনুগ্রহ করে "+n+" টি অক্ষর মুছে দিন।";return 1!=n&&(t="অনুগ্রহ করে "+n+" টি অক্ষর মুছে দিন।"),t},inputTooShort:function(e){return e.minimum-e.input.length+" টি অক্ষর অথবা অধিক অক্ষর লিখুন।"},loadingMore:function(){return"আরো ফলাফল লোড হচ্ছে ..."},maximumSelected:function(e){var n=e.maximum+" টি আইটেম নির্বাচন করতে পারবেন।";return 1!=e.maximum&&(n=e.maximum+" টি আইটেম নির্বাচন করতে পারবেন।"),n},noResults:function(){return"কোন ফলাফল পাওয়া যায়নি।"},searching:function(){return"অনুসন্ধান করা হচ্ছে ..."}}}),e.define,e.require}();
