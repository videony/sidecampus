<?php
set_time_limit(300);
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', './');

require_once './AutoLoader.php';
$session = new SessionController();

$max_download_size = 100000000;

//SecurityUtils::secureWhatIsSent();

if(SecurityUtils::isBlocked())
{
    echo file_get_contents('View/blocked.html');
    die();
}

/**
 * 
 * @param ZipArchive $zip
 * @param type $dirid Identifiant du répertoire à écrire. Si NULL, tout le contenu est écrit dans le répertoire courant
 * @param type $sub Array de tous les éléments présents
 * @param type $curDir Répertoire courant. Défaut = la racine
 */
function writeDir($dirid, $sub, $filesize, $curDir = '')
{
    /*$zip->close();
    $zip->open($filelocation, ZipArchive::CREATE);*/
    if($dirid != NULL)
    {
        $model = new PersonalFolderModel($dirid);
        $data = $model->get();
        //$zip->addEmptyDir($curDir.$data['tx_name']);
        if(!is_dir($curDir))
            mkdir($curDir);
        $activeDir = $curDir.$data['tx_name'].'/';
    }
    else
    {
        $activeDir = $curDir;
    }
    if(!is_dir($activeDir))
        mkdir($activeDir);
    foreach($sub as $id=>$elem)
    {
        $infos = explode('_', $id);
        if($infos[0] == 'folder')
        {
            $return = writeDir($infos[1], $elem, $filesize, $activeDir);
            if($return == NULL)
                return NULL;
        }
        else
        {
            $return = writeFile($infos[1], $filesize, $activeDir);
            if($return == NULL)
                return NULL;
        }
    }
    return TRUE;
}
/**
 * 
 * @param ZipArchive $zip archive
 * @param type $fileid id du fichier à ajouter
 * @param type $dir répertoire dans lequel ajouter
 * @return \ZipArchive
 */
function writeFile($fileid, $filesize, $dir = '')
{
    global $max_download_size;
    /*$zip->close();
    $zip->open($filelocation, ZipArchive::CREATE);*/
    $model = new PersonalFileModel($fileid);
    $data = $model->get();
    $filesize = $filesize + $data['int_size'];
    if($filesize > $max_download_size)
        return NULL;
    $filename = $dir.$data['tx_name'];
    if($data['tx_extension'] != null && $data['tx_extension'] != '')
    {    
        if(strstr($filename, $data['tx_extension']) === FALSE)
        {
            $filename = $filename.'.'.$data['tx_extension'];
        }
    }
    // correct UTF-8 should hold together through this
    /*if($filename === mb_convert_encoding(mb_convert_encoding($dir.$filename, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
    {
      $fixedFilename = $filename;
    }else
    {
      // otherwise we should use 
      $fixedFilename = mb_convert_encoding($dir.$filename, 'UTF-8','CP850');
    }*/
    //$filename = addslashes(str_replace(' ', '_', $filename));
    $fixedFilename = $filename;
    //file_put_contents("ziptemp/log.txt", "writing ".$fixedFilename, FILE_APPEND);
    if(file_exists(getcwd().PersonalFilesController::$filesFolder.$data['tx_encoded_name']))
    {
        copy(getcwd().PersonalFilesController::$filesFolder.$data['tx_encoded_name'], $fixedFilename);
        //$zip->addFile(getcwd().PersonalFilesController::$filesFolder.$data['tx_encoded_name'], $fixedFilename);
        /*$zip->close();
        $zip = new ZipArchive();
        $zip->open($filelocation, ZipArchive::CREATE);*/
    }
    return TRUE;
}
$action = $_REQUEST['action'];
$controller = new PersonalFilesController();
if(!$controller->canAccess())
    return '';
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');
switch($action)
{
    case 'download_file':        
        if($controller->canSeeFile($_REQUEST['id']))
        {
            ob_start();
            $model = new PersonalFileModel($_REQUEST['id']);
            $data = $model->get();
            if($data == null)
                exit;
            $filename = $data['tx_name'];
            $filename = str_replace(' ', '_', $filename);
            if(strstr($filename, $data['tx_extension']) === FALSE)
                    $filename = $filename.'.'.$data['tx_extension'];
            $location = getcwd().PersonalFilesController::$filesFolder.$data['tx_encoded_name']; 
            
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$filename);
            header('Set-Cookie: fileDownload=true; path=/');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($location));
//            ob_clean();
            if (ob_get_length() > 0) { ob_end_flush(); }
//            flush();
//var_dump($location, $filename);
            readfile($location);
            DB::disconnect();
            exit;
        }
        else
        {
            header('HTTP/1.0 403 Forbidden');
            DB::disconnect();
        }
        break;
    case 'download_folder':
        if($controller->canSeeFolder($_REQUEST['id']))
        {
            ob_start();
            $arborescence = $controller->folderContents($_REQUEST['id']);
            if(empty($arborescence))
                exit;
            $plateforme_name = str_replace(' ','_',$_SESSION[SessionController::PLATFORM_NAME]);
            $filename = $_SESSION[SessionController::USERNAME].'_'.time();
            while(file_exists($filename))
            {
                $filename = $filename.substr(uniqid(), 0, 2);
            }
            $location = getcwd().'/../downloads/'.$filename;
            $return = writeDir($_REQUEST['id'], $arborescence, 0, $location.'/');
            if($return == NULL)
            {
                echo 'TOOBIG';
                exit;
            }
            chdir('../downloads');
            exec('zip -r '.$filename.'.zip '.$filename);
            if(is_dir($filename))
                exec('rm -r '.$filename);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$filename.'.zip');
            header('Set-Cookie: fileDownload=true; path=/');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            //header('Content-Length: ' . filesize($location));
            //ob_clean();
            if (ob_get_length() > 0) { ob_end_flush(); }
    //        flush();
            readfile($filename.'.zip');
            unlink($filename.'.zip');
            DB::disconnect();
        }
        break;
    case 'download_selection':
        if(!isset($_SESSION[SessionController::FILE_SELECTION]) 
                || 
                (isset($_SESSION[SessionController::FILE_SELECTION]) && empty($_SESSION[SessionController::FILE_SELECTION]))
        )
                exit;
        ob_start();
        $arborescence = $controller->selectionContents();
        $plateforme_name = str_replace(' ','_',$_SESSION[SessionController::PLATFORM_NAME]);
        $filename = $_SESSION[SessionController::USERNAME].'_'.time();
        while(file_exists($filename))
        {
            $filename = $filename.substr(uniqid(), 0, 2);
        }
        $location = getcwd().'/../downloads/'.$filename;
        $return = writeDir(NULL, $arborescence, 0, $location.'/');
        if($return == NULL)
        {
            echo 'TOOBIG';
            exit;
        }
        chdir('../downloads');
        exec('zip -r '.$filename.'.zip '.$filename);
        if(is_dir($filename))
            exec('rm -r '.$filename);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$filename.'.zip');
        header('Set-Cookie: fileDownload=true; path=/');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        //header('Content-Length: ' . filesize($location));
        //ob_clean();
        if (ob_get_length() > 0) { ob_end_flush(); }
//        flush();
        readfile($filename.'.zip');
        unlink($filename.'.zip');
        DB::disconnect();
        exit;
        break;
}

exit;	
?>