ff.modal = (function () {
    //privates var and functions
    var __ff                                    = false;
    var prefix                                  = "ffm-";
    var frameworkCss                            = "bootstrap";
    var fontIcon                                = "fontawesome";
    var dialogs		                            = {}; //contenitore dei tutte le modali
    var dialogs_rev                             = {};
    var models                                  = {
                                                    "default" : {

                                                    },

                                                    "yesno" : {

                                                    },
                                                    "callToAction" : {
                                                        "footer" : {
                                                            "buttonsContainerClass" : "text-right"
                                                        }
                                                    }
    };
    var defs                                    = {
                                                    "animation" : {
                                                        "coming" : {
                                                            "animation" : {
                                                                in: 'comingIn',
                                                                out: 'bounceOutDown'
                                                            }
                                                        }
                                                    },
                                                    "skin" : {
                                                        "blue" : {
                                                            "header" : {
                                                                "class" : "blue--gradient white"
                                                            }
                                                        }
                                                    },
                                                    "width" : "baseClass",
                                                    "description" : "subtitle",
                                                    "close" : "showClose",
                                                    "exclude" : "excludeElements",
                                                    "header" : "headerBlockSelector",
                                                    "footer" : "footerBlockSelector",
                                                    "buttons" : {
                                                        "label": "text",
                                                        "close": "dismissOnClick",
                                                        "class" : "class",
                                                        "callback": "callback",
                                                        "url" : "url",
                                                        "type": null,
                                                        "icon": null

                                                    }


                                                };
    var defaults                                = {};/* {
        debug                                   : true,
        type                                    : '',
        animation                               : {
                                                    in                      : 'fadeIn',
                                                    out                     : 'fadeOut',
                                                    delay                   : 0
                                                },
        position                                : "center center",
        header                                  : {
                                                    class                   : '',
                                                    buttons                 : []
                                                },
        title                                   : "",
        subtitle                                : "",
        icon                                    : "",
        width                                   : 380, // max = 640
        height                                  : 280, // max = 350
        scroll                                  : false,
        maxHeight                               : 0,
        responsive                              : { //todo:che serve?

                                                },
        globalContainerSelector                 : "body",
        showClose                               : true,
        showCloseText                           : 'Chiudi',
        closeByEscape                           : true,
        closeByDocument                         : true,
        wrapperClass                            : '',
        baseClass                               : '', //tiny | small | medium
        holderClass                             : '',
        overlayClass                            : '',
        hideTitle                               : false,
        enableStackAnimation                    : false,

        openOnEvent                             : true,
        setEvent                                : 'click',
        headerBlockSelector                     : '',
        footerBlockSelector                     : '',
        excludeElements                         : [],
        ///onRender: function(response){             /todo:serve dichiararlo nel default?
        //    if (options.excludeElements.length > 0) {
        //        $("<div class='hidden' id='hui--temp'>"+response+"</div>").appendTo($('body'))
        //        $("#hui--temp").find(options.excludeElements.join(',')).remove();
        //        var $newHtml = $("#hui--temp").html();
       //         $("#hui--temp").remove();
       //         return $newHtml;
        //    }
        //    return response;
        //},
        onBlurContainer                       : '', //todo: cosa e questo? una funzione?
        onOpening                             : function(){},
        onClosing                             : function(){},
        onOpened                              : function (){},
        onClosed                              : function (){},
        url                                     : '',                // AJAX Url
        ajax                                    : {
                                                    type                    : "html",
                                                    loader                  : false,      // AJAX Loader
                                                    data                    : {},           // AJAX Data
                                                },
        template                                : '', //<p>This is test popup content!</p>',
        footer                                  : {
                                                    class                   : '',
                                                    buttonsContainerClass   : '',
                                                    buttons                 : [

                                                       // {
                                                       //   text      : "Procedi",
                                                       //   class     : 'primary',
                                                       //   callback  : function (){
                                                      //      alert('callback');
                                                      //    }
                                                      //  }

                                                    ]
                                                },
        mobile                                  : {
                                                    hideSubtitle            : false,
                                                    headerSticky            : false,
                                                    footerSticky            : false
                                                }
    };*/
    var isUrl = function(url) {
        return (url.indexOf("/") < 0
            ? false
            : /^(https?:\/\/)?((([a-z\d]([a-z\d-]*[a-z\d])*)\.?)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3}))(\:\d+)?(\/[-a-z\d%_.~+]*)*(\?[;&a-z\d%_.~+=-]*)?(\#[-a-z\d_]*)?$|^((\\(\\[^\s\\]+)+|([A-Za-z]:(\\)?|[A-z]:(\\[^\s\\]+)+))(\\)?)$/.test(url)
        );
    }
    var getID = function() {
        return prefix + Date.now();
    }

    var clone = function(obj) {
        return JSON.parse(JSON.stringify(obj));
    }

    var hashCode = function(s) {
        if(s) {
            var h = 0, l = s.length, i = 0;
            if (l > 0)
                while (i < l)
                    h = (h << 5) - h + s.charCodeAt(i++) | 0;
            return h;
        }
    };
    var getIcon = function(icon, params) {
        return (isUrl(icon)
                ? getImageIcon(icon)
                : getClassByFrameworkCss(icon, "icon-tag", params)
        );
    };
    var getImageIcon = function(src) {
        return '<img src="' + src + '" />';
    };

    var getFontIconSettings = function(name) {

    };

    var getFrameworkCss = function(key) { //provvisorio
        var framework_name = ff.frameworkCss || frameworkCss; //;

        var framework_css = {
            "base" : {
                "name" : "base"
            },
            "bootstrap" : {
                "name" : "bootstrap"
            },
            "foundation" : {
                "name" : "foundation"
            }
        };

        return (key
                ? framework_css[framework_name][key]
                : framework_css[framework_name]
        )
    };

    var getFontIconSettings = function(name) {
        var font_icon_setting = {
            "base": {
                "css": "",
                "prefix": "icon",
                "postfix": "",
                "prepend": "ico-",
                "append": ""
            }
            , "glyphicons": {
                "css": window.location.protocol + "://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css",
                "prefix": "glyphicons",
                "postfix": "",
                "prepend": "",
                "append": ""
            }
            , "fontawesome": {
                "css": window.location.protocol + "://netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.css",
                "prefix": "fa",
                "postfix": "",
                "prepend": "fa-",
                "append": ""
            }
        };

        return (name
            ? font_icon_setting[name]
            : font_icon_setting
        );
    };
    var getFontIcon = function(icon, params) {
        var res                         = [];
        var fonticon_name               = ff.fontIcon || fontIcon;
        var fontIcon                    = getFontIconSettings(fonticon_name);

        res.push(fontIcon["prefix"]);
        res.push(fontIcon["prepend"] + icon + fontIcon["append"]);

        if(Array.isArray(params)) {
            params.each(function(i, param) {
                res.push(fontIcon["prepend"] + param + fontIcon["append"]);
            });
        } else if(params) {
            res.push(fontIcon["prepend"] + params + fontIcon["append"]);
        }
        res.push(fontIcon["postfix"]);
        //todo: da implementare ff.getFontIcon
        return res.join(" ").trim(" ");
    };
    var getFontIconTag = function(icon, size) {
        return '<i class="' + getFontIcon(icon, size) + '"></i>';
    };

    var getClassByFrameworkCss = function(name, type, params) {
        switch(type) {
            case "button":
                res = getButtonByFrameworkCss(name, params);
                break;
            case "icon":
            case "icon-tag":
                res = getFontIconTag(name, params);
            default:
        }

        return res;
    };

    var getButtonByFrameworkCss = function(name, params) {
        var res                                         = [];
        var framework_name                              = getFrameworkCss("name");

        var framework_css = {
            "base" : {
                "base"                                  : "btn",
	            "skip-default"                          : false,
	            "width"                                 : {
                                                            "full"          : "expand"
                                                        },
	            "size"                                  : {
                                                            "large"         : "large",
                                                            "small"         : "small",
                                                            "tiny"          : "tiny"
                                                        },
	            "state"                                 : {
                                                            "current"       : "current"
                                                            , "disabled"    : "disabled"
                                                        },
	            "corner"                                : {
                                                            "round"         : "round"
                                                            , "radius"      : "radius"
                                                        },
	            "color"                                 : {
                                                            "default"       : "",
                                                            "primary"       : "primary",
                                                            "secondary"     : "",
                                                            "success"       : "success",
                                                            "info"          : "info",
                                                            "warning"       : "warning",
                                                            "danger"        : "danger",
                                                            "link"          : "link"
                                                        }
            },
            "bootstrap" : {
                "base"                                  : "btn",
	            "skip-default"                          : true,
	            "width"                                 : {
                                                            "full"          : "btn-block"
                                                        },
	            "size"                                  : {
                                                            "large"         : "btn-lg",
                                                            "small"         : "btn-sm",
                                                            "tiny"          : "btn-xs"
                                                        },
	            "state"                                 : {
                                                            "current"       : "active"
                                                            , "disabled"    : "disabled"
                                                        },
	            "corner"                                : {
                                                            "round"         : false
                                                            , "radius"      : false
                                                        },
	            "color"                                 : {
                                                            "default"       : "btn-default",
                                                            "primary"       : "btn-primary",
                                                            "secondary"     : "btn-default",
                                                            "success"       : "btn-success",
                                                            "info"          : "btn-info",
                                                            "warning"       : "btn-warning",
                                                            "danger"        : "btn-danger",
                                                            "link"          : "btn-link"
                                                        }
            },
            "foundation" : {
                "base"                                  : "button",
	            "skip-default"                          : true,
	            "width"                                 : {
                                                            "full"          : "expand"
                                                        },
	            "size"                                  : {
                                                            "large"         : "large",
                                                            "small"         : "small",
                                                            "tiny"          : "tiny"
                                                        },
	            "state"                                 : {
                                                            "current"       : "current"
                                                            , "disabled"    : "disabled"
                                                        },
	            "corner"                                : {
                                                            "round"         : "round"
                                                            , "radius"      : "radius"
                                                        },
	            "color"                                 : {
                                                            "default"       : "secondary",
                                                            "primary"       : "primary",
                                                            "secondary"     : "secondary",
                                                            "success"       : "success",
                                                            "info"          : "secondary",
                                                            "warning"       : "alert",
                                                            "danger"        : "alert",
                                                            "link"          : "secondary"
                                                        }
            }

        };

        res.push(framework_css[framework_name]["base"]);
        res.push(framework_css[framework_name]["color"][name]);

        if(typeof params == "object") {
            for (var property in params) {
                if (params.hasOwnProperty(property)
                    && params[property] !== undefined
                    && framework_css[getFrameworkCss("name")][property][params[property]]
                ) {
                    res.push(framework_css[getFrameworkCss("name")][property][params[property]]);
                } else if(property == "class") {
                    res.push(params[property]);
                }
            }
        } else if(params) {
            res.push(params);
        }

        return res.join(" ").trim(" ");
    };
    var paramsToModel = function(params) {
        var model                                       = clone(params["type"] && models[params["type"]]
                                                            ? models[params["type"]]
                                                            : models["default"]
                                                        );

        if(params["icon"]) {
            model["icon"] = getIcon(params["icon"], "2x");
        }

        for (var property in params) {
            if (params.hasOwnProperty(property)
                && params[property] !== undefined
                && property != "icon"
                && property != "type"
                && property != "class"
            ) {
                var value                               = params[property];

                if(Array.isArray(value)) {
                    var buttons                             = [];
                    value.each(function(i, button) {
                        if(typeof button != "object") {
                            model[defs[property]]           = value;
                            return;
                        }
                        var btn = {};

                        for (var key in button) {
                            if (button.hasOwnProperty(key)
                                && button[key] !== undefined
                                && key != "icon"
                                && key != "type"
                            ) {

                                if (defs["buttons"][key]) {
                                    btn[defs["buttons"][key]] = button[key];
                                }
                            }
                        }

                        if(button["type"]) {
                            btn["class"] = getClassByFrameworkCss(button["type"], "button", button["class"]);
                        }
                        if(button["icon"]) {
                            btn["icon"] =  getIcon(params["icon"]);
                        }

                        buttons.push(btn);
                    });
                    if(buttons.length) {
                        if(!model[property])
                            model[property] = {};

                        model[property]["buttons"] = buttons;
                    }
                } else if(defs[property]) {
                    if(typeof defs[property] == "object") {
                        model                           = Object.assign(model, defs[property][value]);
                    } else {
                        model[defs[property]]           = value;
                    }
                } else {
                    model[property]                     = value;
                }
            }
        }

        return model;
    };
    var add = function (data, params, callback) {
        var limitHash                                   = 30;
        var key                                         = hashCode(params);
        var url                                         = undefined;
        var ajax                                        = undefined;
        var template                                    = undefined;

        if(isUrl(data)) {
            url                                         = data;
            ajax                                        = {
                                                            "data"                  : params,
                                                            "type"                  : "json",
                                                            "loader"                : true
                                                        };
            key                                         = url + "?" + key;
        } else {
            template                                    = data;
            key                                         = hashCode(data.substr(0, limitHash)) + "?" + key;

        }

        if(!dialogs[key]) {
            var model                                   = (typeof params == "object"
                                                            ? paramsToModel(params)
                                                            : clone(typeof params != "object" && models[params]
                                                                ? models[params]
                                                                : model["default"]
                                                            )
                                                        );

            if(params["header"] === false)
                model["hideTitle"]                       = true;

            var source                                  = {
                                                            "id"                    : getID(),
                                                            "url"                   : url,
                                                            "ajax"                  : ajax,
                                                            "template"              : template,
                                                            type                    : getFrameworkCss("name"),
                                                            openOnEvent             : false,
                                                            headerBlockSelector     : "dialogTitle",
                                                            footerBlockSelector     : "dialogActionsPanel",
                                                            "onOpened"              : callback
                                                        };

            dialogs[key]                                = Object.assign(defaults, source, model);

            dialogs_rev[dialogs[key]["id"]]             = key;
        }

        return dialogs[key];
    };


    var that = { // publics var and functions
        "get" : function (id) {
            return dialogs[dialogs_rev[id]];
        },
        "open" : function(url, params, callback) {
            var dialog = add(url, params, callback);

/*dialog = {
    type: "foundation",
    title: "Simple Modal title",
    baseClass: 'large', //tiny | small | medium
    globalContainerSelector: "#example_frame_foundation",
    template: "Modal content"
};*/
            ff.injectCSS("jquery.hui.modal", "/themes/library/plugins/jquery.hui/modal/hui.modal.css");
            ff.pluginLoad("jquery.hui.modal", "/themes/library/plugins/jquery.hui/modal/hui.modal.js", function() {
                jQuery(document).huiModal(dialog); //todo:: non funziona
            });
            /*
            url: https://dev.paginemediche.it/pro/strutture/gestione come ippocrate

jQuery(document).huiModal({
    type: "foundation",
    title: "Simple Modal title",
    baseClass: 'large',
    globalContainerSelector: "#example_frame_foundation",
    template: "Modal content"
});

ff.pluginLoad("ff.modal", "/themes/library/ff/modal.js")

ff.modal.open("/srv/strutture-modifica/save-answer", {
        id_struttura: id_struttura
        , id_anagraph: id_anagraph
        , action: action
        , text: message
        , struttura_smart_url: struttura_smart_url
    }, function (data) {
        alert("entro");
    });

             */

        },
        "addTabs": function (id, tabs) {
            var tab = { //example di params
                "label"             : "",//far apparire la scritta
                "placeholder"       : "", //placeholder che appare sulla icona eventuale
                "icon"              : "", //aggiungere una icona sempre presa dal fontIcon
                "close"             : false, //se visualizzare o meno il bottone chiudi
                "content"           : "" // selettore o porzione html
            };
        },
        "addButtons": function (id, buttons) {
            var button = { //example di params
                "label"             : ""
                , "placeholder"     : "Chiudi"
                , "icon"            : "close"
                , "display"         : true
                , "class"           : {
                    "util"          : ["align-right"]
                }
                , "fixed"           : false //override che permette di posizionare un bottone al di fuori della dialog. Valori possibili: top-left top-right bottom-left bottom-right
                , "positon"         : "header" //header || footer
            };
        },
        "doOpen": function (id, url, params) {


        },
        "goToUrl" : function (id, url) {

        },
        "onSuccess" : function (data, customdata) {

        },
        "onClose" : function (id, hide) {

        },
        "doRequest" : function (id, params) {

        },
        "doAction" : function (id, action) {

        }
    }; // publics' end
    return that;
})();



