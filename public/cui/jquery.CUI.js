(function($) {
    
    if(!$.CUI){
        $.CUI = new Object();
    };
    
    /*
     *  --- GRID DATAS AND MEDIAS ---
     */
    
    $.CUI.Grid = function(el, options){
        
        // base, jQuery and DOM element
        base = this;
        base.$el = $(el);
        base.el = el;
        
        // Add a reverse reference to the DOM object
        base.$el.data("CUI.Form", base);

        // Initialization
        base.init = function(){
            base.options = $.extend({},$.CUI.Grid.defaultOptions, options);
            
            base.rowTemplate = base.$el.find(".row-template").clone().removeClass("row-template");
            base.nbColTemplate = base.rowTemplate.find("> *").length;
            base.$el.coord = base.$el.offset();                
             
            $(base.options.filter).find('input[type=submit]').hide();
            $(base.options.action).find('input[type=submit]').hide();
            
            if(base.options.callback) {
                base.options.callback();
            }
             
            var anchor = window.location.hash;
            if(anchor) {
                anchor = anchor.substring(1,anchor.length);
            }
            $.history.init(base.setDatas, anchor);
            // events definitions
            // rows events
            base.$el.find(".row")
                .live("mouseenter", function() {
                    $(this).addClass("hover")
                })
                .live("mouseleave", function() {
                    $(this).removeClass("hover");
                })
                .live("click", function() {
                    if($(this).parents(".ui-sortable").length == 0) {
                        if($(this).hasClass("select")) {
                            $(this).removeClass("select")
                                   .find("input[type=checkbox]").removeAttr("checked").change();
                        } else {
                            $(this).addClass("select").find("input[type=checkbox]").attr("checked", "checked").change();
                        }
                    }
                });
            base.$el.find('.row').find("a, .switch-1, .switch-0")
                .live("click", function(event) {
                    var rowEl = $(this).parents('.row');
                    if(rowEl.hasClass("select"))  {
                        rowEl.removeClass("select").find("input[type=checkbox]").removeAttr("checked");
                    }
                    else {
                        rowEl.addClass("select").find("input[type=checkbox]").attr("checked", "checked");
                    }
                    //event.stopPropagation();
                }); 
            
            // actions event
            $(base.options.action).find(":radio").bind("change", function(){
                $(base.options.action).submit();
            });
            
            // select event
            $(base.options.action).find('.dropdown-select').live('click', function(e){
                if($(e.target).hasClass('checkbox-all')) {
                    base.$el.find('input[type=checkbox]').attr('checked', $(e.target).attr('checked'));
                    if($(e.target).attr('checked')) {
                        base.$el.find('input[type=checkbox]').parent().parent().addClass('select');
                    } else {
                        base.$el.find('input[type=checkbox]').parent().parent().removeClass('select');
                    }
                } else {
                    $(this).find('.picker').show();
                }
            });
            $(base.options.action).find('.select-all').live('click', function() { 
                    $(base.options.action).find('.checkbox-all').attr('checked', 'checked');
                    base.$el.find('input[type=checkbox]').each(function(){
                        $(this).parent().parent().addClass('select');
                        $(this).attr('checked', 'checked');
                        $(base.options.action).find('.picker').hide();
                        });
                    return false;
                });  
            $(base.options.action).find('a.select-none').live('click', function() {
                    $(base.options.action).find('.checkbox-all').removeAttr('checked');
                    base.$el.find('input[type=checkbox]').each(function(){
                        $(this).parent().parent().removeClass('select');
                        $(this).removeAttr('checked');
                        $(base.options.action).find('.picker').hide();
                        });
                    return false;
                }); 
            $('body').bind('click', function() {
                $(base.options.action).find('.picker').hide();
            });
            
            // order list
            var orderMode = false;
            $(base.options.action).find('.trigger-order').live("click", function(){
                if(!orderMode) {
                    orderMode = true;
                    var self = $(this);
                    $.history.load($(this).attr("href") + '?sorting=true');
                    $(this).html('<span class="ui-icon ui-icon-arrow-1-w"></span><span class="ui-button-text">Back to filter</span>');
                    $(base.options.filter).hide();
                    $(base.options.action).parent().animate({
                        "margin-right" : 0
                    }, 250);
                    $(base.options.action).find(".select").fadeTo("fast",0.3);
                    $(base.options.action).find(".grid-sortable").CUI('sortable', {
                        'basePath': base.options.basePath, 
                        'placeholder': 'ui-state-highlight',
                        'start': function (event, ui) { 
                            if(ui.placeholder[0].tagName == "TR") {
                                ui.placeholder.html('<td colspan="'+base.nbColTemplate+'">&nbsp;</td>');
                            } else {
                                ui.placeholder.html('<div class="row">&nbsp;</div>');
                            }
                        },
                        'update': function() {
                            $(base.options.action).find("input:checkbox").attr('checked', 'checked');
                            $(base.options.action).find("#order-radio").attr('checked', 'checked');
                            var url = $(base.options.action).attr('action') + '?order=true&action=bash&event=order&' + $(base.options.action).serialize();
                            
                            $.getJSON(url, function(data) {
                                //TODO: gestion des errors
                            });
                            
                            $(base.options.action).find("input:checkbox").removeAttr('checked');
                            base.$el.find('.row').removeClass('even').removeClass('odd');
                            base.$el.find('.row:even').not('.row-template').addClass('even');
                            base.$el.find('.row:odd').not('.row-template').addClass('odd');
                        }
                    });
                } else {
                    orderMode = false;
                    $.history.load($(this).attr("href"));
                    $(this).html('<span class="ui-icon ui-icon-arrow-2-n-s"></span><span class="ui-button-text">Order list</span>');
                    $(base.options.action).parent().animate({
                        "margin-right" : 255
                    }, 250, function() {
                        $(base.options.filter).fadeIn();
                        $(base.options.action).find(".select").fadeTo("fast",1);
                    });
                    $(base.options.action).find(".grid-sortable").CUI('sortable', 'destroy');
                }
                return false;
            });
            
            // pager event
            $(base.options.action).find('.pager a').live("click", function() {
                //$.history.load('json.html');
                $.history.load($(this).attr("href"));
                return false;
            });
            
            // sorting event
            base.$el.find('.head a').live("click", function() {
                //$.history.load('json.html');
                $.history.load($(this).attr("href"));
                return false;
            });
            
            // filters events
            $(base.options.filter).find('h3')
                .mouseenter(function() {
                    $('body').css('cursor', 'pointer');
                })
                .mouseleave(function() {
                    $('body').css('cursor', 'auto');
                })
                .click(function() { 
                    var filterDiv = $(this).parent();
                    if(filterDiv.hasClass('filter-closed')) {
                        filterDiv.removeClass('filter-closed')
                    }
                    else {
                        filterDiv.addClass('filter-closed')
                    }
                });
            $(base.options.filter).find(":input").live("change", function(){
                var url = $(base.options.filter).attr("action") + '?filter[submit]=submit&' + $(base.options.filter).serialize();
                //$.history.load('json.html');
                $.history.load(url);
                return false;
            });
            $("<a/>", {
                "html": "<span class=\"icon\"></span><span>Remove all</span>",
                "class": "reset-filter",
                "href": "#"
            }).bind("click", function(){
                $(base.options.filter).find("input:radio, input:checkbox").removeAttr("checked")
                $(base.options.filter).find("input:text").val("");
                var url = $(base.options.filter).attr("action") + '?filter[submit]=submit&' + $(base.options.filter).serialize();
                $.history.load(url);
                return false;
            }).appendTo($(base.options.filter).find("h2"));
        };
        

        // Set datas via ajax request
        base.setDatas = function(url) {
            if(url == '') {
                return;
            }
            
            // loading
            $('.grid-loading')
                .appendTo('body')
                .css('position', 'absolute')
                .css('top', base.$el.coord.top + (base.$el.height() / 2))
                .css('left', base.$el.coord.left + (base.$el.width() / 2))
                .show();
            base.$el.fadeTo(0, 0.5);
            
            // request start
            
            $.getJSON(url, function(data) {
                
                // Update header links
                $.each(data.header, function(i,link){
                    base.$el.find(".head")
                            .eq(i)
                            .removeClass("sorting-asc")
                            .removeClass("sorting-desc")
                            .find("a").attr("href", link.url);
                    
                    if(link.sort) {
                        base.$el.find(".head").eq(i).addClass("sorting-"+link.sort);
                    }   
                });
                
                // Update rows
                base.$el.find('.row').not('.row-template').remove();
                $.each(data.rows.reverse(), function(i,row){
                    rowHtml = base.rowTemplate.clone().insertAfter(base.$el.find(".row-template"));
                    $.each(row, function(j,data){
                        rowHtml.children().eq(j).html(data);
                    });
                    
                    if (data.attributes != undefined) {
                        if (data.attributes[data.rows.length - i - 1] != undefined) {
                            $.each(data.attributes[data.rows.length - i - 1], function (attrib, value) {
                                if (attrib == 'addClass') {
                                    $(rowHtml).addClass(value);
                                } else if (attrib == 'removeClass') {
                                    $(rowHtml).removeClass(value);
                                } else {
                                    $(rowHtml).attr(attrib, value);
                                }
                            });
                        }
                    }
                });
                base.$el.find('.row:even').not('.row-template').addClass('even');
                base.$el.find('.row:odd').not('.row-template').addClass('odd');
                
                if (data.replace != undefined) {
                    $.each(data.replace, function(attrib, element) {
                        $(element[0]).html(element[1]);
                    });
                }
                
                // Update pager links
                $(base.options.action).find('.pager').empty();
                $.each(data.pager, function(i,pager){
                    if(pager.url) {
                        $("<a/>", {
                            "class": i,
                            href: pager.url,
                            text: pager.label
                        }).appendTo($(base.options.action).find('.pager')).before('&nbsp;');
                    } else {
                        if(pager.url != "") {
                            $("<span/>", {
                                "class": i,
                                html: pager.label
                            }).appendTo($(base.options.action).find('.pager')).before('&nbsp;');
                        }
                    }
                });
                
                // Update filter url action
                $(base.options.filter).attr('action', data.filter);
                
                if(base.options.onReloadGrid) {
                    base.options.onReloadGrid(data);
                } 
                
                // end
                $('.grid-loading').hide();
                base.$el.fadeTo(0, 1); 
                
                // callback function(s)
                if(base.options.callback) {
                    base.options.callback();
                } 
            });
        }
 
        // Run initializer
        base.init();
    };
    
    $.CUI.Grid.defaultOptions = {
        filter: '#grid-filter-form',
        action: '#grid-action-form'
    };


    /*
     *  --- FORMS ELEMENTS ---
     */

    $.CUI.Form = function(el, plugin, options){
    
        // base, jQuery and DOM element
        var base = this;
        base.$el = $(el);
        base.el = el;
        
        // Add a reverse reference to the DOM object
        base.$el.data("CUI.Form", base);

        // Initialization
        base.init = function(){
            base.options = $.extend({}, $.CUI.Grid.defaultOptions, options);
            base.options.plugin = plugin;
            (base[base.options.plugin])(options);
        };
        
        // switcher
        base.switcher = function() {
            var selectedOption = base.$el.find('option:selected').val()
            if(base.$el.attr('selected')){
                selectedOption = base.$el.attr('selected')
            }
            
            var output = $('<span/>', {
                "class" : "switch-" + selectedOption,
                text : selectedOption
            }).bind("click", function(){
                
                if(base.options.onclick) { 
                    base.options.onclick(base.$el) 
                } else if (base.options.url != undefined) {
                    var saveThis = this;
                    $.ajax({
                        type: "post", 
                        url: base.options.url, 
                        data:{name: base.$el[0].name, value:base.$el[0].value},
                        dataType: 'json',
                        success: function (data, text) {
                            if (data.statut !== 200) {
                                alert('probleme');
                            } else {
                                if(data.value == 0) {
                                    $(saveThis).removeClass('switch-1').addClass('switch-0').empty().append('Offline');
                                    base.$el.find('option[value=0]').attr('selected', 'selected');
                                } else {
                                    $(saveThis).removeClass('switch-0').addClass('switch-1').empty().append('Online');
                                    base.$el.find('option[value=1]').attr('selected', 'selected');                                
                                }
                            }
                        },
                        error: function (request, status) {
                            var obj = $.parseJSON(request.responseText);
                            alert(obj.error);
                        }
                    });
                    //$.post
                } else {
                    if($(this).hasClass('switch-1')) {
                        $(this).removeClass('switch-1').addClass('switch-0').empty().append('Offline');
                        base.$el.find('option[value=0]').attr('selected', 'selected');
                    } else {
                        $(this).removeClass('switch-0').addClass('switch-1').empty().append('Online');
                        base.$el.find('option[value=1]').attr('selected', 'selected');                                
                    }
                }
            }).insertAfter(base.$el);
            
            base.$el.hide();
            base.$el.addClass('field-switcher-converted');
        }

        base.multiselect = function() {
            // init
            var output = $('<div class="nicy-multiselect"/>');
            var selectedContainer = $('<div class="selectedContainer"/>');
            var selectedActions = $('<div class="actions"><span class="count"></span><a href="#" class="remove-all">Remove All</a></div>');
            var selectedList = $('<ul class="selectedList"/>');
            var availableContainer = $('<div class="availableContainer"/>');
            var availableActions = $('<div class="actions"><input type="text" class="search" name="multiselect-search" value="" /><a href="#" class="add-all">Add All</a></div>');
            var availableList = $('<ul class="availableList"/>');
            var nbSelected = 0; 
            var nbAvailable =  base.$el.find('option').not(':selected').not('[value=]').length;    

            // update the selected items count
            base.multiselect.updateCount = function () {
                if(nbSelected <= 1) {
                    $(output).find('.count').empty()
                                            .append(nbSelected + ' item selected');
                    if(nbSelected == 0) {
                        $(output).prev().find('option[value=]').attr('selected','selected');
                    }
                } else {
                    $(output).find('.count').empty()
                                            .append(nbSelected + ' items selected');
                }
            }  
             
            // generate
            base.$el.find('option:selected').not('[value=]').each(function() {
                $(selectedList).append('<li><a href="#" rel="'+$(this).val()+'"><span class="icon"></span>'+$(this).text()+'</a></li>');
                nbSelected = nbSelected + 1;
            })
            
            base.$el.find('option').not(':selected').not('[value=]').each(function() {
                $(availableList).append('<li><a href="#" rel="'+$(this).val()+'"><span class="icon"></span>'+$(this).text()+'</a></li>');
            });
            
            // displaying
            base.$el.hide();
            $(selectedContainer)
                .append(selectedActions)
                .append(selectedList);
            $(availableContainer)
                .append(availableActions)
                .append(availableList);
            $(output)
                .append(selectedContainer)
                .append(availableContainer)
                .insertAfter(base.$el);  
            base.multiselect.updateCount();


            //Sortable List
            
            if (base.options.multiselectSortable == true) {
                base.multiselect.onSortingEls = function (event, ui) {
                    var $sortedLi = $(ui.item.get(0)), 
                        $sortedEl = $(ui.item.get(0).children, ui),
                        $optionEl = base.$el.find('option[value='+$sortedEl.attr('rel')+']'),
                        position = $sortedLi.index();
                        
                    $optionElBefore = base.$el.find('option:selected').eq(position);
                    $optionElBefore.before($optionEl);
                }
                if ($.browser.msie && $.browser.version.substr(0,1)<7) { /*NocompatibleIE6*/ }
                else {
                    $( "ul.selectedList" ).sortable({
                        stop: base.multiselect.onSortingEls
                    });   
                } 
            }

            // events
            $(output).find('li a').bind('click', function() { 
                if($(this).parent().parent().hasClass('availableList')) {
                    $(this).parent().hide().prependTo(selectedList).fadeIn("slow"); 
                    base.$el.find('option[value='+$(this).attr('rel')+']').attr("selected", "selected");
                    nbSelected = nbSelected + 1; 
                    nbAvailable = nbAvailable - 1;
                    if(base.$el.parents("fieldset.form-aside").length) {
                        availableList.hide();
                    }
                }
                else
                {
                    $(this).parent().fadeOut("fast", function() {
                        $(this).prependTo(availableList).show();
                    })
                    base.$el.find('option[value='+$(this).attr('rel')+']').removeAttr("selected");
                    nbSelected = nbSelected - 1;
                    nbAvailable = nbAvailable + 1;
                }
                if(base.$el.parents("fieldset.form-aside").length) {
                    if(nbAvailable>0) {
                        addLink.show();
                    } 
                }
                base.multiselect.updateCount();
                return false;
            });
            
            $(output).find('.add-all').bind('click', function() {
                $(availableList).find('li').each(function() {
                    $(selectedList).append(this);
                    base.$el.find('option[value='+$(this).find('a').attr('rel')+']').attr("selected", "selected");
                    nbSelected = nbSelected + 1;
                    nbAvailable = nbAvailable - 1;
                }); 
                base.multiselect.updateCount();
                return false;
            });                 
            
            $(output).find('.remove-all').bind('click', function() {
                $(selectedList).find('li').each(function() {
                    $(availableList).append(this);
                    base.$el.find('option[value='+$(this).find('a').attr('rel')+']').removeAttr("selected");
                    nbSelected = 0;
                    nbAvailable = nbAvailable + 1;
                }); 
                base.multiselect.updateCount();
                return false;
            });

            // taken from John Resig's liveUpdate script
            $(output).find('.search').bind('keyup', function() {
                var input = $(this);
                var rows = availableList.children('li'),
                    cache = rows.map(function(){
                        return $(this).text().toLowerCase();
                    });
                var term = $.trim(input.val().toLowerCase()), scores = [];
                if (!term) {
                    rows.show();
                } else {
                    rows.hide();
                    cache.each(function(i) {
                        if (this.indexOf(term)>-1) { scores.push(i); }
                    });
                    $.each(scores, function() {
                        $(rows[this]).show();
                    });
                }
            }); 

            if(base.$el.parents("fieldset.form-aside").length) {
                availableList.hide();
                var addLink = $("<a/>", {
                    "class": "ui-button ui-button-text-only",
                    "href": "#",
                    "html": "<span class=\"ui-button-text\">Add a new</span>"
                }).bind("click", function () {
                    availableList.show();
                    $(this).hide();
                    return false;
                }).prependTo(availableContainer);
            }
        }
        
        base.fieldset = function() {
            var expandLink = $("<a/>", {
                html: "<span class=\"icon icon-less\"></span> Minimize",
                "class": "expand"
            }).prependTo(base.$el)
              .bind("click", function() {
                if(base.$el.hasClass('fieldset-closed')) {
                    base.$el.removeClass('fieldset-closed');
                    expandLink.html("<span class=\"icon icon-less\"></span> Minimize");
                } else {
                    base.$el.addClass('fieldset-closed');
                    expandLink.html("<span class=\"icon\"></span> Maximize");
                }
            });
            
            if((base.$el.prev().length) && (base.options.autoClose!=false)) {
                expandLink.html("<span class=\"icon\"></span> Maximize");
                base.$el.addClass('fieldset-closed');
            }
        }

        base.letterLimit = function() { 
            if(base.$el.attr("maxlength") != undefined) {
                base.options.maxChar = parseInt(base.$el.attr("maxlength"));
                base.$el.removeAttr("maxlength");
            }
            var counter = $("<div>", {
                "class": "count-letter",
                text: base.options.maxChar
            }).insertAfter(base.$el);

            // Update counter function
            base.letterLimit.updateCount = function () {
                var nbLetters = base.$el.val().length;
                var count = base.options.maxChar - base.$el.val().length;
                counter.text(count);
                if (count < 0) {
                    counter.addClass("count-letter-hot")
                             .removeClass("count-letter-cold");
                } else { 
                    counter.addClass("count-letter-cold")
                             .removeClass("count-letter-hot");
                }
            }

            base.letterLimit.updateCount();
            
            // Key up event
            base.$el.bind('keyup', base.letterLimit.updateCount);
        }

        base.file = function() { 
            var wrapper = base.$el.parents('.form-item').find(".ui-button-tiny-squared").show();
            
            base.$el.prependTo(wrapper);
            base.$el.css({
                "position": "absolute",
                "top": "3px",
                "left": "3px",
                "z-index": "3",
                "height": "22px",
                "width": "250px",
                "display": "inline",
                "cursor": "pointer",
                "opacity": "0.0"
            });
            if ($.browser.mozilla) {
                if (/Win/.test(navigator.platform)) {
                    base.$el.css("left", "-142px");
                } else {
                    base.$el.css("left", "-168px");
                };
            } else {
                base.$el.css("left", "-167px");
            };
            base.$el.bind("change", function() {
                var filename = base.$el.parents('.form-item').find(".field-preview-wrapper");
                
                if (filename.length === 0) {
                    filename = $('<div class="field-wrapper field-preview-wrapper"/>');
                    base.$el.parents('.form-item').append(filename);
                    
                    filename.html('<input type="checkbox" checked="checked" value="1" class="field-checkbox" '+
                            'name="' + base.$el.attr('name').replace('filename_', 'filename_delete_') + '"><div class="description">'+
                            +'</div><div class="clear"></div>');
                }
                
                checkbox = filename.find('.field-checkbox');
                
                filename.html('<div class="description">' + base.$el.val() + '</div><div class="clear"></div>');
                filename.prepend(checkbox);
                /*-*/
            });
        }

        // Run initializer
        base.init();
    };
     
    $.CUI.Form.defaultOptions = {
        multiselectSortable:false
    };


    /*
     *  --- DISPATCHER ---
     */
    
    $.CUI.Include = function(plugin, basePath){
        if ($.CUI.IncludeFiles[plugin] != 0) {
            $.each($.CUI.IncludeFiles[plugin], function(i, file){
                $('body').append($('<script />', {
                    "type": "text/javascript",
                    "language": "javascript",
                    "src": [basePath, file].join('')
                }));
            $.CUI.IncludeFiles[plugin] = 0;
            });
        }
    }
    $.CUI.IncludeFiles = {
         'jquery-ui':       ['cui/libs/jquery-ui.js', 'cui/plugins/utils/timepicker-addon.js'],
         'jquery-history':  ['cui/plugins/utils/jquery.history.js'],
         'swfupload':       ['cui/plugins/swfupload/swfupload.js',
                             'cui/plugins/swfupload/swfupload.queue.js',
                             'cui/plugins/swfupload/fileprogress.js',
                             'cui/plugins/swfupload/handlers.js'],
         'tinymce':         ['cui/plugins/tinymce/jquery.tinymce.js'],
         'jquery-jstree':   ['cui/plugins/utils/mustache.js',
                             'cui/libs/jquery.cookie.js',
                             'cui/plugins/jstree/jquery.tree.js'],
         'map' :            ['cui/plugins/map/mod-map.js']
    }
    
    $.fn.CUI = function(plugin, options) {
        options.basePath = [(options ? (options.basePath || '') : ''), '/'].join('');

        return this.each(function(){
            switch (plugin) {
                case 'grid':
                    // CUI Grid for datas and medias:
                    (new $.CUI.Include('jquery-history', options.basePath));
                    (new $.CUI.Grid(this, options));
                break;
                case 'switcher':
                case 'multiselect':
                    if(options.multiselectSortable == true) {
                        (new $.CUI.Include('jquery-ui', options.basePath));
                    }
                    (new $.CUI.Form(this, plugin, options));
                break;
                case 'fieldset':
                case 'letterLimit':
                case 'file':
                    // CUI Form elements:
                    (new $.CUI.Form(this, plugin, options));
                break;
                case 'tree':
                    // JSTree:
                    (new $.CUI.Include('jquery-jstree', options.basePath));
                    $(this).jstree(options);

                break;
                case 'files':
                    // SwfUpload:
                    (new $.CUI.Include('swfupload', options.basePath));
                    var swfu;
                    var defaultOptions = {

                        // Button settings
                        button_image_url: [options.basePath, "layouts/backoffice/images/px.png"].join(''),
                        button_width: "75",
                        button_height: "22",
                        button_placeholder_id: "spanButtonPlaceHolder",
                        button_text: '<span class="btn-upload">Upload Files</span>',
                        button_text_style: ".btn-upload { font-family: Arial; font-size: 11px; color:#5b5b5b; }",
                        button_text_left_padding: 0,
                        button_text_top_padding: 0,
                        button_window_mode: "transparent",
                        // The event handler functions are defined in handlers.js
                        init_swfupload_handler : initSwfupload,
                        file_queued_handler : fileQueued,
                        file_queue_error_handler : fileQueueError,
                        file_dialog_complete_handler : fileDialogComplete,
                        upload_start_handler : uploadStart,
                        upload_progress_handler : uploadProgress,
                        upload_error_handler : uploadError,
                        upload_success_handler : uploadSuccess,
                        upload_complete_handler : uploadComplete,
                        queue_complete_handler : queueComplete
                    };
                    settings = $.extend({}, defaultOptions, options);
                    swfu = new SWFUpload(settings);

                    $('#' + settings.custom_settings.progressTarget).CUI('sortable', { items: '.field-preview-wrapper', placeholder: 'ui-state-highlight'});
                break;
                case 'rte':
                    // TinyMCE:
                    (new $.CUI.Include('tinymce', options.basePath));
                    var defaultOptions = {
                        script_url : [options.basePath, "cui/plugins/tinymce/tiny_mce.js"].join(''),
                        theme : "advanced",
                        width: parseInt($(this).css("width"))+4,
                        height : "400",
                        theme_advanced_resize_horizontal : false,
                        plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,"
                                + "insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,"
                                + "fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager",
                        theme_advanced_buttons1 : "undo,redo,separator,bold,italic,underline,forecolor,formatselect,"
                                                + "styleselect,separator,bullist,numlist,link,unlink,image,hr,"
                                                + "separator,fullscreen,code",
                        theme_advanced_buttons2 : "",
                        theme_advanced_buttons3 : "",
                        extended_valid_elements : "header[*],article[*],time[*],footer[*],aside[*],video[*],source[*]",
                        theme_advanced_toolbar_location : "top",
                        theme_advanced_toolbar_align : "left",
                        theme_advanced_statusbar_location : "bottom",
                        theme_advanced_resizing : true,
                        cleanup : false,
                        body_class : 'rte'
                    };
                    if (options && !options['plugins'] && options['add_plugins']) {
                        options['plugins'] = defaultOptions['plugins'] + ',' + options['add_plugins'];
                        delete(options['add_plugins']);
                    }

                    if (options && !options['extended_valid_elements'] && options['add_extended_valid_elements']) {
                    	options['extended_valid_elements'] = defaultOptions['extended_valid_elements'] + ',' + options['add_extended_valid_elements'];
                    	delete(options['add_extended_valid_elements']);
                    }
                    if (options && !options['theme_advanced_buttons1'] && options['add_buttons']) {
                        options['theme_advanced_buttons1'] = defaultOptions['theme_advanced_buttons1'] + ',' + options['add_buttons'];
                        delete(options['add_buttons']);
                    }
                    settings = $.extend({}, defaultOptions, options);
                    $(this).tinymce(settings);
                break;
                case 'map' :
                   (new $.CUI.Include('map', options.basePath));
                   var popinMap = {
                       config : {
                           mapDialog : 'dialog-map',
                           lnkEdit : 'settings-map',
                           clsAutocomplete : 'field-search',
                           zoom:13
                       },
                       isInit : false,
                       domDialogMap : null,
                       dialogMap : null,
                       inputSearch : null,
                       inputLat : null,
                       inputLng : null,
                       init : function($elem){
                            var that = this;
                            //create dialog
                            this.elem = $elem;
                            this.domDialogMap =  $('#' + this.config.mapDialog, this.elem);

                           //quick fix
                           if(undefined == $.fn.dialog){
                                (new $.CUI.Include('jquery-ui', options.basePath));
                           }
                            this.dialogMap = this.domDialogMap.dialog({
                                'autoOpen' : false,
                                'modal' : true,
                                'draggable' : false,
                                'open': function open(event, ui){
                                    //check if lat lng value exist
                                    var latMarker =  (that.inputLat.val() || 48.859),
                                        lngMarker =  (that.inputLng.val() || 2.35);
                                    if(!that.isInit){
                                       that.buildMap();
                                       if ( $('.' + that.config.clsAutocomplete, $(this)).length ){
                                           that.inputSearch =  $('.' + that.config.clsAutocomplete + ' input', $(this));
                                           that.buildAutocomplete();
                                       }
                                       that.addMarker(latMarker, lngMarker);
                                       that.isInit = true;
                                    } else {
                                        that.updateMarkerPosition(latMarker,lngMarker);
                                    }

                                    that.updateLatLngInput(latMarker, lngMarker);
                                    that.gmapDialog.zoomOn(1,that.config.zoom,false);

                                },
                                'close' : function close(){
                                    that.inputSearch.val('');
                                },
                                'resizable': false,
                                'width' : 433,
                                'dialogClass': 'dialog-map'
                            });

                            this.attachEvents();

                       },
                       /*Attach events on link edits settings + buttons popin save and cancel*/
                       attachEvents : function attachEvents(){
                           var that = this;
                           this.elem.find('.'+this.config.lnkEdit).bind('click', function(e){
                               e.preventDefault();
                               that.inputLat = $('#'+$(this).data('lat-id'));
                               that.inputLng = $('#'+$(this).data('lng-id'));
                               that.dialogMap.dialog('open');
                           });

                           this.domDialogMap.delegate('#ui-button-save','click', function(){
                               that.updateInputs();
                               that.dialogMap.dialog('close');
                           });

                           this.domDialogMap.delegate('#ui-button-cancel','click', function(){
                               that.dialogMap.dialog('close');
                           });

                           $('.ui-widget-overlay').live('click', function(){
                               that.dialogMap.dialog('close');
                           });
                       },
                       /*Build the ooMap*/
                       buildMap : function(){
                            this.gmapDialog = new ooMap();
                            this.gmapDialog.setup({
                                mapId : 'map',
                                lat : 10,
                                lng : 10,
                                zoom:3,
                                visible : true,
                                zoomControlStyle : "SMALL"
                            });
                       },
                       /*Append the Input Search above the map and Attach Autocomplete Search*/
                       buildAutocomplete : function(){
                           var that = this;
                           this.gmapDialog.findByAutocomplete(this.inputSearch,"ui-autocomplete-search", function(lat,lng){
                               that.updateMarkerPosition(lat,lng);
                           });
                       },
                       /*Add the marker : fix id to 1 in order to manage IT*/
                       addMarker : function addMarker(lat,lng){
                           var that = this;
                            this.gmapDialog.addMarker({
                                id : 1,
                                latLng : {
                                    lat : lat,
                                    lng : lng
                                }
                            });

                            //renderDraggable marker
                            this.gmapDialog.renderDraggable(this.gmapDialog.getMarker(1),{
                                dragEnd : function dragEnd(marker){
                                    var pos = marker.getPosition();
                                    that.updateLatLngInput(pos.Na, pos.Oa);
                                }
                            });
                       },
                       /*Store the marker position in data-value of the search input*/
                       updateLatLngInput : function updateLatLngInput(lat,lng){
                            this.inputSearch.data('lat', lat);
                            this.inputSearch.data('lng', lng);
                       },
                       /*Update the marker position on the map*/
                       updateMarkerPosition : function updateMarkerPosition(lat,lng){
                             this.gmapDialog.setPosition(1, lat, lng);
                             this.gmapDialog.zoomOn(1,this.config.zoom,false);
                       },
                       /*Update the form inputs value when the user validate the positino of the marker*/
                       updateInputs : function updateInputs(){
                           this.inputLat.val(this.inputSearch.data('lat'));
                           this.inputLng.val(this.inputSearch.data('lng'));
                       }
                   }
                   popinMap.init($(this));
                break;
                default:
                    // jQuery UI:
                    (new $.CUI.Include('jquery-ui', options.basePath));
                    ($(this)[plugin])(options);
                break;
            }
        })
    }

})(jQuery);

