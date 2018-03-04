function cut() {
    var selection = new Array();
            $('#fileswrapper div.selected').each(function(){
                if($(this).hasClass('folderline'))
                    type = 'folder';
                else
                    type = 'file';

               selection.push(type+'_'+$(this).attr('id')) ;
            });
            var html = sendAsyncPostRequest({
                  handler: 'FilesController',
                  action: 'cut_selection',
                  selection: selection
              }, function(html){
                  if(html == 'OK')
                    return true;
                    else
                    alert('Un problème est arrivé. La sélection n\'a pas pu être mise en mémoire');
              });
}
function copy() {
    var selection = new Array();
            $('#fileswrapper div.selected').each(function(){
                if($(this).hasClass('folderline'))
                    type = 'folder';
                else
                    type = 'file';

               selection.push(type+'_'+$(this).attr('id')) ;
            });
            var html = sendAsyncPostRequest({
                  handler: 'FilesController',
                  action: 'copy_selection',
                  selection: selection
              }, function(html){
                  if(html == 'OK')
                    return true;
                    else
                    alert('Un problème est arrivé. La sélection n\'a pas pu être mise en mémoire');
              });
}
function paste() {
    var html = sendAsyncPostRequest({
                  handler: 'FilesController',
                  action: 'paste_selection'
              }, function(html){
                  if(html == 'EMPTY')
                        alert('La sélection est vide. Opération annulée');
                    else
                    {
                        status = html.substr(0,2);
                        if(status == 'NA')  // Not all was moved
                            alert('Certains éléments de la sélection n\'ont pas pu être déplacés. Ceci est peut-être dû à un manque de droits ou'
                                        +' que le fichier/dossier existe déjà dans le dossier cible');
                        refreshFileList(html.substr(2, (html.length)-1));
                    }
              });
}
function refreshFileList(html) {
    $('.tooltip').tooltip(); 
    $('#fileswrapper').html(html);
    //$('#selection_size').text(0);
    
    if($('.cut').length > 0)
    {
            // DRAG AND DROP
          $( ".fileline" ).multiDraggable({
              revert: "invalid",
              cursor: "move",
              opacity: 0.85,
              group: ".selected",
              dragNative: function(event, ui){
                   ui.helper.addClass('selected');
              }
          });  
           $( ".folderline" ).droppable({
               accept: '.fileline',
               activeClass: 'targeted',
               drop: function(event, ui){
                   cut();
                   var html = sendPostRequest({
                       handler: 'FilesController',
                       action: 'change_folder',
                       id: $(this).closest('div.folderline').attr('id')
                   });	
                   var html = sendPostRequest({
                         handler: 'FilesController',
                         action: 'paste_selection'
                     });	
                   var html = sendPostRequest({
                       handler: 'FilesController',
                       action: 'up_folder',
                       id: $(this).closest('div.folderline').attr('id')
                   });	
                   refreshFileList(html);
               }
           });
       }
}

$(document).ready(function(){

//-------------------------------------------------------------------------------
    // Configuration de l'environnement
//-------------------------------------------------------------------------------    
    $('#add_file_form').hide();
    $('#start-upload-wrapper').hide();
    $('#upload_in_progress').hide();
    refreshFileList($('#fileswrapper').html());
    var max_upload_size = $('#max_upload_size').text()*1;
    var max_download_size = $('#max_download_size').text()*1;
    
//-------------------------------------------------------------------------------    
    // Remonter de dossier
//-------------------------------------------------------------------------------    
    $(document).on('click', '.up_folder', function(){
          // Mise à jour des éléments
          var html = sendPostRequest({
                handler: 'FilesController',
                action: 'up_folder'
            });	
        refreshFileList(html);
           // Mise à jour du path
           var chemin = sendPostRequest({
                handler: 'FilesController',
                action: 'get_folder_path'
            });	
            $('#chemin').val(chemin);
            // Déselection 
            $('#check_uncheck_all').prop('checked', false);
      });
      
//-------------------------------------------------------------------------------    
    // Changement du dossier par la barre du haut
//-------------------------------------------------------------------------------    
    $(document).on('submit', '#chemin_form', function(event){
        event.preventDefault();
        var html = sendPostRequest({
                handler: 'FilesController',
                action: 'change_folder_by_path',
                path: $('#chemin').val()
            });	
            if(html == 'NO')
                alert('Le chemin que vous avez rentré n\'est pas valide');
            else
                refreshFileList(html);
    });
//-------------------------------------------------------------------------------       
    // Ajout de dossier
//-------------------------------------------------------------------------------    
    $(document).on('click', '.add_folder', function(){
         $('#add_folder_form').dialog(); 
      });
      
    $(document).on('click', '#add_folder_button', function(){
         var html = sendPostRequest({
                handler: 'FilesController',
                action: 'add_folder',
                name: $('#folder_name').val()
            });	
            if(html == 'NO')
                alert('Ce dossier existe déjà');
            else
                refreshFileList(html);
            $('#folder_name').val('');
            $('#add_folder_form').dialog('close'); 
      });
      
//-------------------------------------------------------------------------------       
    // Ajout de fichier
//------------------------------------------------------------------------------- 

   if($('#add_file_form').length > 0)
   {
            // Formulaire d'ajout de fichiers
       $(document).on('click', '.add_file', function(){
          $('#add_file_form').toggle(500); 
       });

       // Uploader
       
       var uploader = new plupload.Uploader({
            browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
            url: 'request.php?action=Files&upload_files=1',
            max_file_size: (max_upload_size*1000)+'kb'
          });
          
          uploader.init();

          uploader.bind('FilesAdded', function(up, files) {
              $('#start-upload-wrapper').show();
            var html = '';
            plupload.each(files, function(file) {
              html += '<span id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></span>';
            });
            document.getElementById('filelist').innerHTML += html;
          });

          uploader.bind('UploadProgress', function(up, file) {
            document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<b class="progress_percent">' + file.percent + "%</b>";
          });

          uploader.bind('Error', function(up, err) {
              html = '<span id="' + err.file.id + '" class="too_big error">' + err.file.name + ' (' + plupload.formatSize(err.file.size) + ') <b class="progress_percent">Trop gros</b></span>';
              document.getElementById('filelist').innerHTML += html;

          });
          uploader.bind('UploadComplete', function(up, err) {
              uploader.setOption('url', 'request.php?action=Files&upload_files=1&timestamp='+Date.now());
              $('#filelist').html('');
              var folder = sendPostRequest({
                    handler: 'FilesController',
                    action: 'refresh_folder'
                });
              refreshFileList(folder);	

              var html = sendPostRequest({
                    handler: 'FilesController',
                    action: 'comments_post_upload'
                });	
              $('#comments_form').html(html);
              $('#comments_form').dialog({
                  width: 610,
                  height: 400,
              });
              ajaxindicatorstop();  
                $('#upload_in_progress').hide();
                $('#start-upload-wrapper').hide();
                $('#start-upload').show();

          });

          document.getElementById('start-upload').onclick = function() {
            if($('#extract_archives').prop('checked') == true)
            {
                uploader.setOption('url', uploader.getOption('url')+"&extract_archives=1");
            }
            else
            {
                uploader.setOption('url', uploader.getOption('url')+"&extract_archives=0");
            }
            ajaxindicatorstart('Upload en cours...');
            $('#start-upload').hide();
            $('#upload_in_progress').show();
            uploader.start();
          };

          $(document).on('click', '.ignore_comments', function(event){
              event.preventDefault();
              $('#comments_form').dialog("close");
              $('#comments_form').html('');
              var html = sendPostRequest({
                handler: 'FilesController',
                action: 'refresh_folder'
            });	
            refreshFileList(html);
              $('#add_file_form').hide(500);
            
            // Déselection 
            $('#check_uncheck_all').prop('checked', false);
          });
      }

//-------------------------------------------------------------------------------       
    // Sélectionnage de dossier ou fichier
//-------------------------------------------------------------------------------   

    function sizemerge(operation, size1, unit1, size2, unit2)
    {
        size1 = parseFloat(size1);
        size2 = parseFloat(size2);
        switch(unit1)
        {
            case 'GB':
                size1 = size1*1000000;
                break;
            case 'MB':
                size1 = size1*1000;
        }
        switch(unit2)
        {
            case 'GB':
                size2 = size2*1000000;
                break;
            case 'MB':
                size2 = size2*1000;
        }
        if(operation == '-')
            return size1-size2;
        else
            return size1+size2;
    }
    /*$(document).on('click', '.fileline', function(event){
        $(this).find('.file_checkbox').click(); 
    });*/
   $(document).on('click', '.file_checkbox', function(event){
       event.stopPropagation();
      if($(this).closest('.fileline').hasClass('selected'))
      {
          $(this).prop('checked', false);
          $(this).closest('.fileline').removeClass('selected');
          var size = $(this).closest('.fileline').find('.size').text();
          var unit = $(this).closest('.fileline').find('.size_unit').text();
          //var totalsize = $('#selection_size').text();
          var diff = sizemerge('-', totalsize, 'MB', size, unit);
          
          //$('#selection_size').text(Math.round(diff/100)/10);
      }
      else
      {
          $(this).prop('checked', true);
          $(this).closest('.fileline').addClass('selected');
          var size = $(this).closest('.fileline').find('.size').text();
          var unit = $(this).closest('.fileline').find('.size_unit').text();
          //var totalsize = $('#selection_size').text();
          var diff = sizemerge('+', totalsize, 'MB', size, unit);
          
          //$('#selection_size').text(Math.round(diff/100)/10);
      }
   });
   // Check uncheck tous les fichiers
   $('#check_uncheck_all').closest('div').click(function(){
       $('#check_uncheck_all').click();
   });
   $(document).on('click', '#check_uncheck_all', function(){
       $('.file_checkbox').prop('checked', this.checked);   
        if(this.checked == true)
        {
            $('.file_checkbox').closest('.fileline').addClass('selected');
            var somme = 0;
            $('.fileline').each(function(){
                var size = $(this).find('.size').text();
                var unit = $(this).find('.size_unit').text();
                somme = sizemerge('+', somme, 'KB', size, unit);
            });
            //$('#selection_size').text(Math.round(somme/100)/10);
        }
        else
        {
            $('.file_checkbox').closest('.fileline').removeClass('selected');
            //$('#selection_size').text(0);
        }
   });
   
//-------------------------------------------------------------------------------    
// Explorateur de fichiers
//-------------------------------------------------------------------------------    
      $(document).on('click', '.folderline', function(){
          // Mise à jour des éléments
          var html = sendPostRequest({
                handler: 'FilesController',
                action: 'change_folder',
                id: $(this).closest('div.folderline').attr('id')
            });	
            refreshFileList(html);
           // Mise à jour du path
           var chemin = sendPostRequest({
                handler: 'FilesController',
                action: 'get_folder_path'
            });	
            $('#chemin').val(chemin);
            
            // Déselection 
            $('#check_uncheck_all').prop('checked', false);
      });
      
      $(document).on('click', '.refresh', function(){
          // Mise à jour des éléments
          var html = sendPostRequest({
                handler: 'FilesController',
                action: 'refresh_folder'
            });	
            refreshFileList(html);
            
            // Déselection 
            $('#check_uncheck_all').prop('checked', false);
      });
      
      $(document).on('click', '.make2perline', function(){
          sendPostRequest({
                handler: 'FilesController',
                action: 'change_nb_per_line',
                nbperline: 'perline2'
            });	
          $('.perline3').each(function(){
             $(this).removeClass('perline3');
          });
          $('.perlinefolder3').each(function(){
             $(this).removeClass('perlinefolder3');
          });
      });
      
      $(document).on('click', '.make3perline', function(){
          sendPostRequest({
                handler: 'FilesController',
                action: 'change_nb_per_line',
                nbperline: 'perline3'
            });	
          $('.fileline').each(function(){
             $(this).addClass('perline3');
          });
          $('.folderline').each(function(){
             $(this).addClass('perlinefolder3');
          });
      });
      
      
      $(document).on('click', '.delete', function(){
          if($('.selected').length == 0)
              return;
          if(confirm('Confirmez-vous la suppression?'))
          {
              $('.delete').attr('disabled', 'disabled');
                var selection = new Array();
                $('#fileswrapper div.selected').each(function(){
                    if($(this).hasClass('folderline'))
                        type = 'folder';
                    else
                        type = 'file';

                   selection.push(type+'_'+$(this).attr('id')) ;
                });
                sendAsyncPostRequest({
                      handler: 'FilesController',
                      action: 'delete_selection',
                      selection: selection
                  }, function(html){
                      status = html.substr(0,2);
                        if(status == 'NA')  // Not all was moved
                            alert('Certains éléments de la sélection n\'ont pas pu être supprimés car vous n\'en possédiez pas les droits.');
                        refreshFileList(html.substr(2, (html.length)-1));
                        $('.delete').removeAttr('disabled');
                  });	
                  
            }
      });
      
      $(document).on('click', '.copy', function(){
            if($('.selected').length == 0)
              return;
            result = copy();
      });
      
       $(document).on('click', '.cut', function(){
            if($('.selected').length == 0)
              return;
            result = cut();
      });
      
      $(document).on('click', '.paste', function(){
            paste();
      });
      
      $(document).on('click', '.comment_edit', function(){
            $('#edit_comment_form textarea').text($(this).closest('div.fileline').find('div.file_descr').attr('title'));
            $('#edit_comment_form #edit_comment_form_id').val($(this).closest('div.fileline').attr('id'));
            $('#edit_comment_form').dialog(); 
      });
      
      $(document).on('click', '.rename_file', function(){
          $('#rename_form input#elem_name').val($(this).closest('div.fileline').find('b.filename').attr('title'));
          $('#rename_form input#rename_form_type').val('file');
          $('#rename_form input#rename_form_id').val($(this).closest('div.fileline').attr('id'));
          $('#rename_form').dialog(); 
      });
      
      $(document).on('click', '.rename_folder', function(event){
          event.stopPropagation();
          $('#rename_form input#elem_name').val($(this).closest('div.folderline').find('b.foldername').attr('title'));
          $('#rename_form input#rename_form_type').val('folder');
          $('#rename_form input#rename_form_id').val($(this).closest('div.fileline').attr('id'));
          $('#rename_form').dialog(); 
      });
      
      $(document).on('click', '#edit_comment_button', function(){
         var html = sendPostRequest({
                handler: 'FilesController',
                action: 'edit_comment',
                id: $('#edit_comment_form_id').val(),
                comment: $(this).closest('fieldset').find('textarea').val()
            });	
            if(html != 'NO')
                reloadPage();
      });
      
      $(document).on('click', '#rename_button', function(){
         
         var html = sendPostRequest({
                handler: 'FilesController',
                action: 'rename_'+$('#rename_form input#rename_form_type').val(),
                id: $('#rename_form input#rename_form_id').val(),
                name: $(this).closest('fieldset').find('input#elem_name').val()
            });	
            /*$.fancybox({
                        modal: true,
			content: 'html'
		});*/
            if(html != 'NO')
                reloadPage();
      });
      
      $(document).on('click', '.download_file', function(){
          var size = $(this).closest('div.fileline').find('.size').text();
		  var size_unit = $(this).closest('div.fileline').find('.size_unit').text();
		  switch(size_unit)
		  {
			case 'KB':
				size = size / 1000;
				break;
			case 'GB':
				size = size * 1000;
				break;		
		  }
          if(size > max_download_size)
          {
              var message = 'Vous ne pouvez pas télécharger des fichiers de plus de '+max_download_size+' MO';
              alert(message);
              return false;
          }
           var $preparingFileModal = $("#preparing-file-modal");
           $preparingFileModal.dialog({ modal: true });
           var lien = 'dfi.php?action=download_file&id='+$(this).closest('div.fileline').attr('id')+'&timestamp='+(new Date().getTime());
           $.fileDownload(lien, {
               successCallback: function (url) {
                   $preparingFileModal.dialog('close');
               },
               failCallback: function (responseHtml, url) {
                   if(responseHtml == 'TOOBIG')
                   {
                       $preparingFileModal.dialog('close');
                       $("#too-big-modal").dialog({ modal: true });
                   }
                   else
                   {
                        $preparingFileModal.dialog('close');
                        $("#error-modal").dialog({ modal: true });
                   }
               }
           });
           return false; //this is critical to stop the click event which will trigger a normal file download! 
      });
      
      
      $(document).on('click', '.viewable', function(){
       var file = $(this).closest('.fileline').attr('id');
       sendAsyncPostRequest({
            handler: 'FilesController',
            action: 'authorize_view',
            id_file: file
        }, function(url){
            if(url == 'CANNOT_DISPLAY')
            {
                $.magnificPopup.open({
                    items: {
                      src: $('<div class="session_expire">Ce type de fichier ne peut pas être vu en ligne</div>')
                    },
                    type: 'inline'
                });
            }
            else if(url != 'NO')
            {
                $.magnificPopup.open({
                    items: {
                      src: url
                    },
                    type: 'iframe'
                }); 
            }
            
        });
   });
   
});