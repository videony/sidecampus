function postNotice(message, type)
{
   if(type === undefined)
       type = "information";
    var n = noty({
            text        : message,
            type        : type,
            dismissQueue: true,
            timeout     : 10000,
            closeWith   : ['click'],
            layout      : 'bottomCenter',
            theme       : 'relax',
            maxVisible  : 10
        });
}
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};
function ajaxindicatorstart(text)
{
	jQuery('body').append('<div id="resultLoading" style="display:none"><div><span class="icon spin icon-64 icon-cog"></span><div>'+text+'</div></div><div class="bg"></div></div>');

	jQuery('#resultLoading').css({
		'width':'100%',
		'height':'100%',
		'position':'fixed',
		'z-index':'10000000',
		'top':'0',
		'left':'0',
		'right':'0',
		'bottom':'0',
		'margin':'auto'
	});

	jQuery('#resultLoading .bg').css({
		'background':'#000000',
		'opacity':'0.7',
		'width':'100%',
		'height':'100%',
		'position':'absolute',
		'top':'0'
	});

	jQuery('#resultLoading>div:first').css({
		'width': '250px',
		'height':'75px',
		'text-align': 'center',
		'position': 'fixed',
		'top':'0',
		'left':'0',
		'right':'0',
		'bottom':'0',
		'margin':'auto',
		'font-size':'16px',
		'z-index':'10',
		'color':'#ffffff'

	});

    jQuery('#resultLoading .bg').height('100%');
       jQuery('#resultLoading').fadeIn(300);
    jQuery('body').css('cursor', 'wait');
}
function ajaxindicatorstop()
{
    jQuery('#resultLoading .bg').height('100%');
       jQuery('#resultLoading').fadeOut(300);
    jQuery('body').css('cursor', 'default');
    jQuery('#resultLoading').remove();
}

function sendPostRequest(post_data)
{
	/*$.fancybox({
		content: 'Requête en cours. Veuillez attendre'
	});*/
	var html = '';
	$.ajax({
		url: 'ajax.php',
		async: false,
		type: 'post',
		data: post_data,
		success: function(xhr){
			//$.fancybox.close();
			html = xhr;
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
	return html;
}
function sendAsyncPostRequest(post_data, callback, ajaxindicator)
{
    var indicator = (ajaxindicator === false)?false:true;
	var html = '';
	$.ajax({
		url: 'ajax.php',
		async: true,
		type: 'post',
		data: post_data,
                beforeSend: function(){
                    if(indicator)
                        ajaxindicatorstart('Traitement en cours...');
                },
                complete: function(){
                    if(indicator)
                        ajaxindicatorstop();  
                },
		success: function(xhr){
                    callback(xhr);
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
	return html;
}
function nl2br (str, is_xhtml) {
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function defineDefault(platform) {
    var html = sendPostRequest({
        handler: 'PlatformListController',
        action: 'set_default',
        id: platform
    });
    window.location = 'request.php?action=ConnectedAccueil';
}
function usePlatform(platform) {
    var html = sendPostRequest({
        handler: 'PlatformListController',
        action: 'use',
        id: platform
    });
	
    ajaxindicatorstart('Veuillez patienter...');
	
    window.location = 'request.php?action=ConnectedAccueil';
}

function quitPlatform(platform) {
    if(confirm('Etes-vous sûr(e) de vouloir quitter cette plateforme?'))
    {
        var html = sendPostRequest({
                handler: 'PlatformListController',
                action: 'quit_platform',
                id: platform
        });	
        if(html == 'NO')
            alert('Vous ne pouvez pas quitter cette plateforme car il n\'y a pas d\'autres administrateurs');
        else
        {
            reloadPage();
        }
    }
}
function reloadPage() {
    ajaxindicatorstart('Veuillez patienter...');
    window.location.reload();
}
function changePage(page) {
    ajaxindicatorstart('Veuillez patienter...');
    window.location = page;
}

$(document).ready(function(){
    
    // Si on est sur Mobile, on n'active pas le tooltip
        if($('.mobile_menu_text:visible').length === 0)
        {
            $('.tooltip').tooltip();
            $('.right_tooltip').tooltip({
                track: false,
                position: {my: "right middle", at: "left middle"}
            });
        }
        // Command wrapper, comme dans la gestion des utilisateurs, autour des commandes.
        // Supprime ces encadrés quand aucune commande à l'intérieur
        /*$( ".commandwrapper" ).filter( function() {
                return (!$.trim($(this).text()).length && !$(this).has('img').length);
        }).remove();*/
        
        // Ouvre sur click de la photo
         $(document).on('click', '.see_profile_top', function(event){
                if(!$('.profile_view').is(':visible'))
                  $('.profile_view').show("fade");
          });
          
          // Quand connecté, liste des plateformes auxquelles on est affilié
        $('#PlatformConnected').on('click', function(event){
            event.preventDefault();
            if(!$('#connected_platforms').is(':visible'))
                $("#connected_platforms").show("fade");
                $('#connected_platforms').html('<p class="loading"><span class="icon spin icon64 icon-cog"></span></p>');
                sendAsyncPostRequest({
                        handler: 'PlatformListController',
                        action: 'get_connected_platforms'
                }, function(html){
                    $('#connected_platforms').html(html);
                    $("#connected_platforms").mCustomScrollbar();
                });
        });
        
        // Referme les popups quand on clique sur la page
        $(document).mouseup(function (e)
        {
            var container = $("#connected_platforms, .platform_view, .profile_view, .lower_platform_view");

            if (!container.is(e.target) // if the target of the click isn't the container...
                && container.has(e.target).length === 0) // ... nor a descendant of the container
            {
                container.hide("fade");
            }
        });
        
        // Ouvre les images qui sont extensibles quand on clique dessus
        $(document).on('click', 'img.expandable', function(){
           var img = $(this).attr('src');
          $.magnificPopup.open({
                items: {
                  src: img
                },
                type: 'image'
              });
        });
        
        
        // Session message if one
        if($('#session_message').length > 0)
        {
            var message = $('#session_message').html();
            postNotice(message);
            /*$( "<div>" )
            .appendTo( document.body )
            .text( message )
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
            .delay( 7000 )
            .hide({
                    effect: "blind",
                    duration: "slow"
            }, function() {
                    $( this ).remove();
            });*/
            $('#session_message').remove();
        }
        
        // Disable page when only clickable once button (to avoid problems)
        $('.clickable_only_once').click(function(){
             ajaxindicatorstart('Veuillez patienter...');
        }) ;
        $(document).on('click', '.ui-tooltip', function(){
           $(this).hide(); 
        });
        $(document).on('click', '#reconnect_after_session_expire', function(){
            window.location = 'request.php?action=Connect';
        });
        
        // Menu du côté, propre à la plateforme
        $('#collapse_side_menu').click(function(){
            if($('#side_menu_list').hasClass('force-invisible'))
            { 
                $(this).removeClass('icon-angle-right');
                $(this).addClass('icon-angle-left');
                $(this).closest('div').removeClass('slight-transparent');
                $('#side_menu_list').removeClass('force-invisible').show('slide'); 
                $.ajax({
                    url: 'ajax.php',
                    async: true,
                    type: 'post',
                    data: {
                      handler: 'PreferencesController',
                      action: 'side_menu_visible'
                  }
              });
            }
            else
            {
                $(this).removeClass('icon-angle-left');
                $(this).addClass('icon-angle-right');
                $(this).closest('div').addClass('slight-transparent');
                $('#side_menu_list').addClass('force-invisible').hide('slide'); 
                $.ajax({
                    url: 'ajax.php',
                    async: true,
                    type: 'post',
                    data: {
                      handler: 'PreferencesController',
                      action: 'side_menu_invisible'
                  }
              });
            }
        });
        
        // Pour Mobile, cache le menu de la plateforme (ou l'ouvre)
        $('#platform_name_holder').click(function(){
            if($('#side_menu_list').hasClass('force-invisible'))
            { 
                $(this).css('height', 'auto');
                $(this).find('.icon').removeClass('icon-caret-down').addClass('icon-caret-up');
                $('#side_menu_list').removeClass('force-invisible'); 
            }
            else
            {
                $(this).css('height', '50px');
                $(this).find('.icon').removeClass('icon-caret-up').addClass('icon-caret-down');
                $('#side_menu_list').addClass('force-invisible'); 
            }
        });
    $(document).on('click', '.top_menu .menu_element', function(){
        if($(this).find('a').length > 0)
            changePage($(this).find('a').attr('href'));
    });
   
   if($('#platform_name_holder:visible').length > 0)
   {
       // On est sur Mobile
       $('#side_menu_list').addClass('force-invisible');
       $('.centerhelper').width(0);
       $('.person_icons .menu_element').each(function(){
          $('.centerhelper').width($('.centerhelper').width()+$(this).width()+10); 
       });
       $('.centerhelper').width($('.centerhelper').width()+6+$('.profile_head_picture').width()+50); 
   }
   else
   {
       $('.person_icons').removeClass('force-invisible');
   }
});

$(window).load(function(){
    $('.profile_view').css('right', $('#profile_head_picture').width()/2);
    if($('#which_platform').length > 0)
    {
        postNotice($('#which_platform').text());
        /*$( "<div>" )
        .appendTo( document.body )
        .text( $('#which_platform').text())
        .addClass( "notification_ban ui-state-default ui-corner-bottom" )
        .position({
                my: "center top",
                at: "center top",
                of: window
        })
        .show({
                effect: "blind"
        })
        .delay( 5000 )
        .hide({
                effect: "fade",
                duration: "slow"
        }, function() {
                $( this ).remove();
        });*/
    }
   
});

