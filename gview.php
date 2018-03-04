<?php

/*session_start();
session_destroy();
exit;*/

if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
$session = new SessionController();

SecurityUtils::secureWhatIsSent();

if(isset($_REQUEST['key']))
{
    // 1. Recherche de la ligne correspondant à la clé
    $authmodel = new FileViewAuthorizationModel();
    $results = $authmodel->getFileViewAuthorizationByFilter(array(
       'tx_key' =>  $_REQUEST['key'] 
    ));
    if(empty($results))
    {
        echo 'EMPTY';
        DB::disconnect();
        exit;
    }
    $authorization = $results[0];
    // 2. Check que la date n'est pas périmée
    if(time() > strtotime($authorization['dt_perished']))
    {
        echo '';
        $authmodel = new FileViewAuthorizationModel($authorization['id_authorization']);
        //$authmodel->remove();
        DB::disconnect();
        exit;
    }
    
    // 3. Recherche des informations sur le fichier
    if($authorization['tx_type'] == FileViewAuthorizationModel::PERSONAL_FILE)
        $model = new PersonalFileModel($authorization['id_file']);
    else
        $model = new FileModel($authorization['id_file']);
    $data = $model->get();
    $filename = $data['tx_name'];
    $filename = str_replace(' ', '_', $filename);
    if(strstr($filename, $data['tx_extension']) === FALSE)
            $filename = $filename.'.'.$data['tx_extension'];
    $location = getcwd().FilesController::$filesFolder.$data['tx_encoded_name']; 

    // 4. Suppression de la ligne dans authorize
    $authmodel = new FileViewAuthorizationModel($authorization['id_authorization']);
    $authmodel->remove();
    
    // 5. Lecture du fichier
    switch(strtolower($data['tx_extension']))
    {
        case 'jpg':
        case 'jpe':
        case 'jpeg':
        case 'jps':
        case 'exr':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'bmp':
        case 'cur':
        case 'ico':
        case 'pbm':
        case 'pct':
            ob_start();
            header('Content-type: image/'.strtolower($data['tx_extension']));
            if (ob_get_length() > 0) { ob_end_flush(); }
            readfile($location);
            break;
        default:
            ob_start();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Set-Cookie: fileDownload=true; path=/');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($location));
            if (ob_get_length() > 0) { ob_end_flush(); }
            readfile($location);
            break;
    }
    
    
   
}
exit;	

?>