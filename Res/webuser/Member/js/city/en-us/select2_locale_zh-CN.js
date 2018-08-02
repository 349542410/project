/**
 * Select2 en translation
 */
(function ($) {
    "use strict";
    $.fn.select2.locales['zh-CN'] = {
        formatNoMatches: function () { return "No match is found"; },
        formatInputTooShort: function (input, min) { var n = min - input.length; return "Please enter" + n + "characters";},
        formatInputTooLong: function (input, max) { var n = input.length - max; return "Please delete" + n + "characters";},
        formatSelectionTooBig: function (limit) { return "You can choose at most" + limit + "items"; },
        formatLoadMore: function (pageNumber) { return "loading"; },
        formatSearching: function () { return "searching"; }
    };

    $.extend($.fn.select2.defaults, $.fn.select2.locales['zh-CN']);
})(jQuery);
