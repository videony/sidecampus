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
class ConnectController implements BodyController {
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
        $template = $this->getTemplate();
        $markers['###WEB_ROOT###'] = WEB_ROOT;
        if(!isset($_SESSION[SessionController::USERNAME]))
        {
            $markers['###IF_LOGOUT###'] = FALSE;
            if(isset($_REQUEST['login']) || isset($_COOKIE['sidecampus_login']))
            {
                try
                {
                        $this->sess->createLoginSession();
                        $controller = null;
                        if($_SESSION[SessionController::CATEGORIE] == PageController::MEMBER_NO_DEFAULT_PLATFORM)
                            //header('Location: request.php?action=Tuto');  
                            $controller = new AccueilController(); 
                        else
                            //header('Location: request.php?action=ConnectedAccueil');  
                            $controller = new ConnectedAccueilController();
                        $content = $controller->getContent();
                        $addcss = $controller->getCSS();
                        $this->css = array_merge($this->css, $addcss);
                        $addjs = $controller->getJS();
                        $this->js = array_merge($this->js, $addjs);
                        return $content;
                }
                catch(Exception $ex)
                {
                        $markers['###IF_LOGIN_FORM###'] = TRUE;
                        $markers['###IF_SUCCESS_LOGIN###'] = FALSE;
                        $markers['###IF_ERROR_LOGIN###'] = TRUE;
                        $markers['###LOGIN###'] = $this->sess->getPreviousLogin();
                        $markers['###ERROR_MESSAGE###'] = $ex->getMessage();
                }
            }
            else
            {
                $markers['###IF_LOGIN_FORM###'] = TRUE;
                $markers['###IF_SUCCESS_LOGIN###'] = FALSE;
                $markers['###IF_ERROR_LOGIN###'] = FALSE;
                $markers['###IF_LOGOUT###'] = FALSE;
                $markers['###LOGIN###'] = $login;
            }
        }
        else
        {
                header('Location: request.php?action=ConnectedAccueil');  
        }
        return GenerateUtils::replaceStrings($template, $markers);
    }
    public function getTitle() 
    {
        return 'Login';
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
	public function handlePostRequest() {
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'get_added_code':
                $model = new PersonneModel();
                $infos = $model->getByLogin($_POST['login']);

                if($infos == NULL)
                    return 'KO';
                else
                {
                    return $infos['tx_login_code'];
                }
                break;
            case 'get_salt':
                $_SESSION[SessionController::LOGIN_SALT] = rand(0,60000);
                return $_SESSION[SessionController::LOGIN_SALT];
                break;
        }
    }
}

?>
