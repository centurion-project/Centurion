var inContextLanguage;
$(function() {
    
    $("body").addClass('hasJs');

    // dropdown sites
    $("ul.main li ul").each(function() {
        var $picker = $(this);
        $picker.bind('mouseenter',function() {
            $picker.show();
        }).bind('mouseleave',function() {
            $picker.hide();
        }).parent().bind('mouseenter',function() {
            $picker.show();
        }).bind('mouseleave',function() {
            $picker.hide();
        });
    });

    //$(".datepicker").CUI("datepicker");
    
    $(".hl-template .hl-item .actions").hide();
    $(".hl-template .hl-item")
        .mouseenter(function() {
            $(this)
                .addClass("hl-item-hover")
                .find('.actions').show();
        })
        .mouseleave(function() {
            $(this)
                .removeClass("hl-item-hover")
                .find('.actions').hide();
        });
        
    $("a.help").each(function() {
        $$ = $(this);
        $("<span/>", {
            "class":"bullet",
            text: $$.attr('title'),
            css: {
                width: parseInt($$.attr('title').length)*6
            }
        }).appendTo($$);
        $("<span/>", {
            "class":"ui-icon ui-icon-triangle-1-s queue"
        }).appendTo($$);
    }); 
    
    inContextLanguage = function() {
        cpt=1;
        $('a.help .ui-icon-flag').each(function(){
            $(this).parent().attr('id', 'trigger-translate-'+cpt);
            $(this).parent().parent().find('ul').attr('id', 'picker-translate-'+cpt).appendTo('body').hide();
            $(this).click(function(evt) {
                    var id = $(this).parent().attr('id').split('-')[2]; 
                    $('#picker-translate-'+id).css({'position':'absolute','left':parseInt(evt.pageX-35)+'px','top':parseInt(evt.pageY+10)+'px','z-index':'99'}).show();
                    return false;
                });
            $('#picker-translate-'+cpt).bind({
                mouseenter: function(){ 
                    $(this).show();
                },
                mouseleave: function(){
                    $(this).hide();
                }
            })
            cpt = cpt+1;
        });
    }
    
    inContextLanguage();
    
    $('.box-dashboard-glance .glance div').bind({
        mouseenter: function() {
            $(this).addClass('hover');
        },
        mouseleave: function() {
            $(this).removeClass('hover');
        }
    })
    
    
    // FIX HTML5 FORM
    // function to detect browser support or not by Jeremy Keith
    function checkAttribute(element, attribute) {
        var test = document.createElement(element);
        if (attribute in test) {
            return true;
        } else {
            return false;
        }
    }
    
    if (!checkAttribute('input', 'autofocus')) {
        $("input[autofocus]").focus();
    }

    // minimize-maximize sidebar
    if ( $('body').hasClass('toggle-sidebars')){
        var header = $('body header.sh'),
            buttonToggle = $('<span />',{ 'class' : 'toggle-sidebar-left icon icon-minimize'}),
            nav = $('section nav'),
            section = header.siblings('section'),
            parentSection = header.parent('section'),
            userCookies, valueCookie;
        
        //memorize init value
        header.attr('data-init-margin',header.css('margin-left'));
        section.attr('data-init-margin', section.css('margin-left'));
        parentSection.attr('data-init-background', parentSection.css('background-image'));
        
        if ( !$.support.opacity ){
            nav.attr('data-init-filter', nav.css('filter'));
        }

        function minimizeMaximize(minimize, speed){
            if (minimize){
                nav.hide();
                $.each([header, section], function(){
                    $(this).animate({
                        'margin-left' : 0
                    }, speed);
                    buttonToggle.addClass('icon-maximize').removeClass('icon-minimize');
                });
                parentSection.css('background-image', 'none');
                document.cookie = "sidebarleftHidden=1";
            } else {
                $.each([header, section], function(){
                    $(this).animate({
                        'margin-left' : $(this).attr('data-init-margin')
                    }, speed, function(){
                        parentSection.css('background-image', parentSection.attr('data-init-background'));
                        nav.fadeIn('fast', function(){
                            if ( $(this).attr('data-init-filter') ){
                                $(this).css('filter', $(this).attr('data-init-filter'));
                            }
                            
                        });
                    });
                    buttonToggle.addClass('icon-minimize').removeClass('icon-maximize');
                });
                document.cookie = "sidebarleftHidden=0";
            }
        }

        //manage cookies
        if (document.cookie){
            userCookies = document.cookie.split(';');
            for (var i = 0, len = userCookies.length; i < len; i++){
                if (userCookies[i].indexOf('sidebarleftHidden=') != -1 ){
                    valueCookie = userCookies[i].split('=')[1];
                    minimizeMaximize(parseInt(valueCookie,10) , 0);
                }
            }
        }
        header.append(buttonToggle);
        
        buttonToggle.bind({
            'click' : function(){
                if ( $(this).hasClass('icon-minimize')){
                    minimizeMaximize(true, 250);
                } else {
                    minimizeMaximize(false, 250);
                }
            }
        });
    }

});