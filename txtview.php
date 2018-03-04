<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
$session = new SessionController();

//SecurityUtils::secureWhatIsSent();

if(SecurityUtils::isBlocked())
{
    echo file_get_contents('View/blocked.html');
    die();
}

$action = $_REQUEST['action'];
if(!isset($_REQUEST['key']) || !isset($_SESSION[SessionController::ID_PERSONNE]))
    return '';
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');
switch($action)
{
    case 'see_personal_file':        
        $model = new PersonalFileModel($_REQUEST['key']);
        $data = $model->get();
        $location = getcwd().PersonalFilesController::$filesFolder.$data['tx_encoded_name']; 
        if($data['id_adder'] == $_SESSION[SessionController::ID_PERSONNE])
        {
            $fdata = file_get_contents($location);
            if(in_array(strtolower($data['tx_extension']), array('html', 'xhtml', 'htm')))
                $fdata = htmlentities($fdata);   
            echo '<pre style="background:white">'.$fdata.'</pre>';
        }
        break;
    case 'see_file': 
        $model = new FileModel($_REQUEST['key']);
        $data = $model->get();
        $location = getcwd().FilesController::$filesFolder.$data['tx_encoded_name']; 
        $controller = new FilesController();
        if($controller->canSeeFile($_REQUEST['key']))
        {
            $fdata = file_get_contents($location);
            if(in_array(strtolower($data['tx_extension']), array('html', 'xhtml', 'htm')))
                $fdata = htmlentities($fdata);   
            echo '<pre style="background:white">'.$fdata.'</pre>';
        }
        break;
}
exit;	

?>
