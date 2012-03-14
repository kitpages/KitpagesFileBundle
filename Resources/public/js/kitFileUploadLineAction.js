(function($) {
    var WidgetLineAction = (function() {
        // constructor
        function WidgetLineAction(boundingBox, options) {

            this._settings = {
                fileId: null,
                render: null,
                action: null,
                after_action: null
            };
            if (options) {
                $.extend(this._settings, options);
            }

            // DOM Nodes
            this._boundingBox = boundingBox;

            // memory
            this._boundingBox.data( "kitFileUploadLineAction", this );

            this.init();

        };

        // methods
        WidgetLineAction.prototype = {
            init: function() {
                var self = this;
                var eventList = [
                    'render',
                    'action',
                    'after_action'
                ];
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    if (this._settings[eventList[i]]) {
                        this._boundingBox.bind(eventList[i]+"_kitFileUploadLineAction", {self:self}, self._settings[eventList[i]]);
                    }
                }
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    var callbackName = "_"+eventList[i]+"Callback";
                    this._boundingBox.bind(eventList[i]+"_kitFileUploadLineAction", {self:self}, self[callbackName]);
                }

                self._render();
                self._boundingBox.delegate(
                    "form",
                    "submit",
                    function() {
                        var form = $(this);
                        self._action(form);
                        return false;
                });
            },
            ////
            // callbacks
            ////
            _renderCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._render();
            },
            _actionCallback: function(event, form) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._action(form);
            },
            _after_actionCallback: function(event, form) {
                return ;
            },
            ////
            // real methods that do something
            ////
            _render: function() {
                var self = this;
                $.ajax({
                    type: "POST",
                    url: self._settings.url,
                    dataType: 'html',
                    data: "id="+self._settings.fileId,
                    success: function(dataHtml) {
                        self._boundingBox.html(dataHtml);
                    }
                });
            },
            _action: function(form) {
                var self = this;
   //             alert(form.toSource());
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    dataType: 'json',
                    data: form.serialize(),
                    success: function(data) {
                        self._boundingBox.trigger("after_action_kitFileUploadLineAction", [data]);
                    }
                });
            }
        };
        return WidgetLineAction;
    })();

    var methods = {
        /**
        * add events to a dl instance
        * @this the dl instance (jquery object)
        */
        init : function ( options ) {
            var self = $(this);
            // chainability => foreach
            return this.each(function() {
                var widget = new WidgetLineAction($(this), options);
            });
        },

        render: function() {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadLine");
                widget.render();
            });
        },
        /**
        * unbind all kitFileUploadLine events
        */
        destroy : function( ) {
            var self = $(this);
            self.remove();
        }

    };
    $.fn.kitFileUploadLineAction = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' + method + ' does not exist on jQuery.kitFileUpload' );
        }
    };
})(jQuery);