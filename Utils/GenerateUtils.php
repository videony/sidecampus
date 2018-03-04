<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenerateUtils
 *
 * @author videony
 */
class GenerateUtils {
    //put your code here
    
    /**
     * Remplace dans $template les $strings. 
     * @param String $template Template HTML typiquement, tout texte possible. 
     * @param type $strings Ensemble de correspondances marker=>valeur.
     * Il y a différentes possibilités de marqueurs:
     * IF_MARKER    :   mis à TRUE ou FALSE. Si FALSE, est retiré du template.
     * SUB_MARKER   :   réprésente une sous-section du template. Peut être remplacé par n'importe quoi.
     * RS_MARKER    :   remplace un resultset. Voir la fonction generateRS pour plus de détails.
     * MARKER       :   chaine de caractères remplacée par sa valeur
     * @param type $removeComments Elimine tous les commentaires si mis à TRUE
     * @return String Le template généré.
     */
    static function replaceStrings($template, $strings, $removeComments = true)
    {
        if(!is_array($strings) || empty($strings))
            return $template;
        $html = $template;
        foreach($strings as $marker=>$string)
        {
            if(!strstr($marker, '###'))
                $marker = '###'.strtoupper($marker).'###';
            if(substr($marker, 3,2) == 'IF' && $string === FALSE)
            {
                $html = self::emptyPart($html, $marker);
            }
            /*elseif(substr($marker, 3,2) == 'IF' && $string === TRUE)
            {
                // Search for 'else'
                $ifpart = substr($marker, 0, 6); // ###IF_
                $rest   = substr($marker, 6);    // Rest of statement
                $html = self::emptyPart($html, $ifpart.'NOT_'.$rest);
            }*/
            elseif(substr($marker, 3,2) == 'RS')
            {
                if(is_array($string) || is_empty($string))
                    continue;
                $html = self::generateRS($html, $string);
            }
            elseif(substr($marker, 3,3) == 'SUB')
            {
                $html = self::replaceSubPart($html, $marker, $string);
            }
            else
            {
                $html = str_replace($marker, $string, $html);
            }
        }
        if($removeComments)
        {
            $html = preg_replace('/<!-- -->/', '', $html);
        }
        return $html;
    }
    /**
     * A partir d'un resultset, va générer autant de fois la sous-section du template qu'il y a d'éléments 
     * dans resultset, en remplaçant à chaque fois les marqueurs par les valeurs.
     * @param type $template Template contenant la sous-section
     * @param type $resultset Resultset, liste de correspondances marqueur=>valeur.
     * @param type $empty_RS Texte à afficher lorsque le resultset est vide
     * @param type $subitem_marker Marqueur de sous-section.
     * @return String Template généré.
     */
    static function generateRS($template, $resultset, $empty_RS = '', $subitem_marker = '###SUB_ITEM###')
    {        
        if(!is_array($resultset))
            return $template;
        $item = self::subPart($template, $subitem_marker);
        $all_items = '';
        $html = $template;
        if(!empty($resultset))
        {
            foreach($resultset as $row)
            {
                if(is_array($row))
                    $all_items .= self::replaceStrings($item, $row);
                else
                    $all_items .= str_replace('###VALUE###', $row, $item);
            }
            $html = self::replaceSubPart($html, $subitem_marker, $all_items);
        }
        else
        {
            $html = self::replaceSubPart($html, $subitem_marker, $empty_RS);
        }
        return $html;
    }
    /**
     * Vide une sous-section du template
     * @param type $template Template HTML
     * @param type $marker Marqueur de sous-section. 
     * @return String Le template privé de la sous-section
     */
    static function emptyPart($template, $marker)
    {
        return self::replaceSubPart($template, $marker, '');
    }
    /**
     * Remplace une sous-partie par quelque chose d''autre
     * @param String $template Le template HTML
     * @param String $marker Le marqueur de sous-section
     * @param String $value La valeur à mettre à la place du marqueur
     * @return String Le template avec la sous-section remplacée.
     */
    static function replaceSubPart($template, $marker, $value)
    {
        if(strstr($template, $marker) === FALSE)
                return $template;
        $html = $template;
        // OFFSET DU DEBUT DU COMMENTAIRE
        $begin = strpos($html, $marker)-4;
        if($begin === FALSE)
            return $template;
        do
        {
            $str = substr($html, $begin, 4);
            $begin--; 
        }while($str != '<!--');
        $begin++;
        
        // OFFSET DE LA FIN DU COMMENTAIRE
        $end = strrpos($html, $marker)+strlen($marker);
        if($end === FALSE)
            return $template;
        do
        {
            $str = substr($html, $end, 3);
            $end++;
        }while($str != '-->');
        $end--;
        
        return str_replace(substr($html, $begin, $end+3-$begin), $value, $html);
    }
    /**
     * Renvoie une sous-partie du template
     * @param String $template Le template HTML
     * @param String $marker Le marqueur de sous-partie. Ce marqueur doit avoir la forme '###SUB_MARKER###'
     * @return String La sous-section du template
     */
    static function subPart($template, $marker)
    {
        if(substr($marker, 3, 3) != 'SUB')
            return $template;
        
        $html = $template;
        
        // OFFSET OF AFTER THE OPENING COMMENT
            $begin = strpos($template, $marker)+strlen($marker);
        if($begin === FALSE)
            return $template;
        do
        {
            $str = substr($html, $begin, 3);
            $begin++;
        }while($str != '-->');
        $begin--;
        
        // OFFSET OF AFTER THE CLOSING COMMENT
        $end = strrpos($template, $marker)-4;
        if($end === FALSE)
            return $template;
        do
        {
            $str = substr($html, $end, 4);
            $end--; 
        }while($str != '<!--');
        $end++;
        
        return substr($template, $begin+3, $end-$begin-3);
    }
    /**
     * Renvoie un template du dossier View. 
     * @param String $view Nom du fichier HTML (sans le .html) 
     * @return type
     */
    static function getTemplate($view)
    {
        return file_get_contents(WEB_ROOT.'View/'.$view.'.html');
    }
    /**
     * Sert à retirer de l'html toutes les commandes de norights
     * @param String $template Template HTML
     * @param array $norights Array de droits que l'utilisateur n'a pas et à retirer du template
     * @return String Le template nettoyé
     */
    static function cleanForRights($template, $norights)
    {
        foreach($norights as $noright)
        {
            if(is_array($noright))
                $template = self::emptyPart($template, '###IF_'.strtoupper($noright['permission_name']).'###');
            else
                $template = self::emptyPart($template, '###IF_'.strtoupper($noright).'###');
        }
        return $template;
    }
    
    
	/**
	 *
	 * This function parses BBcode tag to HTML code (XHTML transitional 1.0)
	 *
	 * It parses (only if it is in valid format e.g. an email must to be
	 * as example@example.ext or similar) the text with BBcode and
	 * translates in the relative html code.
	 *
	 * @param string $text
	 * @param boolean $advanced his var describes if the parser run in advanced mode (only *simple* bbcode is parsed).
	 * @return string
	 */
	public static function tohtml($text,$advanced=FALSE){

		//special chars

		/**
		 * This array contains the main static bbcode
		 * @var array $basic_bbcode
		 */
		$basic_bbcode = array(
                    '[b]', '[/b]',
                    '[i]', '[/i]',
                    '[u]', '[/u]',
                    '[s]','[/s]',
                    '[ul]','[/ul]',
                    '[li]', '[/li]',
                    '[ol]', '[/ol]',
                    '[center]', '[/center]',
                    '[left]', '[/left]',
                    '[right]', '[/right]',
                    '[sup]', '[/sup]',
                    '[sub]', '[/sub]',
                    '[justify]', '[/justify]',
                    '[code]', '[/code]',
                    '[quote]', '[/quote]',
                    '[hr]',
                    
		);

		/**
		 * This array contains the main static bbcode's html
		 * @var array $basic_html
		 */
		$basic_html = array(
                            '<b>', '</b>',
                            '<i>', '</i>',
                            '<u>', '</u>',
                            '<s>', '</s>',
                            '<ul>','</ul>',
                            '<li>','</li>',
                            '<ol>','</ol>',
                            '<div style="text-align: center;">', '</div>',
                            '<div style="text-align: left;">',   '</div>',
                            '<div style="text-align: right;">',  '</div>',
                            '<sup>','</sup>',
                            '<sub>','</sub>',
                            '<div style="text-align: justify">','</div>',
                            '<div class="code">','</div>',
                            '<div class="quote">','</div>',
                            '<hr />'
		);

		/**
		 *
		 * Parses basic bbcode, used str_replace since seems to be the fastest
		 */
		$text = str_replace($basic_bbcode, $basic_html, $text);

		//advanced BBCODE
		if ($advanced)
		{
			/**
			 * This array contains the advanced static bbcode
			 * @var array $advanced_bbcode
			 */
			$advanced_bbcode = array(
                            '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/color\]#Usi',
                            '#\[size=([0-9][0-9]?)](.+)\[/size\]#Usi',
                            '#\[quote](\r\n)?(.+?)\[/quote]#si',
                            '#\[quote=(.*?)](\r\n)?(.+?)\[/quote]#si',
                            '#\[url](.+)\[/url]#Usi',
                            '#\[url=(.+)](.+)\[/url\]#Usi',
                            '#\[email]([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})\[/email]#Usi',
                            '#\[email=([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})](.+)\[/email]#Usi',
                            '#\[img](.+)\[/img]#Usi',
                            '#\[img=([0-9]+)x([0-9]+)](.+)\[/img]#Usi',
                            '#\[code](\r\n)?(.+?)(\r\n)?\[/code]#si',
                            '#\[youtube]http://[a-z]{0,3}.youtube.com/watch\?v=([0-9a-zA-Z]{1,11})\[/youtube]#Usi',
                            '#\[youtube]([0-9a-zA-Z]{1,11})\[/youtube]#Usi'
			);

			/**
			 * This array contains the advanced static bbcode's html
			 * @var array $advanced_html
			 */
			$advanced_html = array(
                            '<span style="color: $1">$2</span>',
                            '<span style="font-size: $1px">$2</span>',
                            "<div class=\"quote\"><span class=\"quoteby\">Disse:</span>\r\n$2</div>",
                            "<div class=\"quote\"><span class=\"quoteby\">Disse <b>$1</b>:</span>\r\n$3</div>",
                            '<a rel="nofollow" target="_blank" href="$1">$1</a>',
                            '<a rel="nofollow" target="_blank" href="$1">$2</a>',
                            '<a href="mailto: $1">$1</a>',
                            '<a href="mailto: $1">$2</a>',
                            '<img src="$1" alt="$1" style="border:1px solid black" class="expandable"/>',
                            '<img src="$3" alt="$3" height="$2" width="$1" style="border:1px solid black" class="expandable"/>',
                            '<div class="code">$2</div>',
                            '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>',
                            '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>'
			);

			$text = preg_replace($advanced_bbcode, $advanced_html,$text);
		}

                return $text;
	}

	/**
	 *
	 * removes bbcode from text
	 * @param string $text
	 * @return string text cleaned
	 */
	public static function remove($text)
	{
            return strip_tags(str_replace(array('[',']'), array('<','>'), $text));
	}
        /**
         * Change text to emoticon
         * @param type $text
         */
        public static function replaceEmoticons($text)
        {
            /*$array = [
                ' :) '      =>  ':smile:',
                ' :-)'      =>  ':smile:',
                ' =) '      =>  ':smile:',
                ' 0:) '     =>  ':angel:',
                ' 0:-) '    =>  ':angel:',
                ' :@ '      =>  ':angry:',
                ' >:( '     =>  ':angry:',
                ' oO '      =>  ':blink:',
                ' Oo '      =>  ':blink:',
                ' :$ '      =>  ':blush:',
                ' 8) '      =>  ':cool:',
                ' 3:) '     =>  ':devil:',
                ' 3:-) '    =>  ':devil:',
                ' :D '      =>  ':grin:',
                ' :d '      =>  ':grin:',
                ' =D '      =>  ':grin:',
                ' =d '      =>  ':grin:',
                ' <3 '      =>  
                ' :s '      =>  ':unsure:',
                ' :S '      =>  ':unsure:'
            ];*/
            return preg_replace(":[a-z]+:", '<img src="media/emoticons/$1.png" alt="$1"/>',$text);
        }

	/**
	 *
	 * Inserts HTML line breaks before all newlines in a string
	 * @param string $var
	 */
	public static function nl2br($var)
	{
            return str_replace(array('\\r\\n','\r\\n','r\\n','\r\n', '\n', '\r'), '<br />', nl2br($var));
	}
    
    static function randomString($nbchars = 10) 
    {
        $signs = array(
	0 => 'G',
	1 => '1',
	2 => '2',
	3 => '3',
	4 => '4',
	5 => '5',
	6 => '6',
	7 => '7',
	8 => '8',
	9 => '9',
	10 => 'A',
	11 => 'B',
	12 => 'C',
	13 => 'D',
	14 => 'E',
	15 => 'F',
	16 => 'G',
	17 => 'H',
	18 => 'I',
	19 => 'J',
	20 => 'K',
	21 => 'L',
	22 => 'M',
	23 => 'N',
	24 => '4',
	25 => 'P',
	26 => 'Q',
	27 => 'R',
	28 => 'S',
	29 => 'T',
	30 => 'U',
	31 => 'V',
	32 => 'W',
	33 => 'X',
	34 => 'Y',
	35 => 'Z');

        $i = 0;
        while($i < $nbchars) {
                $chiffre = mt_rand(0, 35); // On génère le nombre aléatoire
                $chiffres[$i] = $signs[$chiffre];
                $i++;
        }
        $nombre = null;
        
        foreach ($chiffres as $caractere) {
                $nombre .= $caractere;
        }
        return $nombre;
    }
}

?>
