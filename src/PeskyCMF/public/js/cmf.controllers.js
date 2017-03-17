var CmfControllerHelpers = {
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
        CmfControllerHelpers.currentContentContainer = $el;
    },
    setCurrentContentEl: function ($el) {
        var deferred = $.Deferred();
        if (CmfControllerHelpers.currentContentEl) {
            if (CmfControllerHelpers.currentContentEl.is($el)) {
                Utils.hidePreloader(CmfControllerHelpers.currentContentContainer);
                deferred.resolve($el);
            } else {
                Utils.fadeOut(CmfControllerHelpers.currentContentEl, function () {
                    CmfControllerHelpers.currentContentEl.remove();
                    CmfControllerHelpers.currentContentEl = $el;
                    CmfControllerHelpers.currentContentEl.fadeOut();
                    CmfControllerHelpers.currentContentContainer.append($el);
                    Utils.fadeIn(CmfControllerHelpers.currentContentEl, function () {
                        Utils.hidePreloader(CmfControllerHelpers.currentContentContainer);
                        deferred.resolve($el);
                    });
                });
            }
        } else {
            CmfControllerHelpers.currentContentEl = $el;
            CmfControllerHelpers.currentContentEl.fadeOut();
            CmfControllerHelpers.currentContentContainer.append($el);
            Utils.fadeIn(CmfControllerHelpers.currentContentEl, function () {
                Utils.hidePreloader(CmfControllerHelpers.currentContentContainer);
                deferred.resolve($el);
            });
        }
        return deferred;
    }
};

var CmfView = Pilot.View.extend({
    getContainer: Utils.getAvailableContentContainer,
    tag: 'div.' + CmfConfig.contentWrapperCssClass,
    viewIntro: CmfControllerHelpers.viewIntro,
    viewOutro: CmfControllerHelpers.viewOutro,
    beforeRouteLoad: CmfControllerHelpers.beforeRouteLoad,
    afterRouteLoad: CmfControllerHelpers.afterRouteLoad,
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
    switchPageTitle: function () {
        Utils.updatePageTitleFromH1(this.$el);
    },
    loadTemplate: function (request) {
        return Utils.downloadHtml(this.getTemplateUrl(request), this.cacheTemplate, this.convertTemplateToDotJs);
    },
    onRoute: function (event, request) {
        //console.log('route');
        this.request = request;
        var deferred = this.showUI ? AdminUI.showUI() : AdminUI.destroyUI();
        var _this = this;
        deferred.done(function () {
            _this.render();
        });
    },
    onRender: function (event) {
        //console.log('render');
        var container = this.getContainer();
        if (container) {
            CmfControllerHelpers.setCurrentContentContainer(container);
            this.$el.attr('data-detachable', this.detachable ? '1' : '0');
            var _this = this;
            CmfControllerHelpers.setCurrentContentEl(this.$el)
                .done(function () {
                    _this.afterRender(event, _this.request);
                });
            this.switchBodyClass(this.request);
            this.switchPageTitle();
        } else {
            Utils.handleMissingContainerError();
        }
    },
    afterRender: function (event, request) {

    }
});

var NotAuthorisedCmfView = CmfView.extend({
    getContainer: Utils.getPageWrapper,
    sigleton: true,
    showUI: false,
    bodyClass: '',
    formSelector: false,
    formContainerSelector: false,
    switchBodyClass: function (request) {
        Utils.switchBodyClass('login-page ' + this.bodyClass, 'not-authorised');
    },
    afterRender: function (event, request){
        if (this.formSelector && this.formContainerSelector) {
            var container = $(this.formContainerSelector);
            var form = container.find(this.formSelector);
            FormHelper.initForm(form, container, function (json, form, container) {
                Utils.cleanCache();
                Utils.handleAjaxSuccess(json);
            });
        }
    }
});

var CmfControllers = {
    loginController: NotAuthorisedCmfView.extend({
        bodyClass: 'login-form',
        formSelector: 'form#login-form',
        formContainerSelector: '#login-form-container'
    }),
    forgotPasswordController: NotAuthorisedCmfView.extend({
        bodyClass: 'forgot-password-form',
        formSelector: 'form#forgot-password-form',
        formContainerSelector: '#forgot-password-form-container'
    }),
    replacePasswordController: NotAuthorisedCmfView.extend({
        bodyClass: 'replace-password-form',
        formSelector: 'form#replace-password-form',
        formContainerSelector: '#replace-password-form-container'
    }),
    pageController: CmfView.extend({
        getContainer: Utils.getContentContainer,
        sigleton: true,
        cacheTemplate: false,
        bodyClass: null,
        switchBodyClass: function (request) {
            Utils.switchBodyClass(this.bodyClass || 'page-' + request.params.uri.replace(/[^a-zA-Z0-9]+/, '-'), 'page');
        },
        afterRender : function (event, request) {

        }
    })
};