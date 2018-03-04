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
class PlatformListController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/platformlist.css'
    );
    private $js = array(
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
        'js/platformlist.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'Plateformes';
    }

    

    public function getContent() {
        $template = GenerateUtils::getTemplate('PlatformList');
        $markers['information'] = FormUtils::infoBox('Cliquez sur les en-têtes de colonne pour trier en ordre croissant '
                . 'ou décroissant. Vous pouvez aussi filtrer les résultats en tapant dans les champs d\'en-tête.<br/>'
                . 'Si vous souhaitez plus d\'informations sur une plateforme particulière, survolez l\'icône d\'oeil dans la dernière case.');
        $markers['###IF_CREATE_PLATFORM_BUTTON###'] = ConfigUtils::isPlaformCreateEnabled();
        $template = GenerateUtils::replaceStrings($template, $markers);
        
        
        $model = new PlatformModel();
        $platforms = $model->getAllPlatforms();
        $affiliatedplatforms = array();
        $askingplatforms = array();
        
        foreach($platforms as $key=>$platform)
        {
            if(!isset($_SESSION[SessionController::ID_PERSONNE]))
            {
                $platforms[$key]['if_pending'] = FALSE;
                $platforms[$key]['if_blocked'] = FALSE;
                $platforms[$key]['if_affiliated'] = FALSE;
                $platforms[$key]['if_not_affiliated'] = FALSE;
                $platforms[$key]['if_in_platform'] = FALSE;
                $platforms[$key]['if_banned'] = FALSE;
                $platforms[$key]['if_notifications'] = FALSE;
            }
            else
            {
                // Check si déjà membre des plateformes ou non
                $memModel = new MemberModel();
                $results = $memModel->getMembersByFilter(array(
                    'id_personne'   => $_SESSION[SessionController::ID_PERSONNE],
                    'id_plateforme' => $platform['id_plateforme']
                ));
                $platforms[$key]['if_notifications'] = FALSE;
                if(!empty($results))
                {
                    $affiliatedplatforms[$key] = $platforms[$key];
                    unset($platforms[$key]);
                    // Affilié à la plateforme				
                    if($results[0]['actif'] == 1)
                    {
                        $affiliatedplatforms[$key]['if_pending'] = FALSE;
                        $affiliatedplatforms[$key]['if_blocked'] = FALSE;
                        $affiliatedplatforms[$key]['if_not_affiliated'] = FALSE;                    
                        $affiliatedplatforms[$key]['if_banned'] = FALSE;
                        $affiliatedplatforms[$key]['if_not_in_platform'] = FALSE;
                        $affiliatedplatforms[$key]['banned_class'] = '';
                        if($results[0]['compteurNotif'] > 0)
                        {
                            $affiliatedplatforms[$key]['###COMPTEURNOTIF###'] = $results[0]['compteurNotif'];
                            $affiliatedplatforms[$key]['if_notifications'] = TRUE;
                        }
                    }
                    else
                    {
                        $affiliatedplatforms[$key]['if_pending'] = FALSE;
                        $affiliatedplatforms[$key]['if_affiliated'] = FALSE;
                        $affiliatedplatforms[$key]['if_not_affiliated'] = FALSE;
                        $affiliatedplatforms[$key]['if_in_platform'] = FALSE;                    
                        $affiliatedplatforms[$key]['if_banned'] = FALSE;
                        $affiliatedplatforms[$key]['banned_class'] = 'banned';
                    }
                }
                else
                {
                    $demModel = new DemandeModel();
                    $demandes = $demModel->getDemandesByFilter(array(
                        'id_personne'   => $_SESSION[SessionController::ID_PERSONNE],
                        'id_plateforme' => $platform['id_plateforme']
                    ));
                    if(empty($demandes))
                    {
                        $platforms[$key]['if_pending'] = FALSE;
                        $platforms[$key]['if_blocked'] = FALSE;
                        $platforms[$key]['if_affiliated'] = FALSE;
                        $platforms[$key]['if_in_platform'] = FALSE;
                        $platforms[$key]['if_banned'] = FALSE;
                        $platforms[$key]['banned_class'] = '';
                    }
                    else
                    {
                        $askingplatforms[$key] = $platforms[$key];
                        unset($platforms[$key]);
                        if($demandes[0]['status'] == DemandeModel::BANNED)
                        {
                            // Banni
                            $askingplatforms[$key]['if_blocked'] = FALSE;
                            $askingplatforms[$key]['if_not_affiliated'] = FALSE;
                            $askingplatforms[$key]['if_affiliated'] = FALSE;
                            $askingplatforms[$key]['if_in_platform'] = FALSE;                        
                            $askingplatforms[$key]['if_pending'] = FALSE;
                            $askingplatforms[$key]['banned_class'] = 'banned';
                        }
                        else
                        {
                            // Demande envoyée
                            $askingplatforms[$key]['if_blocked'] = FALSE;
                            $askingplatforms[$key]['if_not_affiliated'] = FALSE;
                            $askingplatforms[$key]['if_affiliated'] = FALSE;
                            $askingplatforms[$key]['if_in_platform'] = FALSE;                        
                            $askingplatforms[$key]['if_banned'] = FALSE;
                            $askingplatforms[$key]['banned_class'] = '';
                        }
                    }
                }
            }
        }
        
        $template = GenerateUtils::generateRS($template, array_merge($affiliatedplatforms, $askingplatforms, $platforms));
        
        
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
            case 'get_connected_platforms':
                if(!isset($_SESSION[SessionController::ID_PERSONNE]))
                    return '';
                $memberModel = new MemberModel();
                $memberships = $memberModel->getMembersByFilter(array('id_personne' => $_SESSION[SessionController::ID_PERSONNE]));
                $data = array();
                foreach($memberships as $membership)
                {
                    $platform = new PlatformModel($membership['id_plateforme']);
                    $pdata = $platform->get();
                    if($membership['actif'] == 0)
                    {
                        $pdata['disabled_set_default'] = 'disabled';
                        $pdata['title_set_default'] = 'Vous avez été bloqué de cette plateforme.';
                        $pdata['disabled_use'] = 'disabled';
                        $pdata['title_use'] = 'Vous avez été bloqué de cette plateforme.';
                    }
                    else
                    {
                        $pdata['disabled_set_default'] = '';
                        $pdata['title_set_default'] = '';
                        $pdata['disabled_use'] = '';
                        $pdata['title_use'] = '';
                    }
                    if($membership['hierarchie'] == MemberModel::ADMIN)
                    {
                        // Vérification qu'il y a un autre admin avant.
                        $admins = $memberModel->getMembersByFilter(array(
                            'id_plateforme' => $membership['id_plateforme'],
                            'hierarchie'    => MemberModel::ADMIN
                        ));
                        if(count($admins) == 1)
                        {
                            $pdata['title'] = 'Vous ne pouvez pas vous désaffilier car vous êtes l\'unique administrateur';
                            $pdata['disabled'] = 'disabled';
                        }
                        else
                        {
                            $pdata['title'] = '';
                            $pdata['disabled'] = '';
                        }
                    }
                    else
                    {
                        $pdata['title'] = '';
                        $pdata['disabled'] = '';
                    }
                    $pdata['if_platform_details'] = FALSE;
                    if($membership['compteurNotif'] > 0)
                        $pdata['compteurnotif'] = $membership['compteurNotif'];
                    else
                        $pdata['if_notifications'] = FALSE;
                    $data[] = $pdata;
                }
                $template = GenerateUtils::getTemplate('platform');
                $template = GenerateUtils::replaceStrings($template, array('height' => '400px'));
                return GenerateUtils::generateRS($template, $data, '', '###SUB_PLATFORM###');
                break;
            case 'get_platform':
                
                $platform = new PlatformModel($_POST['id']);
                $pdata = $platform->get();
                $pdata['height'] = '400px';
                $pdata['if_commands'] = FALSE;
                $pdata['if_notifications'] = FALSE;
                    
                $template = GenerateUtils::getTemplate('platform');
                $template = GenerateUtils::replaceStrings($template, $pdata);
                return $template;
                break;
            case 'add_platform':
                if(!isset($_SESSION[SessionController::ID_PERSONNE]))
                    return '';
                
                $model = new DemandeModel();
                $model->create($_SESSION[SessionController::ID_PERSONNE], $_POST['id']);
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_NEW_ASK_TO_JOIN, 
                        'Nouvelle demande pour rejoindre la plateforme: '.$_SESSION[SessionController::USERNAME],
                        'request.php?action=UserMan',
                        $_POST['id']);
                
                return 'media/icons/hourglass.png';
                break;
            case 'quit_platform':
                if(!isset($_SESSION[SessionController::ID_PERSONNE]))
                    return '';
                $model = new MemberModel();
                $return = $model->del(array(
                   'id_plateforme'  => $_POST['id'],
                   'id_personne'    => $_SESSION[SessionController::ID_PERSONNE]
                ));
                if($return == TRUE)
                    return 'media/icons/add.png';
                else
                    return 'NO';
                break;
            case 'set_default':
                if(!isset($_SESSION[SessionController::ID_PERSONNE]))
                    return '';
                // Bloqué ou pas?
                $mModel = new MemberModel();
                $results = $mModel->getMembersByFilter(array(
                    'id_plateforme' =>  $_POST['id'],
                    'id_personne'   =>  $_SESSION[SessionController::ID_PERSONNE]
                ));
                if($results[0]['actif'] == 0)
                    return '';
                $model = new PersonneModel($_SESSION[SessionController::ID_PERSONNE]);
                $model->set(array(
                   'id_plateforme'  =>  $_POST['id'] 
                ));
                $sess = new SessionController();
                $sess->changePlatform($_POST['id']);
                break;
            case 'use':
                // Bloqué ou pas?
                $mModel = new MemberModel();
                $results = $mModel->getMembersByFilter(array(
                    'id_plateforme' =>  $_POST['id'],
                    'id_personne'   =>  $_SESSION[SessionController::ID_PERSONNE]
                ));
                if($results[0]['actif'] == 0)
                    return '';
                $sess = new SessionController();
                $sess->changePlatform($_POST['id']);
                break;
            case 'see_activity':
                $template = GenerateUtils::getTemplate("PlatformActivity");
                
                return GenerateUtils::replaceStrings($template, array(
                   'platform_name'  =>  $_SESSION[SessionController::PLATFORM_NAME] 
                ));
                break;
        }    
    }

}
