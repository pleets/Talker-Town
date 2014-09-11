/*
 * JScript Render - Html Class
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

/* JScriptRender alias */
if (!window.hasOwnProperty('JScriptRender'))
   JScriptRender = {};

/* Namespace */
if (!JScriptRender.hasOwnProperty('html'))
   JScriptRender.html = new Object();

/* Html class */
JScriptRender.html.Html = new Function();

JScriptRender.html.Html.prototype = {
    formProcess: function(formElementToProcess, settings)
    {
        var HTML = new JScriptRender.Html();
        var DEBUG = new JScriptRender.Debug();

        var set = settings || {};

        // Highlight inputs on error
        set.highlight = (set.highlight !== undefined) ? set.highlight : true; 

        set.id = set.id || "dialog-ui";

        set.debug = (set.debug !== undefined) ? set.debug : false;

        set.callback = (set.callback instanceof Object) ? set.callback: {};

        // Error and success callback
        set.callback.error = set.callback.error || new Function();
        set.callback.success = set.callback.success || new Function();

        // Debug Callbacks
        set.callback.debug = (set.callback.debug instanceof Object) ? set.callback.debug: {};
        set.callback.debug.success = set.callback.debug.success || new Function();
        set.callback.debug.error = set.callback.debug.error || new Function();

        $("body").delegate(formElementToProcess, "submit", function(event)
        {
            var that = $(this);
            event.preventDefault();

            var form_id = formElementToProcess.replace("#",'');

            var url = $(this)[0].getAttribute("action");
            var _url = (url == null || url.trim() == "") ? document.URL : url;

            var data = new Object();
            $.each($(formElementToProcess).serializeArray(), function() {
                data[this.name] = this.value;
            });

            var _data = JSON.stringify(data);

            set.buttons = set.buttons || {
                "Accept": function() {
                    $(this).dialog("close");
                }
            };

            _validators = (set.validators !== "undefined" && set.validators) ? set.validators : false;

            if (_validators)
            {
                var InputFilter = new JScriptRender.filter.InputFilter(formElementToProcess);
                for (var input in set.validators)
                {
                    InputFilter.add({ name: input, validators: set.validators[input]});
                }

                var invalid = (InputFilter.getInvalidInput().length);

                // Refresh
                if (set.highlight)
                {
                    var inputs = InputFilter.getValidInput();
                    for (var i = inputs.length - 1; i >= 0; i--) {
                        var classes = inputs[i].className.split(" ");
                        var classString = "";
                        for (var j = classes.length - 1; j >= 0; j--) {
                            if (classes[j] != "input-error")
                                classString += " " +classes[j];
                        };
                        inputs[i].className = classString;
                    };
                }

                if (invalid && set.debug)
                {
                    return HTML.dialog({
                        id: set.id,
                        title: set.title,
                        content: $("<div id='" + form_id + "'> \
                                        <div><h3>Warning!</h3></div> \
                                        <p>Message: <strong>Missing parameters!</strong></p> \
                                        Type: " + "validator" + "<br /> \
                                        Response: " + JSON.stringify(invalid) + "<br /> \
                                    </div>"),
                        width: set.width,
                        modal: set.modal,
                        position: set.position,
                        persistence: false,
                        buttons: set.buttons,
                    }, set.callback.debug.error());             // Debug error callback
                }
                else if (invalid)
                {
                    if (set.highlight)
                    {
                        var inputs = InputFilter.getInvalidInput();
                        for (var i = inputs.length - 1; i >= 0; i--) {
                            inputs[i].className = inputs[i].className + " input-error";
                            if (i == 0)
                                inputs[i].focus();
                        };
                    }
                    return set.callback.error();                // Error callback
                }
                else {
                    set.callback.debug.success();
                }
            }

            if (set.debug)
            {
                HTML.dialog({
                    id: set.id,
                    title: set.title,
                    content: $("<div id='" + form_id + "'> \
                                    <div><h3>Request</h3></div> \
                                    Data: " + _data + "<br /> \
                                    Url: " + _url + "<br /> \
                                </div>"),
                    width: set.width,
                    modal: set.modal,
                    position: set.position,
                    persistence: false,
                    buttons: set.buttons,
                }, set.callback.debug.success());               // Debug success callback
            }
            else {
                set.callback.success();
            }

        });
    },
}
