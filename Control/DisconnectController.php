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
class DisconnectController implements BodyController {
    //put your code here
    private $css = array(
        'css/form.css'
    );
    private $js = array(
        'js/Connect.js'
    );
    private $sess;
    
    public function __construct() 
    {
	$this->sess = new SessionController(SessionController::NOACTION);
    }
    public function getContent($login = '')
    {
        if(isset($_SESSION[SessionController::USERNAME]))
        {
            $template = $this->getTemplate();
            $markers['###WEB_ROOT###'] = WEB_ROOT;
            $this->sess->destroyLoginSession();
            $markers['###IF_LOGIN_FORM###'] = TRUE;
            $markers['###IF_SUCCESS_LOGIN###'] = FALSE;
            $markers['###IF_ERROR_LOGIN###'] = FALSE;
            $markers['###IF_LOGOUT###'] = TRUE;
            $markers['###LOGIN###'] = '';
            if(!isset($_REQUEST['redirect']))
                $_REQUEST['redirect'] = 'Accueil';
            header('Location: request.php?action='.$_REQUEST['redirect']);
            //return GenerateUtils::replaceStrings($template, $markers);
        }
        else
        {
            if(!isset($_REQUEST['redirect']))
                $_REQUEST['redirect'] = 'Accueil';
            header('Location: request.php?action='.$_REQUEST['redirect']);
        }
    }
    public function getTitle() 
    {
        return 'Logout';
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
        return file_get_contents(WEB_ROOT.'View/Connect.html');
    }
	
    public function canAccess()
    {
        return TRUE;
    }
    public function handlePostRequest()
    {
            return '';
    }
}

?>
