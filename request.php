<?php

/*session_start();
session_destroy();
exit;*/

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
$session = new SessionController();

if(isset($_SESSION[SessionController::ID_MEMBRE]))
    $_SESSION[SessionController::NOTIFICATION_MEMBER_ID] = $_SESSION[SessionController::ID_MEMBRE];
if(isset($_SESSION[SessionController::PLATFORM_ID]))
    $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID] = $_SESSION[SessionController::PLATFORM_ID];

SecurityUtils::secureWhatIsSent();

if(SecurityUtils::isBlocked())
{
    echo file_get_contents('View/blocked.html');
}
else
{
    $accueil = new PageController();
    echo $accueil->display();
}
DB::disconnect();
exit;	

?>