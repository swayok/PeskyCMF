/*! AdminLTE app.js
* ================
* Main JS application file for AdminLTE v2. This file
* should be included in all pages. It controls some layout
* options and implements exclusive AdminLTE plugins.
*
* @Author  Almsaeed Studio
* @Support <https://www.almsaeedstudio.com>
* @Email   <abdullah@almsaeedstudio.com>
* @version 2.4.8
* @repository git://github.com/almasaeed2010/AdminLTE.git
* @license MIT <http://opensource.org/licenses/MIT>
*/

// Make sure jQuery has been loaded
if (typeof jQuery === 'undefined') {
throw new Error('AdminLTE requires jQuery')
}

/* BoxRefresh()
 * =========
 * Adds AJAX content control to a box.
 *
 * @Usage: $('#my-box').boxRefresh(options)
 *         or add [data-widget="box-refresh"] to the box element
 *         Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.boxrefresh';

  var Default = {
    source         : '',
    params         : {},
    trigger        : '.refresh-btn',
    content        : '.box-body',
    loadInContent  : true,
    responseType   : '',
    overlayTemplate: '<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>',
    onLoadStart    : function () {
    },
    onLoadDone     : function (response) {
      return response;
    }
  };

  var Selector = {
    data: '[data-widget="box-refresh"]'
  };

  // BoxRefresh Class Definition
  // =========================
  var BoxRefresh = function (element, options) {
    this.element  = element;
    this.options  = options;
    this.$overlay = $(options.overlay);

    if (options.source === '') {
      throw new Error('Source url was not defined. Please specify a url in your BoxRefresh source option.');
    }

    this._setUpListeners();
    this.load();
  };

  BoxRefresh.prototype.load = function () {
    this._addOverlay();
    this.options.onLoadStart.call($(this));

    $.get(this.options.source, this.options.params, function (response) {
      if (this.options.loadInContent) {
        $(this.options.content).html(response);
      }
      this.options.onLoadDone.call($(this), response);
      this._removeOverlay();
    }.bind(this), this.options.responseType !== '' && this.options.responseType);
  };

  // Private

  BoxRefresh.prototype._setUpListeners = function () {
    $(this.element).on('click', Selector.trigger, function (event) {
      if (event) event.preventDefault();
      this.load();
    }.bind(this));
  };

  BoxRefresh.prototype._addOverlay = function () {
    $(this.element).append(this.$overlay);
  };

  BoxRefresh.prototype._removeOverlay = function () {
    $(this.element).remove(this.$overlay);
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, (data = new BoxRefresh($this, options)));
      }

      if (typeof data == 'string') {
        if (typeof data[option] == 'undefined') {
          throw new Error('No method named ' + option);
        }
        data[option]();
      }
    });
  }

  var old = $.fn.boxRefresh;

  $.fn.boxRefresh             = Plugin;
  $.fn.boxRefresh.Constructor = BoxRefresh;

  // No Conflict Mode
  // ================
  $.fn.boxRefresh.noConflict = function () {
    $.fn.boxRefresh = old;
    return this;
  };

  // BoxRefresh Data API
  // =================
  $(window).on('load', function () {
    $(Selector.data).each(function () {
      Plugin.call($(this));
    });
  });

}(jQuery);


/* BoxWidget()
 * ======
 * Adds box widget functions to boxes.
 *
 * @Usage: $('.my-box').boxWidget(options)
 *         This plugin auto activates on any element using the `.box` class
 *         Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.boxwidget';

  var Default = {
    animationSpeed : 500,
    collapseTrigger: '[data-widget="collapse"]',
    removeTrigger  : '[data-widget="remove"]',
    collapseIcon   : 'fa-minus',
    expandIcon     : 'fa-plus',
    removeIcon     : 'fa-times'
  };

  var Selector = {
    data     : '.box',
    collapsed: '.collapsed-box',
    header   : '.box-header',
    body     : '.box-body',
    footer   : '.box-footer',
    tools    : '.box-tools'
  };

  var ClassName = {
    collapsed: 'collapsed-box'
  };

  var Event = {
    collapsed: 'collapsed.boxwidget',
    expanded : 'expanded.boxwidget',
    removed  : 'removed.boxwidget'
  };

  // BoxWidget Class Definition
  // =====================
  var BoxWidget = function (element, options) {
    this.element = element;
    this.options = options;

    this._setUpListeners();
  };

  BoxWidget.prototype.toggle = function () {
    var isOpen = !$(this.element).is(Selector.collapsed);

    if (isOpen) {
      this.collapse();
    } else {
      this.expand();
    }
  };

  BoxWidget.prototype.expand = function () {
    var expandedEvent = $.Event(Event.expanded);
    var collapseIcon  = this.options.collapseIcon;
    var expandIcon    = this.options.expandIcon;

    $(this.element).removeClass(ClassName.collapsed);

    $(this.element)
      .children(Selector.header + ', ' + Selector.body + ', ' + Selector.footer)
      .children(Selector.tools)
      .find('.' + expandIcon)
      .removeClass(expandIcon)
      .addClass(collapseIcon);

    $(this.element).children(Selector.body + ', ' + Selector.footer)
      .slideDown(this.options.animationSpeed, function () {
        $(this.element).trigger(expandedEvent);
      }.bind(this));
  };

  BoxWidget.prototype.collapse = function () {
    var collapsedEvent = $.Event(Event.collapsed);
    var collapseIcon   = this.options.collapseIcon;
    var expandIcon     = this.options.expandIcon;

    $(this.element)
      .children(Selector.header + ', ' + Selector.body + ', ' + Selector.footer)
      .children(Selector.tools)
      .find('.' + collapseIcon)
      .removeClass(collapseIcon)
      .addClass(expandIcon);

    $(this.element).children(Selector.body + ', ' + Selector.footer)
      .slideUp(this.options.animationSpeed, function () {
        $(this.element).addClass(ClassName.collapsed);
        $(this.element).trigger(collapsedEvent);
      }.bind(this));
  };

  BoxWidget.prototype.remove = function () {
    var removedEvent = $.Event(Event.removed);

    $(this.element).slideUp(this.options.animationSpeed, function () {
      $(this.element).trigger(removedEvent);
      $(this.element).remove();
    }.bind(this));
  };

  // Private

  BoxWidget.prototype._setUpListeners = function () {
    var that = this;

    $(this.element).on('click', this.options.collapseTrigger, function (event) {
      if (event) event.preventDefault();
      that.toggle($(this));
      return false;
    });

    $(this.element).on('click', this.options.removeTrigger, function (event) {
      if (event) event.preventDefault();
      that.remove($(this));
      return false;
    });
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, (data = new BoxWidget($this, options)));
      }

      if (typeof option == 'string') {
        if (typeof data[option] == 'undefined') {
          throw new Error('No method named ' + option);
        }
        data[option]();
      }
    });
  }

  var old = $.fn.boxWidget;

  $.fn.boxWidget             = Plugin;
  $.fn.boxWidget.Constructor = BoxWidget;

  // No Conflict Mode
  // ================
  $.fn.boxWidget.noConflict = function () {
    $.fn.boxWidget = old;
    return this;
  };

  // BoxWidget Data API
  // ==================
  $(window).on('load', function () {
    $(Selector.data).each(function () {
      Plugin.call($(this));
    });
  });
}(jQuery);


/* ControlSidebar()
 * ===============
 * Toggles the state of the control sidebar
 *
 * @Usage: $('#control-sidebar-trigger').controlSidebar(options)
 *         or add [data-toggle="control-sidebar"] to the trigger
 *         Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.controlsidebar';

  var Default = {
    slide: true
  };

  var Selector = {
    sidebar: '.control-sidebar',
    data   : '[data-toggle="control-sidebar"]',
    open   : '.control-sidebar-open',
    bg     : '.control-sidebar-bg',
    wrapper: '.wrapper',
    content: '.content-wrapper',
    boxed  : '.layout-boxed'
  };

  var ClassName = {
    open : 'control-sidebar-open',
    fixed: 'fixed'
  };

  var Event = {
    collapsed: 'collapsed.controlsidebar',
    expanded : 'expanded.controlsidebar'
  };

  // ControlSidebar Class Definition
  // ===============================
  var ControlSidebar = function (element, options) {
    this.element         = element;
    this.options         = options;
    this.hasBindedResize = false;

    this.init();
  };

  ControlSidebar.prototype.init = function () {
    // Add click listener if the element hasn't been
    // initialized using the data API
    if (!$(this.element).is(Selector.data)) {
      $(this).on('click', this.toggle);
    }

    this.fix();
    $(window).resize(function () {
      this.fix();
    }.bind(this));
  };

  ControlSidebar.prototype.toggle = function (event) {
    if (event) event.preventDefault();

    this.fix();

    if (!$(Selector.sidebar).is(Selector.open) && !$('body').is(Selector.open)) {
      this.expand();
    } else {
      this.collapse();
    }
  };

  ControlSidebar.prototype.expand = function () {
    if (!this.options.slide) {
      $('body').addClass(ClassName.open);
    } else {
      $(Selector.sidebar).addClass(ClassName.open);
    }

    $(this.element).trigger($.Event(Event.expanded));
  };

  ControlSidebar.prototype.collapse = function () {
    $('body, ' + Selector.sidebar).removeClass(ClassName.open);
    $(this.element).trigger($.Event(Event.collapsed));
  };

  ControlSidebar.prototype.fix = function () {
    if ($('body').is(Selector.boxed)) {
      this._fixForBoxed($(Selector.bg));
    }
  };

  // Private

  ControlSidebar.prototype._fixForBoxed = function (bg) {
    bg.css({
      position: 'absolute',
      height  : $(Selector.wrapper).height()
    });
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, (data = new ControlSidebar($this, options)));
      }

      if (typeof option == 'string') data.toggle();
    });
  }

  var old = $.fn.controlSidebar;

  $.fn.controlSidebar             = Plugin;
  $.fn.controlSidebar.Constructor = ControlSidebar;

  // No Conflict Mode
  // ================
  $.fn.controlSidebar.noConflict = function () {
    $.fn.controlSidebar = old;
    return this;
  };

  // ControlSidebar Data API
  // =======================
  $(document).on('click', Selector.data, function (event) {
    if (event) event.preventDefault();
    Plugin.call($(this), 'toggle');
  });

}(jQuery);


/* DirectChat()
 * ===============
 * Toggles the state of the control sidebar
 *
 * @Usage: $('#my-chat-box').directChat()
 *         or add [data-widget="direct-chat"] to the trigger
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.directchat';

  var Selector = {
    data: '[data-widget="chat-pane-toggle"]',
    box : '.direct-chat'
  };

  var ClassName = {
    open: 'direct-chat-contacts-open'
  };

  // DirectChat Class Definition
  // ===========================
  var DirectChat = function (element) {
    this.element = element;
  };

  DirectChat.prototype.toggle = function ($trigger) {
    $trigger.parents(Selector.box).first().toggleClass(ClassName.open);
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        $this.data(DataKey, (data = new DirectChat($this)));
      }

      if (typeof option == 'string') data.toggle($this);
    });
  }

  var old = $.fn.directChat;

  $.fn.directChat             = Plugin;
  $.fn.directChat.Constructor = DirectChat;

  // No Conflict Mode
  // ================
  $.fn.directChat.noConflict = function () {
    $.fn.directChat = old;
    return this;
  };

  // DirectChat Data API
  // ===================
  $(document).on('click', Selector.data, function (event) {
    if (event) event.preventDefault();
    Plugin.call($(this), 'toggle');
  });

}(jQuery);


/* Layout()
 * ========
 * Implements AdminLTE layout.
 * Fixes the layout height in case min-height fails.
 *
 * @usage activated automatically upon window load.
 *        Configure any options by passing data-option="value"
 *        to the body tag.
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.layout';

  var Default = {
    slimscroll : true,
    resetHeight: true
  };

  var Selector = {
    wrapper       : '.wrapper',
    contentWrapper: '.content-wrapper',
    layoutBoxed   : '.layout-boxed',
    mainFooter    : '.main-footer',
    mainHeader    : '.main-header',
    sidebar       : '.sidebar',
    controlSidebar: '.control-sidebar',
    fixed         : '.fixed',
    sidebarMenu   : '.sidebar-menu',
    logo          : '.main-header .logo'
  };

  var ClassName = {
    fixed         : 'fixed',
    holdTransition: 'hold-transition'
  };

  var Layout = function (options) {
    this.options      = options;
    this.bindedResize = false;
    this.activate();
  };

  Layout.prototype.activate = function () {
    this.fix();
    this.fixSidebar();

    $('body').removeClass(ClassName.holdTransition);

    if (this.options.resetHeight) {
      $('body, html, ' + Selector.wrapper).css({
        'height'    : 'auto',
        'min-height': '100%'
      });
    }

    if (!this.bindedResize) {
      $(window).resize(function () {
        this.fix();
        this.fixSidebar();

        $(Selector.logo + ', ' + Selector.sidebar).one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function () {
          this.fix();
          this.fixSidebar();
        }.bind(this));
      }.bind(this));

      this.bindedResize = true;
    }

    $(Selector.sidebarMenu).on('expanded.tree', function () {
      this.fix();
      this.fixSidebar();
    }.bind(this));

    $(Selector.sidebarMenu).on('collapsed.tree', function () {
      this.fix();
      this.fixSidebar();
    }.bind(this));
  };

  Layout.prototype.fix = function () {
    // Remove overflow from .wrapper if layout-boxed exists
    $(Selector.layoutBoxed + ' > ' + Selector.wrapper).css('overflow', 'hidden');

    // Get window height and the wrapper height
    var footerHeight = $(Selector.mainFooter).outerHeight() || 0;
    var headerHeight  = $(Selector.mainHeader).outerHeight() || 0;
    var neg           = headerHeight + footerHeight;
    var windowHeight  = $(window).height();
    var sidebarHeight = $(Selector.sidebar).height() || 0;

    // Set the min-height of the content and sidebar based on
    // the height of the document.
    if ($('body').hasClass(ClassName.fixed)) {
      $(Selector.contentWrapper).css('min-height', windowHeight - footerHeight);
    } else {
      var postSetHeight;

      if (windowHeight >= sidebarHeight) {
        $(Selector.contentWrapper).css('min-height', windowHeight - neg);
        postSetHeight = windowHeight - neg;
      } else {
        $(Selector.contentWrapper).css('min-height', sidebarHeight);
        postSetHeight = sidebarHeight;
      }

      // Fix for the control sidebar height
      var $controlSidebar = $(Selector.controlSidebar);
      if (typeof $controlSidebar !== 'undefined') {
        if ($controlSidebar.height() > postSetHeight)
          $(Selector.contentWrapper).css('min-height', $controlSidebar.height());
      }
    }
  };

  Layout.prototype.fixSidebar = function () {
    // Make sure the body tag has the .fixed class
    if (!$('body').hasClass(ClassName.fixed)) {
      if (typeof $.fn.slimScroll !== 'undefined') {
        $(Selector.sidebar).slimScroll({ destroy: true }).height('auto');
      }
      return;
    }

    // Enable slimscroll for fixed layout
    if (this.options.slimscroll) {
      if (typeof $.fn.slimScroll !== 'undefined') {
        // Destroy if it exists
        // $(Selector.sidebar).slimScroll({ destroy: true }).height('auto')

        // Add slimscroll
        $(Selector.sidebar).slimScroll({
          height: ($(window).height() - $(Selector.mainHeader).height()) + 'px'
        });
      }
    }
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option === 'object' && option);
        $this.data(DataKey, (data = new Layout(options)));
      }

      if (typeof option === 'string') {
        if (typeof data[option] === 'undefined') {
          throw new Error('No method named ' + option);
        }
        data[option]();
      }
    });
  }

  var old = $.fn.layout;

  $.fn.layout            = Plugin;
  $.fn.layout.Constuctor = Layout;

  // No conflict mode
  // ================
  $.fn.layout.noConflict = function () {
    $.fn.layout = old;
    return this;
  };

  // Layout DATA-API
  // ===============
  $(window).on('load', function () {
    Plugin.call($('body'));
  });
}(jQuery);


/* PushMenu()
 * ==========
 * Adds the push menu functionality to the sidebar.
 *
 * @usage: $('.btn').pushMenu(options)
 *          or add [data-toggle="push-menu"] to any button
 *          Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.pushmenu';

  var Default = {
    collapseScreenSize   : 767,
    expandOnHover        : false,
    expandTransitionDelay: 200
  };

  var Selector = {
    collapsed     : '.sidebar-collapse',
    open          : '.sidebar-open',
    mainSidebar   : '.main-sidebar',
    contentWrapper: '.content-wrapper',
    searchInput   : '.sidebar-form .form-control',
    button        : '[data-toggle="push-menu"]',
    mini          : '.sidebar-mini',
    expanded      : '.sidebar-expanded-on-hover',
    layoutFixed   : '.fixed'
  };

  var ClassName = {
    collapsed    : 'sidebar-collapse',
    open         : 'sidebar-open',
    mini         : 'sidebar-mini',
    expanded     : 'sidebar-expanded-on-hover',
    expandFeature: 'sidebar-mini-expand-feature',
    layoutFixed  : 'fixed'
  };

  var Event = {
    expanded : 'expanded.pushMenu',
    collapsed: 'collapsed.pushMenu'
  };

  // PushMenu Class Definition
  // =========================
  var PushMenu = function (options) {
    this.options = options;
    this.init();
  };

  PushMenu.prototype.init = function () {
    if (this.options.expandOnHover
      || ($('body').is(Selector.mini + Selector.layoutFixed))) {
      this.expandOnHover();
      $('body').addClass(ClassName.expandFeature);
    }

    $(Selector.contentWrapper).click(function () {
      // Enable hide menu when clicking on the content-wrapper on small screens
      if ($(window).width() <= this.options.collapseScreenSize && $('body').hasClass(ClassName.open)) {
        this.close();
      }
    }.bind(this));

    // __Fix for android devices
    $(Selector.searchInput).click(function (e) {
      e.stopPropagation();
    });
  };

  PushMenu.prototype.toggle = function () {
    var windowWidth = $(window).width();
    var isOpen      = !$('body').hasClass(ClassName.collapsed);

    if (windowWidth <= this.options.collapseScreenSize) {
      isOpen = $('body').hasClass(ClassName.open);
    }

    if (!isOpen) {
      this.open();
    } else {
      this.close();
    }
  };

  PushMenu.prototype.open = function () {
    var windowWidth = $(window).width();

    if (windowWidth > this.options.collapseScreenSize) {
      $('body').removeClass(ClassName.collapsed)
        .trigger($.Event(Event.expanded));
    }
    else {
      $('body').addClass(ClassName.open)
        .trigger($.Event(Event.expanded));
    }
  };

  PushMenu.prototype.close = function () {
    var windowWidth = $(window).width();
    if (windowWidth > this.options.collapseScreenSize) {
      $('body').addClass(ClassName.collapsed)
        .trigger($.Event(Event.collapsed));
    } else {
      $('body').removeClass(ClassName.open + ' ' + ClassName.collapsed)
        .trigger($.Event(Event.collapsed));
    }
  };

  PushMenu.prototype.expandOnHover = function () {
    $(Selector.mainSidebar).hover(function () {
      if ($('body').is(Selector.mini + Selector.collapsed)
        && $(window).width() > this.options.collapseScreenSize) {
        this.expand();
      }
    }.bind(this), function () {
      if ($('body').is(Selector.expanded)) {
        this.collapse();
      }
    }.bind(this));
  };

  PushMenu.prototype.expand = function () {
    setTimeout(function () {
      $('body').removeClass(ClassName.collapsed)
        .addClass(ClassName.expanded);
    }, this.options.expandTransitionDelay);
  };

  PushMenu.prototype.collapse = function () {
    setTimeout(function () {
      $('body').removeClass(ClassName.expanded)
        .addClass(ClassName.collapsed);
    }, this.options.expandTransitionDelay);
  };

  // PushMenu Plugin Definition
  // ==========================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, (data = new PushMenu(options)));
      }

      if (option === 'toggle') data.toggle();
    });
  }

  var old = $.fn.pushMenu;

  $.fn.pushMenu             = Plugin;
  $.fn.pushMenu.Constructor = PushMenu;

  // No Conflict Mode
  // ================
  $.fn.pushMenu.noConflict = function () {
    $.fn.pushMenu = old;
    return this;
  };

  // Data API
  // ========
  $(document).on('click', Selector.button, function (e) {
    e.preventDefault();
    Plugin.call($(this), 'toggle');
  });
  $(window).on('load', function () {
    Plugin.call($(Selector.button));
  });
}(jQuery);


/* TodoList()
 * =========
 * Converts a list into a todoList.
 *
 * @Usage: $('.my-list').todoList(options)
 *         or add [data-widget="todo-list"] to the ul element
 *         Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.todolist';

  var Default = {
    onCheck  : function (item) {
      return item;
    },
    onUnCheck: function (item) {
      return item;
    }
  };

  var Selector = {
    data: '[data-widget="todo-list"]'
  };

  var ClassName = {
    done: 'done'
  };

  // TodoList Class Definition
  // =========================
  var TodoList = function (element, options) {
    this.element = element;
    this.options = options;

    this._setUpListeners();
  };

  TodoList.prototype.toggle = function (item) {
    item.parents(Selector.li).first().toggleClass(ClassName.done);
    if (!item.prop('checked')) {
      this.unCheck(item);
      return;
    }

    this.check(item);
  };

  TodoList.prototype.check = function (item) {
    this.options.onCheck.call(item);
  };

  TodoList.prototype.unCheck = function (item) {
    this.options.onUnCheck.call(item);
  };

  // Private

  TodoList.prototype._setUpListeners = function () {
    var that = this;
    $(this.element).on('change ifChanged', 'input:checkbox', function () {
      that.toggle($(this));
    });
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, (data = new TodoList($this, options)));
      }

      if (typeof data == 'string') {
        if (typeof data[option] == 'undefined') {
          throw new Error('No method named ' + option);
        }
        data[option]();
      }
    });
  }

  var old = $.fn.todoList;

  $.fn.todoList             = Plugin;
  $.fn.todoList.Constructor = TodoList;

  // No Conflict Mode
  // ================
  $.fn.todoList.noConflict = function () {
    $.fn.todoList = old;
    return this;
  };

  // TodoList Data API
  // =================
  $(window).on('load', function () {
    $(Selector.data).each(function () {
      Plugin.call($(this));
    });
  });

}(jQuery);


/* Tree()
 * ======
 * Converts a nested list into a multilevel
 * tree view menu.
 *
 * @Usage: $('.my-menu').tree(options)
 *         or add [data-widget="tree"] to the ul element
 *         Pass any option as data-option="value"
 */
+function ($) {
  'use strict';

  var DataKey = 'lte.tree';

  var Default = {
    animationSpeed: 500,
    accordion     : true,
    followLink    : false,
    trigger       : '.treeview a'
  };

  var Selector = {
    tree        : '.tree',
    treeview    : '.treeview',
    treeviewMenu: '.treeview-menu',
    open        : '.menu-open, .active',
    li          : 'li',
    data        : '[data-widget="tree"]',
    active      : '.active'
  };

  var ClassName = {
    open: 'menu-open',
    tree: 'tree'
  };

  var Event = {
    collapsed: 'collapsed.tree',
    expanded : 'expanded.tree'
  };

  // Tree Class Definition
  // =====================
  var Tree = function (element, options) {
    this.element = element;
    this.options = options;

    $(this.element).addClass(ClassName.tree);

    $(Selector.treeview + Selector.active, this.element).addClass(ClassName.open);

    this._setUpListeners();
  };

  Tree.prototype.toggle = function (link, event) {
    var treeviewMenu = link.next(Selector.treeviewMenu);
    var parentLi     = link.parent();
    var isOpen       = parentLi.hasClass(ClassName.open);

    if (!parentLi.is(Selector.treeview)) {
      return;
    }

    if (!this.options.followLink || link.attr('href') === '#') {
      event.preventDefault();
    }

    if (isOpen) {
      this.collapse(treeviewMenu, parentLi);
    } else {
      this.expand(treeviewMenu, parentLi);
    }
  };

  Tree.prototype.expand = function (tree, parent) {
    var expandedEvent = $.Event(Event.expanded);

    if (this.options.accordion) {
      var openMenuLi = parent.siblings(Selector.open);
      var openTree   = openMenuLi.children(Selector.treeviewMenu);
      this.collapse(openTree, openMenuLi);
    }

    parent.addClass(ClassName.open);
    tree.slideDown(this.options.animationSpeed, function () {
      $(this.element).trigger(expandedEvent);
    }.bind(this));
  };

  Tree.prototype.collapse = function (tree, parentLi) {
    var collapsedEvent = $.Event(Event.collapsed);

    //tree.find(Selector.open).removeClass(ClassName.open);
    parentLi.removeClass(ClassName.open);
    tree.slideUp(this.options.animationSpeed, function () {
      //tree.find(Selector.open + ' > ' + Selector.treeview).slideUp();
      $(this.element).trigger(collapsedEvent);
    }.bind(this));
  };

  // Private

  Tree.prototype._setUpListeners = function () {
    var that = this;

    $(this.element).on('click', this.options.trigger, function (event) {
      that.toggle($(this), event);
    });
  };

  // Plugin Definition
  // =================
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data  = $this.data(DataKey);

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option == 'object' && option);
        $this.data(DataKey, new Tree($this, options));
      }
    });
  }

  var old = $.fn.tree;

  $.fn.tree             = Plugin;
  $.fn.tree.Constructor = Tree;

  // No Conflict Mode
  // ================
  $.fn.tree.noConflict = function () {
    $.fn.tree = old;
    return this;
  };

  // Tree Data API
  // =============
  $(window).on('load', function () {
    $(Selector.data).each(function () {
      Plugin.call($(this));
    });
  });

}(jQuery);

(function(global) {

'use strict';

global.page = page;

page.isSameOrigin = isSameOrigin;
page.Request = Request;
page.Route = Route;
page.pathToRegexp = pathToRegexp;
page.queryString = queryString;
page.Deferred = Deferred;

/**
 * Detect click event
 */
var clickEvent = ('undefined' !== typeof document) && document.ontouchstart ? 'touchstart' : 'click';

/**
 * To work properly with the URL
 * history.location generated polyfill in https://github.com/devote/HTML5-History-API
 */
var location = ('undefined' !== typeof window) && (window.history.location || window.location);

var isArray = isArray || function (arr) {
    return Object.prototype.toString.call(arr) === '[object Array]';
};

var normalizeArguments = function(args) {
    if (!args) {
        return [];
    } else if (typeof args === 'object' && args.length) {
        return isArray(args) ? args : (args.length === 1 ? [args[0]] : Array.apply(null, args));
    } else {
        return [args];
    }
};

/**
 * Decode URL components (query string, pathname, hash).
 * Accommodates both regular percent encoding and x-www-form-urlencoded format.
 */
var decodeURLComponents = true;

/**
 * Decode URL query string into object for every request
 * @type {boolean}
 */
var decodeURLQuery = false;

/**
 * Base path.
 */
var base = '';

/**
 * Running flag.
 */
var running;

/**
 * Previous request, for capturing
 * page exit events.
 * @type {Request}
 */
var previousRequest;

/**
 * Current request
 * @type {Request}
 */
var currentRequest = {
    promise: Deferred().resolve().promise()
};

/**
 * Amount of Request objects created. Used to identify requests via Request.id() or Request.isSameAs(otherRequest)
 * @type {number}
 */
var requestsCreated = 0;

/**
 * Amount of push states
 * @type {number}
 */
var navs = 0;

/**
 * Shortcut for `page.start(options)`.
 * @param {!Object} options
 * @api public
 */
function page(options) {
    page.start(options);
}

/**
 * Callback functions.
 */

var callbacks = [];
var exits = [];
var errorHandlers = [];
var routeNotFoundHandlers = [];

/**
 * Returns current request
 * @return {Request}
 */
page.currentRequest = function () {
    return currentRequest;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.currentUrlWithoutBase = function () {
    return page.currentRequest().path;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.currentUrl = function () {
    return page.currentRequest().fullUrl(true);
};

/**
 * Returns previous request
 * @return {Request}
 */
page.previousRequest = function () {
    return previousRequest;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.previousUrlWithoutBase = function () {
    return page.previousRequest().path;
};

/**
 * Returns current URL without part provided via page.base()
 * @return {string}
 */
page.previousUrl = function () {
    return page.previousRequest().fullUrl(true);
};

/**
 * Get or set basepath to `path`.
 *
 * @param {string} path
 * @api public
 */
page.base = function (path) {
    if (0 === arguments.length) {
        return base;
    }
    base = path;
};

/**
 * Bind with the given `options`.
 *
 * Options:
 *
 *    - `click` - bool, bind to click events [true]
 *    - `popstate` - bool, bind to popstate [true]
 *    - `dispatch` - bool, perform initial dispatch [true]
 *    - `decodeURLComponents` - bool, remove URL encoding from URL components [true]
 *    - `decodeURLQuery` - bool, convert query string to object for each request request [false]
 *
 * @param {Object} options
 * @api public
 */
page.start = function (options) {
    options = options || {};
    if (running) {
        return;
    }
    running = true;
    navs = 0;
    if (options.decodeURLComponents === false) {
        decodeURLComponents = false;
    }
    if (options.decodeURLQuery) {
        decodeURLQuery = true;
    }
    if (options.popstate !== false) {
        window.addEventListener('popstate', onpopstate, false);
    }
    if (options.click !== false) {
        document.addEventListener(clickEvent, onclick, false);
    }
    page.show(
        getDocumentUrl(true),
        undefined,
        options.dispatch !== false,
        true,
        {env: {is_first: true}}
    );
};

/**
 * Unbind click and popstate event handlers.
 *
 * @api public
 */
page.stop = function () {
    if (!running) {
        return;
    }
    currentRequest = null;
    previousRequest = null;
    running = false;
    document.removeEventListener(clickEvent, onclick, false);
    window.removeEventListener('popstate', onpopstate, false);
};

/**
 * Declare route.
 * Note: You can pass many fn
 *
 * @param {string} path
 * @param {Function=} fn1
 * @param {Function=} fn2
 * @param {Function=} fnx
 * @api public
 */
page.route = function (path, fn1, fn2, fnx) {
    if (typeof path !== 'string') {
        throw new TypeError('1st argument passed to page.route() must be a string. Use \'*\' if you want to apply callbacks to all routes');
    }
    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        if (typeof arguments[i] !== 'function') {
            throw new TypeError('argument ' + (i + 1) + ' passed to page.route() for route ' + path + ' is not a funciton');
        }
        callbacks.push(route.middleware(arguments[i], 'route_handler'));
    }
};

/**
 * Register an error handler on `path` with callback `fn()`,
 * which will be called on error during matching request.path
 * @param {string} path
 * @param {Function=} fn
 * @api public
 */
page.error = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        errorHandlers.push(route.middleware(arguments[i], 'error_handler'));
    }
};

/**
 * Register a page not found handler on `path` with callback `fn()`,
 * which will be called if there is no route that matches request.path
 * but matches provided `path`
 * @param {string} path
 * @param {Function=} fn
 * @api public
 */
page.notFound = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; i++) {
        routeNotFoundHandlers.push(route.middleware(arguments[i], 'page_not_found_handler'));
    }
};

/**
 * Register an exit route on `path` with callback `fn()`,
 * which will be called on the previous request when a new
 * page is visited.
 */
page.exit = function (path, fn) {
    if (typeof path === 'function') {
        return page.exit('*', path);
    }

    if (typeof fn !== 'function') {
        throw new TypeError('2nd argument must be a function');
    }

    var route = new Route(path);
    for (var i = 1; i < arguments.length; ++i) {
        exits.push(route.middleware(arguments[i], 'exit'));
    }
};

function checkIfStarted() {
    if (!running) {
        throw new Error('Attempt to navigate before router is started');
    }
}

function getDocumentUrl(withHashbang) {
    return window.location.pathname + window.location.search + (withHashbang ? window.location.hash : '');
}

/**
 * Show `path` with optional `state` object.
 *
 * @param {string} path
 * @param {Object=} state
 * @param {boolean=} dispatch
 * @param {boolean=} push - not used anymore
 * @param {Object=} customData
 * @return {!Request}
 * @api public
 */
page.show = function (path, state, dispatch, push, customData) {
    checkIfStarted();
    var request = new Request(path, state, customData);
    processRequest(request, dispatch/*, push*/);
    return request;
};

/**
 * Goes back in the history
 * Back should always let the current route push state and then go back.
 *
 * @param {string} fallbackPath - fallback path to go back if no more history exists, if undefined defaults to page.base
 * @param {Object=} state
 * @api public
 */
page.back = function (fallbackPath, state) {
    checkIfStarted();
    if (navs > 1 && window.history.length > 0) {
        // navs > 1 conditions is required to prevent unexpected backs in case when router was just launched and there
        // is no information if previous URL has same domain and base path.
        currentRequest.promise.always(function () {
            navs--;
            window.history.back();
        });
    } else if (fallbackPath) {
        page.show(fallbackPath, state, true, true);
    } else {
        page.show(base, state, true, true);
    }
};

/**
 * Reload current page
 *
 * @api public
 */
page.reload = function () {
    checkIfStarted();
    if (!currentRequest || !currentRequest.path) {
        throw new Error('Attempt to reload page before router has dispatched at least one page')
    }
    page.show(document.location.href, undefined, true, true, {env: {is_reload: true}});
};

/**
 * Replace current request by new one using `path` and optional `state` object.
 *
 * @param {string} path
 * @param {Object=} state
 * @param {boolean=} dispatch
 * @param {Object=} customData
 * @return {Request}
 * @api public
 */
page.replace = function (path, state, dispatch, customData) {
    var request = new Request(path, state, customData);
    //request.push = false; //< it does not change url
    currentRequest.promise.always(function () {
        request.saveState();
    });
    if (dispatch !== false) {
        request.dispatch();
    }
    return request;
};

/**
 * Restore request.
 *
 * @param {Request=} request
 * @param {boolean=} dispatch
 * //@param {boolean=} push
 * @api public
 */
page.restoreRequest = function (request, dispatch/*, push*/) {
    if (request.customData) {
        request.customData.env = {};
    }
    request.customData.env.is_restore = true;
    processRequest(request, dispatch/*, push*/);
};

/**
 * Execute request.
 *
 * @param {Request=} request
 * @param {boolean=} dispatch
 * //@param {boolean=} push
 * @api private
 */
function processRequest(request, dispatch/*, push*/) {
    // if (push === false) {
    //     request.push = false;
    // }
    if (dispatch !== false) {
        request.dispatch();
    } else /*if (request.push)*/ {
        request.pushState();
    }
}

/**
 * Remove URL encoding from the given `str`.
 * Accommodates whitespace in both x-www-form-urlencoded
 * and regular percent-encoded form.
 *
 * @param {string} val - URL component to decode
 */
function decodeURLEncodedURIComponent(val) {
    if (typeof val !== 'string') {
        return val;
    }
    return decodeURLComponents ? decodeURIComponent(val.replace(/\+/g, ' ')) : val;
}

/**
 * Default handler for requests that do not have matching routes
 * Called only when there is no matching route not found handlers provided via page.notFound(path, fn)
 *
 * @param {Request} request
 */
function routeNotFound(request) {
    var current = getDocumentUrl(true);
    if (current !== request.originalPath && current !== request.fullUrl()) {
        document.location = request.originalPath;
    }
}

/**
 * Initialize a new "request" `Request`
 * with the given `path` and optional initial `state`.
 *
 * @constructor
 * @param {string} path
 * @param {Object=} state
 * @param {Object=} customData
 * @api public
 */
function Request(path, state, customData) {
    requestsCreated++;
    var id = requestsCreated;

    this.id = function () {
        return id;
    };

    this.originalPath = path;

    if (path.indexOf('http') === 0 && isSameOrigin(path)) {
        // absolute url provided: remove "https?://doma.in" from path
        path = path.replace(/https?:\/\/.+?\//, '/');
    }

    if (path[0] === '/' && path.indexOf(base) !== 0) {
        path = base + path;
    }
    var i = path.indexOf('?');

    this.title = document.title;
    this.state = state || {};
    this.querystring = ~i ? decodeURLEncodedURIComponent(path.slice(i + 1)) : '';
    this.pathname = decodeURLEncodedURIComponent(~i ? path.slice(0, i) : path);
    this.path = this.pathname.replace(base, '') || '/';
    this.params = {};
    this.customData = (customData && (typeof customData === 'object')) ? customData : {};
    if (!this.customData.env || typeof this.customData.env !== 'object') {
        this.customData.env = {};
    }

    this.promise = null;

    //this.push = true;
    this.routeFound = false;
    this.routeNotFoundHandled = false;
    this.error = false;
    this.errorHandled = false;

    this.parentRequest = null;  //< current request is subrequest of this request
    this.subRequest = null;     //< current request has a subrequest

    // fragment
    this.hash = '';
    if (~this.originalPath.indexOf('#')) {
        var parts = this.originalPath.split('#');
        this.hash = decodeURLEncodedURIComponent(parts[1]) || '';
        this.querystring = this.querystring.split('#', 2)[0];
        this.path = this.path.split('#', 2)[0];
        this.pathname = this.pathname.split('#', 2)[0];
        if (this.hash[0] === '!') {
            this.subRequest = new Request(this.hash.slice(1));
            this.subRequest.parentRequest = this;
            this.hash = '';
        }
    }

    if (decodeURLQuery) {
        this.query = queryString(this.querystring);
    }

}

/**
 * Reload page using url and state of this request
 */
Request.prototype.reload = function () {
    page.show(currentRequest.fullUrl(), currentRequest.state, true, true, {env: {is_reload: true}});
};

/**
 * Compare paths and query strings of 2 requests
 * @param {Request} otherRequest
 * @return {boolean}
 */
Request.prototype.hasSamePathAs = function (otherRequest) {
    return typeof otherRequest === 'object' && this.path === otherRequest.path && this.querystring === otherRequest.querystring;
};

/**
 * Make full URL for this request
 *
 * @param {boolean=} withHashbang - false: URL will not include hasbang part [default: true]
 * @return {string}
 */
Request.prototype.fullUrl = function (withHashbang) {
    if (this.isSubRequest()) {
        return this.parentRequest.fullUrl();
    } else {
        var url = base + this.path;
        if (this.querystring.length > 0) {
            url += '?' + this.querystring;
        }
        if (withHashbang !== false) {
            if (this.hasSubRequest()) {
                url += '#!' + this.subRequest.makeUrlToUseItInParentRequest();
            } else if (this.hash.length > 0) {
                url += '#' + this.hash;
            }
        }
        return url;
    }
};

/**
 * Make URL for this request. This URL may be used in hasbang of parent request
 *
 * @return {string}
 */
Request.prototype.makeUrlToUseItInParentRequest = function () {
    var url = base + this.path;
    if (this.querystring.length > 0) {
        url += '?' + this.querystring;
    }
    return url;
};

/**
 * Get requests unique id
 *
 * @return {integer}
 */
Request.prototype.id = function () {
    throw new Error('This method should never be called. Something wrong happened.');
};

/**
 * Compare unique ids of 2 requests
 *
 * @param {Request} otherRequest
 * @return {boolean}
 */
Request.prototype.isSameAs = function (otherRequest) {
    return this.id() === otherRequest.id();
};

/**
 * Get environment-related data for this request
 * @return {object}
 */
Request.prototype.env = function () {
    return this.customData.env || {};
};

/**
 * Push state if current url differs from new one (otherwise - history.replaceState() will be used instead)
 * @param {boolean=} force - true: ignore url equality testing and force history.pushState()
 * @api private
 */
Request.prototype.pushState = function (force) {
    var url = this.fullUrl(true);
    if (force || getDocumentUrl(true) !== url) {
        navs++;
        history.pushState(this.state, this.title, url);
    } else {
        this.saveState();
    }
};

/**
 * Save the request state.
 *
 * @api public
 */
Request.prototype.saveState = function () {
    history.replaceState(this.state, this.title, this.fullUrl(true));
};

/**
 * Convert this request to subrequest.
 * This will modify only hash part of current page's address when calling pushState()
 */
Request.prototype.convertToSubrequest = function () {
    if (!currentRequest.isSameAs(this)) {
        this.parentRequest = currentRequest;
    } else {
        this.parentRequest = previousRequest;
    }
    this.parentRequest.setSubRequest(this);
};

/**
 * Check if request is handled by modal dialog
 */
Request.prototype.isSubRequest = function () {
    return !!this.parentRequest;
};

/**
 * Check if request has subrequest
 */
Request.prototype.hasSubRequest = function () {
    return !!this.subRequest;
};

/**
 * Restore parent request
 * @param dispatch
 */
Request.prototype.restoreParentRequest = function (dispatch) {
    if (this.isSubRequest() && currentRequest.hasSubRequest() && currentRequest.isSameAs(this.parentRequest)) {
        this.parentRequest.removeSubRequest();
        if (dispatch === false) {
            //if (this.parentRequest.push) {
                this.parentRequest.pushState();
            //}
        } else {
            this.parentRequest.customData.env.is_restore = true;
            this.parentRequest.dispatch();
        }
    }
};

/**
 * Set sub request for current request
 * @param request
 */
Request.prototype.setSubRequest = function (request) {
    this.subRequest = request;
    this.parentRequest = null;
    request.removeSubRequest();
};

/**
 * Remove sub request from current request
 */
Request.prototype.removeSubRequest = function () {
    this.subRequest = null;
    //this.push = true;
};
/**
 * Dispatch the given `request`.
 * @return {Deferred.promise}
 * @api public
 */
Request.prototype.dispatch = function () {
    var request = this;
    var deferred = Deferred();
    var currentRequestBackup = currentRequest && currentRequest.backup ? currentRequest.backup() : currentRequest;
    var prevRequestBackup = previousRequest && previousRequest.backup ? previousRequest.backup() : previousRequest;

    // note: currentRequest may potentially be same as request wuth same 'promise' property
    // so to avoid deadlocking we need to store currentRequest.promise into variable before setting new request.promise
    var currentPromise = currentRequest.promise;
    request.promise = deferred.promise();

    currentPromise.always(function () {
        previousRequest = currentRequest;
        currentRequest = request;

        Deferred
            .queue(
                previousRequest && previousRequest.path ? exits : [],
                previousRequest,
                [previousRequest, request]
            )
            .done(function () {
                Deferred
                    .queue(callbacks, request, [request])
                    .done(function () {
                        deferred.resolve();
                    })
                    .fail(function () {
                        deferred.reject.apply(deferred, arguments);
                    });
            })
            .fail(function () {
                deferred.reject.apply(deferred, arguments);
            });

        request.promise
            .done(function () {
                if (request.routeFound) {
                    // if (request.push) {
                        request.pushState();
                    // }
                    delete request.customData.env.is_restore; //< not needed anymore
                    if (request.hasSubRequest()) {
                        var prevRequest = previousRequest;
                        setTimeout(function () {
                            request.subRequest.dispatch().always(function () {
                                previousRequest = prevRequest;
                                currentRequest = request;
                            });
                        }, 300);
                    } else if (request.isSubRequest()) {
                        previousRequest = prevRequestBackup;
                        currentRequest = request.parentRequest;
                    }
                } else {
                    Deferred
                        .queue(routeNotFoundHandlers, request, [request])
                        .done(function () {
                            if (!request.routeNotFoundHandled) {
                                routeNotFound(request);
                                request.routeNotFoundHandled = true;
                            }
                        });
                }
            })
            .fail(function (error) {
                Deferred
                    .queue(errorHandlers, request, [request])
                    .done(function () {
                        if (!request.errorHandled) {
                            request.errorHandled = true;
                            console.error('Error occured while handling a request to ' + request.originalPath);
                            console.groupCollapsed('Error details');
                            console.warn('request', request);
                            console.warn('request error', error);
                            console.groupEnd();
                        }
                    })
                    .fail(function () {
                        console.error('Error occured while handling a request and error' + request.originalPath);
                        console.groupCollapsed('Error details');
                        console.warn('request', request);
                        console.warn('request error', error);
                        console.warn('error handler fail info:', arguments);
                        console.groupEnd();
                        request.errorHandled = true;
                    })
                    .always(function () {
                        // restore previous state of requests
                        currentRequest = currentRequestBackup;
                        previousRequest = prevRequestBackup;
                    });
            });
        });

    return request.promise;
};

/**
 * Clone this Request
 * @return {Request}
 */
Request.prototype.clone = function () {
    return new Request(this.fullUrl(), $.extend(true, {}, this.state), $.extend(true, {}, this.customData));
};

/**
 * Clone this Request preserving promise
 * @return {Request}
 */
Request.prototype.backup = function () {
    var request = this.clone();
    request.promise = this.promise;
    return request;
};

/**
 * Initialize `Route` with the given HTTP `path`,
 * and an array of `callbacks` and `options`.
 *
 * Options:
 *
 *   - `sensitive`    enable case-sensitive routes
 *   - `strict`       enable strict matching for trailing slashes
 *
 * @constructor
 * @param {string} path
 * @param {Object=} options
 * @api private
 */
function Route(path, options) {
    options = options || {};
    this.isWildcard = (path === '*');
    this.path = this.isWildcard ? '(.*)' : path;
    this.method = 'GET';
    this.regexp = pathToRegexp(this.path, this.keys = [], options);
}

/**
 * Return route middleware with
 * the given callback `fn()`.
 *
 * @param {Function} fn
 * @param {string} type - type of function: route_handler, route_not_found_handler, exit, error_handler
 * @return {Function}
 * @api public
 */
Route.prototype.middleware = function (fn, type) {
    var self = this;
    return function (request) {
        var args = arguments;
        return Deferred(function (deferred) {
            if (self.match(request.path, request.params)) {
                // placed here to be able to rollback these values in callbacks
                if (type === 'route_handler' && !self.isWildcard) {
                    request.routeFound = true;
                } else if (type === 'route_not_found_handler') {
                    request.routeNotFoundHandled = true;
                } else if (type === 'error_handler') {
                    request.errorHandled = true;
                }
                try {
                    var ret = fn.apply(request, args);
                    if (typeof ret === 'object' && typeof ret.then === 'function') {
                        ret.then(
                            function () {
                                deferred.resolve();
                            },
                            function () {
                                deferred.reject.apply(deferred, arguments);
                            }
                        );
                    } else {
                        deferred.resolve();
                    }
                } catch (exc) {
                    deferred.reject(exc);
                }
            } else {
                deferred.resolve();
            }
        });
    };
};

/**
 * Check if this route matches `path`, if so
 * populate `params`.
 *
 * @param {string} path
 * @param {Object} params
 * @return {boolean}
 * @api private
 */
Route.prototype.match = function (path, params) {
    var keys = this.keys,
        qsIndex = path.indexOf('?'),
        pathname = ~qsIndex ? path.slice(0, qsIndex) : path,
        m = this.regexp.exec(decodeURIComponent(pathname));

    if (!m) {
        return false;
    }

    for (var i = 1, len = m.length; i < len; ++i) {
        var key = keys[i - 1];
        var val = decodeURLEncodedURIComponent(m[i]);
        if (val !== undefined || !(Object.prototype.hasOwnProperty.call(params, key.name))) {
            params[key.name] = val;
        }
    }

    return true;
};


/**
 * Handle "populate" events.
 */
var onpopstate = (function () {
    var loaded = false;
    if ('undefined' === typeof window) {
        return;
    }
    if (document.readyState === 'complete') {
        loaded = true;
    } else {
        window.addEventListener('load', function () {
            setTimeout(function () {
                loaded = true;
            }, 0);
        });
    }
    return function onpopstate(e) {
        if (!loaded) {
            return;
        }
        page.show(getDocumentUrl(true), e.state || null, true, false, {env: {is_history: true}});
    };
})();

/**
 * Handle "click" events.
 */
function onclick(e) {

    if (which(e) !== 1) {
        return;
    }

    if (e.metaKey || e.ctrlKey || e.shiftKey) {
        return;
    }
    if (e.defaultPrevented) {
        return;
    }


    // ensure link
    // use shadow dom when available
    var el = e.path ? e.path[0] : e.target;
    while (el && el.nodeName !== 'A') {
        el = el.parentNode;
    }
    if (!el || el.nodeName !== 'A') {
        return;
    }


    // Ignore if tag has
    // 1. "download" attribute
    // 2. rel="external" attribute
    if (el.hasAttribute('download') || el.getAttribute('rel') === 'external') {
        return;
    }

    // ensure non-hash for the same path
    var link = el.getAttribute('href');
    if (el.pathname === window.location.pathname && (el.hash || '#' === link)) {
        return;
    }


    // Check for mailto: in the href
    if (link && link.indexOf('mailto:') > -1) {
        return;
    }

    // check target
    if (el.target) {
        return;
    }

    // x-origin
    if (!isSameOrigin(el.href)) {
        return;
    }

    // rebuild path
    var path = el.pathname + el.search + (el.hash || '');

    // same page
    var orig = path;

    if (path.indexOf(base) === 0) {
        path = path.substr(base.length);
    }

    if (base && orig === path) {
        return;
    }

    e.preventDefault();
    page.show(orig, undefined, true, true, {env: {is_click: true, target: this.activeElement || e.target}});
}

/**
 * Event button.
 */
function which(e) {
    e = e || window.event;
    return null === e.which ? e.button : e.which;
}

/**
 * Check if `href` is the same origin.
 */

function isSameOrigin(href) {
    var origin = window.location.protocol + '//' + window.location.hostname;
    if (window.location.port) {
        origin += ':' + window.location.port;
    }
    return (href && (0 === href.indexOf(origin)));
}

/**
 * Normalize the given path string, returning a regular expression.
 *
 * An empty array can be passed in for the keys, which will hold the
 * placeholder key descriptions. For example, using `/user/:id`, `keys` will
 * contain `[{ name: 'id', delimiter: '/', optional: false, repeat: false }]`.
 *
 * @param  {(String|RegExp|Array)} path
 * @param  {Array}                 [keys]
 * @param  {Object}                [options]
 * @return {RegExp}
 */
function pathToRegexp(path, keys, options) {

    /**
     * Compile a string to a template function for the path.
     *
     * @param  {String}   str
     * @return {Function}
     */
    pathToRegexp.compile = compile;

    /**
     * The main path matching regexp utility.
     *
     * @type {RegExp}
     */
    var PATH_REGEXP = new RegExp([
        // Match escaped characters that would otherwise appear in future matches.
        // This allows the user to escape special characters that won't transform.
        '(\\\\.)',
        // Match Express-style parameters and un-named parameters with a prefix
        // and optional suffixes. Matches appear as:
        //
        // "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?", undefined]
        // "/route(\\d+)"  => [undefined, undefined, undefined, "\d+", undefined, undefined]
        // "/*"            => ["/", undefined, undefined, undefined, undefined, "*"]
        '([\\/.])?(?:(?:\\:(\\w+)(?:\\(((?:\\\\.|[^()])+)\\))?|\\(((?:\\\\.|[^()])+)\\))([+*?])?|(\\*))'
    ].join('|'), 'g');


    keys = keys || [];

    if (!isArray(keys)) {
        options = keys;
        keys = [];
    } else if (!options) {
        options = {};
    }

    if (path instanceof RegExp) {
        return regexpToRegexp(path, keys);
    }

    if (isArray(path)) {
        return arrayToRegexp(path, keys, options);
    }

    return stringToRegexp(path, keys, options);

    /**
     * Parse a string for the raw tokens.
     *
     * @param  {String} str
     * @return {Array}
     */
    function parseTokens(str) {
        var tokens = [];
        var key = 0;
        var index = 0;
        var path = '';
        var res;

        while ((res = PATH_REGEXP.exec(str)) !== null) {
            var m = res[0];
            var escaped = res[1];
            var offset = res.index;
            path += str.slice(index, offset);
            index = offset + m.length;

            // Ignore already escaped sequences.
            if (escaped) {
                path += escaped[1];
                continue;
            }

            // Push the current path onto the tokens.
            if (path) {
                tokens.push(path);
                path = '';
            }

            var prefix = res[2];
            var name = res[3];
            var capture = res[4];
            var group = res[5];
            var suffix = res[6];
            var asterisk = res[7];

            var repeat = suffix === '+' || suffix === '*';
            var optional = suffix === '?' || suffix === '*';
            var delimiter = prefix || '/';
            var pattern = capture || group || (asterisk ? '.*' : '[^' + delimiter + ']+?');

            tokens.push({
                name: name || key++,
                prefix: prefix || '',
                delimiter: delimiter,
                optional: optional,
                repeat: repeat,
                pattern: escapeGroup(pattern)
            });
        }

        // Match any characters still remaining.
        if (index < str.length) {
            path += str.substr(index);
        }

        // If the path exists, push it onto the end.
        if (path) {
            tokens.push(path);
        }

        return tokens;
    }

    /**
     * Compile a string to a template function for the path.
     *
     * @param  {String}   str
     * @return {Function}
     */
    function compile(str) {
        return tokensToFunction(parseTokens(str));
    }

    /**
     * Expose a method for transforming tokens into the path function.
     */
    function tokensToFunction(tokens) {
        // Compile all the tokens into regexps.
        var matches = new Array(tokens.length);

        // Compile all the patterns before compilation.
        for (var i = 0; i < tokens.length; i++) {
            if (typeof tokens[i] === 'object') {
                matches[i] = new RegExp('^' + tokens[i].pattern + '$');
            }
        }

        return function (obj) {
            var path = '';
            var data = obj || {};

            for (var i = 0; i < tokens.length; i++) {
                var token = tokens[i];

                if (typeof token === 'string') {
                    path += token;

                    continue;
                }

                var value = data[token.name];
                var segment;

                if (value === null) {
                    if (token.optional) {
                        continue;
                    } else {
                        throw new TypeError('Expected "' + token.name + '" to be defined');
                    }
                }

                if (isArray(value)) {
                    if (!token.repeat) {
                        throw new TypeError('Expected "' + token.name + '" to not repeat, but received "' + value + '"');
                    }

                    if (value.length === 0) {
                        if (token.optional) {
                            continue;
                        } else {
                            throw new TypeError('Expected "' + token.name + '" to not be empty');
                        }
                    }

                    for (var j = 0; j < value.length; j++) {
                        segment = encodeURIComponent(value[j]);

                        if (!matches[i].test(segment)) {
                            throw new TypeError('Expected all "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"');
                        }

                        path += (j === 0 ? token.prefix : token.delimiter) + segment;
                    }

                    continue;
                }

                segment = encodeURIComponent(value);

                if (!matches[i].test(segment)) {
                    throw new TypeError('Expected "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"');
                }

                path += token.prefix + segment;
            }

            return path;
        }
    }

    /**
     * Escape a regular expression string.
     *
     * @param  {String} str
     * @return {String}
     */
    function escapeString(str) {
        return str.replace(/([.+*?=^!:${}()[\]|\/])/g, '\\$1');
    }

    /**
     * Escape the capturing group by escaping special characters and meaning.
     *
     * @param  {String} group
     * @return {String}
     */
    function escapeGroup(group) {
        return group.replace(/([=!:$\/()])/g, '\\$1');
    }

    /**
     * Attach the keys as a property of the regexp.
     *
     * @param  {RegExp} re
     * @param  {Array}  keys
     * @return {RegExp}
     */
    function attachKeys(re, keys) {
        re.keys = keys;
        return re;
    }

    /**
     * Get the flags for a regexp from the options.
     *
     * @param  {Object} options
     * @return {String}
     */
    function flags(options) {
        return options.sensitive ? '' : 'i';
    }

    /**
     * Pull out keys from a regexp.
     *
     * @param  {RegExp} path
     * @param  {Array}  keys
     * @return {RegExp}
     */
    function regexpToRegexp(path, keys) {
        // Use a negative lookahead to match only capturing groups.
        var groups = path.source.match(/\((?!\?)/g);

        if (groups) {
            for (var i = 0; i < groups.length; i++) {
                keys.push({
                    name: i,
                    prefix: null,
                    delimiter: null,
                    optional: false,
                    repeat: false,
                    pattern: null
                });
            }
        }

        return attachKeys(path, keys);
    }

    /**
     * Transform an array into a regexp.
     *
     * @param  {Array}  path
     * @param  {Array}  keys
     * @param  {Object} options
     * @return {RegExp}
     */
    function arrayToRegexp(path, keys, options) {
        var parts = [];

        for (var i = 0; i < path.length; i++) {
            parts.push(convertPathToRegexp(path[i], keys, options).source);
        }

        var regexp = new RegExp('(?:' + parts.join('|') + ')', flags(options));

        return attachKeys(regexp, keys);
    }

    /**
     * Create a path regexp from string input.
     *
     * @param  {String} path
     * @param  {Array}  keys
     * @param  {Object} options
     * @return {RegExp}
     */
    function stringToRegexp(path, keys, options) {
        var tokens = parseTokens(path);
        var re = tokensToRegExp(tokens, options);

        // Attach keys back to the regexp.
        for (var i = 0; i < tokens.length; i++) {
            if (typeof tokens[i] !== 'string') {
                keys.push(tokens[i]);
            }
        }

        return attachKeys(re, keys);
    }

    /**
     * Expose a function for taking tokens and returning a RegExp.
     *
     * @param  {Array}  tokens
     * @param  {Object} options
     * @return {RegExp}
     */
    function tokensToRegExp(tokens, options) {
        options = options || {};

        var strict = options.strict;
        var end = options.end !== false;
        var route = '';
        var lastToken = tokens[tokens.length - 1];
        var endsWithSlash = typeof lastToken === 'string' && /\/$/.test(lastToken);

        // Iterate over the tokens and create our regexp string.
        for (var i = 0; i < tokens.length; i++) {
            var token = tokens[i];

            if (typeof token === 'string') {
                route += escapeString(token);
            } else {
                var prefix = escapeString(token.prefix);
                var capture = token.pattern;

                if (token.repeat) {
                    capture += '(?:' + prefix + capture + ')*';
                }

                if (token.optional) {
                    if (prefix) {
                        capture = '(?:' + prefix + '(' + capture + '))?';
                    } else {
                        capture = '(' + capture + ')?';
                    }
                } else {
                    capture = prefix + '(' + capture + ')';
                }

                route += capture;
            }
        }

        // In non-strict mode we allow a slash at the end of match. If the path to
        // match already ends with a slash, we remove it for consistency. The slash
        // is valid at the end of a path match, not in the middle. This is important
        // in non-ending mode, where "/test/" shouldn't match "/test//route".
        if (!strict) {
            route = (endsWithSlash ? route.slice(0, -2) : route) + '(?:\\/(?=$))?';
        }

        if (end) {
            route += '$';
        } else {
            // In non-ending mode, we need the capturing groups to match as much as
            // possible by using a positive lookahead to the end or next path segment.
            route += strict && endsWithSlash ? '' : '(?=\\/|$)';
        }

        return new RegExp('^' + route, flags(options));
    }

}

/**
 * Parse the given query `str` or `obj`, returning an object.
 *
 * @param {String} str | {Object} obj
 * @return {Object}
 * @api public
 */
function queryString (str) {

    /**
     * Turn the given `obj` into a query string
     *
     * @param {Object|Array} obj
     * @param {string} prefix
     * @return {String}
     */
    queryString.stringify = stringify;

    /**
     * Object#toString() ref for stringify().
     */
    var toString = Object.prototype.toString;

    /**
     * Cache non-integer test regexp.
     */
    var isInt = /^[0-9]+$/;

    if (str === null || str === '') {
        return {};
    }
    return typeof str === 'object' ? parseObject(str) : parseString(str);

    function promote(parent, key) {
        if (parent[key].length === 0) {
            return parent[key] = {};
        }
        var t = {};
        for (var i in parent[key]) {
            t[i] = parent[key][i];
        }
        parent[key] = t;
        return t;
    }

    function parse(parts, parent, key, val) {
        var part = parts.shift();
        // end
        if (!part) {
            if (isArray(parent[key])) {
                parent[key].push(val);
            } else if (typeof parent[key] === 'object') {
                parent[key] = val;
            } else if (typeof parent[key] === 'undefined') {
                parent[key] = val;
            } else {
                parent[key] = [parent[key], val];
            }
            // array
        } else {
            var obj = parent[key] = parent[key] || [];
            if (part === ']') {
                if (isArray(obj)) {
                    if (val !== '') {
                        obj.push(val);
                    }
                } else if (typeof obj === 'object') {
                    obj[Object.keys(obj).length] = val;
                } else {
                    parent[key] = [parent[key], val];
                }
                // prop
            } else if (~part.indexOf(']')) {
                part = part.substr(0, part.length - 1);
                if (!isInt.test(part) && isArray(obj)) {
                    obj = promote(parent, key);
                }
                parse(parts, obj, part, val);
                // key
            } else {
                if (!isInt.test(part) && isArray(obj)) {
                    obj = promote(parent, key);
                }
                parse(parts, obj, part, val);
            }
        }
    }

    /**
     * Merge parent key/val pair.
     */
    function merge(parent, key, val) {
        if (~key.indexOf(']')) {
            parse(key.split('['), parent, 'base', val);
        } else {
            if (!isInt.test(key) && isArray(parent.base)) {
                var t = {};
                for (var k in parent.base) {
                    t[k] = parent.base[k];
                }
                parent.base = t;
            }
            set(parent.base, key, val);
        }

        return parent;
    }

    /**
     * Parse the given obj.
     * @return {Object}
     */
    function parseObject(obj) {
        var ret = {base: {}};
        Object.keys(obj).forEach(function (name) {
            merge(ret, name, obj[name]);
        });
        return ret.base;
    }

    /**
     * Parse the given str.
     * @return {Object}
     */
    function parseString(str) {
        var ret = {base: {}};
        String(str)
            .split('&')
            .reduce(
                function (ret, pair) {
                    try {
                        pair = decodeURIComponent(pair.replace(/\+/g, ' '));
                    } catch (e) {
                        // ignore
                    }

                    var eql = pair.indexOf('=');
                    var brace = lastBraceInKey(pair);
                    var key = pair.substr(0, brace || eql);
                    var val = pair.substr(brace || eql, pair.length);
                    val = val.substr(val.indexOf('=') + 1, val.length);

                    // ?foo
                    if (key === '') {
                        key = pair;
                        val = '';
                    }

                    return merge(ret, key, val);
                },
                ret
            );
        return ret.base;
    }

    /**
     * Turn the given `obj` into a query string
     *
     * @param {Object|Array} obj
     * @param {string} prefix
     * @return {String}
     */
    function stringify(obj, prefix) {
        if (isArray(obj)) {
            return stringifyArray(obj, prefix);
        } else if (toString.call(obj) === '[object Object]') {
            return stringifyObject(obj, prefix);
        } else if (typeof obj === 'string') {
            return stringifyString(obj, prefix);
        } else {
            return prefix + '=' + obj;
        }
    }

    /**
     * Stringify the given `str`.
     *
     * @param {String} str
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyString(str, prefix) {
        if (!prefix) {
            throw new TypeError('stringify expects an object');
        }
        return prefix + '=' + encodeURIComponent(str);
    }

    /**
     * Stringify the given `arr`.
     *
     * @param {Array} arr
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyArray(arr, prefix) {
        var ret = [];
        if (!prefix) {
            throw new TypeError('stringify expects an object');
        }
        for (var i = 0; i < arr.length; i++) {
            ret.push(stringify(arr[i], prefix + '[' + i + ']'));
        }
        return ret.join('&');
    }

    /**
     * Stringify the given `obj`.
     *
     * @param {Object} obj
     * @param {String} prefix
     * @return {String}
     * @api private
     */
    function stringifyObject(obj, prefix) {
        var ret = [];
        var keys = Object.keys(obj);
        var key;

        for (var i = 0, len = keys.length; i < len; ++i) {
            key = keys[i];
            ret.push(stringify(obj[key], prefix
                ? prefix + '[' + encodeURIComponent(key) + ']'
                : encodeURIComponent(key)));
        }

        return ret.join('&');
    }

    /**
     * Set `obj`'s `key` to `val` respecting
     * the weird and wonderful syntax of a qs,
     * where "foo=bar&foo=baz" becomes an array.
     *
     * @param {Object} obj
     * @param {String} key
     * @param {String} val
     * @api private
     */
    function set (obj, key, val) {
        var v = obj[key];
        if (v === undefined) {
            obj[key] = val;
        } else if (isArray(v)) {
            v.push(val);
        } else {
            obj[key] = [v, val];
        }
    }

    /**
     * Locate last brace in `str` within the key.
     *
     * @param {String} str
     * @return {Number}
     * @api private
     */
    function lastBraceInKey(str) {
        var len = str.length;
        var brace, c;
        for (var i = 0; i < len; ++i) {
            c = str[i];
            if (c === ']') {
                brace = false;
            }
            if (c === '[') {
                brace = true;
            }
            if (c === '=' && !brace) {
                return i;
            }
        }
    }
}

/**
 * Promise/Deferred implementation
 * Source: https://github.com/warpdesign/deferred-js
 * @param {function=} fn
 * @return {promise|deferred}
 * @constructor
 */
function Deferred(fn) {
    var status = 'pending';
    var doneFuncs = [];
    var failFuncs = [];
    var progressFuncs = [];
    var resultArgs = null;

    var promise = {
        done: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'resolved') {
                            arr[j].apply(this, resultArgs);
                        }

                        doneFuncs.push(arr[j]);
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'resolved') {
                        arguments[i].apply(this, resultArgs);
                    }

                    doneFuncs.push(arguments[i]);
                }
            }

            return this;
        },

        fail: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'rejected') {
                            arr[j].apply(this, resultArgs);
                        }

                        failFuncs.push(arr[j]);
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'rejected') {
                        arguments[i].apply(this, resultArgs);
                    }

                    failFuncs.push(arguments[i]);
                }
            }

            return this;
        },

        always: function () {
            return this.done.apply(this, arguments).fail.apply(this, arguments);
        },

        progress: function () {
            for (var i = 0; i < arguments.length; i++) {
                // skip any undefined or null arguments
                if (!arguments[i]) {
                    continue;
                }

                if (isArray(arguments[i])) {
                    var arr = arguments[i];
                    for (var j = 0; j < arr.length; j++) {
                        // immediately call the function if the deferred has been resolved
                        if (status === 'pending') {
                            progressFuncs.push(arr[j]);
                        }
                    }
                }
                else {
                    // immediately call the function if the deferred has been resolved
                    if (status === 'pending') {
                        progressFuncs.push(arguments[i]);
                    }
                }
            }

            return this;
        },

        then: function () {
            // fail callbacks
            if (arguments.length > 1 && arguments[1]) {
                this.fail(arguments[1]);
            }

            // done callbacks
            if (arguments.length > 0 && arguments[0]) {
                this.done(arguments[0]);
            }

            // notify callbacks
            if (arguments.length > 2 && arguments[2]) {
                this.progress(arguments[2]);
            }
        },

        promise: function (obj) {
            if (!obj) {
                return promise;
            } else {
                for (var i in promise) {
                    obj[i] = promise[i];
                }
                return obj;
            }
        },

        state: function () {
            return status;
        },

        debug: function () {
            console.log('[debug]', doneFuncs, failFuncs, status);
        },

        isRejected: function () {
            return status === 'rejected';
        },

        isResolved: function () {
            return status === 'resolved';
        }
    };

    var deferred = {
        resolveWith: function (context, args) {
            if (status === 'pending') {
                status = 'resolved';
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < doneFuncs.length; i++) {
                    doneFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        rejectWith: function (context, args) {
            if (status === 'pending') {
                status = 'rejected';
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < failFuncs.length; i++) {
                    failFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        notifyWith: function (context, args) {
            if (status === 'pending') {
                resultArgs = normalizeArguments(args);
                for (var i = 0; i < progressFuncs.length; i++) {
                    progressFuncs[i].apply(context, resultArgs);
                }
            }
            return this;
        },

        resolve: function () {
            return this.resolveWith(this, arguments);
        },

        reject: function () {
            return this.rejectWith(this, arguments);
        },

        notify: function () {
            return this.notifyWith(this, arguments);
        }
    };

    var obj = promise.promise(deferred);

    if (typeof fn === 'function') {
        fn.apply(obj, [obj]);
    }

    return obj;
}

/**
 * Run functions one by one. Each function MUST return Deferred object
 * @param {Array=} functions
 * @param {Object=} context - context of each funciton
 * @param {Array=} args - arguments to pass to each funciton
 * @return {*}
 */
Deferred.queue = function (functions, context, args) {
    var deferred = Deferred();
    if (!functions) {
        return deferred.resolve();
    }
    if (!isArray(functions)) {
        throw new TypeError('Deferred.queue: argument 1 (functions) must be an array or empty');
    }
    if (!functions.length) {
        return deferred.resolve();
    }
    if (!context) {
        context = deferred;
    } else if (typeof context !== 'object') {
        throw new TypeError('Deferred.queue: argument 2 (context) must be an object or empty');
    }
    if (!args) {
        args = [];
    } else if (!isArray(args)) {
        throw new TypeError('Deferred.queue: argument 3 (args) must be an array or empty');
    }

    var i = 0;
    var results = [];
    var next = function () {
        if (i < functions.length) {
            functions[i++]
                .apply(context, args)
                .then(
                    function () {
                        results.push(normalizeArguments(arguments));
                        next();
                    },
                    function () {
                        deferred.reject.apply(deferred, arguments);
                    });
        } else {
            deferred.resolve.call(deferred, results);
        }
    };
    next();
    return deferred.promise();
};

})(typeof window !== "undefined" ? window : this);