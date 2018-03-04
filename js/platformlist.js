$(document).ready(function(){
     
    $(document).on('click', '.view', function(event){
        var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'PlatformListController',
                action: 'get_platform',
                id: pid
        });	

        $.fancybox({
                    transitionIn: 'elastic',
                    transitionOut: 'elastic',
                    content: html
            });
        
    });
    $(document).on('click', '.ask', function(){
        if(confirm('Etes-vous sûr de vouloir rejoindre cette plateforme?'))
        {
            var pid = $(this).closest('tr').attr('id');
            var html = sendPostRequest({
                    handler: 'PlatformListController',
                    action: 'add_platform',
                    id: pid
            });
            postNotice('La demande a été envoyée aux administrateurs de la plateforme.', 'success');
            /*$( "<div>" )
                .appendTo( document.body )
                .text( 'La demande a été envoyée aux administrateurs de la plateforme.' )
                .addClass( "notification_ban ui-state-default ui-corner-bottom" )
                .position({
                        my: "center top",
                        at: "center top",
                        of: window
                })
                .show({
                        effect: "blind"
                })
                .click(function(){
                        $( this ).remove();
                })
                .delay( 3000 )
                .hide({
                        effect: "blind",
                        duration: "slow"
                }, function() {
                        $( this ).remove();
                });*/
            $(this).attr('src', html);
            $(this).removeClass('ask');
            $(this).addClass('redraw');
            $(this).tooltip( "option", "content", "Demande envoyée mais pas encore acceptée" );
        }
    });
    $(document).on('click', '.quit', function(event){
        if(confirm('Etes-vous sûr(e) de vouloir quitter cette plateforme?'))
        {
            var pid = $(this).closest('tr').attr('id');
            var html = sendPostRequest({
                    handler: 'PlatformListController',
                    action: 'quit_platform',
                    id: pid
            });	
            if(html == 'NO')
                alert('Vous ne pouvez pas quitter cette plateforme car il n\'y a pas d\'autres administrateurs');
            else
            {
                reloadPage();
            }
        }
    });
    $('.view').tooltip({
       position: { my: "center", at: "center", of: "body"},
       show: { duration: 0 },
       tooltipClass: "platform-view",
       content: function() {
           var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'PlatformListController',
                action: 'get_platform',
                id: pid
        });	
        return html;
       } 
    });
    
});
