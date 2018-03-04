<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require WEB_ROOT.'Utils/Mail.php';

function autoloader($classname)
{
    if(stristr($classname, 'Control'))
    {
        if(file_exists(WEB_ROOT.'Control/'.$classname.'.php'))
            require_once WEB_ROOT.'Control/'.$classname.'.php';
        else
        {
            unset($_REQUEST['action']);
        }
    }
    elseif(stristr($classname, 'Model'))
    {
        if(file_exists(WEB_ROOT.'Model/'.$classname.'.php'))
            require_once WEB_ROOT.'Model/'.$classname.'.php';
    }
    elseif(stristr($classname, 'DB'))
    {
        if(file_exists(WEB_ROOT.'Model/DB.php'))
            require_once WEB_ROOT.'Model/DB.php';
    }
    elseif(stristr($classname, 'Utils'))
    {
        if(file_exists(WEB_ROOT.'Utils/'.$classname.'.php'))
            require_once WEB_ROOT.'Utils/'.$classname.'.php';
    }
}

//error_reporting(E_ALL);
ini_set('display_errors', 'On');

ini_set('mysqlnd_qc.enable_qc', '1');
spl_autoload_register('autoloader');

error_reporting(0); 

function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    
    echo file_get_contents("View/Error.html");
    // timestamp for the error entry.
    $dt = date('d-m-Y H:i:s (T)');

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE.
    $errortype = array (
                E_ERROR => 'Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parsing Error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_STRICT => 'Runtime Notice'
                );
    // set of errors for which a var trace will be saved.
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

    $err = "<errorentry>\n";
    $err .= "\t<datetime>" .$dt. "</datetime>\n";
    $err .= "\t<errornum>" .$errno. "</errornum>\n";
    $err .= "\t<errortype>" .$errortype[$errno]. "</errortype>\n";
    $err .= "\t<errormsg>" .$errmsg. "</errormsg>\n";
    $err .= "\t<scriptname>" .$filename. "</scriptname>\n";
    $err .= "\t<scriptlinenum>" .$linenum. "</scriptlinenum>\n";
    $err .= "\t<session>" .  print_r($_SESSION, TRUE). "</session>\n";
    $err .= "\t<request>" .  print_r($_REQUEST, TRUE). "</request>\n";
    $err .= "\t<client>" .  print_r($_SERVER, TRUE). "</client>\n";

    if (in_array($errno, $user_errors)) {
        $err .= "\t<vartrace>" .wddx_serialize_value($vars, 'Variables'). "</vartrace>\n";
    }
    $err .= "</errorentry>\n\n";
    $err .= "<stacktrace>";
    $err .= print_r(debug_backtrace(), true);
    $err .= "</stacktrace>";

    // save to the error log file, and e-mail me 
    $id = uniqid();
    error_log($err, 3, './errors/APP_'.$id.'.log');
    
    // Notification pour mise en maintenance potentielle
    SecurityUtils::notifyFailure("APP");
    
    // Logout pour éviter que l'erreur se reproduise
    //if($errno != E_WARNING && $errno != E_NOTICE)
       //session_destroy();
    exit;
}
$old_error_handler = set_error_handler('userErrorHandler');

?>
