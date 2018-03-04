$(document).ready(function(){
   $(document).on('click', '#hide_platform', function(){
       sendAsyncPostRequest({
                handler: 'PlatformManController',
                action: 'hide_platform'
        }, function(html){
            reloadPage();
        });
   }) ;
   $(document).on('click', '#show_platform', function(){
       sendAsyncPostRequest({
                handler: 'PlatformManController',
                action: 'unhide_platform'
        }, function(html){
            reloadPage();
        });
   }) ;
});


