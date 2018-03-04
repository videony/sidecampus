function edit_event(id, type)
    {
        var link = sendPostRequest({
            handler: 'CalendarController',
            action: 'get_edit_link',
            id: id,
            type: type
        });
        window.location = link;
    }
    function delete_event(id, type)
    {
        if(confirm('Voulez-vous retirer cet évènement de l\'agenda?'))
        {
            var html = sendPostRequest({
                handler: 'CalendarController',
                action: 'delete_event',
                id: id,
                type: type
            });	
            changePage("request.php?action=Calendar");
        }
    }
   $(document).ready(function(){
       
       $(document).on('click', '#download_ics_button', function(){

           var $preparingFileModal = $("#preparing-file-modal");
           $preparingFileModal.dialog({ modal: true });
           var lien = $('#ics_url').text();
           $.fileDownload(lien, {
               successCallback: function (url) {
                   $preparingFileModal.dialog('close');
               },
               failCallback: function (responseHtml, url) {
                   $preparingFileModal.dialog('close');
                   $("#error-modal").dialog({ modal: true });
               }
           });
           return false; //this is critical to stop the click event which will trigger a normal file download! 
      });
   }) ;
   $(window).load(function(){
       var event = getUrlParameter('event'); 
       var type = getUrlParameter('type'); 
       if(event !== undefined && type !== undefined)
       {
           var html = sendPostRequest({
                    handler: 'CalendarController',
                    action: 'see_full_event',
                    id: event,
                    type: type
            });	

            $.fancybox({
                        transitionIn: 'elastic',
                        transitionOut: 'elastic',
                        content: html
                });
       }
   });
     
