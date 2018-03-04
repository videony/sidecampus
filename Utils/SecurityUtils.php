<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SecurityUtils
 *
 * @author videony
 */
class SecurityUtils {
    public static $allowedTags = "<b><i><u><div><ol><ul><li><font><p><pre><h1><h2><h3><h4><h5><h6><img><a>";
    public static $allowedFields = array(
        'ForumTopicCreate'  => array('post'),
        'ForumTopicEdit'    => array('post'),
        'ForumPostCreate'   => array('post'),
        'ForumPostEdit'     => array('post'),
        'Contact'           => array('message')
    );

    public static function secureWhatIsSent()
    {
        foreach($_REQUEST as $key=>$req)
        {
            if(is_string($req))
                $_REQUEST[$key] = htmlspecialchars ($req);
        }
        foreach($_POST as $key=>$req)
        {
            if(is_string($req))
                $_POST[$key] = htmlspecialchars ($req);
        }
        foreach($_GET as $key=>$req)
        {
            if(is_string($req))
                $_GET[$key] = htmlspecialchars ($req);
        }
    }
    public static function isBlocked()
    {
        if(!ConfigUtils::isIpBlockerEnabled()) {
            return false;
        }
        // Blocked IP?
        $ipblock = new IpBlockerModel();
        $results = $ipblock->getIpBlockerByFilter(array(
           'ip_address' =>  $_SERVER['REMOTE_ADDR'],
            'cd_active' =>  1
        ));
        return !empty($results);
    }
    public static function notify($message)
    {
        $template = file_get_contents("View/mysql_failure.html");
        $template = GenerateUtils::replaceStrings($template, array(
            'message'       =>  $message,
            'trace'         =>  $exception->getTraceAsString(),
            'request'       => print_r($_REQUEST, TRUE),
            'session'       => print_r($_SESSION, TRUE)
        ));
        $from = ConfigUtils::get('website.title').' security <'.ConfigUtils::get('noreply.email').'>';
        $to = 'Web admin <'.ConfigUtils::get('audit.email').'>';
        $mail = new Mail($from, $to, ConfigUtils::get('website.title')." notify attack");
        $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
        $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
        $mail->addMessagePart($template);              
        $mail->send();
    }
    public static function notifyFailure($type)
    {
        // Paramètres
        $max_db_errors = 15;
        $max_app_errors = 25;
        $app_time_to_reset = 30;
        $db_time_to_reset = 20;
        // Paramétrage en fonction du type
        if($type == 'APP')
        {
            $max_errors = $max_app_errors;
            $time_to_reset = $app_time_to_reset;
        }
        else
        {
           $max_errors = $max_db_errors;
           $time_to_reset = $db_time_to_reset;
        }
        // Va chercher tous les fichiers d'erreurs qui se sont passées dans les dernières secondes
        $nb_errors = 0;
        if($handle = opendir('../errors'))
        {
            while(FALSE !== ($entry = readdir($handle)))
            {
                $file = '../errors/'.$entry;
                if(is_file($file))
                {
                    if(substr($entry, 0, strlen($type)) == $type)
                    {
                        if(time() - filemtime($file) < $time_to_reset)
                            $nb_errors++;
                    }
                }
            }
        }
        if($nb_errors >= $max_errors)
        {
            // On met en maintenance
            file_put_contents("../maintenance.txt", $nb_errors." erreurs en ".$time_to_reset." secondes");
            
            // Envoi d'email pour prévenir
            $from = ConfigUtils::get('website.title').' security <'.ConfigUtils::get('noreply.email').'>';
            $to = 'Web admin <'.ConfigUtils::get('audit.email').'>';
            $mail = new Mail($from, $to, ConfigUtils::get('website.title')." mis en maintenance");
            $mail->setHeader(Mail::HEADER_CONTENT_TYPE, "text/html");
            $mail->setHeader(Mail::HEADER_CONTENT_TRANSFERT_ENCODING, "utf-8");
            $mail->addMessagePart(ConfigUtils::get('website.title').' a été mis en maintenance pour cause de trop d\'erreurs de type '.$type);
            if (!$mail->send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
            } 
        }
    }
	public static function isSecure() {
	  return
		(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| $_SERVER['SERVER_PORT'] == 443;
	}
}
