$(document).ready(function(){
    //alert($('forminput:first-child').html());
    $('form').append('<input type="hidden" name="sent" value="1" />');
    
    $('.error_reason').click(function(){
        var reason = $('#'+$(this).attr('aria-describedby')).text()
        $.fancybox({
           content : reason
        });
    });
    $('.error_reason').tooltip({
            content: function(){
              return $(this).attr('title')
            }
          });
    /*$('input').click(function(){
        $(this).select();
    });*/
    /*if($('.hash').length > 0)
    {*/
        $( "form" ).submit(function( event ) {
            var toosmall = false;
            var additional = sendPostRequest({
                handler: 'RegisterController',
                action: 'get_login_code'
            });
            $('.hash').each(function(){
                if($(this).val().length >= 5)
                    $(this).val($.sha1($.sha1($(this).val())+additional));
            });
            //var val = $(this).find('input:password.hash').val();
            //$(this).find('input:password').val($.sha1(val));
        });
    //}
    $("input:password").change(function(){
       $(this).addClass("hash"); 
    });
    $('.datefield').each(function(){
          $(this).removeClass('hasDatepicker'); 
          $(this).datepicker({ 
              dateFormat: 'dd-mm-yy',
              firstDay: 1
        });
       });
 

});

