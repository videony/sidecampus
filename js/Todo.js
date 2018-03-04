$(document).ready(function(){
    var seq_datefield = 0;
    $(document).on('click', '.remove', function(){
        if($(this).closest('.note').attr('id') == 'new')
            return $(this).closest('.note').remove();
        if(confirm('Voulez-vous vraiment supprimer cette note?'))
        {
            var todo_id = $(this).closest('div.note').attr('id');
            var todo = $(this).closest('div.note');
            sendAsyncPostRequest({
                handler: 'TodoController',
                action: 'remove',
                id: todo_id
            }, function(html){
                if(html == 'OK')
                    todo.remove()
                else
                    alert('La note n\'a pas pu être supprimée. Si celà se reproduit, veuillez nous en informer.');
            });	
        }
    });
    $(document).on('click', '.save', function(){
       var dateval = $(this).closest('.note').find('.datefield').val();
       var noteval = $(this).closest('.note').find('.note_textarea').val();
       var newnote = $(this).closest('.note');
       var idval   = newnote.attr('id');
       var actionval;
       if(newnote.attr('id') == 'new')
           actionval = 'add';
       else
           actionval = 'edit';
       sendAsyncPostRequest({
            handler: 'TodoController',
            action: actionval,
            id: idval,
            date: dateval,
            note: noteval
        }, function(html){
            if(html.substr(0,2) == 'KO')
                alert(html.substr(2))
            else
            {
                newnote.find('.datefield').remove();
                newnote.find('.note_textarea').remove();
                newnote.find('.deadline').append("<b>"+dateval+"</b>");
                newnote.append("<p></p>");
                newnote.find("p").text(noteval).css("white-space", "pre-wrap");
                var command = newnote.find('.commands').find('.save');
                command.attr('src', 'media/icons/pencil.png');
                command.removeClass('save');
                command.addClass('edit');
                newnote.attr('id', html);
                var status = sendPostRequest({
                    handler: 'TodoController',
                    action: 'get_status',
                    id: html
                });
                newnote.attr('class', 'note '+status);
            }
        });	
    });
    $('#add_button').click(function(){
       $('#clearer').before($('#add_form').html()); 
       $('.datefield').each(function(){
          $(this).removeClass('hasDatepicker'); 
          $(this).attr('id', "df_"+seq_datefield++);
          $(this).datepicker({ dateFormat: 'dd-mm-yy' });
       });
    });
    
    $(document).on('click', '.edit', function(){
       $(this).attr('src', 'media/icons/disk.png');
       $(this).removeClass('edit');
       $(this).addClass('save');
       var note = $(this).closest('.note');
       var dateval = note.find('.deadline').find("b").text().trim();
       var noteval = note.find('p').text().trim();
       var datefield = $('#add_form').find('.datefield_container').html();
       var notefield = $('#add_form').find('.notefield_container').html();
       note.find('.deadline').find("b").replaceWith(datefield);
       note.find(".datefield").val(dateval);
       note.find("p").replaceWith(notefield);
       note.find("textarea").text(noteval);
       $('.datefield').each(function(){
          $(this).removeClass('hasDatepicker'); 
          $(this).attr('id', "df_"+seq_datefield++);
          $(this).datepicker({ dateFormat: 'dd-mm-yy' });
       });
       
      
       
    });
    
    $('.quote-container').sortable({
        containment: "parent",
        cursor: "move",
        items: "> .note",
        update: function( event, ui ) {
            var todo = ui.item.attr('id');
            var pos = $('.note').index(ui.item);
            sendAsyncPostRequest({
                    handler: 'TodoController',
                    action: 'exchange_position',
                    id: todo,
                    position: pos+1
                }, function(html){});
        }
    });
});