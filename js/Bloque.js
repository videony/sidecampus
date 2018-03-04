var last_color_index = 0;
var default_case_html = '';
function init()
{
    $( ".resizable" ).each(function(){
        var id = $(this).closest('table').attr('id');
        $(this).resizable({
            grid: [10000000, 21],
            maxHeight: 480,
            minHeight: 20,
            handles: "s",
              containment: 'table#'+id+' tbody',
            stop: function( event, ui ) {
                var id = ui.element.attr('id');
                var nb_hours = Math.round(ui.size.height/21);
                sendAsyncPostRequest({
                      handler: 'BloqueController',
                      action: 'resize_session',
                      id_session: id,
                      nb_hours: nb_hours
                  }, function(html){

                  }, false);	
            }

        });
    });
    $('.resizable').each(function(){
        var id = $(this).closest('table').attr('id');
        $(this).draggable({
            grid: [10000000, 21],
            containment: 'table#'+id+' tbody',
            //grid: [col_width+2, 22],
            stop: function( event, ui ) {
              var id = ui.helper.attr('id');
              var start = Math.round(parseInt(ui.helper.css('top').replace('px', ''))/21);
              var min_hour = parseInt($('.interval:first-child').attr('id'));
                sendAsyncPostRequest({
                    handler: 'BloqueController',
                    action: 'move_session',
                    id_session: id,
                    start: start+min_hour
                }, function(html){

                }, false);	
            }
        });
    });
}
function getColor(elem, differentFromPairs)
{
    var diff = (differentFromPairs === false?false:true);
    var color = ['green', 'red', 'blue', 'orange', 'beige', 'brown', 'gray'];
    if(last_color_index == color.length)
        last_color_index = 0;
    if(!elem.hasClass('virgin') && !diff)
    {
        return color[last_color_index++];
    }
    var i = 0;
    while(rand = Math.floor((Math.random() * 5) + 1))
    {
        if(elem.closest('.container').find('.'+color[rand]).length == 0 || i > 10)
            return color[rand];
        else
            i++;
    }
}
$(document).ready(function(){
    
    init();
    sendAsyncPostRequest({
        handler: 'BloqueController',
        action: 'get_default_case'
    }, function(html){
        default_case_html = html;
    }, false);	
    
    
    
    $('.session').dblclick(function() {
        $(this).find('span.solid_course').hide();	
        $(this).find('form.form_course').show();
    } );
    
    function validate(){
        var id = $(this).closest('.session').attr('id');
        var elem = $(this).closest('.session');
        var text = elem.find('.course_field').val(); 
        if(id % 1 === 0)
        {
            sendAsyncPostRequest({
                handler: 'BloqueController',
                action: 'set_text',
                id_session: id,
                course: text,
            }, function(html){
                elem.find('span.solid_course').text(elem.find('.course_field').val()); 
                elem.find('span.solid_course').show();	
                elem.find('form').hide();
            }, false);
            return false;
        }
        var infos = id.split('_');
        var color = getColor(elem)
        
        sendAsyncPostRequest({
            handler: 'BloqueController',
            action: 'add_session',
            course: text,
            start_hour: infos[2],
            end_hour: parseInt(infos[2])+1,
            color: color,
            day: infos[1],
            id_semaine: infos[0]
        }, function(html){
            elem.attr('id', html);
            elem.addClass(color).removeClass('virgin');
            elem.find('span.solid_course').text(elem.find('.course_field').val()); 
            elem.find('span.solid_course').show();	
            elem.addClass('resizable');
            elem.find('form').hide();
            elem.find('form').append('<span class="icon icon-32 icon-remove right delete_session"></span>'
                                    +'<span class="icon icon-32 icon-dashboard right change_color"></span>'
                                    +'<span class="icon icon-32 icon-check right validate"></span>');
            init();
        }, false);
        return false;
    }
    $(document).on('submit', 'form.form_course', validate);
    $(document).on('click', '.validate', validate);
    $(document).on('click', '.delete_session', function(){
            var id = $(this).closest('.session').attr('id');
            var elem = $(this).closest('.session');
           sendAsyncPostRequest({
                    handler: 'BloqueController',
                    action: 'delete_session',
                    id_session: id
                }, function(html){
                    elem.before(html);
                    elem.remove();
                }, false); 
    });
    $(document).on('click', '.change_color', function(){
        var elem = $(this).closest('.session');
        var id = elem.attr('id');
        var color = getColor(elem, false);
        elem.removeClass('green red blue orange beige brown gray');
        elem.addClass(color);
        sendAsyncPostRequest({
                    handler: 'BloqueController',
                    action: 'change_color',
                    id_session: id,
                    color: color
                }, function(html){
                    
                }, false); 
    });
    $(document).on('change', '.min_range, .max_range', function(){
        var semaine = $(this).closest('table');
        var min_session_start = 25;
        var max_session_length = 1; 
        semaine.find('.resizable').each(function(){
            var begin = Math.round(parseInt($(this).css('top').replace('px', ''))/21);
            var nbhours = Math.round($(this).height()/21);
            //console.log(begin, nbhours)
            if(begin < min_session_start)
                min_session_start = begin;
            if(nbhours > max_session_length)
                max_session_length = nbhours;
        });
        var first_range = parseInt(semaine.find('.interval:first-child').attr('id'));
        var last_range = parseInt(semaine.find('.interval:last-child').attr('id'));
        
        var max_session_end = min_session_start + max_session_length;
        if(semaine.find('.min_range').val() > min_session_start + first_range)
            semaine.find('.min_range').val(min_session_start + first_range);
        else if(semaine.find('.max_range').val() < max_session_end + first_range)
            semaine.find('.max_range').val(max_session_end + first_range);
        else
        {
            if(semaine.find('.min_range').val() < first_range)
            {
                // Ajouter des rows avant
                var nbrows = first_range - semaine.find('.min_range').val();
                for(var i=0;i<nbrows;i++)
                {
                    semaine.find('td.day_col .container').each(function(){
                        $(this).find('.session').each(function(){
                           $(this).css('top', (parseInt($(this).css('top').replace('px', ''))+21)+'px'); 
                        });
                        var ids = $(this).find('.virgin').attr('id').split('_');
                        $(this).prepend(default_case_html);
                        $(this).find('.virgin').first().attr('id', ids[0]+'_'+ids[1]+'_'+(first_range-i-1));
                       
                    });
                    var cur = (first_range-i-1);
                    semaine.find('td:nth-child(2)').prepend('<div class="interval" id="'+cur+'">'+cur+'</div>')
                }
            }
            else if(semaine.find('.min_range').val() > first_range)
            {
                // Supprimer des rows avant
                var nbrows = semaine.find('.min_range').val() - first_range;
                for(var i=0;i<nbrows;i++)
                {
                    semaine.find('td.day_col .container').each(function(){
                        $(this).find('.virgin').first().remove();
                    });
                    semaine.find('.session').each(function(){
                        $(this).css('top', (parseInt($(this).css('top').replace('px', ''))-21)+'px'); 
                     });
                    semaine.find('td:nth-child(2)').find('.interval').first().remove();
                }
            }
            else if(semaine.find('.max_range').val() > last_range)
            {
                // Ajouter des rows après
                var nbrows = semaine.find('.max_range').val() - last_range - 1;
                for(var i=0;i<nbrows;i++)
                {
                    var cur = (last_range+i+1);
                    semaine.find('td:nth-child(2)').append('<div class="interval" id="'+cur+'">'+cur+'</div>')
                    
                    semaine.find('td.day_col .container').each(function(){
                        var ids = $(this).find('.virgin').attr('id').split('_');
                        $(this).append(default_case_html);
                        $(this).find('.virgin').last()
                                .attr('id', ids[0]+'_'+ids[1]+'_'+cur)
                                .css('top', ((cur-first_range)*21 - 1)+'px'); 
                       
                    });
                }
            }
            else if(semaine.find('.max_range').val() < last_range+1)
            {
                // Supprimer des rows après
                var nbrows = last_range - semaine.find('.max_range').val();
                for(var i=0;i<nbrows;i++)
                {
                    semaine.find('td.day_col .container').each(function(){
                        $(this).find('.virgin').last().remove();
                    });
                    semaine.find('td:nth-child(2)').find('.interval').last().remove();
                }
            }
        }
    });
    $(document).on('click', '.delete_semaine', function(){
        if(confirm('Voulez-vous vraiment supprimer cette semaine de bloque? Cette action n\'est pas réversible'))
        {
            var semaine = $(this).closest('table');
            sendAsyncPostRequest({
                handler: 'BloqueController',
                action: 'delete_semaine',
                id_semaine: semaine.attr('id')
            }, function(html){
                semaine.remove();
            }); 
        }
    });
    $(document).on('click', '.reset', function(){
        if(confirm('Voulez-vous vraiment réinitialiser cette bloque? Les sessions et semaines que vous aurez enregistrées'+
                        ' seront définitivement perdues'));
        {
            sendAsyncPostRequest({
                handler: 'BloqueController',
                action: 'reset_bloque'
            }, function(html){
                reloadPage();
            }); 
        }
    });
    
    $(document).on('click', '.add_semaine', function(){
       var form = $('<div class="session_expire">'+$('.add_form').html()+'</div>');
       form.find('input').datepicker({ 
            dateFormat: 'dd-mm-yy',
            firstDay: 1,
            minDate: 0,
            beforeShowDay: function(date){ return [date.getDay() == 1,""]}
        });
       $.magnificPopup.open({
            items: {
              src: form
            },
            type: 'inline'
          }); 
    });
    $(document).on('click', '.add_semaine_form', function(){
        var val = $('#dt_lundi').val();
        sendAsyncPostRequest({
            handler: 'BloqueController',
            action: 'add_semaine',
            lundi:  val
        }, function(html){
            if(html == 'NOT_MONDAY')
                alert('La date de départ doit être un lundi!');
            else if(html == 'TAKEN')
                alert('Une semaine existe déjà qui commence à cette date');
            else if(html == 'NO_DATE')
                alert('Veuillez préciser une date');
            else
                reloadPage();
        }); 
    });
    if($('.mobile-device:visible').length > 0)
    {
        $('.mobile-device').hide();
        $.magnificPopup.open({
            items: {
              src: $('<div class="session_expire">'+$('.mobile-device').html()+'</div>')
            },
            type: 'inline'
          }); 
          
    }
});