$(document).ready(function(){
    //$("#slideshow > div:gt(0)").hide();
    var current_slide = 0;
    var nbslides = $('#slideshow .slideshow-element').length;
    $('#slideshow .slideshow-element:eq(0)').show();
    var last_manual_change = Date.now();
    window.setInterval(function(){
        if(Date.now() - last_manual_change >= 6000)
            $('.als-next').click();
    }, 6000);
    $('.als-next').click(function(){
        var next_slide = ((current_slide+1))%nbslides;
        var selector = $('#slideshow .slideshow-element');
        selector.eq(current_slide).hide(0).closest('#slideshow')
                .find('.slideshow-element').eq(next_slide).effect('slide', { direction: 'right', mode: 'show' }, 300);
        current_slide = next_slide;
        last_manual_change = Date.now();
    });
    $('.als-prev').click(function(){
        console.log(current_slide);
        var next_slide = current_slide - 1;
        var selector = $('#slideshow .slideshow-element');
        if(next_slide < 0)
            next_slide = selector.length - 1;
        selector.eq(current_slide).hide(0).closest('#slideshow')
                .find('.slideshow-element').eq(next_slide).effect('slide', { direction: 'left', mode: 'show' }, 300);
        current_slide = next_slide;
        last_manual_change = Date.now();
    });


});


