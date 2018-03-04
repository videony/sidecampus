$(document).ready(function(){
    $(document).on('click', '.deleteM', function(event){
       if(confirm('Etes-vous sûr de vouloir effacer ce message?'))
       {
            event.preventDefault();
            var pid = $(this).attr('id');
            
             var html = sendPostRequest({
                     handler: 'ForumTopicController',
                     action: 'delete_post',
                     id: pid
             });   
             if(html == 'OK')
             {
                 window.location.reload();
             } 
             else 
             {
                 alert("Message non supprimé ! Si l'erreur persiste, contactez un des webmasters.");
             }
         }
   });
   
   $(document).on('click', '.editM', function(event){       
        event.preventDefault();
        var pid = $(this).attr('id');

         var post = sendPostRequest({
                 handler: 'ForumTopicController',
                 action:  'get_post',
                 id: pid
         });   
         
         alert(post);
         
     });
 
});
