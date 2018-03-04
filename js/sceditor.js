$(document).ready(function(){
  // $.getScript('js/sceditor/jquery.sceditor.bbcode.min.js');
   // Formated textareas
   
   // prevent date/current time/left to right/right to left/view source/tableau/font
    $(".formated_textarea").sceditor({
            plugins: 'bbcode',
            emoticonsCompat: true,
            toolbarExclude: 'removeformat,date,time,ltr,rtl,source,table,font,size,subscript,superscript,cut,copy,paste,pastetext,youtube',
             // Emoticons to be included in the dropdown
            dropdown: {
                ":alien:": "emoticons/alien.png",
                ":angel:": "emoticons/angel.png",
                ":angel:": "emoticons/angel.png",
                ":angry:": "emoticons/angry.png",
                ":blink:": "emoticons/blink.png",
                ":blush:": "emoticons/blush.png",
                ":cheerful:": "emoticons/cheerful.png",
                ":cool:": "emoticons/cool.png",
                ":cwy:": "emoticons/cwy.png",
                ":devil:": "emoticons/devil.png",
                ":dizzy:": "emoticons/dizzy.png",
                ":ermm:": "emoticons/ermm.png",
                ":getlost:": "emoticons/getlost.png",
                ":grin:": "emoticons/grin.png",
                ":happy:": "emoticons/happy.png",
                ":heart:": "emoticons/heart.png",
                ":kissing:": "emoticons/kissing.png",
                ":laughing:": "emoticons/laughing.png",
                ":ninja:": "emoticons/ninja.png",
                ":pinch:": "emoticons/pinch.png",
                ":pouty:": "emoticons/pouty.png",
                ":sad:": "emoticons/sad.png",
                ":shocked:": "emoticons/shocked.png",
                ":sick:": "emoticons/sick.png",
                ":sideways:": "emoticons/sideways.png",
                ":silly:": "emoticons/silly.png",
                ":sleeping:": "emoticons/sleeping.png",
                ":smile:": "emoticons/smile.png",
                ":tongue:": "emoticons/tongue.png",
                ":unsure:": "emoticons/unsure.png",
                ":woot:": "emoticons/woot.png",
                ":wassat:": "emoticons/wassat.png",
                ":whistling:": "emoticons/whistling.png",
                ":wink:": "emoticons/wink.png",
                ":wub:": "emoticons/wub.png",
            },
            style: "css/sceditor/jquery.sceditor.default.min.css",
            locale: "fr",
            emoticonsRoot: "media/",
            parserOptions: {
                    breakBeforeBlock: true
                    // ect.
            }
    }); 
});