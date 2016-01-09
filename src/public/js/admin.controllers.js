var AdminControllerHelpers = {
    viewIntro: function (request) {
        //console.log('view inro');
    },
    viewOutro: function (request) {
        //console.log('view outro');
    },
    beforeRouteLoad: function (request, isReload) {
        //console.log('before load');
        Utils.showPreloader(this.getContainer());
    },
    afterRouteLoad: function (request, isReload) {
        //console.log('after load');
    },
    currentContentContainer: Utils.getPageWrapper(),
    currentContentEl: null,
    setCurrentContentContainer: function ($el) {
        AdminControllerHelpers.currentContentContainer = $el;
    },
    setCurrentContentEl: function ($el) {
        var deferred = $.Deferred();
        if (AdminControllerHelpers.currentContentEl) {
            if (AdminControllerHelpers.currentContentEl.is($el)) {
                Utils.hidePreloader(AdminControllerHelpers.currentContentContainer);
                deferred.resolve($el);
            } else {
                Utils.fadeOut(AdminControllerHelpers.currentContentEl, function () {
                    $el.fadeOut();
                    AdminControllerHelpers.currentContentContainer.append($el);
                    if (AdminControllerHelpers.currentContentEl.attr('data-detachable') == '1') {
                        AdminControllerHelpers.currentContentEl.detach();
                    } else {
                        AdminControllerHelpers.currentContentEl.remove();
                    }
                    AdminControllerHelpers.currentContentEl = $el;
                    Utils.fadeIn(AdminControllerHelpers.currentContentEl, function () {
                        Utils.hidePreloader(AdminControllerHelpers.currentContentContainer);
                        deferred.resolve($el);
                    });
                });
            }
        } else {
            AdminControllerHelpers.currentContentEl = $el;
            AdminControllerHelpers.currentContentEl.fadeOut();
            AdminControllerHelpers.currentContentContainer.append($el);
            Utils.fadeIn(AdminControllerHelpers.currentContentEl, function () {
                Utils.hidePreloader(AdminControllerHelpers.currentContentContainer);
                deferred.resolve($el);
            });
        }
        return deferred;
    }
};

var AdminView = Pilot.View.extend({
    getContainer: Utils.getAvailableContentContainer,
    tag: 'div.' + GlobalVars.contentWrapperCssClass,
    viewIntro: AdminControllerHelpers.viewIntro,
    viewOutro: AdminControllerHelpers.viewOutro,
    beforeRouteLoad: AdminControllerHelpers.beforeRouteLoad,
    afterRouteLoad: AdminControllerHelpers.afterRouteLoad,
    cacheTemplate: true,
    convertTemplateToDotJs: false,
    showUI: true,
    request: null,
    detachable: false,
    getTemplateUrl: function (request) {
        return request.path
    },
    switchBodyClass: function (request) {

    },
    loadTemplate: function (request) {
        return Utils.downloadHtml(this.getTemplateUrl(request), this.cacheTemplate, this.convertTemplateToDotJs);
    },
    onRoute: function (event, request) {
        //console.log('route');
        this.request = request;
        var deferred;
        if (this.showUI) {
            deferred = AdminUI.showUI();
        } else {
            deferred = AdminUI.destroyUI();
        }
        var _this = this;
        deferred.done(function () {
            _this.render();
        });
    },
    onRender: function (event) {
        //console.log('render');
        var container = this.getContainer();
        if (container) {
            AdminControllerHelpers.setCurrentContentContainer(container);
            this.$el.attr('data-detachable', this.detachable ? '1' : '0');
            AdminControllerHelpers.setCurrentContentEl(this.$el);
            this.switchBodyClass(this.request);
            this.afterRender(event, this.request);
        } else {
            Utils.handleMissingContainerError();
        }
    },
    afterRender: function (event, request) {

    }
});

var AdminControllers = {
    loginController: AdminView.extend({
        getContainer: Utils.getPageWrapper,
        sigleton: true,
        showUI: false,
        switchBodyClass: function (request) {
            Utils.switchBodyClass('login-page');
        },
        afterRender: function (event, request){
            var container = $('#login-form-container');
            var form = container.find('form#login-form');
            FormHelper.initForm(form, container, function (json, form, container) {
                Utils.cleanCache();
                window.adminApp.nav(json.redirect);
            });
        }
    }),
    pageController: AdminView.extend({
        getContainer: Utils.getContentContainer,
        sigleton: true,
        cacheTemplate: false,
        switchBodyClass: function (request) {
            Utils.switchBodyClass('page-' + request.params.uri);
        },
        afterRender : function (event, request) {

        }
    })
};