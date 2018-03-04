function validate_contact_form(form)
{
    var name = form.find('.tx_name'); 
    var mail = form.find('.tx_email'); 
    var mess = form.find('.tx_message'); 
    var ok = true;
    if(name.length > 0)
    {
        if(name.val().length < 3)
        {
            name.addClass('error');
            form.find('.name_error').text(' (Veuillez introduire un nom de plus de 3 caractères)');
            ok = false;
        }
        else
        {
            name.removeClass('error');
            form.find('.name_error').text('');
        }
    }
    if(mail.length > 0)
    {
        if(!mail.val().match(/\S+@\S+\.\S+/))
        {
            mail.addClass('error');
            form.find('.mail_error').text(' (L\'adresse e-mail n\'est pas valide)');
            ok = false;
        }
        else
        {
            mail.removeClass('error');
            form.find('.mail_error').text('');
        }
    }
    return ok;
}
$(document).ready(function(){
    $(document).on('click', '.view_messages', function(){
       $(this).closest('.contact').find('.reponses').toggle('slide'); 
    });
    $(document).on('click', '.respond', function(){
       $(this).closest('.contact').find('.contact_form').toggle('slide'); 
    });
    $(document).on('click', '.remove_annonce', function(){
        if(confirm('Voulez-vous vraiment supprimer votre annonce?'))
        {
            var annonce = $(this).closest('.annonce');
            sendAsyncPostRequest({
                    handler: 'AnnoncesController',
                    action: 'remove_annonce',
                    id_annonce: annonce.attr('id')
                }, function(html){
                    annonce.remove();
            });
        }
    })
    $(document).on('change', '.tx_name, .tx_email', function(){
        validate_contact_form($(this).closest('.contact_form'));
    });
    $(document).on('click','.send_message', function(){
       
        var ok = validate_contact_form($(this).closest('.contact_form'));
        if(ok)
        {
            var name = $(this).closest('.contact_form').find('.tx_name');
            var email = $(this).closest('.contact_form').find('.tx_email');
            var message = $(this).closest('.contact_form').find('.tx_message');
            var form = $(this).closest('.contact_form');
            if(name.length == 0 && email.length == 0)
            {
                sendAsyncPostRequest({
                    handler: 'AnnoncesController',
                    action: 'send_message',
                    id_annonce: form.closest('.annonce').attr('id'),
                    message: message.val()
                }, function(html){
                    if(html == 'SENT')
                    {
                        message.val('');
                        form.hide();
                        html = "Votre message a bien été envoyé à l'annonceur";
                        $.magnificPopup.open({
                            items: {
                              src: $('<div class="session_expire">'+html+'</div>')
                            },
                            type: 'inline'
                        });
                    }
                });
            }
            else
            {
                sendAsyncPostRequest({
                    handler: 'AnnoncesController',
                    action: 'send_message',
                    id_annonce: form.closest('.annonce').attr('id'),
                    message: message.val(),
                    mail:   email.val(),
                    name:   name.val()
                }, function(html){
                    if(html == 'SENT')
                    {
                        message.val('');
                        form.hide();
                        html = "Votre message a bien été envoyé à l'annonceur";
                        $.magnificPopup.open({
                            items: {
                              src: $('<div class="session_expire">'+html+'</div>')
                            },
                            type: 'inline'
                        });
                    }
                });
            }
                
        }
    });
    $(document).on('keyup', '#search', function(){
        var val = $(this).val().toLowerCase();
        $('.annonce').each(function(){
            if($(this).find('.titre').text().toLowerCase().indexOf(val) >= 0
                    || $(this).find('.description').text().toLowerCase().indexOf(val) >= 0)
                $(this).show();
            else
                $(this).hide();
        });
    });
    $(document).on('click', '.get_link', function(){
        var link = $(this).attr('title');
        var input  = '<input type="text" id="copy_link" value="'+link+'" />';
        var button = '<button class="button copy_link">Copier le lien</button>';
        $.magnificPopup.open({
            items: {
              src: $('<div class="session_expire">'+input+'<br/><br/>'+button+'</div>')
            },
            type: 'inline'
        });
    });
    $(document).on('click', '.copy_link', function(){
       $('#copy_link').select();
        document.execCommand("copy"); 
        $(this).before('<span class="icon icon-check-sign" style="color: darkgreen"></span>Copié<br/><br/>');
    });
});