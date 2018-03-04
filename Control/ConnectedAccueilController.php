<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AccueilController
 *
 * @author videony
 */
class ConnectedAccueilController implements BodyController{
  
    private $css = array(
        'css/FeatherLight.css',
        'css/form.css',
        'css/ConnectedAccueil.css'
    );
    private $js = array(
        'js/FeatherLight.js',
        'js/jquery-ui/ui/jquery.ui.resizable.js',
       'js/ConnectedAccueil.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_PERSONNE]))
            return ($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM);
        else
            return FALSE;
    }
    public function getTitle() {
        return 'Accueil';
    }

    

    public function getContent() {
        $template = GenerateUtils::getTemplate('ConnectedAccueil');
        
        $markers = array();
       
        if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_WRITE_PLATFORM_MESSAGE"))
            $markers['IF_CAN_WRITE_PLATFORM_MESSAGE'] = FALSE;
        $template = GenerateUtils::replaceStrings($template, $markers);
        
        $model = new PlatformMessageModel();
        $pmessages = $model->getPlatformMessageByFilter(array(
           'id_plateforme'  =>  $_SESSION[SessionController::PLATFORM_ID] 
        ));
        foreach($pmessages as $key=>$pmessage)
        {
            $pmessages[$key]['tx_message'] = nl2br($pmessage['tx_message']);
            $pmessages[$key]['tx_login'] = strtoupper($pmessage['tx_login']);
            if($pmessage['id_writer'] != $_SESSION[SessionController::ID_PERSONNE])
            {
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 
                        'CAN_REMOVE_OTHERS_PLATFORM_MESSAGES'))
                    $pmessages[$key]['if_can_delete_platform_messages'] = false;
            }
        }
        $messages = GenerateUtils::subPart($template, "###SUB_PLATFORM_MESSAGES###");
        $messages = GenerateUtils::generateRS($messages, $pmessages);
        $template = GenerateUtils::replaceSubPart($template, "###SUB_PLATFORM_MESSAGES###", $messages);
        return $template;
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    public function handlePostRequest() {
        if(!isset($_POST['action']))
           return '';
        switch($_POST['action'])
        {
            case 'add_message':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_WRITE_PLATFORM_MESSAGE"))
                    return 'NO';
                $model = new PlatformMessageModel();
                $data = $model->create($_SESSION[SessionController::PLATFORM_ID], 
                        $_SESSION[SessionController::ID_PERSONNE], $_REQUEST['tx_message']);
                if($data != null)
                    return 'OK';
                else
                    return 'NO';
                break;
            case 'delete_message':
                $model = new PlatformMessageModel($_REQUEST['id_message']);
                $data = $model->get();
                if($data != null && ($data['id_writer'] == $_SESSION[SessionController::ID_PERSONNE]
                    || UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 
                        'CAN_REMOVE_OTHERS_PLATFORM_MESSAGES')))
                {
                    $model->remove();
                    return 'OK';
                }
                else
                    return 'NO';
                break;
            default:
                return '';
                break;
        }
    }

}
