$(document).ready(function(){
    $( "form" ).submit(function( event ) {
        // Mail check
        if($('#mail').val().indexOf("unamur") >= 0)
        {
            if($('#mail').val().indexOf("student.unamur.be") == -1)
            {
                var resume = confirm("Nous avons détecté que vous utilisiez une adresse e-mail de l'Unamur mais qui ne"+
                            " semble pas correcte. Le format correct est 'prenom.nom@student.unamur.be'. Voulez-vous"+
                            " vraiment vous inscrire avec cet adresse e-mail?");
                if(resume)
                    return true;
                else {
                    window.setTimeout(function(){
                        $.fancybox.close();
                    }, 1000);
                    return false;
                }
                    
            }
            else
                return true;
        } 
    });
});


