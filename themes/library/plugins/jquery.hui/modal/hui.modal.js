/**
 *  HUIComponents/Modal
 *  jQuery Modal Plugin
 *  @CarmineRumma
 *
 *  (c) http://branding.paginemediche.it/
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = factory;
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    "use strict";

    var pluginName = 'huiModal';

    $.fn.huiModal = function (options) {

        var _instance;
        var _tempHeaderBlock;
        var _tempBodyBlock;
        var _tempFooterBlock;


        var foundation_opt_defaults = {
          "data-animation-in": '',
          "data-animation-out": '',
          "data-show-delay": 1500,
          "data-hide-delay": 0,
          "data-close-on-click": true,
          "data-close-on-esc": true,
          "data-multiple-opened": true,
          "data-v-offset":0, //Distance, in pixels, the modal should push down from the top of the screen.
          "data-h-offset":0, //Distance, in pixels, the modal should push in from the side of the screen.
          "data-full-screen": false,
          "data-btm-offset-pct": 0, // Percentage of screen height the modal should push up from the bottom of the view.
          "data-overlay": true , // Allows the modal to generate an overlay div, which will cover the view when modal opens.
          "data-reset-on-close": false,
          "data-deep-link": false, // Allows the modal to alter the url on open/close, and allows the use of the `back` button to close modals. ALSO, allows a modal to auto-maniacally open on page load IF the hash === the modal's user-set id.
          "data-update-history": false, // Update the browser history with the open modal
          "data-append-to": "body", // 	Allows the modal to append to custom div.
          "data-additional-overlay-classes": "",
        };

        var defaults = {
            debug: true,
            type: "foundation", // foundation || bootstrap || custom - Default: "foundation"
            id: undefined,
            animation: {
                in: 'fadeIn',
                out: 'fadeOut',
                delay: 0
            },
            position: "center center",
            header: {
                class: '',
                buttons:[]
            },
            title: "",
            subtitle:"",
            icon:"",
            width: 380, // max = 640
            height: 280, // max = 350
            scroll: false,
            maxHeight:0,
            responsive: {

            },
            globalContainerSelector: "body",
            onBlurContainer: '',
            showClose: true,
            showCloseText: 'Chiudi',
            closeByEscape: true,
            closeByDocument: true,
            wrapperClass:'',
            baseClass:'',
            holderClass: '',
            overlayClass: '',
            hideTitle: false,
            enableStackAnimation: false,

            openOnEvent: true,
            setEvent: 'click',
            headerBlockSelector: '',
            footerBlockSelector: '',
            excludeElements: [],
            onRender: function(response){
                if (options.excludeElements.length > 0) {
                    var $response = $('<div id="hui--temp">' + response + '</div>');
                    $response.find(options.excludeElements.join(',')).remove();
                    return $response.html();
                    /*
                    $("<div class='hidden' id='hui--temp'>"+response+"</div>").appendTo($('body'))
                    $("#hui--temp").find(options.excludeElements.join(',')).remove();
                    var $newHtml = $("#hui--temp").html();
                    $("#hui--temp").remove();
                    return $newHtml;*/
                }
                return response;
            },
            onOpening: function(){},
            onClosing: function(){},
            onOpened: function (){},
            onClosed: function (){},
            url: '',                // AJAX Url
            ajax: {
                type: "html",
                loader: false,      // AJAX Loader
                data: {},           // AJAX Data
            },
            template: '<p>This is test popup content!</p>',
            footer: {
              class: '',
              buttonsContainerClass: '',
              buttons: [
                /*
                {
                  text: "Procedi",
                  icon: "<i class='fa fa-icon></i>' || <img src='' />",
                  url: ''
                  class: 'primary',
                  callback: function (){
                    alert('callback');
                  }
                }
                */
              ]
            },
            mobile: {
                hideSubtitle: false,
                headerSticky: false,
                footerSticky: false
            }
        };

        options = $.extend(defaults, options);

        var _modalIDPrefix = "modal";
        var _lastModalID;

        return this.each(function () {
            var self = $(this),
                body = $('body'),
                maxWidth = options.width > 640 ? 640 : options.width,
                maxHeight = options.height > 350 ? 350 : options.height,
                template = typeof options.template === 'function' ? options.template(self) : options.template;

            body.addClass('hui');

            /*
            if ($('.pmModal-overlay').length === 0) {
                body.append('<div class="pmModal-overlay ' + options.overlayClass + '"></div>');
            }
            */

            if ($(options.globalContainerSelector).length === 0) {
                if (options.globalContainerSelector.charAt(0) == "#") {
                    $("<div id=" + options.globalContainerSelector.replace('#','') + " class='foundation' ></div>").appendTo($("body"));
                }
            }

            function onDocumentKeyup (e) {
                if (options.closeByEscape === true) {
                    if (e.keyCode === 27) {
                        close();
                    }
                }
            }

            function onDocumentClick (e) {
                if (options.closeByDocument) {
                    if ($(e.target).is('.pmModal-overlay, .pmModal-close')) {
                        e.preventDefault();
                        deactivate();
                    }
                } else if ($(e.target).is('.pmModal-close')) {
                        e.preventDefault();
                        deactivate();
                }
            }


            function _buildLoader() {
                var $loader =   '<div class="hui-loader" >' +
                                  '<div class="load-three-bounce">' +
                                    '<div class="load-child bounce1"></div>' +
                                    '<div class="load-child bounce2"></div>' +
                                    '<div class="load-child bounce3"></div>' +
                                  '</div>' +
                                '</div>';
                if (options.url && options.ajax.loader === true) {
                    $("body").append($loader);
                }

            }

            function getAnimationEnd() {
                var animationEnd = (function(el) {
                    var animations = {
                        animation: 'animationend',
                        OAnimation: 'oAnimationEnd',
                        MozAnimation: 'mozAnimationEnd',
                        WebkitAnimation: 'webkitAnimationEnd',
                    };

                    for (var t in animations) {
                        if (el.style[t] !== undefined) {
                            return animations[t];
                        }
                    }
                })(document.createElement('div'));
                return animationEnd;
            }


            function cssAnimate(animationName, callback) {
              var animationEnd = getAnimationEnd();

              this.addClass('animated ' + animationName).one(animationEnd, function() {
                $(this).removeClass('animated ' + animationName);
                if (typeof callback === 'function') callback();
              });

              return this;
            }

            function getTemplate (callback){
                var url = options.url;
                if (options.url) {

                    var promise = $.ajax({
                        url: options.url,
                        success: function (res){
                            callback(res);
                        }
                    });


                } else {
                    var template = typeof options.template === 'function' ? options.template(self) : options.template;
                    callback(template);
                }
            }

            function genRandom() {
                return Math.random().toString(36).substring(7);
            }

            function genModalID() {
                _lastModalID = options.id || (_modalIDPrefix + "_" + genRandom());
                return getModalSelector();
            }

            function getModalSelector() {
                return _lastModalID;
            }

            function popupRender () {

            }

            function blockOverride(responseHtml, callback) {
                var $blockRes = $('<div id="hui--temp">' + responseHtml + '</div>');

                if (options.headerBlockSelector !== '') {
                    // Override Footer Behaviour
                    if($(options.headerBlockSelector, $blockRes).length > 0) {
                        _tempHeaderBlock = $(options.headerBlockSelector, $blockRes).html();
                        $blockRes.find(options.headerBlockSelector).remove();
                    } else {
                        buildDebug(" (blockOverride) headerBlockSelector " + options.headerBlockSelector + " not found in response", "warn");
                    }

                    /*
                    $("<div class='hidden' id='hui--temp'>" + _blockRes + "</div>").appendTo($('body'));
                    if ($("#hui--temp").find(options.headerBlockSelector).length > 0){
                        _tempHeaderBlock = $("#hui--temp").find(options.headerBlockSelector).html();
                        $("#hui--temp").find(options.headerBlockSelector).remove();
                        var $htmlWithoutHeaderBlock = $("#hui--temp").html();
                        $("#hui--temp").remove();
                        _blockRes = $htmlWithoutHeaderBlock;
                        //callback($htmlWithoutFooterBlock);
                    } else {
                        buildDebug(" (blockOverride) headerBlockSelector " + options.headerBlockSelector + " not found in response", "warn");
                        //callback(responseHtml);

                    }*/

                }

                if (options.footerBlockSelector !== '') {
                    // Override Footer Behaviour

                   if($(options.footerBlockSelector, $blockRes).length > 0) {
                       _tempFooterBlock = $(options.footerBlockSelector, $blockRes).html();
                       $blockRes.find(options.footerBlockSelector).remove();
                   } else {
                       buildDebug(" (blockOverride) footerBlockSelector " + options.footerBlockSelector + " not found in response", "warn");
                   }

                   /*
                    $("<div class='hidden' id='hui--temp'>" + responseHtml + "</div>").appendTo($('body'));
                    if ($("#hui--temp").find(options.footerBlockSelector).length > 0){
                        _tempFooterBlock = $("#hui--temp").find(options.footerBlockSelector).html();
                        $("#hui--temp").find(options.footerBlockSelector).remove();
                        var $htmlWithoutFooterBlock = $("#hui--temp").html();
                        $("#hui--temp").remove();
                        //callback($htmlWithoutFooterBlock);
                        _blockRes = $htmlWithoutFooterBlock;
                    } else {
                        buildDebug(" (blockOverride) footerBlockSelector " + options.footerBlockSelector + " not found in response", "warn");
                        //callback(responseHtml);
                    }*/
                }

                callback($blockRes.html());

                /*options.headerBlockSelector @todo*/

            }

            function _buildHeader (readFromAjax, jsonResponse) {
                var _hHtml = '';
                var _optObject = options;
                if (readFromAjax === true) {
                    _optObject = jsonResponse;
                }

                if (options.hideTitle == false) {

                    var params = [];
                    if (_optObject.icon != '') {
                        params.push("has-icon");
                    }
                    if (_optObject.subtitle != '') {
                        params.push("has-subtitle");
                    }

                    _hHtml += '<div class="hui-header-container ' + options.header.class + '" >';
                    _hHtml += '<div class="hui-modal-title ' + params.join(' ') + '" >';

                    if (_optObject.icon != '') {
                        _hHtml += '<div class="hui-img-header" >' +
                                    _optObject.icon +
                                 '</div>';
                    }
                    if (options.showClose) {
                        _hHtml += "<a class='close-button' data-close aria-label='" + options.showCloseText + "' title='" + options.showCloseText + "' type='button'>" +
                                            "<span aria-hidden='true'>&times;</span>" +
                                  "</a>";
                    }
                    _hHtml += '<div class="hui-titles">' +
                                '<div>' +
                                    '<div class="title">' + _optObject.title + '</div>';
                                    if (_optObject.subtitle != '') {
                                        _hHtml += '<div class="subtitle">' + _optObject.subtitle + '</div>';
                                    }
                    _hHtml +=    '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                }
                return _hHtml;
            }

            function activate_foundationPopup() {

                $("body").addClass("hui-modal-opening");
                if (options.scroll === true) {
                    $("body").addClass("hui-scroll");
                }
                buildDebug("Activate Foundation Popup", "INFO");
                var $html = '';
                var _style = [];

                options.wrapperClass = "reveal-overlay show";

                if (options.wrapperClass) {
                    $html += "<div class='" + options.wrapperClass + "' >";
                }
                /*
                if (options.css.padding != '') {
                    _style.push("padding:" + options.css.padding);
                }
                */

                //$overlay = "<div class='hui-overlay' ></div>";

                var extraClasses = [];
                if (options.mobile.headerSticky == true) {
                    extraClasses.push('m-sticky-h');
                }
                if (options.mobile.footerSticky == true) {
                    extraClasses.push('m-sticky-f');
                }
                if (options.mobile.hideSubtitle == true) {
                    extraClasses.push('m-hd-st');
                }
                $html += "<div class='reveal hui-modal " + extraClasses.join(' ') + " " + options.baseClass + "' id='" + genModalID() + "' " +
                    " data-additional-overlay-classes='test' " +
                    " data-append-to='" + options.globalContainerSelector + "' style='" + _style.join(';') + "' >";

                _buildLoader();
                getTemplate( function (response){
                    var res;

                    var _jsonRes = {};
                    if (options.ajax.type == "html") {
                        response = options.onRender(response);
                        res = response;

                        blockOverride(res, function (new_res){
                            _tempBodyBlock = new_res;
                            //$html += "<div class='hui-body' >" + new_res + "</div>";
                        });
                    } else if (options.ajax.type == "json") {
                        res = _jsonRes = $.parseJSON(response);

                        res = options.onRender(res.html);

                        blockOverride(res, function (new_res){
                            _tempBodyBlock = new_res;
                            //$html += "<div class='hui-body' >" + new_res + "</div>";
                        });
                    }


                    // HEADER --------------------------------------------------------------------------------------------

                    if (_tempHeaderBlock) {
                        $html += '<div class="hui-header-container ' + options.header.class + '" >';
                        $html += _tempHeaderBlock;
                        if (options.showClose) {
                            $html += "<a class='close-button inj' data-close aria-label='" + options.showCloseText + "' title='" + options.showCloseText + "' type='button'>" +
                                        "<span aria-hidden='true'>&times;</span>" +
                                     "</a>";
                        }
                        $html += "</div>";
                    } else {
                        if (options.ajax.type == "json" && _jsonRes && _jsonRes.header) {
                            $html += _buildHeader(true, _jsonRes.header);
                        } else {
                            $html += _buildHeader();
                        }
                    }

                    // BODY ----------------------------------------------------------------------------------------------
                    var _bodyID = genRandom();
                    $html += "<div class='hui-body' id='" + _bodyID + "' >" + _tempBodyBlock + "</div>";
                    if (options.maxHeight > 0) {
                        $("body").addClass("hui-mheight");
                        $("<style type='text/css' >#"+_bodyID+"{ height:" + options.maxHeight + "px; }</style>").appendTo("head");
                    }
                    // FOOTER --------------------------------------------------------------------------------------------
                    var _footerClass = options.footer.class || "";
                    var _footerContainerClass = options.footer.buttonsContainerClass || "hui-footer-buttons";

                    if (_tempFooterBlock) {
                        // Override Footer Behaviour
                        $html +=  "<div class='hui-footer " + _footerClass + "' >";
                        $html +=  "<div class='" + _footerContainerClass + "' >";
                        $html += _tempFooterBlock;
                        $html += "</div>";
                    } else {
                        //if (options.ajax.type == "json" && options.footer && options.footer.buttons.length > 0) {
                        if (options.ajax.type == "json" && _jsonRes && _jsonRes.footer) {
                            $html +=  "<div class='hui-footer " + _footerClass + "' >";
                            if (_jsonRes.footer.buttons) {
                                $html +=  "<div class='" + _footerContainerClass + "' >";
                                $(_jsonRes.footer.buttons).each( function (i, item){
                                    var _class      = item.class || "button";
                                    var _text       = item.text;
                                    var _icon       = item.icon || "";
                                    var _url        = item.url;
                                    var _callback   = item.onClick;
                                    var _id         = 'hui-footer-' + genRandom();
                                    var _dismissOnClick   = item.dismissOnClick;

                                    $html +=  "<a class=\"" + _class + "\" id='" + _id + "' " + (_url ? 'href="' + _url + '" ' : '') + ">" + _icon + " " + _text + "</a>";

                                    if(_callback ||_dismissOnClick) {
                                        $(document).on("click", "#" + _id, function (e) {
                                            e.preventDefault();
                                            e.stopImmediatePropagation();
                                            if (_callback) {
                                                eval(_callback);
                                            } else if (_dismissOnClick == true) {
                                                //_instance.close();
                                                close();
                                            }
                                        });
                                    }
                                });
                                $html += "</div>";
                            }


                        //} else if (res && res.footer) {
                        } else if (options.footer) {
                            $html +=  "<div class='hui-footer " + _footerClass + "' >";
                            if (typeof options.footer.buttons !== 'undefined') {
                                $html +=  "<div class='" + _footerContainerClass + "' >";
                                $(options.footer.buttons).each( function (i, item){
                                    var _class      = item.class || "button";
                                    var _text       = item.text;
                                    var _icon       = item.icon || "";
                                    var _url        = item.url;
                                    var _callback   = item.callback;
                                    var _dismissOnClick   = item.dismissOnClick;
                                    var _id         = 'hui-footer-' + genRandom();
                                    $html +=  "<a class=\"" + _class + "\" id='" + _id + "' " + (_url ? 'href="' + _url + '" ' : '') + ">" + _icon + " " + _text + "</a>";

                                    if(_callback ||_dismissOnClick) {
                                        $(document).on("click", "#" + _id, function (e) {
                                            e.preventDefault();
                                            e.stopImmediatePropagation();
                                            if (_callback) {
                                                if(jQuery.isFunction(_callback)) {
                                                    _callback.call(this);
                                                } else {
                                                    eval(_callback);
                                                }
                                            } else if (_dismissOnClick == true) {
                                                //_instance.close();
                                                close();
                                            }
                                        });
                                    }
                                });
                                $html += "</div>";
                            }
                        }
                    }
                    //$html +=  "<a class=\"button\" >AVANTI</a>";
                    $html +=  "</div>";
                    // Footer - end
                    $html +=  "</div>";

                    if (options.wrapperClass) {
                        $html +=  "</div>";
                    }

                    if (options.onBlurContainer !== '') {
                        $(options.onBlurContainer).addClass('hui-blur');
                    }

                    setTimeout ( function (){
                        $($html).appendTo($(options.globalContainerSelector));
                        bindListeners();
                        open();
                    }, options.animation.delay);

                    setTimeout( function (){
                        $("body").removeClass("hui-modal-opening");
                        $("body").addClass("hui-ready");

                        setTimeout( function (){
                            $(".hui-loader").remove();
                        }, 400);
                    }, 700);

                    //var $modal = new Foundation.Reveal($('#' + getModalSelector()), options.extra);
                    //_instance = $modal;
                    //$modal.open();
                    //$('#' + getModalSelector()).addClass("show");
                    /*
                    $(document).on('open.zf.reveal', '#' + getModalSelector(), function (){
                        options.onOpening(this);
                    });


                    $(document).on('closed.zf.reveal', '#' + getModalSelector(), function (){
                        $("body").addClass("hui-modal-closing");
                        setTimeout( function (){
                            $("body").removeClass("hui-modal-closing");
                        }, 600);


                        //$("body").removeClass("hui-ready");
                        if (options.onBlurContainer !== '') {
                            $(options.onBlurContainer).removeClass('hui-blur');
                        }
                        options.onClosing(this);
                    });
                    */



                });
            }

            function bindListeners() {
                var $handler = $('#' + getModalSelector());
                var $close_button = $('.close-button', $handler);
                var $overlay = $handler.parent();

                //$(document).on('click', $close_button , function (e){
                $close_button.bind('click' , function (e){
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    close();
                });
                if (options.closeByDocument === true) {
                    //$(document).on('click', $overlay , function (e){
                    $overlay.bind("click", function (e){
                        e.preventDefault();

                        if (e.target.id == getModalSelector() || $(e.target).parents("#" + getModalSelector()).length) {
                          //alert("Inside div");
                        } else {
                          //alert("Outside div");
                          e.stopImmediatePropagation();
                          close();
                        }
                        //
                    });
                }
                $("body").bind('keyup', function (e){
                    onDocumentKeyup(e);
                });
            }

            function unbindListeners() {
                var $handler = $('#' + getModalSelector());
                var $close_button = $('.close-button', $handler);
                var $overlay = $handler.parent();
                $(document).unbind('click', $close_button);
                $(document).unbind('click', $overlay);
                $("body").unbind('keyup');

            }

            function open() {
                options.onOpening(this);
                if (options.animation.in !== '') {
                    //setTimeout ( function (){
                        cssAnimate.call($('#' + getModalSelector()), options.animation.in, function (){
                            //$('#' + getModalSelector()).removeClass("comingIn");
                            $("body").addClass("hui-modal-open");
                        });
                    //}, options.animation.delay);
                } else {
                    $("body").addClass("hui-modal-open");
                }
            }

            function close() {
                unbindListeners();
                options.onClosing(this);
                $('#' + getModalSelector()).off(getAnimationEnd());
                if (options.onBlurContainer !== '') {
                    $(options.onBlurContainer).removeClass('hui-blur');
                }
                if (options.animation.out !== '') {
                    cssAnimate.call($('#' + getModalSelector()), options.animation.out, function (){
                        $('#' + getModalSelector()).parent().removeClass("show");
                        $("body").removeClass("hui-modal-open");
                        $("body").removeClass("hui-ready");
                        $("body").removeClass("hui-scroll hui-mheight");
                    });
                } else {
                    $("body").removeClass("hui-modal-open");
                    $("body").removeClass("hui-ready");
                    $("body").removeClass("hui-scroll hui-mheight");
                }
            }

            function activate_bootstrapPopup() {
                var $html = "";

                if (options.wrapperClass) {
                    $html += "<div class='" + options.wrapperClass + "' >";
                }

                $html += "<div class='modal fade' id='" + genModalID() + "'  >";
                $html += "<div class=\"modal-dialog\">";
                $html += "<div class=\"modal-content\">";
                if (options.hideTitle == false) {
                    $html += "<div class=\"modal-header\">";
                    if (options.showClose) {
                        $html +=    "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">&times;</button>";
                    }
                    $html += "<h4 class=\"modal-title\">" + options.title + "</h4>";

                    $html += "</div>";
                }

                _buildLoader();
                var template = getTemplate(function (response){
                    var content = response;
                    $html += "<div class=\"modal-body\">" + content + "</div>";


                    $html +=  "<div class=\"modal-footer\">" +
                                "<button type=\"button\" class=\"btn btn-primary\">Save changes</button>" +
                                "<button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>" +
                              "</div>";
                    $html += "</div>";
                    $html += "</div>";
                    $html += "</div>";

                    if (options.wrapperClass) {
                        $html +=  "</div>";
                    }

                    $($html).appendTo($(options.globalContainerSelector));

                    $('#' + getModalSelector()).modal();
                    $('#' + getModalSelector()).on('shown.bs.modal', options.onOpening);
                    $('#' + getModalSelector()).on('hidden.bs.modal', options.onClosing);
                    $('.modal-backdrop').appendTo($(options.globalContainerSelector));

                });

            }

            function activate_customPopup() {
                setTimeout(function () {
                    body.addClass('pmModal-active');
                }, 100);

                var $popin = $('<div class="pmModal-popin ' + options.holderClass + '"></div>');
                $popin.append(template);
                body.append($popin);

                $('.pmModal-popin').css({
                    'width': maxWidth + 'px',
                    'height': maxHeight + 'px',
                    'margin-left': '-' + (maxWidth / 2 + 10) + 'px',
                    'margin-top': '-' + (maxHeight / 2 + 10) + 'px'
                });

                if (options.showClose) {
                    $('.pmModal-popin').append('<a href="#" class="pmModal-close">' + options.showCloseText + '</a>');
                }

                if (options.enableStackAnimation) {
                    $('.pmModal-popin').addClass('stack');
                }

            }

            function activate () {
                //$(".reveal-overlay").remove();

                if (typeof options.onLoad === 'function') {
                    options.onLoad(self);
                }

                var type = options.type;
                var method = "activate_" + type + "Popup";
                //console.log(method);
                eval(method)();


                //body.bind('keyup', onDocumentKeyup)
                //    .bind('click', onDocumentClick);
            }

            function deactivate () {
                if (typeof options.onClosing === 'function') {
                    if (!options.onClosing(self)) {
                        return false;
                    }
                }

                body.unbind('keyup', onDocumentKeyup)
                    .unbind('click', onDocumentClick)
                    .removeClass('pmModal-active');

                setTimeout(function () {
                    $('.pmModal-popin').remove();

                }, 500);

                if (typeof options.onUnload === 'function') {
                    options.onUnload(self);
                }
            }

            function buildDebug(message, _type) {
                var alertFallback = false;
                if (typeof console === "undefined" || typeof console.log === "undefined") {
                    console = {};
                    if (alertFallback) {
                        console.log = function (msg) {
                            alert(msg);
                        };
                    } else {
                        console.log = function () { };
                    }
                }

                console.log(" [" + _type.toUpperCase() + "] ==> " + message);
            }

            if (options.openOnEvent) {
                self.bind(options.setEvent, function (e) {
                    e.stopPropagation();

                    if ($(e.target).is('a')) {
                        e.preventDefault();
                    }

                    activate();
                });
            } else {
                activate();
            }
        });
    };


      //  The   _   ___ ___
      //       /_\ | _ \_ _|
      //      / _ \|  _/| |
      //     /_/ \_\_| |___|
      //
      $.huiModal = {};


      $.huiModal.unbind = function($obj){
        var seq = $obj.data('plugin_' + pluginName);

        if ( typeof seq === 'undefined' )
          return;

        $obj.removeData('plugin_' + pluginName);

      };

}));
