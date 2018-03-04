<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CVController
 *
 * @author videony
 */
class PreferencesController implements BodyController {
    //put your code here
    private $css = array(
    );
    private $js = array(
    );
    
    public function __construct() 
    {
	
    }
    public function getContent($login = '')
    {
        return '';
    }
    public function getTitle() 
    {
        return '';
    }
    public function getCSS()
    {
        return $this->css;
    }
    public function getJS()
    {
        return $this->js;
    }
    private function getTemplate()
    {
        return '';
    }
	
    public function canAccess()
    {
        return TRUE;
    }
	public function handlePostRequest() {
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'side_menu_visible':
                $_SESSION[SessionController::HIDE_SIDE_MENU] = FALSE;
                break;
            case 'side_menu_invisible':
                $_SESSION[SessionController::HIDE_SIDE_MENU] = TRUE;
                break;
        }
    }
}

?>
