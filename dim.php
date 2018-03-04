<?php
	if(!defined('WEB_ROOT'))
		define('WEB_ROOT', './');

$type = $_REQUEST['type'];

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
	$session = new SessionController();
        
//SecurityUtils::secureWhatIsSent();

if(SecurityUtils::isBlocked())
{
    echo file_get_contents('media/pics/interdit.png');
    die();
}
        
        
/* Display image */
switch($type)
{
    case 'person_profile_pic':
        header("Content-type: image/jpeg");
        $controller = new CompteController();
        echo $controller->getProfilePicData($_REQUEST['person']);
        break;
    case 'file_img_type':
        $controller = new FilesController();
        if(!$controller->canAccess())
            return '';
        $info = $controller->getPictureDisplay($_REQUEST['file']);
        if($info != NULL)
        {
            header("Content-type: image/".$info['type']);
            echo $info['data'];
        }
        break;
    case 'personal_file_img_type':
        $controller = new PersonalFilesController();
        if(!$controller->canSeeFile($_REQUEST['file']))
            return '';
        $info = $controller->getPictureDisplay($_REQUEST['file']);
        if($info != NULL)
        {
            header("Content-type: image/".$info['type']);
            echo $info['data'];
        }
        break;
    case 'publicite':
        $filename = $_REQUEST['filename'];
        $infos = explode('.', $filename);
        header("Content-type: image/".$infos[count($infos)-1]);
        echo file_get_contents("media/pubs/".$filename);
        break;
}
DB::disconnect();
exit;	


