/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var page_title = document.title.replace(/\[(\d+)\] /g, "");
function updateNotifications(forceUpdate)
{
	var force = (forceUpdate === true);
    $.ajax({
        url: 'ajax.php',
        async: true,
        type: 'post',
        global: false,
        data: {
            handler: 'CompteController',
            action: 'get_nb_notifs'
        },
        success: function(result){
            
            var infos = JSON.parse(result);
            var totalNotifs = infos[1];
            
            $('.total_notifs_count').text(totalNotifs);
            var totalNotifs = $('.total_notifs_count').text();
            if(parseInt(totalNotifs) > 0)
                document.title = "["+totalNotifs+"] "+page_title;
            else
                document.title = page_title;
		
            var nbNotifs = infos[0];
                //$.fancybox.close();
            if(force || parseInt(nbNotifs) > parseInt($('#platform_notif_count').text().trim()))
            {
                $.ajax({
                    url: 'ajax.php',
                    async: true,
                    type: 'post',
                    data: {
                        handler: 'CompteController',
                        action: 'get_notifications'
                    },
                    success: function(html){
                        $('.notifs').replaceWith(html);
                        $(".notifs").mCustomScrollbar();
                        $('.notifs_count').text(nbNotifs);
                        if(force || $('.platform_view').is(':visible'))
                            markAsRead(true);
                    }
                });
            } 
        },
        error: function(xhr, text, error) {
            if(xhr.status == 403)
            {
                $.magnificPopup.open({
                 items: {
                   src: $('<div class="session_expire">'
                             +'<p>Votre session a expirée ou vous avez perdu des droits.'
                                 +'Cliquez sur OK pour revenir sur la page de connexion. '
                             +'</p>'
                             +'<button id="reconnect_after_session_expire" class="button">OK</button>'
                         +'</div>')
                 },
                 type: 'inline',
                 modal: true
               });
            }
        }
});
}
function markAsRead(forceRead)
{
    var force = (forceRead === true);
    if(force || parseInt($('.notifs_count').text()) > 0)
    {
        sendAsyncPostRequest({
                handler: 'CompteController',
                action: 'notification_mark_as_read'
        }, function(html){
            //$('.notifs_count').text('0');
            $('.total_notifs_count').text(html);
            if(parseInt(html) > 0)
                document.title = "["+html+"] "+page_title;
            else
                document.title = page_title;
        }, false);
    }
}

$(document).ready(function(){
    $('.next_platform').click(function(){
       sendAsyncPostRequest({
                handler: 'CompteController',
                action: 'next_platform'
        }, function(html){
            var infos = JSON.parse(html);
            var id = infos[0];
            var nom = infos[1];
            $('.platform_title').attr('id', id);
            $('.platform_title h2').text(nom);
            updateNotifications(true);
        });
    });
    $('.previous_platform').click(function(){
       sendAsyncPostRequest({
                handler: 'CompteController',
                action: 'previous_platform'
        }, function(html){
            var infos = JSON.parse(html);
            var id = infos[0];
            var nom = infos[1];
            $('.platform_title').attr('id', id);
            $('.platform_title h2').text(nom);
            updateNotifications(true);
            
        });
    });
    $(document).on('click', '.notification_link', function(e){
        if($(this).attr('href') !== '#')
        {
            e.preventDefault();
            var link = $(this).attr('href');
            sendAsyncPostRequest({
                    handler: 'CompteController',
                    action: 'notification_click'
            }, function(html){
                changePage(link);
            });
        }
    });
    window.setInterval(function(){ 
        updateNotifications();
        
    }, 5000);    
    $('.nb_notifs').hide();
    $('.nb_notifs').click(function(){
        $('#open_notifications').click();
    });
    $(".notifs").mCustomScrollbar();
    $(document).on('click', '#open_notifications', function(event){
          if(!$('.platform_view').is(':visible'))
          {
                $('.platform_view').show("fade");
                markAsRead();
          }
    });
    
    $(document).on('click', '.platform_title', function(){
       usePlatform($(this).attr('id')); 
    });
    
    $(document).on('click', '.load_more_notifications', function(){
        var cnt = $('.notifs .notification').length;
        var link = $(this);
        link.hide();
        $('.notification_loader').show();
        sendAsyncPostRequest({
            handler: 'CompteController',
            action: 'more_notifications',
            current: cnt
        }, function(html){
            link.before(html);
            $(".notifs").mCustomScrollbar();
            $('.notification_loader').hide();
            link.show();
        }, false);
    });
      
});

$(window).load(function(){
    $('.platform_view').css('right', $('#profile_head_picture').width()+15);
    //$('.nb_notifs').css('right', $('#profile_head_picture').width()-5);
    $('.nb_notifs').show(500);
});