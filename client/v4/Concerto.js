/*
 Concerto Platform - Online Adaptive Testing Platform
 Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University
 
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

$.ajaxSetup({
    cache: false
});

function Concerto(container, wid, hash, sid, tid, queryPath, callbackGet, callbackSend, debug, remote, defaultLoadingImageSource, resumeFromLastTemplate) {

    this.timeFormat = "HH:mm:ss";
    this.isFirstTemplate = true;
    this.effectTransition = 0;
    this.loaderTransition = 0;

    this.isTemplateReady = false;

    this.defaultLoadingImageSource = 'css/img/ajax-loader.gif';
    if (defaultLoadingImageSource != null)
        this.defaultLoadingImageSource = defaultLoadingImageSource;
    this.defaultLoader = "<div align='center' style='width:100%;height:100%;'><table style='width:100%;height:100%;'><tr><td valign='middle' align='center'><img src='" + this.defaultLoadingImageSource + "' /></td></tr></table></div>";

    this.resumeFromLastTemplate = false;
    if (resumeFromLastTemplate != null)
        this.resumeFromLastTemplate = resumeFromLastTemplate;
    this.remote = false;
    if (remote != null)
        this.remote = remote;
    this.isDebug = false;
    if (debug != null && debug == true)
        this.isDebug = true;
    this.container = container;
    this.sessionID = sid;
    this.workspaceID = wid;
    this.hash = hash;
    this.testID = tid;
    this.queryPath = queryPath == null ? "query/" : queryPath;
    this.callbackGet = callbackGet;
    this.callbackSend = callbackSend;
    this.isStopped = false;

    this.data = null;
    this.debug = null;
    this.status = Concerto.statusTypes.created;
    this.finished = false;

    this.loaderHTML = "";
    this.loaderHead = "";
    this.loaderEffectShow = "";
    this.loaderEffectHide = "";
    this.loaderEffectShowOptions = "";
    this.loaderEffectHideOptions = "";

    this.effectShow = "";
    this.effectHide = "";
    this.effectShowOptions = "";
    this.effectHideOptions = "";

    this.timer = 0;
    this.timeObj = null;

    this.timeTemplateLoaded = null;

    this.clearTimer = function() {
        if (this.timeObj != null) {
            clearTimeout(this.timeObj);
        }
    }
    this.iniTimer = function() {
        var thisClass = this;
        var limit = this.data["TIME_LIMIT"];
        this.timeTemplateLoaded = new Date();

        if (limit > 0) {
            this.timer = limit;
            $(".fontTimeLeft").html(moment.utc(new Date(1000 * this.timer)).format(this.timeFormat));
            this.timeObj = setInterval(function() {
                thisClass.timeTick();
            }, 1000);
        }
    }

    this.timeTick = function() {
        if (this.isStopped)
            return;
        if (this.timer > 0) {
            this.timer--;

            var date = moment.utc(new Date(1000 * this.timer));

            $(".fontTimeLeft").html(date.format(this.timeFormat));
            if (this.timer == 0) {
                this.submit("NONE", true);
            }
        }
    }

    this.stop = function() {
        this.clearTimer();
        this.isStopped = true;
    }

    this.run = function(btnName, values, code) {
        this.isTemplateReady = false;
        if (this.isStopped)
            return;
        if (this.testID != null && this.workspaceID != null && this.sessionID == null && this.hash == null && !this.isDebug && !this.remote) {
            var lastSession = Concerto.getSessionObject(this.workspaceID, this.testID);
            if (lastSession != null) {
                Concerto.iniSessionResumeDialog(this, btnName, values, lastSession);
                return;
            }
        }
        this.status = Concerto.statusTypes.working;
        if (this.isFirstTemplate)
            $(this.container).html(this.defaultLoader);
        var thisClass = this;

        var params = {};
        params["resume_from_last_template"] = this.resumeFromLastTemplate ? "1" : "0";
        this.resumeFromLastTemplate = false;
        if (this.workspaceID != null && this.hash != null && this.sessionID != null)
        {
            params["wid"] = this.workspaceID;
            params["hash"] = this.hash;
            params["sid"] = this.sessionID;
        }
        else
        {
            if (this.workspaceID != null && this.testID != null) {
                params["wid"] = this.workspaceID;
                params["tid"] = this.testID;
            }
        }
        if (btnName != null)
            params["btn_name"] = btnName;
        if (values != null)
            params["values"] = $.toJSON(values);
        if (this.isDebug != null && this.isDebug == true)
            params["debug"] = 1;
        else
            params["debug"] = 0;
        if (code != null)
            params["code"] = code;

        var date = new Date();
        $.post((this.remote ? this.queryPath : this.queryPath + "r_call.php") + "?timestamp=" + date.getTime(),
                params,
                function(data) {
                    thisClass.data = data.data;
                    if (data.debug) {
                        thisClass.debug = data.debug;
                    }

                    thisClass.hash = thisClass.data["HASH"];
                    thisClass.sessionID = thisClass.data["TEST_SESSION_ID"];
                    thisClass.testID = thisClass.data["TEST_ID"];
                    thisClass.status = thisClass.data["STATUS"];
                    thisClass.finished = thisClass.data["FINISHED"] == 1;

                    thisClass.loaderHTML = thisClass.data["LOADER_HTML"];
                    thisClass.loaderHead = thisClass.data["LOADER_HEAD"];
                    thisClass.loaderEffectShow = thisClass.data["LOADER_EFFECT_SHOW"];
                    thisClass.loaderEffectHide = thisClass.data["LOADER_EFFECT_HIDE"];
                    thisClass.loaderEffectShowOptions = thisClass.data["LOADER_EFFECT_SHOW_OPTIONS"];
                    thisClass.loaderEffectHideOptions = thisClass.data["LOADER_EFFECT_HIDE_OPTIONS"];

                    thisClass.effectShow = thisClass.data["EFFECT_SHOW"];
                    thisClass.effectShowOptions = thisClass.data["EFFECT_SHOW_OPTIONS"];
                    thisClass.effectHide = thisClass.data["EFFECT_HIDE"];
                    thisClass.effectHideOptions = thisClass.data["EFFECT_HIDE_OPTIONS"];

                    if (thisClass.data["STATUS"] == Concerto.statusTypes.template) {
                        if (thisClass.isFirstTemplate)
                            $(thisClass.container).hide(0);

                        thisClass.isTemplateReady = true;
                        if (thisClass.isFirstTemplate)
                            thisClass.loadTemplate();

                        if (thisClass.effectTransition == 0 && thisClass.loaderTransition == 2) {
                            thisClass.hideLoader();
                        }
                    }
                    if (thisClass.data["STATUS"] == Concerto.statusTypes.completed && (thisClass.loaderTransition == 2 || thisClass.isFirstTemplate))
                        thisClass.hideLoader();

                    if (thisClass.data["STATUS"] == Concerto.statusTypes.tampered && (thisClass.loaderTransition == 1 || thisClass.loaderTransition == 2 || thisClass.isFirstTemplate))
                        thisClass.printError(Concerto.statusTypes.tampered);

                    if (!thisClass.remote) {
                        if (thisClass.finished && !thisClass.isDebug)
                            Concerto.removeSessionCookie(thisClass.workspaceID, thisClass.sessionID, thisClass.hash);
                        else
                            Concerto.saveSessionCookie(thisClass.workspaceID, thisClass.sessionID, thisClass.hash, thisClass.testID);
                    }

                    if (thisClass.data["STATUS"] == Concerto.statusTypes.error && (thisClass.loaderTransition == 1 || thisClass.loaderTransition == 2 || thisClass.isFirstTemplate)) {
                        thisClass.printError(Concerto.statusTypes.error);
                    }
                    if (thisClass.callbackGet != null)
                        thisClass.callbackGet.call(thisClass, data);
                    return thisClass.data;
                }, "json");
        return null;
    };

    this.printError = function(status) {
        switch (status) {
            case Concerto.statusTypes.tampered:
                {
                    $(this.container).html("<h3>Session unavailable.</h3>");
                    break;
                }
            case Concerto.statusTypes.error:
                {
                    if (this.debug == null) {
                        $(this.container).html("<h3>Fatal test exception encountered. Test halted.</h3>");
                    }
                    break;
                }
        }
    }

    this.insertSpecialVariables = function(html) {
        html = html.replace("{{TIME_LEFT}}", "<font class='fontTimeLeft'></font>");
        return html;
    };

    this.loadTemplate = function() {
        this.isFirstTemplate = false;
        this.effectTransition = 1;
        var thisClass = this;
        $("head").append(this.data["HEAD"]);
        $(thisClass.container).html(thisClass.insertSpecialVariables(this.data["HTML"]));

        this.showEffect();
    };

    this.showEffect = function() {
        if (this.effectShow == "none" || this.effectShow.trim() == "") {
            this.effectTransition = 2;
            $(this.container).show(0);
            this.addSubmitEvents();
            this.iniTimer();
            return;
        }

        var thisClass = this;

        var options = {};
        if (this.effectShowOptions.trim() != "") {
            options = $.parseJSON(this.effectShowOptions);
        }

        for (var k in options) {
            switch (k) {
                case "duration":
                case "pieces":
                case "size":
                case "percent":
                    options[k] = parseInt(options[k]);
                    break;
            }
        }
        $(this.container).show(this.effectShow, options, options.duration, function() {
            this.effectTransition = 2;
            thisClass.addSubmitEvents();
            thisClass.iniTimer();
        });

    }

    this.getControlsValues = function() {
        var vars = {};

        $(this.container).find("input:text, input[type='hidden'], input:password, textarea, select, input:checkbox:checked, input:radio:checked").each(function() {
            var name = $(this).attr("name");
            var value = $(this).val();

            var found = false;
            for (var k in vars) {
                if (k == name) {
                    found = true;
                    if (vars[k] instanceof Array)
                        vars[k].push(value);
                    else
                        vars[k] = [vars[k], value];
                }
            }

            if (!found) {
                vars[name] = value;
            }
        });

        return vars;
    }

    this.hideEffect = function() {
        this.removeSubmitEvents();
        this.effectTransition = 3;
        if (this.effectHide == "none" || this.effectHide.trim() == "") {
            this.effectTransition = 0;
            $(this.container).hide(0);
            if (this.isTemplateReady && this.loaderTransition == 0) {
                this.loadTemplate();
            }
            else if (this.loaderTransition == 0 && this.status == Concerto.statusTypes.working) {
                this.showLoader();
            }
            else if (this.loaderTransition == 0 && (this.status == Concerto.statusTypes.tampered || this.status == Concerto.statusTypes.error))
                this.printError(this.status);
            return;
        }

        var thisClass = this;

        var options = {};
        if (this.effectHideOptions.trim() != "") {
            options = $.parseJSON(this.effectHideOptions);
        }

        for (var k in options) {
            switch (k) {
                case "duration":
                case "pieces":
                case "size":
                case "percent":
                    options[k] = parseInt(options[k]);
                    break;
            }
        }

        $(this.container).hide(this.effectHide, options, options.duration, function() {
            thisClass.effectTransition = 0;
            if (thisClass.isTemplateReady && thisClass.loaderTransition == 0) {
                thisClass.loadTemplate();
            }
            else if (thisClass.loaderTransition == 0 && thisClass.status == Concerto.statusTypes.working) {
                thisClass.showLoader();
            }
            else if (thisClass.loaderTransition == 0 && (thisClass.status == Concerto.statusTypes.tampered || thisClass.status == Concerto.statusTypes.error)){
                $(thisClass.container).show(0);
                thisClass.printError(thisClass.status);
            }
        });

    }

    this.hideLoader = function() {
        this.loaderTransition = 3;
        if (this.loaderEffectHide == "none" || this.loaderEffectHide.trim() == "") {
            this.loaderTransition = 0;
            $(this.container).hide(0);
            if (this.isTemplateReady) {
                this.loadTemplate();
            }
            return;
        }

        var thisClass = this;

        var options = {};
        if (this.loaderEffectHideOptions.trim() != "") {
            options = $.parseJSON(this.loaderEffectHideOptions);
        }

        for (var k in options) {
            switch (k) {
                case "duration":
                case "pieces":
                case "size":
                case "percent":
                    options[k] = parseInt(options[k]);
                    break;
            }
        }

        $(this.container).hide(this.loaderEffectHide, options, options.duration, function() {
            thisClass.loaderTransition = 0;
            if (thisClass.isTemplateReady)
                thisClass.loadTemplate();
        });

    }

    this.showLoader = function() {
        this.loaderTransition = 1;
        if (this.data["LOADER_HTML"].trim() != "") {
            if (this.data["LOADER_HEAD"].trim() != "")
                $("head").append(this.data["LOADER_HEAD"]);
            $(this.container).html(this.insertSpecialVariables(this.data["LOADER_HTML"]));
        } else {
            $(this.container).html(this.defaultLoader);
        }

        if (this.loaderEffectShow == "none" || this.loaderEffectShow.trim() == "") {
            this.loaderTransition = 2;
            $(this.container).show(0);
            if (this.isTemplateReady && this.loaderTransition == 2 || this.status == Concerto.statusTypes.completed)
                this.hideLoader();
            return;
        }

        var thisClass = this;

        var options = {};
        if (this.loaderEffectShowOptions.trim() != "") {
            options = $.parseJSON(this.loaderEffectShowOptions);
        }

        for (var k in options) {
            switch (k) {
                case "duration":
                case "pieces":
                case "size":
                case "percent":
                    options[k] = parseInt(options[k]);
                    break;
            }
        }

        $(this.container).show(this.loaderEffectShow, options, options.duration, function() {
            thisClass.loaderTransition = 2;
            if (thisClass.isTemplateReady && thisClass.loaderTransition == 2 || thisClass.status == Concerto.statusTypes.completed && thisClass.loaderTransition == 2)
                thisClass.hideLoader();
        });

    }

    this.submit = function(btnName, timeout) {
        var currentTime = new Date();
        if (timeout == null)
            timeout = false;

        this.status = Concerto.statusTypes.working;

        var thisClass = this;
        this.clearTimer();
        if (this.isStopped)
            return;
        var vars = this.getControlsValues();
        vars["TIME_TAKEN"] = (currentTime.getTime() - thisClass.timeTemplateLoaded.getTime()) / 1000;
        vars["OUT_OF_TIME"] = timeout ? 1 : 0;
        this.isTemplateReady = false;
        this.hideEffect();
        this.run(btnName, vars);
        if (this.callbackSend != null)
            this.callbackSend.call(this, btnName, vars);
    };

    this.addSubmitEvents = function() {
        var thisClass = this;

        $(container).find(":button:not(.notInteractive)").click(function() {
            thisClass.submit($(this).attr("name"));
        });
        $(container).find("input:image:not(.notInteractive)").click(function() {
            thisClass.submit($(this).attr("name"));
        });
        $(container).find("input:submit:not(.notInteractive)").click(function() {
            thisClass.submit($(this).attr("name"));
        });
    }

    this.removeSubmitEvents = function() {

        $(container).find(":button:not(.notInteractive)").unbind("click");
        $(container).find("input:image:not(.notInteractive)").unbind("click");
        $(container).find("input:submit:not(.notInteractive)").unbind("click");
    }
}
;

Concerto.statusTypes = {
    newSession: 0,
    working: 1,
    template: 2,
    completed: 3,
    error: 4,
    tampered: 5,
    waiting: 6,
    serialized: 7,
    initQTI: 8,
    rpQTI: 9,
    waitingCode: 10
};

Concerto.getSessionCookie = function() {
    var session = $.cookie('concerto_test_sessions');
    if (session == null)
        return [];
    else
        return $.evalJSON(session);
}

Concerto.resetSessionCookie = function() {
    $.cookie('concerto_test_sessions', $.toJSON([]), {
        expires: 1,
        path: "/"
    });
}

Concerto.saveSessionCookie = function(wid, sid, hash, tid) {
    var session = Concerto.getSessionCookie();
    var date = new Date();
    var exists = false;
    for (var i = 0; i < session.length; i++) {
        var elem = session[i];
        if (elem.tid == tid && elem.wid == wid) {
            exists = true;
            session[i].date = date.toUTCString();
            session[i].sid = sid;
            session[i].hash = hash;
        }
    }
    if (!exists) {
        session.push({
            wid: wid,
            sid: sid,
            hash: hash,
            date: date.toUTCString(),
            tid: tid
        });
    }
    $.cookie('concerto_test_sessions', $.toJSON(session), {
        expires: 1,
        path: "/"
    });
}

Concerto.removeSessionCookie = function(wid, sid, hash) {
    var session = Concerto.getSessionCookie();
    var result = [];
    for (var i = 0; i < session.length; i++) {
        var elem = session[i];
        if (elem.wid != wid || elem.sid != sid || elem.hash != hash) {
            result.push(elem);
        }
    }
    $.cookie('concerto_test_sessions', $.toJSON(result), {
        expires: 1,
        path: "/"
    });
}

Concerto.selectTest = function() {
    var select = $("#selectTest");
    var tid = select.val();
    var wid = select.children("option[value='" + tid + "']").attr("workspace");
    if (typeof test != 'undefined' && test != null) {
        test.stop();
        test = new Concerto(test.container, wid, null, null, tid, test.queryPath, test.callbackGet, test.callbackSend, test.isDebug, test.remote, test.defaultLoadingImageSource, test.resumeFromLastTemplate);
    }
    else
        test = new Concerto($("#divTestContainer"), wid, null, null, tid);
    test.run(null, []);
    select.val(0);
}

Concerto.selectSession = function(wid, sid, hash) {
    if (typeof test != 'undefined' && test != null) {
        test.stop();
        test = new Concerto(test.container, wid, hash, sid, null, test.queryPath, test.callbackGet, test.callbackSend, test.isDebug, test.remote, test.defaultLoadingImageSource, true);
    }
    else
        test = new Concerto($("#divTestContainer"), wid, hash, sid, null, null, null, null, null, null, null, true);
    test.run(null, []);
}

Concerto.getSessionObject = function(wid, tid) {
    var session = Concerto.getSessionCookie();
    for (var i = 0; i < session.length; i++) {
        var s = session[i];
        if (s.wid == wid && s.tid == tid)
            return s;
    }
    return null;
}

Concerto.iniSessionResumeDialog = function(obj, btnName, values, lastSession) {
    $("#divSessionResumeDialog").dialog({
        modal: true,
        resizable: false,
        open: function() {
            $(".ui-dialog").css("font-size", "10px");
        },
        buttons: [
            {
                text: "resume",
                click: function() {
                    $(this).dialog("close");
                    Concerto.selectSession(lastSession.wid, lastSession.sid, lastSession.hash);
                }
            },
            {
                text: "start new",
                click: function() {
                    $(this).dialog("close");
                    Concerto.removeSessionCookie(lastSession.wid, lastSession.sid, lastSession.hash);
                    obj.run(btnName, values);
                }
            }
        ]
    });
}