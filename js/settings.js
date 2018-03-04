$(document).ready(function(){
    $(document).on('click', '.resetDefault', function(event){
        event.preventDefault();
       if(confirm('Etes-vous sûr de vouloir rétablir les valeurs par défaut ?'))
       {
           event.preventDefault();
           var pid = $(this).attr('id');
           
            var html = sendPostRequest({
                     handler: 'CompteController',
                     action: 'reset_default_settings'
            });   
            if(html == 'OK')
            {
                /*$.fancybox({
                   content: 'Vos informations ont bien été sauvegardées.' 
                });*/
                changePage('request.php?action=Compte');               
            } 
            else 
            {
                $.fancybox({
                   content: 'Une erreur est survenue, les valeurs  n\'ont pas été réinitialisées.' 
                });
             }
         }
    });
    
});

