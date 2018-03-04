$(document).ready(function(){
   $('.platform_message').each(function(){
       if($(this).find('p.message').height() < 60)
       {
           $(this).find('.show_full_message').remove();
       }
       else
       {
           $(this).height(120).append('<div class="biggable">Voir plus</div>');;
           $(this).find('p.message').height(50).css('overflow', 'hidden');
       }
   }) ;
   $( ".commandwrapper" ).filter( function() {
                return (!$.trim($(this).text()).length && !$(this).has('img').length);
        }).remove();
   
   $(document).on('click', '.biggable', function(){
        var parent = $(this).closest('.platform_message');
        parent.css('height', 'auto');
        parent.find('.message').css('height', 'auto');
        $(this).removeClass('biggable');
        $(this).addClass('smallable');
        $(this).text('Voir moins');
   });
   $(document).on('click', '.smallable', function(){
        var parent = $(this).closest('.platform_message');
        parent.height(120);
        parent.find('.message').height(50).css('overflow', 'hidden');
        $(this).addClass('biggable');
        $(this).removeClass('smallable');
        $(this).text('Voir plus');
   });
   $(document).on('click', '.show_full_message', function(){
       var fulltext = $(this).closest('.platform_message').find('p.message').html();
        $.magnificPopup.open({
          items: {
            src: '<div class="expand_container"><p>'+fulltext+'</p></div>'
          }
        }); 
   });
   $(document).on('click', '.delete_message', function(){
      var id = $(this).closest('.platform_message').attr('id');
      var message = $(this).closest('.platform_message');
      sendAsyncPostRequest({
            handler: 'ConnectedAccueilController',
            action: 'delete_message',
            id_message: id
        }, function(html){
            if(html == 'OK')
                message.remove();
            else
                alert('Une erreur s\'est produite. Si l\'erreur se reproduit, veuillez nous en informer');
        });
   });
   $(document).on('click', '.write_platform_message', function(){
       var form = $('#pl_message_form').html();
       $.magnificPopup.open({
          items: {
            src: '<div class="write_message_container">'+form+'</div>'
          }
        }); 
   });
   $(document).on('click', '#write_message', function(){
       var txt = $('#new_platform_message').val().trim();
       if(txt == '')
       {
           alert('Vous ne pouvez pas publier un message vide');
           return;
       }
       sendAsyncPostRequest({
            handler: 'ConnectedAccueilController',
            action: 'add_message',
            tx_message: txt
        }, function(html){
            if(html == 'OK')
                reloadPage();
            else
                alert('Une erreur s\'est produite. Si l\'erreur se reproduit, veuillez nous en informer');
        });
   });
   
   $('.news').mCustomScrollbar();
   $('.utils').mCustomScrollbar();
   $('.fun').mCustomScrollbar();
   $('.fun_elem').mCustomScrollbar();
   
   if($('#popup_news_message').length > 0)
    {
        $.featherlight($('#popup_news_message div.message_content'));
        /*$.magnificPopup.open({
            items: {
              src: '<div class="session_expire">'+content+'</div>'
            },
            type: 'inline'
          });*/
      }
});
