function defineDefaultPerms(member)
{
    var html = sendPostRequest({
        handler: 'UserManController',
        action: 'set_default_permissions',
        id: member
    });
    reloadPage();
}
function savePermissions(member)
{
    var selected = new Array();
    $("input[type=checkbox]:checked").each(function(){
        selected.push($(this).attr('id'));
    });
    var html = sendPostRequest({
        handler: 'UserManController',
        action: 'save_permissions',
        id: member,
        permissions: selected
    });
    reloadPage();
}

$(document).ready(function(){
   $('input').attr('name', 'permissions[]');
   
   $(document).on('click', '.see', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'see_profile',
                id_member: pid
        });	
        
        $.magnificPopup.open({
            items: {
              src: $('<div class="session_expire"> '+html+' </div>')
            },
            type: 'inline'
        });
   });
   /*$('.see').tooltip({
       position: { my: "left", at: "left+100", of: ".body"},
       show: { duration: 500 },
       tooltipClass: "platform-view",
       content: function() {
           var pid = $(this).closest('tr').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'see_profile',
                id_member: pid
        });	
        return html;
       } 
    });*/
   
   $(document).on('click', '.permissions', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'permissions',
                id: pid
        });	

        $.fancybox({
                    transitionIn: 'elastic',
                    transitionOut: 'elastic',
                    content: html
            });
   });
   
   $(document).on('click', '.block', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'block_user',
                id: pid
        });
        if(html == 'OK')
        {
            $(this).removeClass('icon-lock').addClass('icon-unlock');
            $(this).closest('div.userman_profile').addClass('blocked');
            $(this).removeClass('block');
            $(this).addClass('unblock');
            $(this).tooltip( "option", "content", "Débloquer" );
        }
   });
   $(document).on('click', '.unblock', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'unblock_user',
                id: pid
        });
        if(html == 'OK')
        {
            $(this).removeClass('icon-unlock').addClass('icon-lock');
            $(this).closest('div.userman_profile').removeClass('blocked');
            $(this).removeClass('unblock');
            $(this).addClass('block');
            $(this).tooltip( "option", "content", "Bloquer" );
        }
   });
   $(document).on('click', '.eject', function(event){
       if(confirm('Etes-vous sûr de vouloir exclure cet utilisateur de la plateforme?'))
       {
            event.preventDefault();
            var pid = $(this).closest('div.userman_profile').attr('id');
             var html = sendPostRequest({
                     handler: 'UserManController',
                     action: 'exclude_user',
                     id: pid
             });
             if(html == 'OK')
             {
                 $(this).closest('div.userman_profile').remove();
             }
         }
   });
   $(document).on('click', '.set_admin', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'set_admin',
                id: pid
        });
        if(html == 'OK')
        {
            reloadPage();
        }
   });
   $(document).on('click', '.set_moderator', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'set_mod',
                id: pid
        });
        if(html == 'OK')
        {
            reloadPage();
        }
   });
   $(document).on('click', '.set_member', function(event){
       event.preventDefault();
       var pid = $(this).closest('div.userman_profile').attr('id');
        var html = sendPostRequest({
                handler: 'UserManController',
                action: 'set_member',
                id: pid
        });
        if(html == 'OK')
        {
            reloadPage();
        }
   });
   $(document).on('click', '.profile_picture', function(){
      $(this).closest('.userman_profile').find('.profile_hover').toggle('drop');
   });
   $(document).on('keyup', '#nom, #statut', function(){
      $('.userman_profile').each(function(){
            if($(this).find('.nom').text().toLowerCase().indexOf($('#nom').val().toLowerCase()) >= 0
                    && $(this).find('.statut').text().toLowerCase().indexOf($('#statut').val().toLowerCase()) >= 0)
                $(this).show();
            else
                $(this).hide();
      });
   });
   
   
   
   
});
$(window).load(function(){
   /*$('#demandes').find('tr td:last-child').each(function(){
      var totalwidth = 0;
      $(this).find('.commandwrapper').each(function(){
          totalwidth += $(this).width();
      }) ;
      $(this).find('.endcolwrapper').width(totalwidth+50);
   }); */
});


