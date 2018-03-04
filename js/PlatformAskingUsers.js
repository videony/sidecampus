$(document).ready(function(){
   
    // Tablesorter
    $(document).on('click', '.asking_see', function(event){
       event.preventDefault();
       var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'see_profile',
                id_demande: pid
        });	

        $.fancybox({
                    transitionIn: 'elastic',
                    transitionOut: 'elastic',
                    content: html
            });
   });
     $(document).on('click', '.asking_accept', function(event){
         var row = $(this).closest('tr');
        var pid = $(this).closest('tr').attr('id');
        var html = sendAsyncPostRequest({
                handler: 'PlatformAskingUsersController',
                action: 'accept_user',
                id: pid
        }, function(){
            
            row.remove();
        });	
    });
    $(document).on('click', '.asking_reject', function(event){
       var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'PlatformAskingUsersController',
                action: 'reject_user',
                id: pid
        });	
        $(this).closest('tr').remove();
    });
    $(document).on('click', '.asking_ban', function(){
       var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'PlatformAskingUsersController',
                action: 'ban_user',
                id: pid
        });	
        $(this).removeClass('asking_ban').addClass('asking_unban');
        $(this).removeClass('icon-hand-right').addClass('icon-thumbs-up');
        $(this).closest('tr').attr('class', 'banned');
        $(this).tooltip( "option", "content", "Débannir" );
    });
    $(document).on('click', '.asking_unban', function(){
        var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'PlatformAskingUsersController',
                action: 'unban_user',
                id: pid
        });	
        $(this).addClass('asking_ban').removeClass('asking_unban');
        $(this).addClass('icon-hand-right').removeClass('icon-thumbs-up');
        $(this).closest('tr').attr('class', '');
        $(this).tooltip( "option", "content", "Bannir" );
    });
    
    $('.asking_see').tooltip({
       position: { my: "center", at: "center", of: "body"},
       show: { duration: 0 },
       tooltipClass: "platform-view",
       content: function() {
           var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'see_profile',
                id_demande: pid
        });	
        return html;
       } 
    });
   
    
});
