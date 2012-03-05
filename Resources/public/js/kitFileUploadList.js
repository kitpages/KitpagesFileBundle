(function($) {
    var WidgetList = (function() {
        // constructor
        function WidgetList(boundingBox, options) {

            this._settings = {
                isMulti: false,
                urlDeletePng: null,
                urlArrowUpPng: null,
                urlArrowDownPng: null,
                render: null,
                moveUp: null,
                moveDown: null,
                add: null,
                delete: null,
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
                var eventList = ['render', 'moveUp', 'moveDown', 'add', 'delete', 'renumbering', 'populate'];
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

                self._boundingBox.delegate(
                    "a.kit-file-upload-list-delete",
                    "click",
                    function() {
                        var buttonDelete = $(this);
                        self._boundingBox.trigger("delete_kitFileUploadList", [buttonDelete]);
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-list-move-up",
                    "click",
                    function() {
                        var buttonDelete = $(this);
                        self._boundingBox.trigger("moveUp_kitFileUploadList", [buttonDelete]);
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-list-move-down",
                    "click",
                    function() {
                        var buttonDelete = $(this);
                        self._boundingBox.trigger("moveDown_kitFileUploadList", [buttonDelete]);
                    }
                );
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
                    self._boundingBox.html('');
                } else {
                    buttonMoveList = '<a class="kit-file-upload-list-move-up" >'+
                        '<img src="' + self._settings.urlArrowUpPng + '" width="20" height="20" alt="[<]"/>'+
                        '</a>' +
                        '<a class="kit-file-upload-list-move-down" >'+
                        '<img src="' + self._settings.urlArrowDownPng + '" width="20" height="20" alt="[>]"/>'+
                        '</a>'
                }

                if (fileInfo.isImage) {
                    iconHtml = '<img src="'+fileInfo.url+'"/>';
                }
                else {
                    iconHtml = '<div class="kit-file-upload-list-document-icon"><div>'+fileInfo.fileExtension+'</div></div>';
                }
                self._boundingBox.append(
                    '<li  data-kitfileuploadlist-id="' + fileInfo.id + '" >' +
                        '<a class="kit-file-upload-list-delete" >'+
                        '<img src="' + self._settings.urlDeletePng + '" width="20" height="20" alt="[X]"/>'+
                        '</a>' +
                            buttonMoveList
                            + iconHtml+' '+
                            fileInfo.fileName+
                    '</li>'
                );
                self._boundingBox.trigger("after_add_kitFileUploadList", [fileInfo, index]);
            },
            _delete: function(buttonElement) {
                var self = this;
                var res = confirm("Are you sure you want delete this document ?");
                if (res == false) {
                    return;
                }
                var idCountDelete = buttonElement.parent().data('kitfileuploadlist-id');
                buttonElement.parent().remove();
                self._boundingBox.trigger("after_delete_kitFileUploadList", idCountDelete);
                self._boundingBox.trigger("renumbering_kitFileUploadList");
            },
            _moveUp: function(buttonElement) {
                var self = this;
                var element = buttonElement.parent();
                var idCountMove = buttonElement.parent().data('kitfileuploadlist-id');
                element.prev().before(element);
                self._boundingBox.trigger("after_moveUp_kitFileUploadList", idCountMove);
            },
            _moveDown: function(buttonElement) {
                var self = this;
                var element = buttonElement.parent();
                var idCountMove = buttonElement.parent().data('kitfileuploadlist-id');
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