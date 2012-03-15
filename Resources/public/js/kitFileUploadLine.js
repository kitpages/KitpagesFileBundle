(function($) {
    var WidgetLine = (function() {
        // constructor
        function WidgetLine(boundingBox, options) {

            this._settings = {
                fileInfo: null,
                isButtonMove: false,
                urlDeletePng: null,
                urlArrowUpPng: null,
                urlArrowDownPng: null,
                urlParentPng: null,
                render: null,
                moveUp: null,
                moveDown: null,
                delete: null,
                after_moveUp: null,
                after_moveDown: null,
                after_delete: null,
                actionList: null,
                addVersion: null,
                after_init: null
            };
            if (options) {
                $.extend(this._settings, options);
            }

            // DOM Nodes
            this._boundingBox = boundingBox;

            // memory
            this._boundingBox.data( "kitFileUploadLine", this );

            this.init();

        };

        // methods
        WidgetLine.prototype = {
            init: function() {
                var self = this;
                var eventList = [
                    'render',
                    'moveUp',
                    'moveDown',
                    'add',
                    'delete',
                    'actionList',
                    'addVersion',
                    'rollbackParent',
                    'after_delete',
                    'after_moveUp',
                    'after_moveDown',
                    'after_addVersion',
                    'after_rollbackParent'
                ];
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    if (this._settings[eventList[i]]) {
                        this._boundingBox.bind(eventList[i]+"_kitFileUploadLine", {self:self}, self._settings[eventList[i]]);
                    }
                }
                // init custom events according to settings callback values
                for (var i = 0 ; i < eventList.length ; i++ ) {
                    var callbackName = "_"+eventList[i]+"Callback";
                    this._boundingBox.bind(eventList[i]+"_kitFileUploadLine", {self:self}, self[callbackName]);
                }

                self._boundingBox.delegate(
                    "a.kit-file-upload-line-delete",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("delete_kitFileUploadLine");
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-line-move-up",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("moveUp_kitFileUploadLine");
                    }
                );
                self._boundingBox.delegate(
                    "a.kit-file-upload-line-move-down",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("moveDown_kitFileUploadLine");
                    }
                );
                self._boundingBox.delegate(
                    ".kit-file-upload-action-list",
                    "change",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("actionList_kitFileUploadLine", [button]);
                    }
                );
                self._boundingBox.delegate(
                    ".kit-file-upload-line-parent",
                    "click",
                    function() {
                        var button = $(this);
                        self._boundingBox.trigger("rollbackParent_kitFileUploadLine");
                    }
                );
                self._boundingBox.html(self._render());
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
            _rollbackParentCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._rollbackParent();
            },
            _addVersionCallback: function(event, fileInfo) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._addVersion(fileInfo);
            },
            _deleteCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._delete();
            },
            _moveUpCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._moveUp();
            },
            _moveDownCallback: function(event) {
                if (event.isDefaultPrevented()) {
                    return;
                }

                var self = event.data.self;
                self._moveDown();
            },
            _after_deleteCallback: function(event) {
                return;
            },
            _after_moveUpCallback: function(event) {
                return;
            },
            _after_moveDownCallback: function(event) {
                return;
            },
            _after_addVersionCallback: function(event) {
                return;
            },
            _after_rollbackParentCallback: function(event) {
                return;
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
            _render: function() {
                var self = this;
                var iconHtml = null;
                var buttonMoveList = '';
                if (self._settings.isButtonMove == true) {
                    buttonMoveList = '<a class="kit-file-upload-line-move-up" >'+
                        '<img src="' + self._settings.urlArrowUpPng + '" width="20" height="20" alt="[<]"/>'+
                        '</a>' +
                        '<a class="kit-file-upload-line-move-down" >'+
                        '<img src="' + self._settings.urlArrowDownPng + '" width="20" height="20" alt="[>]"/>'+
                        '</a>'
                }

                if (self._settings.fileInfo.fileType == 'image') {
                    iconHtml = '<img src="'+self._settings.fileInfo.url+'"/>';
                }
                else {
                    iconHtml = '<div class="kit-file-upload-line-document-icon"><div>'+self._settings.fileInfo.fileExtension+'</div></div>';
                }

                var selectAction = '';
                var countActionList = 0;
                var actionList = self._settings.fileInfo.actionList;
                if (actionList != undefined) {
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
                }

                var buttonParent = '';
                if (self._settings.fileInfo.fileParent != null) {
                    buttonParent = '<a class="kit-file-upload-line-parent" >'+
                        '<img src="' + self._settings.urlParentPng + '" width="20" height="20" alt="[O]"/>'+
                        '</a>';
                }

                var render =
                        '<a class="kit-file-upload-line-delete" >'+
                        '<img src="' + self._settings.urlDeletePng + '" width="20" height="20" alt="[X]"/>'+
                        '</a>'
                            + buttonParent
                            + buttonMoveList
                            + selectAction
                            + iconHtml+' '
                            + self._settings.fileInfo.fileName
                            + '<div  style="clear:both"></div>';
                return render;

            },
            _rollbackParent: function() {
                var self = this;
                var res = confirm("Are you sure you want a rollback this document ?");
                if (res == false) {
                    return;
                }
                var fileInfo = self._settings.fileInfo;
                self._settings.fileInfo = self._settings.fileInfo.fileParent;
                self._boundingBox.html(self._render());
                self._boundingBox.trigger("after_rollbackParent_kitFileUploadLine", fileInfo);
            },
            _addVersion: function(fileInfo) {
                var self = this;
                self._settings.fileInfo = fileInfo;
                self._boundingBox.html(self._render());
                self._boundingBox.trigger("after_addVersion_kitFileUploadLine", fileInfo);
            },
            _delete: function() {
                var self = this;
                var res = confirm("Are you sure you want delete this document ?");
                if (res == false) {
                    return;
                }
                self._boundingBox.detach();
                self._boundingBox.trigger("after_delete_kitFileUploadLine", self._settings.fileInfo);
                self._boundingBox.remove();
            },
            _moveUp: function() {
                var self = this;
                self._boundingBox.trigger("after_moveUp_kitFileUploadLine", self._settings.fileInfo);
            },
            _moveDown: function() {
                var self = this;
                self._boundingBox.trigger("after_moveDown_kitFileUploadLine", self._settings.fileInfo);
            },
            _actionList: function(buttonElement) {
                var self = this;
                var fileId = self._settings.fileInfo.id;

                self._boundingBox.children('.kit-file-upload-action').kitFileUploadLineAction({
                    url: buttonElement.val(),
                    fileId: fileId,
                    after_action: function(event, fileInfo) {self._addVersion(fileInfo)}
                });

                return;
            }
        };
        return WidgetLine;
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
                var widget = new WidgetLine($(this), options);
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
    $.fn.kitFileUploadLine = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' + method + ' does not exist on jQuery.kitFileUpload' );
        }
    };
})(jQuery);