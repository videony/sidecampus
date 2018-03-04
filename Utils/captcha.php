<?php

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

require_once WEB_ROOT.'./AutoLoader.php';
$session = new SessionController();	

header ("Content-type: image/gif");
$_img = imagecreatefromgif(WEB_ROOT.'media/pics/fond_captcha.gif');
// Couleur de fond :
$arriere_plan = imagecolorallocate($_img, 0, 0, 0); 
// Autres couleurs :
$avant_plan = imagecolorallocate($_img, 255, 255, 255); 

if(!isset($_SESSION[SessionController::RENEW_CAPTCHA])
        || $_SESSION[SessionController::RENEW_CAPTCHA] === TRUE
        || !isset($_SESSION[SessionController::CAPTCHA]))
{
    $nombre = GenerateUtils::randomString(6);
    $_SESSION[SessionController::CAPTCHA] = $nombre;
}

imagestring($_img, 5, 18, 8, $_SESSION[SessionController::CAPTCHA], $avant_plan);

imagegif($_img);
?>