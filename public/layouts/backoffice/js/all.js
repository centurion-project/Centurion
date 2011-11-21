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
    

});