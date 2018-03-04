$(document).ready(function(){
    
    $('#tabs').tabs({
        show: { effect: "blind", duration: 200 } 
    });
    
    if($('#connexion').attr('checked') == 'checked')
        $('#connexion_fieldset').show(800);
    else
        $('#connexion_fieldset').hide(800);
    $('#connexion').click(function(){
            $('#connexion_fieldset').toggle(800);
     });
     
    
    var uploader = new plupload.Uploader({
        browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
        url: 'request.php?action=Compte&change_profile_picture=1',
        multi_selection: false
      });

      uploader.init();

      uploader.bind('FilesAdded', function(up, files) {
        var html = '';
        plupload.each(files, function(file) {
          html += '<span id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></span>';
        });
        document.getElementById('filelist').innerHTML += html;
      });

      uploader.bind('UploadProgress', function(up, file) {
        document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
      });

      uploader.bind('Error', function(up, err) {
        document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
      });
      uploader.bind('UploadComplete', function(up, err) {
          $('#filelist').html('');
        var src = $('#profile_head_picture').attr('src');
                    $('#profile_head_picture').attr('src', src+"&timstamp="+new Date().getTime());
                    $('#form_profile_pic').attr('src', src+"&timstamp="+new Date().getTime());
                    alert('Votre photo de profil a été modifiée avec succès. Cliquez sur OK pour terminer.');
      });

      document.getElementById('start-upload').onclick = function() {
        uploader.start();
      };
 
      $('#email_events').click(function(){
         $('#event_frequency').toggle(500); 
      });
      $('#email_todo').click(function(){
         $('#todo_frequency').toggle(500); 
      });
      if($('#email_events:checked').length == 0)
          $('#event_frequency').hide(); 
      if($('#email_todo:checked').length == 0)
          $('#todo_frequency').hide(); 
    
});