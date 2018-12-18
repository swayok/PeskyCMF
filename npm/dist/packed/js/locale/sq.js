/*!
 * jQuery QueryBuilder 2.5.2
 * Locale: Albanian (sq)
 * Author: Tomor Pupovci
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

QueryBuilder.regional['sq'] = {
  "__locale": "Albanian (sq)",
  "__author": "Tomor Pupovci",
  "add_rule": "Shto rregull",
  "add_group": "Shto grup",
  "delete_rule": "Fshij",
  "delete_group": "Fshij",
  "conditions": {
    "AND": "DHE",
    "OR": "OSE"
  },
  "operators": {
    "equal": "barabartë",
    "not_equal": "e ndryshme prej",
    "in": "në",
    "not_in": "jo në",
    "less": "më e vogël",
    "less_or_equal": "më e vogël ose e barabartë me",
    "greater": "më e madhe",
    "greater_or_equal": "më e madhe ose e barabartë",
    "between": "në mes",
    "begins_with": "fillon me",
    "not_begins_with": "nuk fillon me",
    "contains": "përmban",
    "not_contains": "nuk përmban",
    "ends_with": "mbaron me",
    "not_ends_with": "nuk mbaron me",
    "is_empty": "është e zbrazët",
    "is_not_empty": "nuk është e zbrazët",
    "is_null": "është null",
    "is_not_null": "nuk është null"
  },
  "errors": {
    "no_filter": "Nuk ka filter të zgjedhur",
    "empty_group": "Grupi është i zbrazët",
    "radio_empty": "Nuk ka vlerë të zgjedhur",
    "checkbox_empty": "Nuk ka vlerë të zgjedhur",
    "select_empty": "Nuk ka vlerë të zgjedhur",
    "string_empty": "Vlerë e zbrazët",
    "string_exceed_min_length": "Duhet të përmbajë së paku {0} karaktere",
    "string_exceed_max_length": "Nuk duhet të përmbajë më shumë se {0} karaktere",
    "string_invalid_format": "Format i pasaktë ({0})",
    "number_nan": "Nuk është numër",
    "number_not_integer": "Nuk është numër i plotë",
    "number_not_double": "Nuk është numër me presje",
    "number_exceed_min": "Duhet të jetë më i madh se {0}",
    "number_exceed_max": "Duhet të jetë më i vogël se {0}",
    "number_wrong_step": "Duhet të jetë shumëfish i {0}",
    "datetime_empty": "Vlerë e zbrazët",
    "datetime_invalid": "Format i pasaktë i datës ({0})",
    "datetime_exceed_min": "Duhet të jetë pas {0}",
    "datetime_exceed_max": "Duhet të jetë para {0}",
    "boolean_not_valid": "Nuk është boolean",
    "operator_not_multiple": "Operatori \"{1}\" nuk mund të pranojë vlera të shumëfishta"
  }
};

QueryBuilder.defaults({ lang_code: 'sq' });
}));
//! moment.js locale configuration

;(function (global, factory) {
   typeof exports === 'object' && typeof module !== 'undefined'
       && typeof require === 'function' ? factory(require('../moment')) :
   typeof define === 'function' && define.amd ? define(['../moment'], factory) :
   factory(global.moment)
}(this, (function (moment) { 'use strict';


    var sq = moment.defineLocale('sq', {
        months : 'Janar_Shkurt_Mars_Prill_Maj_Qershor_Korrik_Gusht_Shtator_Tetor_Nëntor_Dhjetor'.split('_'),
        monthsShort : 'Jan_Shk_Mar_Pri_Maj_Qer_Kor_Gus_Sht_Tet_Nën_Dhj'.split('_'),
        weekdays : 'E Diel_E Hënë_E Martë_E Mërkurë_E Enjte_E Premte_E Shtunë'.split('_'),
        weekdaysShort : 'Die_Hën_Mar_Mër_Enj_Pre_Sht'.split('_'),
        weekdaysMin : 'D_H_Ma_Më_E_P_Sh'.split('_'),
        weekdaysParseExact : true,
        meridiemParse: /PD|MD/,
        isPM: function (input) {
            return input.charAt(0) === 'M';
        },
        meridiem : function (hours, minutes, isLower) {
            return hours < 12 ? 'PD' : 'MD';
        },
        longDateFormat : {
            LT : 'HH:mm',
            LTS : 'HH:mm:ss',
            L : 'DD/MM/YYYY',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY HH:mm',
            LLLL : 'dddd, D MMMM YYYY HH:mm'
        },
        calendar : {
            sameDay : '[Sot në] LT',
            nextDay : '[Nesër në] LT',
            nextWeek : 'dddd [në] LT',
            lastDay : '[Dje në] LT',
            lastWeek : 'dddd [e kaluar në] LT',
            sameElse : 'L'
        },
        relativeTime : {
            future : 'në %s',
            past : '%s më parë',
            s : 'disa sekonda',
            ss : '%d sekonda',
            m : 'një minutë',
            mm : '%d minuta',
            h : 'një orë',
            hh : '%d orë',
            d : 'një ditë',
            dd : '%d ditë',
            M : 'një muaj',
            MM : '%d muaj',
            y : 'një vit',
            yy : '%d vite'
        },
        dayOfMonthOrdinalParse: /\d{1,2}\./,
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });

    return sq;

})));
