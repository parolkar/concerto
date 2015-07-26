/*
 Concerto Platform - Online Adaptive Testing Platform
 Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; version 2
 of the License, and not any of the later versions.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

dictionary = {};

String.prototype.format = function() {
    var formatted = this;
    for (var i = 0; i < arguments.length; i++) {
        formatted = formatted.replace("{" + i + "}", arguments[i]);
    }
    return formatted;
};

function Methods() {
}
;

Methods.toDate = function(value) {
    var date = value.split("-");
    return new Date(parseInt(date[0]), parseInt(date[1]), parseInt(date[2]));
}


Methods.loading = function(selector) {
    $(selector).html("<div align='center' style='width:100%;height:100%;'><table style='width:100%;height:100%;'><tr><td valign='middle' align='center'><img src='css/img/ajax-loader.gif' /></td></tr></table></div>")
};

Methods.currentView = 0;
Methods.uiChangeView = function(view) {
    $.post("query/change_view.php", {
        view: view
    }, function(data) {
        if (data.result == 0) {
            Methods.currentView = view;
            if (view == 0) {
                $(".viewDependant").addClass("notVisible");
                $(".viewReverslyDependant").removeClass("notVisible");
            } else {
                $(".viewDependant").removeClass("notVisible");
                $(".viewReverslyDependant").addClass("notVisible");
                Template.uiRefreshCodeMirrors();
            }
            Table.onViewSwitch(view);
        }
    }, "json");
}

Methods.toggleExpand = function(selector, btnSelector) {
    var icon = $(selector).is(":visible") ? "arrowthick-1-s" : "arrowthick-1-n";
    $(selector).toggle(0);
    $(btnSelector).button("option", "icons", {
        primary: "ui-icon-" + icon
    });
}

Methods.incrementProgress = function(value, max) {
    if (Methods.modalProgressMaxValue == 0)
        return;
    if (value == null)
        value = 1;
    if (max == null)
        max = Methods.modalProgressMaxValue;
    Methods.modalProgressValue += value;
    $("#divProgressBar").progressbar("value", Math.floor(Methods.modalProgressValue / Methods.modalProgressMaxValue * 100));
    if (Methods.modalProgressValue == max)
        Methods.stopModalProgress();
}

Methods.changeProgress = function(value, max) {
    if (Methods.modalProgressMaxValue == 0)
        return;
    if (value == null)
        value = 0;
    if (max == null)
        max = Methods.modalProgressMaxValue;
    Methods.modalProgressValue = value;
    $("#divProgressBar").progressbar("value", Math.floor(Methods.modalProgressValue / Methods.modalProgressMaxValue * 100));
    if (Methods.modalProgressValue == max)
        Methods.stopModalProgress();
}

Methods.modalProgressMaxValue = 0;
Methods.modalProgressValue = 0;
Methods.modalProgress = function(title, max) {
    if (max == null)
        max = 100;
    Methods.modalProgressMaxValue += max;
    if (Methods.modalProgressMaxValue == max) {
        if (title == null)
            title = dictionary["s319"];
        $("#divProgressDialog").dialog({
            title: title,
            minHeight: 50,
            resizable: false,
            modal: true,
            closeOnEscape: false,
            dialogClass: "no-close",
            open: function() {
                $('.ui-widget-overlay').css('position', 'fixed');
                $("#divProgressBar").progressbar();
            },
            close: function() {
                //$('.ui-widget-overlay').css('position', 'absolute');  
                $("#divProgressBar").progressbar("destroy");
                Methods.modalProgressMaxValue = 0;
                Methods.modalProgressValue = 0;
            },
            buttons:
                    {
                    }
        });
    }
}

Methods.stopModalProgress = function() {
    $("#divProgressDialog").dialog("close");
}

Methods.reload = function(hash) {
    var address = location.href.split("#");
    location.replace(address[0] + (hash != null ? "#" + hash : ""));
    location.reload();
}

Methods.iniSortableTableHeaders = function() {
    $(".thSortable").mouseover(function() {
        $(this).addClass("ui-state-highlight");
    });
    $(".thSortable").mouseout(function() {
        $(this).removeClass("ui-state-highlight");
    })
}

Methods.iniIconButton = function(selector, icon) {
    $(selector).button({
        icons: {
            primary: "ui-icon-" + icon
        }
    });
}

Methods.confirmUnsavedLost = function(callback, modules) {
    var message = "";
    if (modules != null) {
        message = dictionary["s321"];
        message += "<br/><br/>";
        for (var i = 0; i < modules.length; i++) {
            message += "<b>" + modules[i] + "</b><br/>"
        }
    }
    else
        message = dictionary["s322"];
    Methods.confirm(message, dictionary["s320"], callback);
}

Methods.confirm = function(message, title, callback)
{
    if (title == null)
        title = dictionary["s4"];
    $("#divGeneralDialog").html('<span class="ui-icon ui-icon-help" style="float:left; margin:0 7px 0px 0;"></span>' + message);
    $("#divGeneralDialog").dialog({
        title: title,
        minHeight: 50,
        resizable: false,
        modal: true,
        closeOnEscape: false,
        dialogClass: "no-close",
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            //$('.ui-widget-overlay').css('position', 'absolute');
        },
        buttons: [
            {
                text: dictionary["s628"],
                click: function() {
                    $(this).dialog("close");
                }
            },
            {
                text: dictionary["s627"],
                click: function() {
                    $(this).dialog("close");
                    callback.call(this);
                }
            }
        ]
    });
};

Methods.alert = function(message, icon, title, callback)
{
    if (title == null)
        title = dictionary["s5"];
    $("#divGeneralDialog").html((icon != null ? '<span class="ui-icon ui-icon-' + icon + '" style="float:left; margin:0 7px 0px 0;"></span>' : '') + message);
    $("#divGeneralDialog").dialog({
        title: title,
        minHeight: 50,
        resizable: false,
        modal: true,
        open: function() {
            $('.ui-widget-overlay').css('position', 'fixed');
        },
        close: function() {
            //$('.ui-widget-overlay').css('position', 'absolute');
            if (callback != null)
                callback.call(this);
        },
        buttons: [
            {
                text: dictionary["s629"],
                click: function() {
                    $(this).dialog("close");
                    //if(callback!=null) callback.call(this);
                }
            }
        ]
    });
}

Methods.CKEditorDialogShowListener = function(e) {
    var dialog = CKEDITOR.dialog.getCurrent();
    var validatedDialogs = [
        "checkbox",
        "radio",
        "textfield",
        "textarea",
        "select",
        "button",
        "hiddenfield"
    ];

    var name = null;

    if (validatedDialogs.indexOf(dialog.getName()) != -1) {
        var contents = dialog.definition.contents;
        for (var i = 0; i < contents.length; i++) {
            var content = contents[i];
            var elements = content.elements;
            for (var j = 0; j < elements.length; j++) {
                var element = elements[j];
                if (element.type == "hbox" || element.type == "vbox") {
                    var children = element.children;
                    for (var k = 0; k < children.length; k++) {
                        var child = children[k];
                        if (child.label == "Name") {
                            name = dialog.getContentElement(content.id, child.id);
                            break;
                        }
                    }
                    if (name != null)
                        break;
                }
                else {
                    if (element.label == "Name") {
                        name = dialog.getContentElement(content.id, element.id);
                        break;
                    }
                }
            }
            if (name != null)
                break;
        }
        if (name != null) {
            name.validate = function() {
                var oldValue = this.getValue();
                if (!Test.variableValidation(oldValue))
                {
                    var newValue = Test.convertVariable(oldValue);
                    name.setValue(Test.convertVariable(newValue));
                    alert(dictionary["s6"].format(oldValue, newValue));
                    return false;
                }
            }
        }
    }
};

Methods.iniCKEditor = function(selector, callback, width)
{
    var opts = {};
    if (width != null) {
        opts.width = width;
    }
    Methods.removeCKEditor(selector);
    var editor = $(selector).ckeditor(function() {
        this.removeListener('dialogShow', Methods.CKEditorDialogShowListener);
        this.on('dialogShow', Methods.CKEditorDialogShowListener);
        this.on("mode", function(e) {
        })
        if (callback != null)
            callback.call(this);
    }, opts);
    return editor;
};


Methods.removeCKEditor = function(selector)
{
    var name = $(selector).attr("name");
    var instance = CKEDITOR.instances[name];
    if (instance)
    {
        try {
            $(selector).ckeditorGet().destroy(true);
        } catch (err) {

        }
        CKEDITOR.remove(instance);
    }
};

Methods.iniColorPicker = function(selector, color)
{
    $(selector).css("background-color", "#" + color);
    $(selector).ColorPicker({
        color: color,
        onShow: function(colpkr) {
            $(colpkr).fadeIn(250);
            return false;
        },
        onHide: function(colpkr) {
            $(colpkr).fadeOut(250);
            return false;
        },
        onChange: function(hsb, hex, rgb) {
            $(selector).val(hex);
            $(selector).css("background-color", "#" + hex);
        }
    });
};

Methods.iniDatePicker = function(selector)
{
    $(selector).datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
    });
};

Methods.iniTimePicker = function(selector)
{
    $(selector).each(function(index) {
        var id = $(this).attr("id");
        $("#" + id).timepicker({});
    });
};

Methods.getCKEditorData = function(selector)
{
    try {
        return $(selector).ckeditorGet().getData();
    }
    catch (err) {
        return "";
    }
}

Methods.setCKEditorData = function(selector, data) {
    try {
        return $(selector).ckeditorGet().setData(data);
    }
    catch (err) {
    }
}

Methods.getTempID = function()
{
    var time = new Date().getTime();
    return User.sessionID + "_" + time;
};

Methods.iniCodeMirror = function(id, mode, readOnly, autocomplete, widgetsPossible, lineNumbers)
{
    if (autocomplete == null)
        autocomplete = false;
    if (widgetsPossible == null) {
        widgetsPossible = false;
    }
    if (lineNumbers == null) {
        lineNumbers = true;
    }
    var obj = null;
    if (id.substring) {
        obj = document.getElementById(id);
    } else {
        obj = id;
    }

    var myCodeMirror = CodeMirror.fromTextArea(obj, {
        mode: mode,
        fixedGutter: false,
        theme: "neat",
        lineNumbers: lineNumbers,
        matchBrackets: true,
        lineWrapping: true,
        autoClearEmptyLines: true,
        indentWithTabs: true,
        "readOnly": (readOnly != null & readOnly ? true : false),
        extraKeys: {
            "F11": function(instance) {
                Methods.setCodeMirrorFullScreen(instance, !Methods.isCodeMirrorFullScreen(instance));
            },
            "Esc": function(instance) {
                if (Methods.isCodeMirrorFullScreen(instance))
                    Methods.setCodeMirrorFullScreen(instance, false);
            },
            "F2": function(instance) {
                var range = {
                    from: instance.getCursor(true),
                    to: instance.getCursor(false)
                }
                instance.autoFormatRange(range.from, range.to);
                instance.autoIndentRange(range.from, range.to);
            },
            "Ctrl-Space": function(instance) {
                if (!autocomplete)
                    return;
                Test.iniAutoCompleteCodeMirror(mode, instance, widgetsPossible);
            }
        }
    });

    myCodeMirror.on("change", function(instance) {
        //instance.save();
        //instance.refresh();
    });
    myCodeMirror.on("blur", function(instance) {
        instance.save();
    });
    myCodeMirror["previousLineHandle"] = myCodeMirror.getLineHandle(0);
    myCodeMirror.addLineClass(myCodeMirror["previousLineHandle"], "background", "codeMirrorActiveLine");
    myCodeMirror.on("cursorActivity", function(instance) {
        instance.removeLineClass(instance["previousLineHandle"], "background", "codeMirrorActiveLine");
        var no = instance.getCursor(true).line;
        instance.addLineClass(no, "background", "codeMirrorActiveLine");
        instance["previousLineHandle"] = instance.getLineHandle(no);
    });

    //if(maxWidth!=null) $(obj).next().find(".CodeMirror-scroll").css("max-width",maxWidth);
    myCodeMirror.refresh();

    /*
     if (myCodeMirror.lineCount() > 0) {
     var range = {
     from: {
     line: 0,
     ch: 0
     },
     to: {
     line: myCodeMirror.lineCount() - 1,
     ch: myCodeMirror.getLine(myCodeMirror.lineCount() - 1).length
     }
     }
     myCodeMirror.autoFormatRange(range.from, range.to);
     myCodeMirror.autoIndentRange(range.from, range.to);
     //myCodeMirror.setSelection(range.to);
     }
     */

    return myCodeMirror;
};

Methods.uiToggleHover = function(obj, highlight) {
    if (obj.hasClass("ui-state-highlight") && !highlight)
        obj.removeClass("ui-state-highlight");
    else
        obj.addClass("ui-state-highlight");
}

Methods.iniTooltips = function() {
    $(".tooltip").tooltip({
        tooltipClass: "tooltipWindow",
        position: {
            my: "left top",
            at: "left bottom",
            offset: "15 0"
        },
        content: function() {
            return $(this).attr("title");
        },
        show: false,
        hide: false
    });
};

Methods.currentVersion = "";
Methods.latestVersion = "";
Methods.checkLatestVersion = function(callback, proxy)
{
    jQuery.getFeed({
        url: proxy == null ? 'lib/jfeed/proxy.php' : proxy,
        data: {
            url: "http://code.google.com/feeds/p/concerto-platform/downloads/basic"
        },
        success: function(feed) {
            var max = Methods.currentVersion;
            var isNewerVersion = false;
            if (feed.items == undefined) {
                Methods.latestVersion = "?";

                callback.call(this, 1, Methods.latestVersion);
                return;
            }
            for (var i = 0; i < feed.items.length; i++)
            {
                var desc = feed.items[i].description;
                if (desc.indexOf("Source-Version:") == -1)
                    continue;
                var version = desc.substr(desc.indexOf("Source-Version:") + 15);
                version = version.substr(0, version.indexOf("\n"));

                var amax = max.split(".");
                var avers = version.split(".");

                for (var a = 0; a < avers.length && a < amax.length; a++)
                {
                    var m = amax[a];
                    var v = avers[a];
                    if (a < 3) {
                        m = parseInt(m);
                        v = parseInt(v);
                    }
                    if (m > v)
                        break;
                    if (m < v)
                    {
                        max = version;
                        isNewerVersion = true;
                        break;
                    }
                }
            }

            Methods.latestVersion = max;

            callback.call(this, isNewerVersion ? 1 : 0, Methods.latestVersion);
        }
    });
};

Methods.iniDescriptionTooltips = function() {
    $(".tooltipDescription").tooltip({
        content: function() {
            return dictionary["s104"] + "<hr/>" + $(this).next().val();
        },
        position: {
            my: "left top",
            at: "left bottom",
            offset: "15 0"
        },
        show: false,
        hide: false
    });
}

Methods.isCodeMirrorFullScreen = function(cm) {
    return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
}
Methods.winHeight = function() {
    return window.innerHeight || (document.documentElement || document.body).clientHeight;
}

Methods.winWidth = function() {
    return window.innerWidth || (document.documentElement || document.body).clientWidth;
}

Methods.setCodeMirrorFullScreen = function(cm, full) {
    var wrap = cm.getWrapperElement();
    if (full) {
        wrap.className += " CodeMirror-fullscreen";
        wrap.style.height = Methods.winHeight() + "px";
        wrap.style.width = Methods.winWidth() + "px";
        document.documentElement.style.overflow = "hidden";
    } else {
        wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
        wrap.style.height = "auto";
        wrap.style.width = "";
        document.documentElement.style.overflow = "";
    }
    cm.refresh();
}

Methods.uiBlockModule = function(module, message) {
    Methods.uiBlock("#tnd_mainMenu-" + module, message);
    Methods.uiBlock(".divFormFloatingBar", "");
}

Methods.uiUnblockModule = function(module) {
    Methods.uiUnblock("#tnd_mainMenu-" + module);
    Methods.uiUnblock(".divFormFloatingBar");
}

Methods.uiBlock = function(selector, message) {
    if (message == null)
        message = dictionary["s319"];
    $(selector).block({
        message: message,
        overlayCSS: {
            backgroundColor: '#8FA1B5',
            opacity: 0.6,
            cursor: 'wait'
        }
    });
}

Methods.uiBlockAll = function(message) {
    if (message == null)
        message = dictionary["s319"];
    $.blockUI({
        message: message,
        overlayCSS: {
            backgroundColor: '#8FA1B5',
            opacity: 0.6,
            cursor: 'wait'
        }
    });
}

Methods.uiUnblockAll = function() {
    $.unblockUI();
}

Methods.uiUnblock = function(selector) {
    $(selector).unblock();
}