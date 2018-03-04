<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FilesController
 *
 * @author videony
 */
class PersonalFilesController implements BodyController{

    private $css = array(
        'css/Files.css',
        'css/form.css'
    );
    private $js = array(
        'js/form.js',
        'js/plupload/plupload.full.min.js',
        'js/jquery-ui/ui/jquery.ui.dialog.js',
        'js/jquery-ui/ui/jquery.ui.draggable.js',
        'js/jquery-ui/ui/jquery.ui.droppable.js',
        'js/multidraggable.js',
        'js/jquery.gdocsviewer.min.js',
       'js/PersonalFiles.js',
        'js/fileDownload.js'
    );

    static $filesFolder = '/media/Files/';

    public function canAccess() {
        return isset($_SESSION[SessionController::ID_PERSONNE]);
    }
    public function canSeeFile($fileid) {
        $model = new PersonalFileModel($fileid);
        $data = $model->get();
        return ($_SESSION[SessionController::ID_PERSONNE] == $data['id_adder']);
    }
    public function canSeeFolder($folderid) {
        $model = new PersonalFolderModel($folderid);
        $data = $model->get();
        return ($_SESSION[SessionController::ID_PERSONNE] == $data['id_personne']);
    }
    public function getTitle() {
        return 'Fichiers personnels';
    }


    public function getContent() {
        if(isset($_REQUEST['upload_files']))
            return $this->handleUpload();
        if(isset($_REQUEST['commentsform']))
            $this->handleComments();
        if(isset($_REQUEST['folder']))
        {
            $model = new PersonalFolderModel($_REQUEST['folder']);
            $data = $model->get();
             if($data['id_personne'] == $_SESSION[SessionController::ID_PERSONNE])
                $_SESSION[SessionController::CURRENT_FOLDER] = $_REQUEST['folder'];
        }
        define('MAX_MB_UPLOAD_SIZE', ConfigUtils::get('upload.max_file_size'));
        define('MAX_MB_PERSONAL_UPLOAD_SIZE', ConfigUtils::get('upload.max_file_size'));
        define('MAX_DOWNLOAD_SIZE', ConfigUtils::get('download.max_file_size'));
        
        $template = GenerateUtils::getTemplate('PersonalFiles');

        $template = GenerateUtils::replaceStrings($template, array(
            'chemin'                    =>  $this->getChemin() ,
            'max_upload_size'           =>  MAX_MB_UPLOAD_SIZE,
            'max_download_size'           =>  MAX_DOWNLOAD_SIZE,
            '###SUB_COMMENTS_FORM###'   =>  ''
        ));

        $template = $this->generateBrowserWindow($template);

        return $template;
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    private function generateBrowserWindow($template)
    {
        // Recherche du répertoire de travail
        $folder = $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER];

        // Recherche de tous les dossiers
        $foldersmodel = new PersonalFolderModel();
        $folders = $foldersmodel->getFolderByFilter(array(
            'id_parent_folder'     =>  $folder
        ));

        foreach($folders as $key=>$curfolder)
        {
            if(strlen($curfolder['tx_name']) > 20)
            {
                $folders[$key]['tx_shortname'] = substr($curfolder['tx_name'], 0, 20).'...';
            }
            else
            {
                $folders[$key]['tx_shortname'] = $curfolder['tx_name'];
            }
            $foldersize = $foldersmodel->getSize($curfolder['id_folder']);
            if($foldersize < 1000000)
            {
                $folders[$key]['int_size'] = round($foldersize/1000);
                $folders[$key]['size_unit'] = 'KB';
            }
            elseif($foldersize > 1000000 && $foldersize < 1000000000)
            {
                $folders[$key]['int_size'] = round($foldersize/1000000);
                $folders[$key]['size_unit'] = 'MB';
            }
            elseif($foldersize > 1000000000)
            {
                $folders[$key]['int_size'] = round($foldersize/1000000000);
                $folders[$key]['size_unit'] = 'GB';
            }
        }
        $template = GenerateUtils::generateRS($template, $folders, '', '###SUB_FOLDER_ITEM###');
        if($_SESSION[SessionController::NB_ELEMS_PER_LINE] == SessionController::THREE_PER_LINE)
        {
            $template = GenerateUtils::replaceStrings($template, array(
                'folder_classes'    =>  'perline3 perlinefolder3',
                'file_classes'      =>  'perline3'
            ));
        }
        else
        {
            $template = GenerateUtils::replaceStrings($template, array(
                'folder_classes'    =>  '',
                'file_classes'      =>  ''
            ));
        }

        // Recherche de tous les fichiers
        $filesmodel = new PersonalFileModel();
        $files = $filesmodel->getFileByFilter(array(
            'id_folder'     =>  $folder
        ));
        foreach($files as $key=>$file)
        {
            $personmodel = new PersonneModel($file['id_adder']);
            $persondata = $personmodel->get();
            $files[$key]['id_personne'] = $persondata['id_pers'];
            $files[$key]['file_icon'] = $this->getFileIcon($file['id_file'], $file['tx_extension']);
            $files[$key]['dt_ajout'] = date('d-m-Y H:i', strtotime($file['dt_ajout']));
            
            if(strlen($file['tx_name']) > 20)
            {
                $files[$key]['tx_shortname'] = substr($file['tx_name'], 0, 20).'...';
            }
            else
            {
                $files[$key]['tx_shortname'] = $file['tx_name'];
            }
            if($file['int_size'] < 1000000)
            {
                $files[$key]['int_size'] = round($file['int_size']/1000);
                $files[$key]['size_unit'] = 'KB';
            }
            elseif($file['int_size'] > 1000000)
            {
                $files[$key]['int_size'] = round($file['int_size']/1000000);
                $files[$key]['size_unit'] = 'MB';
            }
        }
        $template = GenerateUtils::generateRS($template, $files, '', '###SUB_FILE_ITEM###');

        return $template;
    }

    private function getFileIcon($id, $extension) {
        $extension = strtolower($extension);
        switch ($extension)
        {
            // COMMON
            case 'txt':
            case 'c':
            case 'cpp':
            case 'cp':
            case 'cs':
            case 'cfg':
            case 'css':
            case 'conf':
            case 'config':
            case 'cmd':
            case 'f':
            case 'fla':
            case 'flac':
            case 'h':
            case 'h++':
            case 'hh':
            case 'hlp':
            case 'hpp':
            case 'ini':
            case 'js':
            case 'jse':
            case 'jsp':
            case 'l':
            case 'lic':
            case 'log':
            case 'lst':
            case 'lua':
            case 'm':
            case 'ma':
            case 'man':
            case 'mat':
            case 'ott':
            case 'p':
            case 'p12':
            case 'pas':
            case 'py':
            case 'pyw':
            case 'rb':
            case 'rc':
            case 'sh':
            case 'sql':
            case 'tab':
            case 'tmp':
            case 'tro':
            case 'url':
            case 'vb':
            case 'wps':
            case 'wri':
            case 'y':
                return 'media/file_icons/txt.png';
                break;
            case 'pdf':
                return 'media/file_icons/pdf.png';
                break;
            case 'rar':
            case 'zip':
            case 'ice':
            case 'lha':
            case 'lzh':
            case 'ova':
            case 'pak':
            case 'pif':
            case 'sfx':
            case 'tar':
            case 'taz':
            case 'tgz':
            case 'uha':
            case 'xar':
            case 'gz':
            case 'zap':
            case 'zdg':
            case 'zim':
            case 'zom':
            case 'zoo':
            case 'zoom':
            case '7z':
                return 'media/file_icons/zip.png';
                break;
            case 'doc':
            case 'docx':
            case 'docm':
            case 'dot':
            case 'dotx':
            case 'odt':
            case 'odb':
            case 'odc':
            case 'odf':
            case 'odg':
            case 'odp':
            case 'ods':
            case 'oxt':
            case 'sdw':
            case 'sxw':
            case 'uot':
            case 'wp':
            case 'wpd':
            case 'wpg':
                return 'media/file_icons/word.png';
                break;
            case 'xls';
            case 'xlsx':
            case 'csv':
            case 'ots':
            case 'sdc':
            case 'stc':
            case 'sxc':
            case 'uos':
            case 'xla':
            case 'xlc':
            case 'xlsm':
            case 'xlt':
            case 'xltx':
            case 'xltm':

                return 'media/file_icons/excel.png';
                break;
            case 'ppt':
            case 'pptx':
            case 'otp':
            case 'pot':
            case 'pps':
            case 'shw':
            case 'sxi':
            case 'uop':
                return 'media/file_icons/powerpoint.png';
                break;
            // IMGS
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
                return 'media/file_icons/png.png';

            // EXECUTABLE
            case 'exe':
            case 'bin':
            case 'ipa':
                return 'media/file_icons/exe.png';
                break;
            case 'class':
            case 'dat':
            case 'iso':
            case 'mb':
            case 'msi':
            case 'msf':
            case 'nrg':
            case 'o':
            case 'ps':
            case 'rsd':
            case 'sig':
            case 'std':
            case 'sys':
            case 'vbproj':
            case 'vbproject':
            case 'vbs':
            case 'vdi':
            case 'vmdk':
            case 'vmem':
            case 'vmsd':
            case 'xnb':
                return 'media/file_icons/bin.png';
                break;
            case 'jar':
            case 'war':
                return 'media/file_icons/jar.png';
                break;
            case 'java':
            case 'jav':
                return 'media/file_icons/java.png';
                break;

            // LATEX
            case 'latex':
            case 'tex':
            case 'toc':
                return 'media/file_icons/latex.png';
                break;
            // MUSIC
            case 'mp3':
            case 'gp3':
            case 'gp4':
            case 'gp5':
            case 'gpx':
            case 'hcom':
            case 'itdb':
            case 'itf':
            case 'itl':
            case 'jo':
            case 'kar':
            case 'logic':
            case 'mid':
            case 'midi':
            case 'mp2':
            case 'ogg':
            case 'ogm':
            case 'sb':
            case 'sf':
            case 'song':
            case 'txw':
            case 'uw':
            case 'voc':
            case 'zvd':
                return 'media/file_icons/musique.png';
                break;

            // VIDEO
            case 'wma':
            case 'avi':
            case 'flv':
            case 'mkv':
            case 'movie':
            case 'mp4':
            case 'mpe':
            case 'mpeg':
            case 'mpg':
            case 'mts':
            case 'qt':
            case 'ra':
            case 'ram':
            case 'rm':
            case 'rmvb':
            case 'rv':
            case 'torrent':
            case 'vro':
            case 'wmv':
            case 'wav':
                return 'media/file_icons/video.png';
                break;

            // WEB
            case 'rss':
                return 'media/file_icons/rss.png';
                break;
            case 'xhtml':
            case 'html':
            case 'htm':
            case 'shtml':
            case 'oth':
            case 'xht':
                return 'media/file_icons/html.png';
                break;
            case 'php':
                return 'media/file_icons/php.png';
                break;
            case 'xml':
            case 'fodg':
            case 'fodp':
            case 'fods':
            case 'fodt':
            case 'xsl':
            case 'xspf':
            case 'yml':
                return 'media/file_icons/xml.png';
                break;

            default:
                return 'media/file_icons/empty_file.png';
                break;

        }
    }

    private function handleUpload() {
        if (empty($_FILES) || $_FILES["file"]["error"]) {
            die('{"OK": 0}');
          }

        if (!empty($_FILES) && isset($_SESSION['id_user'])) {
                $fileParts = pathinfo($_FILES['file']['name']);

                $tempFile   = $_FILES['file']['tmp_name'];
                $filename   = $_FILES['file']['name'];
                $encoded    = $this->encodeFilename($filename);
                $size       = $_FILES['file']['size'];
                if(isset($fileParts['extension']))
                    $extension = $fileParts['extension'];
                else	
                    $extension = '';
                if($_REQUEST['extract_archives'] == 1 && $this->isArchive($extension))
                {
                    return $this->handleExtractingUpload();
                }

                if($this->putFile($tempFile, $encoded, TRUE))
                {
                    // Ajout dans la base de données s'il n'existe pas déjà
                    $model = new PersonalFileModel();
                    $existing = $model->getFileByFilter(array(
                       'id_folder'  =>  $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER],
                        'tx_name'   =>  $filename
                    ));
                    $message = "";
                    if(empty($existing))
                    {
                        $id = $model->create($_SESSION[SessionController::ID_PERSONNE],
                                $filename,
                                $extension, $filename, $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER],
                                $size, $encoded);
                    }
                    else
                    {
                        $id = $existing[0]['id_file'];
                    }
                    $_SESSION['new_files'][$id] = $filename;
                    return '{"OK": 1}';
                }
        }
    }
   
    private function handleExtractingUpload()
    {
        // 1. Infos du fichier en question
        $fileParts = pathinfo($_FILES['file']['name']);
        $tempFile   = $_FILES['file']['tmp_name'];
        $filename   = $_FILES['file']['name'];
        if(!isset($fileParts['extension']) || !$this->isArchive($fileParts['extension']))
            return;
        // 2. Extraction vers ziptemp
        move_uploaded_file($tempFile, 'ziptemp/'.$filename);
        $zip = new ZipArchive;
        if ($zip->open('ziptemp/'.$filename) === TRUE) {
            if(!is_dir('ziptemp/'.$filename.'_zipdir'))
                mkdir('ziptemp/'.$filename.'_zipdir');
            $zip->extractTo('ziptemp/'.$filename.'_zipdir');
            $zip->close();
        } 
        else 
            return FALSE;
        // 3. Ajout dans la base de données
        $this->systemFilesToDatabase('ziptemp/'.$filename.'_zipdir', $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER]);
        
        // 4. Suppression dans ziptemp
        unlink('ziptemp/'.$filename);
        exec('rm -R ziptemp/*');
    }
    
    public function systemFilesToDatabase($dir, $parentdir)
    {
        $path = explode(DIRECTORY_SEPARATOR, $dir);
        if(count($path) == 0)
            return;
        $name = $path[count($path)-1];
        $name = str_replace('ziptemp/', '', $name);
        if(is_dir($dir) && $name != '.' && $name != '..')
        {
            $model = new PersonalFolderModel();
            $folderid = null;
            if(!$model->folderExists($parentdir, $name))
            {
                $folderid = $model->create($name, $_SESSION[SessionController::ID_PERSONNE],
                        $parentdir,
                        $_SESSION[SessionController::ID_PERSONNE]);
            }
            else
            {
                $folders = $model->getFolderByFilter(array(
                    'id_parent_folder'  =>  $parentdir,
                    'tx_name'           =>  $name
                ));
                if(!empty($folders))
                    $folderid = $folders[0]['id_folder'];
                else
                    $folderid = null;
            }
            if($folderid != null && $handle = opendir($dir))
            {
                while(FALSE !== ($entry = readdir($handle)))
                {
                    $this->systemFilesToDatabase($dir.DIRECTORY_SEPARATOR.$entry, $folderid);
                }
            }
        }
        else
        {
            if(is_file($dir))
            {
                $fileParts = pathinfo($dir);
                $encoded    = $this->encodeFilename($name, $parentdir);
                $size       = filesize($dir);
                if(isset($fileParts['extension']))
                    $extension = $fileParts['extension'];
                else	
                    $extension = '';
                if($this->putFile($dir, $encoded, FALSE))
                {
                    // Ajout dans la base de données s'il n'existe pas déjà
                    $model = new PersonalFileModel();
                    $existing = $model->getFileByFilter(array(
                       'id_folder'  =>  $parentdir,
                        'tx_name'   =>  $name
                    ));
                    $message = "";
                    if(empty($existing))
                    {
                        $id = $model->create($_SESSION[SessionController::ID_PERSONNE],
                                $name,
                                $extension, $name, $parentdir,
                                $size, $encoded);
                    }
                    else
                    {
                        $id = $existing[0]['id_file'];
                    }
                    $_SESSION['new_files'][$id] = $name;
                }
                unlink($dir);
            }
        }
    }
    
    /**
     * Retourne une version "encodée" du fichier et de son path dans les répertoires
     * @param type $filename
     * @return type
     */
    private function encodeFilename($filename, $directory = NULL){
        if($directory == NULL)
            $directory = $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER];
        // Implémentation
        // On considère que sha1 ne crée pas de collisions, mais on traite tout de même le cas
        // où il y en aurait une.
        $targetFolder = getcwd().self::$filesFolder;
        $encoded = sha1('-'.$_SESSION[SessionController::ID_PERSONNE]
                    .$directory
                    .$filename);
        do
        {
            $encoded = sha1($encoded.rand(0, 10000));
            $dir1       = substr($encoded, 0, 1);
            $dir2       = substr($encoded, 1, 1);
        }while(file_exists($targetFolder.$filename));

        return $dir1.'/'.$dir2.'/'.$encoded;
    }
    private function isArchive($extension)
    {
        switch($extension)
        {
            case 'zip':
            case 'rar':
                return TRUE;
                break;
            default:
                return FALSE;
                break;
        }
    }
    /**
     * Bouge le fichier dans le bon répertoire en fonction de nom encodé.
     * @param type $current_file Emplacement du fichier actuellement
     * @param type $encoded Nom du fichier encodé
     * @param type $newupload TRUE seulement si le fichier vient d'être uploadé
     */
    private function putFile($current_file, $encoded, $newupload = FALSE) {
        $targetFolder = getcwd().self::$filesFolder;

        $folders = explode('/', $encoded);
        $path = $targetFolder.'';
        for($i=0;$i<count($folders)-1;$i++)
        {
            $path = $path.$folders[$i].'/';
            if(!is_dir($path))
                mkdir($path);
        }
        if($newupload)
            return move_uploaded_file($current_file, $targetFolder.$encoded);
        else
            return copy($current_file, $targetFolder.$encoded);
    }

    private function handleComments() {
        foreach($_POST as $post=>$val)
        {
            if(substr($post, 0, 4) == "file" && !empty($val))
            {
                $infos = explode('_', $post);
                $id = $infos[1];
                $model = new PersonalFileModel($id);
                $model->set(array(
                   'tx_commentaire' =>  $val
                ));
            }
        }
    }
    /**
     * Retourne un tableau de tous les éléments d'un dossier.
     * Les dossiers ont des indice de type folder_###
     * Les fichiers ont des indices de type file_###
     * @param type $id Identifiant du dossier en question
     */
    public function folderContents($id) {
        $foldermodel = new PersonalFolderModel();
        $inner_folders = $foldermodel->getFolderByFilter(array(
           'id_parent_folder' => $id
        ));
        $filemodel = new PersonalFileModel();
        $inner_files = $filemodel->getFileByFilter(array(
           'id_folder'  =>  $id
        ));

        $arborescence = array();

        foreach($inner_folders as $folder)
        {
            $arborescence['folder_'.$folder['id_folder']] = $this->folderContents($folder['id_folder']);
        }
        foreach($inner_files as $file)
        {
            $arborescence['file_'.$file['id_file']] = $file['id_file'];
        }
        return $arborescence;
    }

    /**
     * Retourne un tableau de tous les éléments d'un dossier.
     * Les dossiers ont des indice de type folder_###
     * Les fichiers ont des indices de type file_###
     */
    public function selectionContents() {
        $arborescence = array();
        foreach($_SESSION[SessionController::FILE_SELECTION] as $elem)
        {
            $infos = explode('_', $elem);
            $type = $infos[0];
            $id = $infos[1];

            if($type == 'file')
            {
                $arborescence['file_'.$id] = $id;
            }
            elseif($type == 'folder')
            {
                $arborescence['folder_'.$id] = $this->folderContents($id);
            }
        }
        // Nettoyage de la sélection
        $sess = new SessionController(SessionController::NOACTION);
        $sess->clearSelection();
        return $arborescence;
    }

    public function changeFolder($id){
        // On vérifie que le dossier est bien accessible

        $model = new PersonalFolderModel();
        $available = $model->getFolderByFilter(array(
            'id_personne'  =>  $_SESSION[SessionController::ID_PERSONNE]
        ));
        $found = false;
        foreach($available as $folder)
        {
            if($id == $folder['id_folder'])
            {
                $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER] = $id;
                return TRUE;
            }
        }

        return FALSE;
    }
    public function getChemin() {
        $model = new PersonalFolderModel($_SESSION[SessionController::CURRENT_PERSONAL_FOLDER]);
        $data = $model->get();
        $chemin = '';
        while($data['id_parent_folder'] != NULL)
        {
            $chemin = $data['tx_name'].'/'.$chemin;
            $model = new PersonalFolderModel($data['id_parent_folder']);
            $data = $model->get();
        }
        $chemin = '/'.$chemin;
        return $chemin;
    }
    public function getPictureDisplay($fileid) {
        $model = new PersonalFileModel($fileid);
        $file = $model->get();
        $extension = strtolower($file['tx_extension']);

        if($this->canSeeFile($fileid))
        {
            $imgs = array('png', 'gif', 'jpg', 'jpeg', 'bmp');
            if(in_array($extension, $imgs))
            {
                return array(
                    'type'  =>  $file['tx_extension'],
                    'data'  =>  file_get_contents(getcwd().self::$filesFolder.$file['tx_encoded_name'])
                );
            }
            else
            {
                return NULL;
            }
        }
        else
        {
            switch($extension)
            {
                case 'png':
                    return array(
                    'type'  =>  'png',
                    'data'  =>  file_get_contents(getcwd().'/media/file_icons/png.png')
                );
                    break;
                case 'jpg':
                case 'jpeg':
                    return array(
                    'type'  =>  'png',
                    'data'  =>  file_get_contents(getcwd().'/media/file_icons/jpg.png')
                );
                    break;
                case 'bmp':
                    return array(
                    'type'  =>  'png',
                    'data'  =>  file_get_contents(getcwd().'/media/file_icons/bmp.png')
                );
                    break;
                case 'gif':
                    return array(
                    'type'  =>  'png',
                    'data'  =>  file_get_contents(getcwd().'/media/file_icons/gif.png')
                );
                    break;
            }
        }
    }

    public function handlePostRequest() {
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'add_folder':
                $model = new PersonalFolderModel();
                if(!$model->folderExists($_SESSION[SessionController::CURRENT_PERSONAL_FOLDER], $_POST['name']))
                {
                    $return = $model->create($_POST['name'], $_SESSION[SessionController::ID_PERSONNE],
                            $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER],
                            $_SESSION[SessionController::ID_PERSONNE]);
                }
                else
                {
                    return 'NO';
                }
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                return $this->generateBrowserWindow($template);
                break;
            case 'get_folder_path':
                return $this->getChemin();
                break;
            case 'change_folder':
                $this->changeFolder($_POST['id']);
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                return $this->generateBrowserWindow($template);
                break;
            case 'change_folder_by_path':
                $path = $_POST['path'];
                if(substr($path, 0, 1) != '/')
                    $path = '/'.$path;
                $path = str_replace('\\', '/', $path);

                $model = new PersonalFolderModel();
                // On commence avec le dossier par défaut (=racine)
                $cur = $model->getDefaultFolder($_SESSION[SessionController::ID_PERSONNE]);
                $cur = $cur['id_folder'];
                $folders = explode('/', $path);

                foreach($folders as $folder)
                {
                    if(strlen($folder) > 0)
                    {
                        $results = $model->getFolderByFilter(array(
                            'tx_name'           =>  $folder,
                            'id_parent_folder'  =>  $cur
                        ));
                        if(empty($results))
                        {
                            // Path non valide, dossiers incorrects
                            return 'NO';
                        }
                        $cur = $results[0]['id_folder'];
                    }
                }
                $this->changeFolder($cur);
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                return $this->generateBrowserWindow($template);
                break;
            case 'up_folder':
                $model = new PersonalFolderModel($_SESSION[SessionController::CURRENT_PERSONAL_FOLDER]);
                $data = $model->get();
                if($data['id_parent_folder'] != NULL)
                    $this->changeFolder($data['id_parent_folder']);
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                return $this->generateBrowserWindow($template);
                break;
            case 'comments_post_upload':
                if(!isset($_SESSION['new_files']))
                {
                    return '';
                }
                $html = GenerateUtils::getTemplate('PersonalFiles');
                $html = GenerateUtils::subPart($html, '###SUB_COMMENTS_FORM###');

                $fields = array();
                foreach($_SESSION['new_files'] as $fileid=>$filename)
                {
                    $fields[] = array('id_file'=>$fileid, 'tx_filename'=>$filename);
                }
                unset($_SESSION['new_files']);
                return GenerateUtils::generateRS($html, $fields);
                break;
            case 'refresh_folder':
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                return $this->generateBrowserWindow($template);
                break;
            case 'delete_selection':
                $notall = FALSE;
                foreach($_POST['selection'] as $elem)
                {
                    $infos = explode('_', $elem);
                    $type = $infos[0];
                    $id = $infos[1];
                    if($type == 'file'){
                        $model = new PersonalFileModel($id);
                        $data = $model->get();
                    }
                    elseif($type == 'folder'){
                        $model = new PersonalFolderModel($id);
                        $data = $model->get();
                    }

                    // Suppression
                    $model->remove();
                }
                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                if($notall == FALSE)
                    return 'OK'.$this->generateBrowserWindow($template);
                else
                    return 'NA'.$this->generateBrowserWindow($template);
                break;
            case 'copy_selection':
                $ids = array();
                foreach($_POST['selection'] as $elem)
                {
                    $infos = explode('_', $elem);
                    $type = $infos[0];
                    $id = $infos[1];
                    if($type == 'file' || $type == 'folder')
                        $ids[] = $elem;
                }
                $sess = new SessionController(SessionController::NOACTION);
                $sess->clearSelection();
                $sess->addSelection($ids, SessionController::SELECTION_COPY);
                return 'OK';
                break;
            case 'cut_selection':
                $ids = array();
                foreach($_POST['selection'] as $elem)
                {
                    $infos = explode('_', $elem);
                    $type = $infos[0];
                    $id = $infos[1];
                    if($type == 'file' || $type == 'folder')
                        $ids[] = $elem;
                }
                $sess = new SessionController(SessionController::NOACTION);
                $sess->clearSelection();
                $sess->addSelection($ids, SessionController::SELECTION_CUT);
                return 'OK';
                break;
            case 'download_selection':
                $ids = array();
                foreach($_POST['selection'] as $elem)
                {
                    $infos = explode('_', $elem);
                    $type = $infos[0];
                    $id = $infos[1];
                    if($type == 'file')
                    {
                        if($this->canSeeFile($id))
                            $ids[] = $elem;
                    }
                    elseif($type == 'folder')
                    {
                        if($this->canSeeFolder($id))
                            $ids[] = $elem;
                    }
                }
                $sess = new SessionController(SessionController::NOACTION);
                $sess->clearSelection();
                $sess->addSelection($ids, SessionController::SELECTION_DOWNLOAD);
                return 'OK';
                break;
            case 'paste_selection':
                $notall = FALSE;
                if(!isset($_SESSION[SessionController::FILE_SELECTION]) ||
                        empty($_SESSION[SessionController::FILE_SELECTION]))
                    return 'EMPTY';
                foreach($_SESSION[SessionController::FILE_SELECTION] as $elem)
                {
                    $infos = explode('_', $elem);
                    $type = $infos[0];
                    $id = $infos[1];

                    if($type == 'file')
                    {
                        $moved = $this->moveFile($id,
                                $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER],
                                $_SESSION[SessionController::LAST_SELECTION_TYPE]);
                    }
                    elseif($type == 'folder')
                    {
                        $moved = $this->moveFolder($id,
                                $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER],
                                $_SESSION[SessionController::LAST_SELECTION_TYPE]);
                    }
                    if($moved === FALSE)
                        $notall = TRUE;
                }
                // Nettoyage de la sélection
                $sess = new SessionController(SessionController::NOACTION);
                $sess->clearSelection();

                $template = GenerateUtils::getTemplate('PersonalFiles');
                $template = GenerateUtils::subPart($template, '###SUB_FILELIST###');
                if($notall == FALSE)
                    return 'OK'.$this->generateBrowserWindow($template);
                else
                    return 'NA'.$this->generateBrowserWindow($template);
                break;
            case 'edit_comment':
                $model = new PersonalFileModel($_POST['id']);
                $data = $model->get();
                // Permissions
                if($data['id_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                    return 'NO';
                return $model->set(array(
                   'tx_commentaire' =>  $_POST['comment']
                ));
                break;
            case 'rename_folder':
                $model = new PersonalFolderModel($_POST['id']);
                $data = $model->get();
                // Permissions
                if($data['id_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                    return 'NO';
                return $model->set(array(
                   'tx_name' =>  $_POST['name']
                ));
                break;
            case 'rename_file':
                $model = new PersonalFileModel($_POST['id']);
                $data = $model->get();
                // Permissions
                if($data['id_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                    return 'NO';
                return $model->set(array(
                   'tx_name' =>  $_POST['name']
                ));
                break;
            case 'change_nb_per_line':
                $_SESSION[SessionController::NB_ELEMS_PER_LINE] = $_POST['nbperline'];
                break;
            case 'authorize_view':
                if(isset($_REQUEST['id_file']))
                {
                    $model = new PersonalFileModel($_REQUEST['id_file']);
                    if($this->canSeeFile($_REQUEST['id_file']))
                    {
                        $data = $model->get();
                        switch(strtolower($data['tx_extension']))
                        {
                            case 'php':
                            case 'js':
                                return 'CANNOT_DISPLAY';
                                break;
                            case 'txt':
                            case 'c':
                            case 'cpp':
                            case 'cp':
                            case 'cs':
                            case 'cfg':
                            case 'css':
                            case 'conf':
                            case 'config':
                            case 'cmd':
                            case 'f':
                            case 'fla':
                            case 'flac':
                            case 'h':
                            case 'h++':
                            case 'hh':
                            case 'hlp':
                            case 'hpp':
                            case 'ini':
                            case 'js':
                            case 'jse':
                            case 'jsp':
                            case 'l':
                            case 'lic':
                            case 'log':
                            case 'lst':
                            case 'lua':
                            case 'm':
                            case 'ma':
                            case 'man':
                            case 'mat':
                            case 'ott':
                            case 'p':
                            case 'p12':
                            case 'pas':
                            case 'py':
                            case 'pyw':
                            case 'rb':
                            case 'rc':
                            case 'sh':
                            case 'sql':
                            case 'tab':
                            case 'tmp':
                            case 'tro':
                            case 'url':
                            case 'vb':
                            case 'wps':
                            case 'wri':
                            case 'y':
                            case 'java':
                            case 'latex':
                            case 'tex':
                            case 'xhtml':
                            case 'html':
                            case 'htm':
                            case 'shtml':
                            case 'oth':
                            case 'xht':
                            case 'xml':
                            case 'fodg':
                            case 'fodp':
                            case 'fods':
                            case 'fodt':
                            case 'xsl':
                            case 'xspf':
                            case 'yml':
                                return 'txtview.php?action=see_personal_file&key='.$_REQUEST['id_file'];
                        break;
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
                                return 'dim.php?type=personal_file_img_type&file='.$_REQUEST['id_file'].'&timestamp='.time();
                                break;
                            default:
                                $key = '';
                                $model = new FileViewAuthorizationModel(); 
                                do
                                {
                                    $key = sha1($data['id_file'].$_SESSION[SessionController::ID_PERSONNE].time().rand(0,100));
                                    $results = $model->getFileViewAuthorizationByFilter(array(
                                        'tx_key'=>$key
                                    ));
                                }while(!empty($results));
                                $model->create($key, date('Y-m-d H:i:s', time()+20), $_REQUEST['id_file'],
                                FileViewAuthorizationModel::PERSONAL_FILE);
                                $url = 'https://docs.google.com/viewer?embedded=true&url='
                                        .urlencode(ConfigUtils::get('website.url').'/gview.php?key='.$key);
                                return $url;
                                break;
                        }
                    }
                    else
                    {
                        return 'NO';
                    }
                }
                else
                {
                    return 'NO';
                }
                break;
            default:
                return '';
                break;
        }
    }

    private function moveFile($fileid, $newfolder, $type = SessionController::SELECTION_COPY) {
        $targetFolder = getcwd().self::$filesFolder;
        $model = new PersonalFileModel($fileid);
        $data = $model->get();

        // Permissions
        if($data['id_adder'] != $_SESSION[SessionController::ID_PERSONNE])
            return FALSE;
        if($type == SessionController::SELECTION_COPY)
        {
            $encoded = $this->encodeFilename($data['tx_name']);
            if($this->putFile($targetFolder.$data['tx_encoded_name'], $encoded))
            {
                $existing = $model->getFileByFilter(array(
                    'tx_name'       =>  $data['tx_name'],
                    'id_folder'     =>  $newfolder
                ));
                if(empty($existing))
                {
                    $model->create($data['id_adder'],
                        $data['tx_commentaire'],
                        $data['tx_extension'], $data['tx_name'], $newfolder,
                        $data['int_size'], $encoded);
                }
                else
                    return FALSE;
            }
        }
        elseif($type == SessionController::SELECTION_CUT)
        {
            $existing = $model->getFileByFilter(array(
                'tx_name'       =>  $data['tx_name'],
                'id_folder'     =>  $newfolder
            ));
            if(empty($existing))
            {
                $model->set(array(
                    'id_folder' =>  $newfolder
                ));
            }
            else
                return FALSE;
        }
        return TRUE;
    }
    private function moveFolder($folderid, $newParentFolder, $type = SessionController::SELECTION_COPY) {
        $targetFolder = getcwd().self::$filesFolder;
        $model = new PersonalFolderModel($folderid);
        $data = $model->get();

        // Permissions
        if($data['id_adder'] != $_SESSION[SessionController::ID_PERSONNE])
            return FALSE;

        if($type == SessionController::SELECTION_COPY)
        {
            $existing = $model->getFolderByFilter(array(
                'tx_name'       =>  $data['tx_name'],
                'id_parent_folder'     =>  $newParentFolder
            ));
            if(empty($existing))
            {
                $newfolderid = $model->create($data['tx_name'], $data['id_adder'],
                            $newParentFolder,
                            $data['id_personne']);
            }
            else
            {
                $newfolderid = $existing[0]['id_folder'];
            }
            // Il faut copier tout ce qu'il y a à l'intérieur

            // Dossiers
            $folders = $model->getFolderByFilter(array(
                'id_parent_folder'  =>  $folderid
            ));
            foreach($folders as $folder)
            {
                $this->moveFolder($folder['id_folder'], $newfolderid, SessionController::SELECTION_COPY);
            }

            // Fichiers
            $model = new PersonalFileModel();
            $files = $model->getFileByFilter(array(
               'id_folder'  => $folderid
            ));
            foreach($files as $file)
            {
                $this->moveFile($file['id_file'], $newfolderid, SessionController::SELECTION_COPY);
            }
        }
        elseif($type == SessionController::SELECTION_CUT)
        {
            $model->set(array(
                'id_parent_folder' =>  $newParentFolder
            ));
            $newfolderid = $folderid;
        }
        return $newfolderid;
    }

}
