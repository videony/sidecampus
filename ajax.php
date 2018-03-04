<?php

	if(!defined('WEB_ROOT'))
		define('WEB_ROOT', './');
	
	if(!isset($_POST['handler']))
		exit;
		
	require_once './AutoLoader.php';
	$session = new SessionController();
        
        SecurityUtils::secureWhatIsSent();

        if(SecurityUtils::isBlocked())
        {
            echo file_get_contents('View/blocked.html');
            die();
        }
        
        $_POST['ajax'] = TRUE;
	
	$class = $_POST['handler'];
	
	$object = new $class();
	if($object->canAccess())
            echo $object->handlePostRequest();
        else
            header('HTTP/1.0 403 Forbidden');
        
        
DB::disconnect();
exit;	
?>