<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlatformAskingUsersController
 *
 * @author videony
 */
class UserManController implements BodyController{
  
    private $css = array(
        'css/form.css',
		'css/UserMan.css',
        'css/Tables.css'
    );
    private $js = array(
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
       'js/UserMan.js'
    );
    
    public function canAccess() {
        if(isset($_SESSION[SessionController::ID_MEMBRE]))
			return TRUE;
		elseif(isset($_SESSION[SessionController::ID_PERSONNE]) && isset($_REQUEST['action']) && isset($_REQUEST['id_person']))
			return ($_REQUEST['action'] == 'see_profile' && $_SESSION[SessionController::ID_PERSONNE] == $_REQUEST['id_person']);
		else
			return FALSE;
    }
    public function getTitle() {
        return 'Utilisateurs';
    }

    

    public function getContent() {
       $template = GenerateUtils::getTemplate('UserMan');
       $controller = new PlatformAskingUsersController();
       $markers['asking'] = $controller->getContent();
       $this->css = array_unique(array_merge($this->css, $controller->getCSS()));
       $this->js = array_unique(array_merge($this->js, $controller->getJS()));
        $markers['information'] = FormUtils::infoBox('Cliquez sur les en-têtes de colonne pour trier en ordre croissant '
                . 'ou décroissant. Tapez quelque chose dans les champs d\'en-tête pour filtrer');
        $template = GenerateUtils::replaceStrings($template, $markers);
        // Permissions
        $permissionmodel = new UserPermissionModel();
        $norights = $permissionmodel->getUserPermissionByFilter(array(
           'id_membre'  =>  $_SESSION[SessionController::ID_MEMBRE],
            'value'     =>  0
        ));
        $template = GenerateUtils::cleanForRights($template, $norights);
        
        
        $model = new MemberModel();
        $members = $model->getMembersByFilter(array(
           'id_plateforme'  =>  $_SESSION[SessionController::PLATFORM_ID] 
        ));
        
        foreach($members as $key=>$member)
        {
            if($member['id_personne'] == $_SESSION[SessionController::ID_PERSONNE])
            {
                unset($members[$key]);
            }
            else
            {
                $model = new PersonneModel($member['id_personne']);
                $personne = $model->get();
                $members[$key]['tx_nom'] = $personne['tx_nom'];
                $members[$key]['tx_prenom'] = $personne['tx_prenom'];
                $members[$key]['tx_email'] = $personne['tx_email'];
                $members[$key]['tx_login'] = $personne['tx_login'];
                if($member['hierarchie'] == MemberModel::MEMBER)
                {
                    $members[$key]['if_can_set_member'] = FALSE;
                    $members[$key]['tx_statut'] = 'Membre';
                    $members[$key]['class'] = 'member';
                }
                elseif($member['hierarchie'] == MemberModel::MODERATOR)
                {
                    $members[$key]['if_can_set_moderator'] = FALSE;
                    $members[$key]['tx_statut'] = 'Modérateur';
                    $members[$key]['class'] = 'moderator';
                }
                elseif($member['hierarchie'] == MemberModel::ADMIN)
                {
                    $members[$key]['if_can_set_admin'] = FALSE;
                    $members[$key]['tx_statut'] = 'Administrateur';
                    $members[$key]['class'] = 'admin';
                }
                if($member['actif'] == 1)
                    $members[$key]['if_can_unblock_user'] = FALSE;
                else
                {
                    $members[$key]['if_can_block_user'] = FALSE;
                    $members[$key]['class'] = 'blocked';
                }
                if(MemberModel::hasHigherHierarchy($member['id_membre'], $_SESSION[SessionController::ID_MEMBRE]))
                {
                    $members[$key]['if_can_set_member'] = FALSE;
                    $members[$key]['if_can_set_moderator'] = FALSE;
                    $members[$key]['if_can_set_admin'] = FALSE;
                    
                    $members[$key]['if_can_unblock_user'] = FALSE;
                    $members[$key]['if_can_block_user'] = FALSE;
                    
                    $members[$key]['if_can_reject_user'] = FALSE;
                    $members[$key]['if_can_edit_rights'] = FALSE;
                }
                if($personne['tx_profile_pic'] == 'default_profile_pic.png')
                    $members[$key]['profile_picture'] = 'media/pics/default_profile_pic.png';
                else
                    $members[$key]['profile_picture'] = 'dim.php?type=person_profile_pic&person='.$member['id_personne']
                        .'&timestamp='.time();
            }
        }
        
        $template = GenerateUtils::generateRS($template, $members);
        
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
            case 'permissions':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_RIGHTS"))
                        return '';
                $files_html = '';
                $event_html = '';
                $forum_html = '';
                $manage_html = '';
                $divers_html = '';
                $model = new UserPermissionModel();
                $permissions = $model->getUserPermissionByFilter(array(
                    'id_membre'     =>  $_POST['id']
                ));
                foreach($permissions as $key=>$perm)
                {
                    $pmodel = new PermissionModel($perm['permission_name']);
                    $permdata = $pmodel->get();
                    $html = FormUtils::boolBox($perm['permission_name'], 
                                                $permdata['tx_description'], 
                                                ($perm['value'] == 1)?TRUE:FALSE);
                    switch($permdata['section'])
                    {
                        case PermissionModel::SECTION_FILES:
                            $files_html .= $html;
                            break;
                        case PermissionModel::SECTION_EVENTS:
                            $event_html .= $html;
                            break;
                        case PermissionModel::SECTION_FORUM:
                            $forum_html .= $html;
                            break;
                        case PermissionModel::SECTION_PLATFORM_MANAGE:
                            $manage_html .= $html;
                            break;
                        default:
                            $divers_html .= $html;
                            break;
                        
                    }
                }
                $template = GenerateUtils::getTemplate('permissions');
                $template = GenerateUtils::replaceStrings($template, array(
                    'file_permissions'      =>  $files_html,
                    'event_permissions'      =>  $event_html,
                    'forum_permissions'     =>  $forum_html,
                    'manage_permissions'    =>  $manage_html,
                    'divers_permissions'    => $divers_html,
                    'id_membre'     =>  $_POST['id']
                ));
                return $template;
                break;
            case 'set_default_permissions':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_RIGHTS"))
                        return '';
                $model = new UserPermissionModel();
                $model->setDefaultPermissions($_POST['id']);
                $memberModel = new MemberModel($_POST['id']);
                $memberdata = $memberModel->get();
                $personModel = new PersonneModel($memberdata['id_personne']);
                $persondata = $personModel->get();
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_CHANGE_PERM_USER,
                    'Autorisations remises aux valeurs par défaut pour '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                break;
            case 'save_permissions':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_EDIT_RIGHTS"))
                        return '';
                $model = new UserPermissionModel();
                $permissions = $model->getUserPermissionByFilter(array(
                   'id_membre'      =>  $_POST['id'] 
                ));
                foreach($permissions as $permission)
                {
                    if(in_array($permission['permission_name'], $_POST['permissions']))
                    {
                        // permission granted
                        $userperm_mod = new UserPermissionModel($permission['id_up']);
                        $userperm_mod->set(array('value' => 1));
                    }
                    else
                    {
                        // permission not granted
                        $userperm_mod = new UserPermissionModel($permission['id_up']);
                        $userperm_mod->set(array('value' => 0));
                    }
                }
                $memberModel = new MemberModel($_POST['id']);
                $memberdata = $memberModel->get();
                $personModel = new PersonneModel($memberdata['id_personne']);
                $persondata = $personModel->get();
                NotificationModel::fireNotif(NotificationModel::NOTIF_ON_CHANGE_PERM_USER,
                    'Autorisations modifiées pour '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                break;
            case 'block_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_BLOCK_USER"))
                        return '';
                $model = new MemberModel($_POST['id']);
                $return = $model->set(array(
                    'actif' =>  0
                ));
                if($return == TRUE){
                    $memberdata = $model->get();
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_BLOCK_USER,
                    'Utilisateur bloqué: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;
            case 'unblock_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_UNBLOCK_USER"))
                        return '';
                $model = new MemberModel($_POST['id']);
                $return = $model->set(array(
                    'actif' =>  1
                ));
                if($return == TRUE){
                    $memberdata = $model->get();
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_UNBLOCK_USER,
                    'Utilisateur débloqué: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;
            case 'exclude_user':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REJECT_USER"))
                        return '';
                $model = new MemberModel($_POST['id']);
                $memberdata = $model->get();
                $return = $model->remove();
                if($return == TRUE){
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_EJECT_USER,
                    'Utilisateur exclu: '.$persondata['tx_prenom'].' '.$persondata['tx_nom']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;
            case 'set_member':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SET_MEMBER"))
                        return '';
               $model = new MemberModel($_POST['id']);
                $return = $model->set(array(
                    'hierarchie' => MemberModel::MEMBER
                ));
                if($return == TRUE){
                    $memberdata = $model->get();
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_SET_MEMBER,
                    'Utilisateur nommé comme simple membre: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                    $settingmodel = new UserSettingModel();
                    $settingmodel->setDefaultSettings($_POST['id']);
                    echo 'OK';
                }
                else
                    echo 'KO'; 
                break;
            case 'set_admin':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SET_ADMIN"))
                        return '';
                $model = new MemberModel($_POST['id']);
                $return = $model->set(array(
                    'hierarchie' => MemberModel::ADMIN
                ));
                if($return == TRUE){    
                    $memberdata = $model->get();
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();                
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_SET_ADMIN,
                    'Utilisateur nommé comme administrateur: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                    $settingmodel = new UserSettingModel();
                    $settingmodel->setDefaultSettings($_POST['id']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;
                            
            case 'set_mod':
                if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SET_MODERATOR"))
                        return '';
                $model = new MemberModel($_POST['id']);
                $return = $model->set(array(
                    'hierarchie' => MemberModel::MODERATOR
                ));
                if($return == TRUE){
                    $memberdata = $model->get();
                    $personModel = new PersonneModel($memberdata['id_personne']);
                    $persondata = $personModel->get();
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_SET_MODERATOR,
                    'Utilisateur nommé comme modérateur: '.$persondata['tx_prenom'].' '.$persondata['tx_nom'],
                        'request.php?action=UserMan#'.$_POST['id']);
                    $settingmodel = new UserSettingModel();
                    $settingmodel->setDefaultSettings($_POST['id']);
                    echo 'OK';
                }
                else
                    echo 'KO';
                break;    
            case 'see_profile':
                
                $template = GenerateUtils::getTemplate('profile');
                if(isset($_POST['id_member']))
                {
                    $mModel = new MemberModel($_POST['id_member']);
                    $mdata = $mModel->get();
                    $personid = $mdata['id_personne'];
                }
                elseif(isset($_POST['id_demande']))
                {
                    $dModel = new DemandeModel($_POST['id_demande']);
                    $ddata = $dModel->get();
                    $personid = $ddata['id_personne'];
                }
                elseif(isset($_POST['id_person']))
                {
                    $personid = $_POST['id_person'];
                }
                if($personid != $_SESSION[SessionController::ID_PERSONNE]) {
                    if(!UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SEE_OTHERS_PROFILE"))
                        return '';
                }
                
                $pModel = new PersonneModel($personid);
                $person = $pModel->get();
                if($person['dt_naissance'] == NULL)
                    $person['dt_naissance'] = '/';
                else
                    $person['dt_naissance'] = date('d-m-Y', strtotime($person['dt_naissance']));
                $person['tx_login'] = strtoupper($person['tx_login']);
                $person['id_personne'] = $personid;
                return GenerateUtils::replaceStrings($template, $person);
                break;
        }
    }

}
