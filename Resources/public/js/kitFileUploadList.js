(function($) {
    var WidgetList = (function() {
        // constructor
        function WidgetList(boundingBox, options) {

            this._settings = {
                isMulti: false,
                render: null,
                moveUp: null,
                moveDown: null,
                add: null,
                delete: null,
                replace: null,
                fileList: new Array()
            };
            if (options) {
                $.extend(this._settings, options);
            }

            // DOM Nodes
            this._boundingBox = boundingBox;

            // memory
            this._boundingBox.data( "kitFileUploadList", this );

            this.init();

        };

        // methods
        WidgetList.prototype = {
            init: function() {
                var self = this;
                var eventList = ['render', 'moveUp', 'moveDown', 'add', 'delete', 'renumbering', 'populate', 'replace'];
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    if (this._settings[eventList[i]]) {
                        this._boundingBox.bind(eventList[i]+"_kitFileUploadList", {self:self}, this._settings[eventList[i]]);
                    }
                }
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    var callbackName = "_"+eventList[i]+"Callback";
                    this._boundingBox.bind(eventList[i]+"_kitFileUploadList", {self:self}, this[callbackName]);
                }
            },
            ////
            // callbacks
            ////
            _populateCallback: function(event, fileList) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._settings.fileList = fileList;
                self._populate();
            },
            _renderCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._render();
            },
            _addCallback: function(event, fileInfo, index) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._add(fileInfo, index);
            },
            _replaceCallback: function(event, fileInfo) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._replace(fileInfo);
            },
            _deleteCallback: function(event, buttonElement) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._delete(buttonElement);
            },
            _moveUpCallback: function(event, buttonElement) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._moveUp(buttonElement);
            },
            _moveDownCallback: function(event, buttonElement) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._moveDown(buttonElement);
            },
            ////
            // real methods that do something
            ////
            _populate: function() {
                var self = this;
                $.each(self._settings['fileList'], function(index, value){
                    self._add(value, index);
                });

            },
            _render: function() {
                var self = this;
                return;
            },
            _add: function(fileInfo, index) {
                var self = this;
                var iconHtml = null;
                var buttonMoveList = '';
                if (index==undefined) {
                    index = self._boundingBox.children('li').length;
                }
                if (self._settings.isMulti == false) {
                    self._settings['lineList'] = new Array();
                    self._boundingBox.html('');
                }

                self._boundingBox.append('<li data-kitfileuploadlist-id="' + fileInfo.id + '" ></li>');

                self._boundingBox.children('li[data-kitfileuploadlist-id="' + fileInfo.id + '"]').kitFileUploadLine({
                    fileInfo:fileInfo,
                    isButtonMove: self._settings.isMulti,
                    after_moveUp:function(event, fileInfo) {self._moveUp(fileInfo)},
                    after_moveDown:function(event, fileInfo) {self._moveDown(fileInfo)},
                    after_delete:function(event, fileInfo) {self._delete(fileInfo)},
                    after_addVersion:function(event, fileInfo) {self._replace(fileInfo.fileParent.id, fileInfo.id)},
                    after_rollbackParent:function(event, fileInfo) {self._replace(fileInfo.id, fileInfo.fileParent.id)},
                    urlDeletePng: self._settings.urlDeletePng,
                    urlArrowUpPng: self._settings.urlArrowUpPng,
                    urlArrowDownPng: self._settings.urlArrowDownPng
                });

                self._boundingBox.trigger("after_add_kitFileUploadList", [fileInfo, index]);
            },
            _replace: function(fileParentId, fileId) {
                var self = this;
                self._boundingBox.children('li[data-kitfileuploadlist-id="' + fileParentId + '"]').attr('data-kitfileuploadlist-id', fileId);
                self._boundingBox.trigger("after_replace_kitFileUploadList", [fileParentId, fileId]);
            },
            _delete: function(fileInfo) {
                var self = this;
                self._boundingBox.trigger("after_delete_kitFileUploadList", fileInfo.id);
            },
            _moveUp: function(fileInfo) {
                var self = this;
                var element = self._boundingBox.children('li[data-kitfileuploadlist-id="' + fileInfo.id + '"]');
                var idCountMove = element.data('kitfileuploadlist-id');
                element.prev().before(element);
                self._boundingBox.trigger("after_moveUp_kitFileUploadList", idCountMove);
            },
            _moveDown: function(fileInfo) {
                var self = this;
                var element = self._boundingBox.children('li[data-kitfileuploadlist-id="' + fileInfo.id + '"]');
                var idCountMove = element.data('kitfileuploadlist-id');
                element.next().after(element);
                self._boundingBox.trigger("after_moveDown_kitFileUploadList", idCountMove);
            },
            ////
            // external methods
            ////
            add: function(fileInfo, index) {
                var self = this;
                self._boundingBox.trigger("add_kitFileUploadList", [fileInfo, index]);
            },
            populate: function(fileList) {
                var self = this;
                self._settings.fileList = fileList;
                self._boundingBox.trigger("populate_kitFileUploadList");
            }
        };
        return WidgetList;
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
                var widget = new WidgetList($(this), options);
            });
        },

        add: function(fileInfo, index) {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadList");
                widget.add(fileInfo, index);
            });
        },
        populate: function() {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadList");
                widget.populate();
            });
        },
        render: function() {
            return this.each(function() {
                var widget = $(this).data("kitFileUploadList");
                widget.render();
            });
        },
        /**
        * unbind all kitFileUploadList events
        */
        destroy : function( ) {
        }

    };
    $.fn.kitFileUploadList = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' + method + ' does not exist on jQuery.kitFileUpload' );
        }
    };
})(jQuery);