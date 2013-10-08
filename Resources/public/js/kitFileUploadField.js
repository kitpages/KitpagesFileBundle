(function($) {
    var WidgetField = (function() {
        // constructor
        function WidgetField(boundingBox, options) {

            this._settings = {
                boundingBoxList: null,
                event_replace: null,
                event_add: null,
                event_delete: null,
                event_moveUp: null,
                event_moveDown: null,
                renumbering: null,
                isMulti: false
            };
            if (options) {
                $.extend(this._settings, options);
            }

            // DOM Nodes
            this._boundingBox = boundingBox;

            // memory
            this._boundingBox.data( "kitFileUploadField", this );

            this.init();

        };

        // methods
        WidgetField.prototype = {
            init: function() {
                var self = this;
                var eventList = ['add', 'delete', 'moveUp', 'moveDown', 'renumbering', 'replace'];
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    if (this._settings[eventList[i]]) {
                        this._boundingBox.bind(eventList[i]+"_kitFileUploadField", {self:self}, this._settings[eventList[i]]);
                    }
                }
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    var callbackName = "_"+eventList[i]+"Callback";
                    var eventName = "event_"+eventList[i];

                    if (this._settings[eventName] == undefined) {
                        this._boundingBox.bind(eventList[i]+"_kitFileUploadField", {self:self}, this[callbackName]);
                    } else {
                        this._settings["boundingBoxList"].bind(this._settings[eventName], {self:self}, this[callbackName]);
                    }
                }
            },
            ////
            // callbacks
            ////
            _addCallback: function(event, fileInfo) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._add(fileInfo);
            },
            _replaceCallback: function(event, idTarget, idNew) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._replace(idTarget, idNew);
            },
            _moveUpCallback: function(event, fileInfo) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._boundingBox.trigger("renumbering_kitFileUploadField");
            },
            _moveDownCallback: function(event, fileInfo) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._boundingBox.trigger("renumbering_kitFileUploadField");
            },
            _deleteCallback: function(event, idCount) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._delete(idCount);
            },
            _renumberingCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._renumbering();
            },
            ////
            // real methods that do something
            ////
            _add: function(fileInfo, idCount) {
                var self = this;

                if (this._settings.isMulti) {
                    var prototype = self._boundingBox.data('prototype');
                    if (idCount == undefined) {
                        var idCount = self._settings["boundingBoxList"].find('li').length;
                        idCount--;
                    }
                    var p = prototype.replace(/__name__/g, idCount);
                    self._boundingBox.append(p);
                    $('#' + self._boundingBox.attr('id') +'_' + idCount).attr('value', fileInfo.id);
                } else {
                    self._boundingBox.attr('value', fileInfo.id);
                }
            },
            _replace: function(idTarget, idNew) {
                var self = this;

                if (self._settings.isMulti) {
                    self._boundingBox.find("input[value="  + idTarget + "]").attr('value', idNew);
                } else {
                    self._boundingBox.attr('value', idNew);
                }
            },
            _delete: function(idCount) {
                var self = this;

                if (self._settings.isMulti) {
                    self._boundingBox.find("input[value="  + idCount + "]").remove();
                } else {
                    self._boundingBox.attr('value', '');
                }
                self._boundingBox.trigger("renumbering_kitFileUploadField");
            },
            _renumbering: function() {
                var self = this;
                if (self._settings.isMulti == true) {
                    self.deleteContent();
                    self._settings["boundingBoxList"].children('li').each(function(index){
                        var element = $(this);
                        var fileInfo = new Array();
                        fileInfo['id'] = element.data('kitfileuploadlist-id');
                        self._add(fileInfo, index);
                    });
                }
            },

            ////
            // external methods
            ////
            deleteContent: function() {
                var self = this;
                self._boundingBox.html("");
            }
        };
        return WidgetField;
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
                var widget = new WidgetField($(this), options);
            });
        },

        render: function() {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadField");
                widget.render();
            });
        },
        deleteContent: function() {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadField");
                widget.deleteContent();
            });
        },
        /**
        * unbind all kitFileUploadField events
        */
        destroy : function( ) {
        }

    };
    $.fn.kitFileUploadField = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' + method + ' does not exist on jQuery.kitFileUpload' );
        }
    };
})(jQuery);