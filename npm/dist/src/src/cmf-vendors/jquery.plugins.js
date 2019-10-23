$._getComputedStyle = function (element, styleName) {
    var styles = window.parent ? window.parent.getComputedStyle(element, '') : window.getComputedStyle(element, '');
    return styles[styleName];
};

/**
 * Test if element has transition
 * @param element
 */
$.hasTransition = function (element) {
    var name = $._getComputedStyle(element, Modernizr.prefixed('transitionProperty'));
    return (name && name != '' && name != 'none');
};

/**
 * Test if element has animation
 * @param element
 */
$.hasAnimation = function (element) {
    var name = $._getComputedStyle(element, Modernizr.prefixed('animationName'));
    return (name && name != '' && name != 'none');
};

/**
 * Bind transitionEnd event and execute callback once, remove event handler after triggered (crossbrowser)
 * Runs callback at once if transitins not supported or not attached to element
 */
$.prototype.onTransitionEndOnce = function (callback) {
    this.each(function (index, item) {
        $(item).onTransitionEnd(function () {
            $(item).unbindTransitionEnd();
            callback();
        })
    });
    return this;
};

var animationListeners = 'transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd animationend webkitAnimationEnd MSAnimationEnd oanimationend oAnimationEnd';
/**
 * Unbind transition end events from all items
 */
$.prototype.unbindTransitionEnd = function () {
    this.each(function (index, item) {
        $(item).unbind(animationListeners);
    });
    return this;
};

/**
 * Bind transitionEnd event and execute callback each time it is triggered
 * Runs callback at once if transitins not supported or not attached to element
 */
$.prototype.onTransitionEnd = function (callback) {
    this.each(function (index, item) {
        console.log($.hasTransition(item));
        if (!$.hasTransition(item) && !$.hasAnimation(item)) {
            console.log(0);
            callback();
        } else {
            console.log(1);
            $(item).bind(animationListeners, callback);
        }
    });
    return this;
};