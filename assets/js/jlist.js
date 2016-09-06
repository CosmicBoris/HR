/**
 * Created by boris on 06.09.2016.
 */

!function ($) {
    "use strict";

    var JList = function (element, options) {
        this.options = options;
        this.$element = $(element);
        this.$container = $('<div/>', { 'class': "jl-container" });
    };

    JList.prototype = {
        constructor: JList,
        init: function()
        {
            var element = this,
                jl      = this.$element;
        }
    };

    /* JLIST PLUGIN DEFINITION*/

    $.fn.jlist = function () {
        var option = arguments[0],
            args = arguments;

        return this.each(function () {
            var $this = $(this),
                data = $this.data('jlist'),
                options = $.extend({}, $.fn.jlist.defaults, $this.data(), typeof option === 'object' && option);

            if (!data){ $this.data('jlist', (data = new JList(this, options))); }

            if (typeof option === 'string'){
                data[option](args[1]);
            } else {
                data.init();
            }
        });
    };

    $.fn.jlist.defaults = {};

    $.fn.jlist.Constructor = JList;

    $.fn.insertAt = function(index, $parent) {
        return this.each(function() {
            if (index === 0) {
                $parent.prepend(this);
            } else {
                $parent.children().eq(index - 1).after(this);
            }
        });
    };
}(window.jQuery);