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
                actionList: null,
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
                var eventList = ['render', 'moveUp', 'moveDown', 'add', 'delete', 'renumbering', 'populate', 'actionList'];
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
                        var button = $(this);
                        self._boundingBox.trigger("delete_kitFileUploadList", [button]);
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-list-move-up",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("moveUp_kitFileUploadList", [button]);
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-list-move-down",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("moveDown_kitFileUploadList", [button]);
                    }
                );
                self._boundingBox.delegate(
                    ".kit-file-upload-action-list",
                    "change",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("actionList_kitFileUploadList", [button]);
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
            _actionListCallback: function(event, buttonElement) {
                if (event.isDefaultPrevented()) {
                    return;
                }
                var self = event.data.self;
                self._actionList(buttonElement);
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

                if (fileInfo.fileType == 'image') {
                    iconHtml = '<img src="'+fileInfo.url+'"/>';
                }
                else {
                    iconHtml = '<div class="kit-file-upload-list-document-icon"><div>'+fileInfo.fileExtension+'</div></div>';
                }

                var selectAction = '';
                var countActionList = 0;
                var actionList = fileInfo.actionList;
                $.each(actionList, function(index, action) {
                    if (countActionList == 0) {
                        selectAction = '<div class="kit-file-upload-action"></div><select class="kit-file-upload-action-list">';
                    }
                    selectAction = selectAction + '<option value="' + action  + '">' + index + '</option>';
                    countActionList++;
                });
                if (selectAction != '') {
                    selectAction = selectAction + '</select>';
                }
                self._boundingBox.append(
                    '<li data-kitfileuploadlist-id="' + fileInfo.id + '" >' +
                        '<a class="kit-file-upload-list-delete" >'+
                        '<img src="' + self._settings.urlDeletePng + '" width="20" height="20" alt="[X]"/>'+
                        '</a>'
                            + buttonMoveList
                            + selectAction
                            + iconHtml+' '
                            + fileInfo.fileName
                            + '<div  style="clear:both"></div>' +
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
            _actionList: function(buttonElement) {
                var self = this;
                var element = buttonElement.parent();
                var fileId = element.data('kitfileuploadlist-id');
                $.ajax({
                    type: "POST",
                    url: buttonElement.val(),
                    dataType: 'html',
                    data: "id="+fileId,
                    success: function(dataHtml) {
                        element.children('.kit-file-upload-action').html(dataHtml);
                        self._boundingBox.trigger("after_actionList_kitFileUploadList", fileId);
                    }
                });

                return;
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