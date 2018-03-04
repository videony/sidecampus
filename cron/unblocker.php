<?php
/**
 * Débloque toutes les adresses IPs bloqués depuis plus d'une demi-heure
 * 
 */
try
{
    //echo getcwd();
    if(!defined('WEB_ROOT'))
        define('WEB_ROOT', '../public_html/');
    require_once WEB_ROOT.'AutoLoader.php';
    $model = new IpBlockerModel();
    $ips = $model->getIpBlockerByFilter(array(
       'cd_active'  =>  1 
    ));
    foreach($ips as $blocked)
    {
            $date = strtotime($blocked['dt_blocked']);
            if($date < strtotime('-30 minutes'))
            {
                    $model = new IpBlockerModel($blocked['id_block']);
                    $model->set(array(
                            'cd_active' =>  0
                    ));
            }
    }
}
catch(Exception $ex)
{
    
}


?>

