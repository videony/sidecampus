$(document).ready(function(){
    $(document).on('click', '.deleteC', function(event){
       if(confirm('Etes-vous sûr de vouloir supprimer cette catégorie ainsi que tous les sujets et messages qu\'elle contient?'))
       {
            event.preventDefault();
            var pid = $(this).attr('id');
            
             var html = sendPostRequest({
                     handler: 'ForumController',
                     action: 'delete_category',
                     id: pid
             });   
             if(html == 'OK')
             {
                 //window.location.reload()
                 $(".cat"+pid).remove();
             } 
             else 
             {
                 alert("Catégorie non supprimée ! Si l'erreur persiste, contactez un des webmasters.");
             }
         }
   });
   $(document).on('click', '.editC', function(event){       
        event.preventDefault();
        var pid = $(this).attr('id');

//        
//        sendPostRequest({
//                handler: 'ForumCategoriesEditController',
//                action: 'edit_post',
//                id: pid
//        });   
        window.location.href = "request.php?action=ForumCategoriesEdit&idcategorie="+pid;
     });
});