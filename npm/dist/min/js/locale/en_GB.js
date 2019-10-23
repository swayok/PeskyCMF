!function(e,t){"function"==typeof define&&define.amd?define(["jquery","query-builder"],t):t(e.jQuery)}(this,function(e){"use strict";var t=e.fn.queryBuilder;t.regional.en={__locale:"English (en)",__author:'Damien "Mistic" Sorel, http://www.strangeplanet.fr',add_rule:"Add rule",add_group:"Add group",delete_rule:"Delete",delete_group:"Delete",conditions:{AND:"AND",OR:"OR"},operators:{equal:"equal",not_equal:"not equal",in:"in",not_in:"not in",less:"less",less_or_equal:"less or equal",greater:"greater",greater_or_equal:"greater or equal",between:"between",not_between:"not between",begins_with:"begins with",not_begins_with:"doesn't begin with",contains:"contains",not_contains:"doesn't contain",ends_with:"ends with",not_ends_with:"doesn't end with",is_empty:"is empty",is_not_empty:"is not empty",is_null:"is null",is_not_null:"is not null"},errors:{no_filter:"No filter selected",empty_group:"The group is empty",radio_empty:"No value selected",checkbox_empty:"No value selected",select_empty:"No value selected",string_empty:"Empty value",string_exceed_min_length:"Must contain at least {0} characters",string_exceed_max_length:"Must not contain more than {0} characters",string_invalid_format:"Invalid format ({0})",number_nan:"Not a number",number_not_integer:"Not an integer",number_not_double:"Not a real number",number_exceed_min:"Must be greater than {0}",number_exceed_max:"Must be lower than {0}",number_wrong_step:"Must be a multiple of {0}",number_between_invalid:"Invalid values, {0} is greater than {1}",datetime_empty:"Empty value",datetime_invalid:"Invalid date format ({0})",datetime_exceed_min:"Must be after {0}",datetime_exceed_max:"Must be before {0}",datetime_between_invalid:"Invalid values, {0} is greater than {1}",boolean_not_valid:"Not a boolean",operator_not_multiple:'Operator "{1}" cannot accept multiple values'},invert:"Invert",NOT:"NOT"},t.defaults({lang_code:"en"})}),function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;e.define("select2/i18n/en",[],function(){return{errorLoading:function(){return"The results could not be loaded."},inputTooLong:function(e){var t=e.input.length-e.maximum,n="Please delete "+t+" character";return 1!=t&&(n+="s"),n},inputTooShort:function(e){return"Please enter "+(e.minimum-e.input.length)+" or more characters"},loadingMore:function(){return"Loading more results…"},maximumSelected:function(e){var t="You can only select "+e.maximum+" item";return 1!=e.maximum&&(t+="s"),t},noResults:function(){return"No results found"},searching:function(){return"Searching…"},removeAllItems:function(){return"Remove all items"}}}),e.define,e.require}(),function(e,t){void 0===e&&void 0!==window&&(e=window),"function"==typeof define&&define.amd?define(["jquery"],function(e){return t(e)}):"object"==typeof module&&module.exports?module.exports=t(require("jquery")):t(e.jQuery)}(this,function(e){!function(e){e.fn.selectpicker.defaults={noneSelectedText:"Nothing selected",noneResultsText:"No results match {0}",countSelectedText:function(e,t){return 1==e?"{0} item selected":"{0} items selected"},maxOptionsText:function(e,t){return[1==e?"Limit reached ({n} item max)":"Limit reached ({n} items max)",1==t?"Group limit reached ({n} item max)":"Group limit reached ({n} items max)"]},selectAllText:"Select All",deselectAllText:"Deselect All",multipleSeparator:", "}}(e)}),function(e){e.fn.ajaxSelectPicker.locale["en-US"]={currentlySelected:"Currently Selected",emptyTitle:"Select and begin typing",errorText:"Unable to retrieve results",searchPlaceholder:"Search...",statusInitialized:"Start typing a search query",statusNoResults:"No Results",statusSearching:"Searching...",statusTooShort:"Please enter more characters"},e.fn.ajaxSelectPicker.locale.en=e.fn.ajaxSelectPicker.locale["en-US"]}(jQuery),function(e,t){"object"==typeof exports&&"undefined"!=typeof module&&"function"==typeof require?t(require("../moment")):"function"==typeof define&&define.amd?define(["../moment"],t):t(e.moment)}(this,function(e){"use strict";return e.defineLocale("en-gb",{months:"January_February_March_April_May_June_July_August_September_October_November_December".split("_"),monthsShort:"Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),weekdays:"Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),weekdaysShort:"Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),weekdaysMin:"Su_Mo_Tu_We_Th_Fr_Sa".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD/MM/YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY HH:mm",LLLL:"dddd, D MMMM YYYY HH:mm"},calendar:{sameDay:"[Today at] LT",nextDay:"[Tomorrow at] LT",nextWeek:"dddd [at] LT",lastDay:"[Yesterday at] LT",lastWeek:"[Last] dddd [at] LT",sameElse:"L"},relativeTime:{future:"in %s",past:"%s ago",s:"a few seconds",ss:"%d seconds",m:"a minute",mm:"%d minutes",h:"an hour",hh:"%d hours",d:"a day",dd:"%d days",M:"a month",MM:"%d months",y:"a year",yy:"%d years"},dayOfMonthOrdinalParse:/\d{1,2}(st|nd|rd|th)/,ordinal:function(e){var t=e%10;return e+(1==~~(e%100/10)?"th":1===t?"st":2===t?"nd":3===t?"rd":"th")},week:{dow:1,doy:4}})});
