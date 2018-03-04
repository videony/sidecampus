  
$(document).ready(function(){
    $('form').append('<input type="hidden" name="sent" value="1" />');
    
    $( "form" ).submit(function( event ) {
       var mdp = $('#mdp').val();
       var additional = sendPostRequest({
            handler: 'ConnectController',
            action: 'get_added_code',
            login: $('#login').val()
        });
        var salt = sendPostRequest({
            handler: 'ConnectController',
            action: 'get_salt'
        });
        var pass = $.sha1(mdp);
        pass = $.sha1(pass+additional);
        pass = $.sha1(pass+salt);
        $('#mdp').val(pass);
    });
});


