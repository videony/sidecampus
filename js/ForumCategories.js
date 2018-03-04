$(document).ready(function(){
    $(document).on('click', '.deleteS', function(event){
       if(confirm('Etes-vous sûr de vouloir supprimer ce sujet ainsi que tous les messages qu\'il contient?'))
       {
            event.preventDefault();
            var pid = $(this).attr('id');
            
             var html = sendPostRequest({
                     handler: 'ForumCategoriesController',
                     action: 'delete_subject',
                     id: pid
             });   
             if(html == 'OK')
             {                 
                 //window.location.reload();
                 $(".topic"+pid).remove();
             } 
             else 
             {
                 alert("Sujet non supprimé ! Si l'erreur persiste, contactez un des webmasters.");
             }
         }
   });
   
});

