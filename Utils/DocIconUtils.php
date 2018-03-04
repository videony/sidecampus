<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenerateUtils
 *
 * @author videony
 */
class DocIconUtils {
    //put your code here
    static function getIconLink($filename)
	{
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		if(file_exists(WEB_ROOT.'Images/doc_icons/'.$extension.'.png'))
			return WEB_ROOT.'Images/doc_icons/'.$extension.'.png';
		else
			return WEB_ROOT.'Images/doc_icons/st_doc.png';
	}
}

?>
