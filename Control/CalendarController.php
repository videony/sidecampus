<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarController
 *
 * @author videony
 */
class CalendarController implements BodyController{
  
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PUBLIC = 'public';
    
    private $css = array(
        'js/fullcalendar/fullcalendar.css',
        'css/form.css',
        'css/Calendar.css'
    );
    private $js = array(

        'js/jquery-ui/ui/jquery.ui.tabs.js',
        'js/Moment.js',
        'js/fullcalendar/fullcalendar.min.js',
        'js/fullcalendar/lang/fr.js',
        'js/form.js',
        'js/plupload/plupload.full.min.js',
        'js/jquery-ui/ui/jquery.ui.dialog.js',
        'js/fileDownload.js',
       'js/Calendar.js'
    );
    
    public function canAccess() {
        return isset($_SESSION[SessionController::ID_PERSONNE]);
    }
    public function getTitle() {
        return 'Calendrier';
    }

    

    public function getContent() {
        $template = GenerateUtils::getTemplate('Calendar');
        
        // FORMULAIRE D'AJOUT
        $template = $this->handleForm($template);
        
        // VUE CALENDRIER
        $person_event_model = new PersonEventModel();
        $person_event_all = $person_event_model->getPersonEventByFilter(array(
            'int_adder' =>  $_SESSION[SessionController::ID_PERSONNE]
        ));
        // Si est affilié à une plateforme et a le droit de visualiser les évènements
        if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM
                && UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_SEE_PUBLIC_EVENTS"))
        {
            $platform_event_model = new PlatformEventModel();
            $platform_event_all = $platform_event_model->getPlatformEventsOfPerson($_SESSION[SessionController::ID_PERSONNE]);
            $all = array_merge($person_event_all, $platform_event_all);
        }
        else
        {
            $all = $person_event_all;
        }
        foreach($all as $key=>$event)
        {
            if(isset($event['id_plateforme']))
                $type = self::VISIBILITY_PUBLIC;
            else
                $type = self::VISIBILITY_PRIVATE;
            
            $all[$key] = array(
            'id'                =>      ($type == self::VISIBILITY_PUBLIC?
                                            'public_'.$event['id_platform_event']:'private_'.$event['id_person_event']),  
            'class_event_name'  =>      $type,
            'titre'             =>	str_replace("'", "\'", $event['tx_titre']),
            's_year'            =>	date('Y', strtotime($event['dt_begin'])),
            's_month'           =>	date('m', strtotime($event['dt_begin'])) - 1,
            's_day'             =>	date('d', strtotime($event['dt_begin'])),
            's_hour'            =>	date('H', strtotime($event['dt_begin'])),
            's_min'             =>	date('i', strtotime($event['dt_begin'])),
            'e_year'            =>	date('Y', strtotime($event['dt_end'])),
            'e_month'           =>	date('m', strtotime($event['dt_end'])) - 1,
            'e_day'             =>	date('d', strtotime($event['dt_end'])),
            'e_hour'            =>	date('H', strtotime($event['dt_end'])),
            'e_min'             =>	date('i', strtotime($event['dt_end']))
            );
        }
        $template = GenerateUtils::generateRS($template, $all, '', '###SUB_ITEM_CALENDAR_EVENT###');
        
        return $template;
    }
    private function handleForm($template) {
        // FORMULAIRE DE CREATION
        if(isset($_POST['sent']))
        {
            $template = GenerateUtils::replaceStrings($template, array("active_tab" => 1));
            $errors = $this->check();
            if(empty($errors))
            {
                // succès
                if($_POST['dt_fin'] == NULL)
                {
                    // Si pas de date de fin donnée, date de début est prise comme date de fin.
                    $_POST['dt_fin'] = $_POST['dt_debut'];
                    $_POST['hr_fin'] = $_POST['hr_debut'];
                    $_POST['mn_fin'] = $_POST['mn_debut'];
                }
                $debut = new DateTime();
                $debut->setTimestamp(strtotime($_POST['dt_debut']));
                $debut->setTime($_POST['hr_debut'], $_POST['mn_debut'], 0);
                $dt_debut = $debut->format('Y-m-d H:i');

                $fin = new DateTime();
                $fin->setTimestamp(strtotime($_POST['dt_fin']));
                $fin->setTime($_POST['hr_fin'], $_POST['mn_fin'], 0);
                $dt_fin = $fin->format('Y-m-d H:i');
                
                if(isset($_POST['visibilite']))
                    $platformid = $_POST['visibilite'];

                if($_SESSION[SessionController::CATEGORIE] == PageController::MEMBER_NO_DEFAULT_PLATFORM
                        || !isset($_POST['visibilite'])
                        || $_POST['visibilite'] == self::VISIBILITY_PRIVATE)
                {
                    $model = new PersonEventModel();
                    $return = $model->create($dt_fin, $dt_debut, $_SESSION[SessionController::ID_PERSONNE], 
                            $_POST['description'], $_POST['titre']);
                    $type = self::VISIBILITY_PRIVATE;
                }
                else
                {
                    $mmodel = new MemberModel();
                    $memberships = $mmodel->getMembersByFilter(array(
                        'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                        'id_plateforme' =>  $platformid
                    ));
                    
                    if(!empty($memberships) && UserPermissionModel::can($memberships[0]['id_membre'], 'CAN_ADD_PUBLIC_EVENT'))
                    {
                        $model = new PlatformEventModel();
                        $return = $model->create($dt_debut, $dt_fin, 
                                $_SESSION[SessionController::PLATFORM_ID],
                                $_SESSION[SessionController::ID_PERSONNE], 
                                $_POST['description'], $_POST['titre']);
                        $type = self::VISIBILITY_PUBLIC;
                    }
                    else
                        $return = FALSE;
                }
                if($return !== FALSE)
                {
                    $form = $this->getForm(array());
                    $successbox = FormUtils::successBox('Votre évènement a bien été créé.');
                    if(isset($_POST['visibilite'])
                            && $_POST['visibilite'] != self::VISIBILITY_PRIVATE)
                    {
                        NotificationModel::fireNotif(NotificationModel::NOTIF_ON_NEW_EVENT, 
                                'Nouvel évènement le '.$debut->format('d/m/Y').': '.$_POST['titre'],
                                'request.php?action=Calendar&event='.$return.'&type='.$type,
                                $platformid);
                    }
                    $form = GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $successbox);
                    $template = GenerateUtils::replaceStrings($template, array(
                        'active_tab' => 1,
                        'sub_add_form'  =>  $form
                    ));
                }
            }
            else
            {
                $form = $this->getForm($errors);
                $errorbox = FormUtils::errorBox();
                $form = GenerateUtils::replaceSubPart($form, "###SUB_INFOS###", $errorbox);
                $template = GenerateUtils::replaceStrings($template, array(
                    'active_tab' => 1,
                    'sub_add_form'  =>  $form
                ));
            }
        }
        else
        {
            $template = GenerateUtils::replaceStrings($template, array("active_tab" => 0));
            $form = $this->getForm(array());
            $template = GenerateUtils::replaceSubPart($template, '###SUB_ADD_FORM###', $form);
        }
        return $template;
    }
    public function check(){
        $errors = array();
        // Titre
        if(empty($_POST['titre'])) {
            $errors['titre'] = array('val' => '', 'status' => TRUE, 'reason' => 'Le titre est vide');
        }
        elseif(strlen($_POST['titre']) > 30) {
            $errors['titre'] = array('val' => $_POST['titre'], 'status' => TRUE, 'reason' => 'Maximum 30 caractères pour le titre');
        }
        // Description
        /*if(empty($_POST['description'])) {
            $errors['description'] = array('val' => '', 'status' => TRUE, 'reason' => 'Veuillez donner une description de l\'évènement');
        }*/
        // Dates
        $date_errors = array();
        if(preg_match('/([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $_POST['dt_debut']))
        {
            $infos = explode('-', $_POST['dt_debut']);
            if(!checkdate($infos[1], $infos[0], $infos[2])) {
                $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => TRUE, 'reason' => 'La date de début doit être une date valide');
            }
            else
            {
                if(!empty($_POST['dt_fin']))
                {
                    if(preg_match('/([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $_POST['dt_fin']))
                    {
                        $infos = explode('-', $_POST['dt_fin']);
                        if(!checkdate($infos[1], $infos[0], $infos[2])) {
                            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'La date de fin doit être une date valide');
                        }
                        if(strtotime($_POST['dt_fin']) < strtotime($_POST['dt_debut'])) {
                            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'La date de fin devrait être postérieure à la date de début');
                        }
                    }
                    else
                    {
                        $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => TRUE, 'reason' => 'Veuillez respecter le format de la date (jj-mm-aaaa)');
                    }
                }
            }
            
        }
        else
        {
            $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => TRUE, 'reason' => 'Veuillez respecter le format de la date (jj-mm-aaaa)');
        }
        // Heure de début
        if(empty($_POST['hr_debut']) || $_POST['hr_debut'] == 'HH') {
            $errors['hr_debut'] = array('val' => $_POST['hr_debut'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de commencement');
        }
        // Heure de fin (peut être laissée à vide sauf si date précisée)
        if(!empty($_POST['dt_fin'])) {
            if($_POST['hr_fin'] == 'HH')
                $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de fin');
            elseif($_POST['dt_debut'] == $_POST['dt_fin'] && $_POST['hr_debut'] > $_POST['hr_fin'])
                $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => TRUE, 'reason' => 'Veuillez choisir une heure de fin postérieure à l\'heure de début');
        }
        if(isset($errors['dt_debut']) || isset($errors['hr_debut'])) {
            $errors['debut']['reason'] = array();
            if(isset($errors['dt_debut']))
                $errors['debut']['reason'][] = $errors['dt_debut']['reason'];
            if(isset($errors['hr_debut']))
                $errors['debut']['reason'][] = $errors['hr_debut']['reason'];   
        }
        if(isset($errors['dt_fin']) || isset($errors['hr_fin'])) {
            $errors['fin']['reason'] = array();
            if(isset($errors['dt_fin']))
                $errors['fin']['reason'][] = $errors['dt_fin']['reason'];
            if(isset($errors['hr_fin']))
                $errors['fin']['reason'][] = $errors['hr_fin']['reason'];      
        }
        
        
        return $errors;
    }
    public function getForm($wrong_fields = array()) {
        
        $template = GenerateUtils::getTemplate('Calendar');
        $part = GenerateUtils::subPart($template, "###SUB_ADD_FORM###");
            
        if(empty($wrong_fields)) 
        {
            $titre = FormUtils::stringBox('titre', 'Titre de l\'évènement (max 30 caractères) *');
            $descr = FormUtils::textAreaBox('description', 'Description de l\'évènement', '', '100px');
            
            if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM)
            {
                $plmodel = new MemberModel();
                $platforms = $plmodel->getMembersByFilter(array(
                   'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE] 
                ));
                $visibilities = array(
                    'private'   =>  'Privée (visible uniquement par vous)'
                );
                foreach($platforms as $platform)
                {
                    if(UserPermissionModel::can($platform['id_membre'], 'CAN_ADD_PUBLIC_EVENT'))
                    {
                        $platfommodel = new PlatformModel($platform['id_plateforme']);
                        $platdata = $platfommodel->get();
                        $visibilities[$platform['id_plateforme']] = 'Publique sur '.$platdata['tx_nom'];
                    }
                }
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', $visibilities, 'private');
            }
            else
                $visib = '';
            $begin = FormUtils::dateTimeBox('debut', 'Commence ');
            $fin = FormUtils::dateTimeBox('fin', 'Finit ');
            $part = GenerateUtils::replaceSubPart($part, "###SUB_FORM###", $titre.$descr.$visib.$begin.$fin);
            return $part;
           
        }
        else
        {
            $wrong_fields = $this->processErrors($wrong_fields);
            
            $titre = FormUtils::stringBox('titre', 'Titre de l\'évènement (max 30 caractères) *', $wrong_fields['titre']['val'], $wrong_fields['titre']['status'], $wrong_fields['titre']['reason']);
            $descr = FormUtils::textAreaBox('description', 'Description de l\'évènement', $wrong_fields['description']['val'], '100px', $wrong_fields['description']['status'], $wrong_fields['description']['reason']);
            
            if($_SESSION[SessionController::CATEGORIE] != PageController::MEMBER_NO_DEFAULT_PLATFORM)
            {
                $plmodel = new MemberModel();
                $platforms = $plmodel->getMembersByFilter(array(
                   'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE] 
                ));
                $visibilities = array(
                    'private'   =>  'Privée (visible uniquement par vous)'
                );
                foreach($platforms as $platform)
                {
                    if(UserPermissionModel::can($platform['id_membre'], 'CAN_ADD_PUBLIC_EVENT'))
                    {
                        $platfommodel = new PlatformModel($platform['id_plateforme']);
                        $platdata = $platfommodel->get();
                        $visibilities[$platform['id_plateforme']] = 'Publique sur '.$platdata['tx_nom'];
                    }
                }
                $visib = FormUtils::selectBox('visibilite', 'Visiblité *', $visibilities, $wrong_fields['visibilite']['val'], $wrong_fields['visibilite']['status'], $wrong_fields['visibilite']['reason']);
            }
            else
                $visib = '';
            $begin = FormUtils::dateTimeBox('debut', 'Commence *', 
                    $wrong_fields['dt_debut']['val'], $wrong_fields['hr_debut']['val'], $wrong_fields['mn_debut']['val'], 
                    $wrong_fields['dt_debut']['status'], $wrong_fields['hr_debut']['status'], $wrong_fields['mn_debut']['status'], 
                    $wrong_fields['debut']['reason']);
            $fin = FormUtils::dateTimeBox('fin', 'Finit ', 
                    $wrong_fields['dt_fin']['val'], $wrong_fields['hr_fin']['val'], $wrong_fields['mn_fin']['val'], 
                    $wrong_fields['dt_fin']['status'], $wrong_fields['hr_fin']['status'], $wrong_fields['mn_fin']['status'], 
                    $wrong_fields['fin']['reason']);
            $part = GenerateUtils::replaceSubPart($part, "###SUB_FORM###", $titre.$descr.$visib.$begin.$fin);
            return $part;
        }
        return $template;
    }
    public function processErrors($errors) {
        if(!isset($errors['titre'])){
            $errors['titre'] = array('val' => $_POST['titre'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['description'])){
            $errors['description'] = array('val' => $_POST['description'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['visibilite']) && isset($_POST['visibilite'])){
            $errors['visibilite'] = array('val' => $_POST['visibilite'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['debut'])){
            $errors['debut']['reason'] = array();
        }
        if(!isset($errors['dt_debut'])){
            $errors['dt_debut'] = array('val' => $_POST['dt_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['hr_debut'])){
            $errors['hr_debut'] = array('val' => $_POST['hr_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['mn_debut'])){
            $errors['mn_debut'] = array('val' => $_POST['mn_debut'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['fin'])){
            $errors['fin']['reason'] = array();
        }
        if(!isset($errors['dt_fin'])){
            $errors['dt_fin'] = array('val' => $_POST['dt_fin'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['hr_fin'])){
            $errors['hr_fin'] = array('val' => $_POST['hr_fin'], 'status' => FALSE, 'reason'=>'');
        }
        if(!isset($errors['mn_fin'])){
            $errors['mn_fin'] = array('val' => $_POST['mn_fin'], 'status' => FALSE, 'reason'=>'');
        }
        
        return $errors;
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
            case 'see_full_event':
                $id = $_POST['id'];
                $type = $_POST['type'];
                
                if($type == self::VISIBILITY_PUBLIC)
                    $model = new PlatformEventModel($id);
                else
                    $model = new PersonEventModel($id);
                
                $infos = $model->get();
                if($infos == NULL)
                    return 'Evènement supprimé';
                
                $id_membre = null;
                // Peut visualiser l'évènement?
                if($type == self::VISIBILITY_PUBLIC)
                {
                    $mmodel = new MemberModel();
                    $membership = $mmodel->getMembersByFilter(array(
                        'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                        'id_plateforme' =>  $infos['id_plateforme']
                    ));
                    if(empty($membership))
                        return '';
                    else
                    {
                        $id_membre = $membership[0]['id_membre'];
                    }
                    if(!UserPermissionModel::can($id_membre, "CAN_SEE_PUBLIC_EVENTS"))
                        return '';
                }
                elseif($type == self::VISIBILITY_PRIVATE)
                {
                    if($infos['int_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                        return '';
                }
                
                
                $template = GenerateUtils::getTemplate('event');
                if(!isset($_SESSION[SessionController::ID_MEMBRE])
                        || !UserPermissionModel::can($id_membre, 'CAN_SEE_OTHERS_PROFILE'))
                    $infos['if_can_see_others_profile'] = FALSE;
                $infos['dt_begin'] = date('d-m-Y H:i', strtotime($infos['dt_begin']));
                $infos['dt_end'] = date('d-m-Y H:i', strtotime($infos['dt_end']));
                $infos['visibilite'] = ($type == self::VISIBILITY_PRIVATE?'privé':'public');
                $infos['tx_description'] = nl2br($infos['tx_description']);
                $infos['visibilite'] = $type;
                if($type == self::VISIBILITY_PUBLIC)
                    $infos['id_event'] = $infos['id_platform_event'];
                else
                    $infos['id_event'] = $infos['id_person_event'];
                
                $personModel = new PersonneModel($infos['int_adder']);
                $persondata = $personModel->get();
                $infos = array_merge($infos, $persondata);
                $infos['timestamp'] = time();
                
                if($type == self::VISIBILITY_PUBLIC)
                {
                    if(!UserPermissionModel::can($id_membre, 'CAN_REMOVE_OWN_EVENTS')
                            && $infos['int_adder'] == $_SESSION[SessionController::ID_PERSONNE])
                        $infos['if_can_delete'] = FALSE;
                    elseif(!UserPermissionModel::can($id_membre, 'CAN_REMOVE_OTHERS_EVENTS')
                            && $infos['int_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                    {
                        $infos['if_can_delete'] = FALSE;
                    }
                    if(!UserPermissionModel::can($id_membre, 'CAN_EDIT_OWN_EVENTS')
                            && $infos['int_adder'] == $_SESSION[SessionController::ID_PERSONNE])
                        $infos['if_can_edit'] = FALSE;
                    elseif(!UserPermissionModel::can($id_membre, 'CAN_EDIT_OTHERS_EVENTS')
                            && $infos['int_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                    {
                        $infos['if_can_edit'] = FALSE;
                    }
                }
                return GenerateUtils::replaceStrings($template, $infos);
                break;
            case 'change_dates':
                $id = $_POST['id'];
                $type = $_POST['type'];
                if($type == self::VISIBILITY_PUBLIC)
                    $model = new PlatformEventModel($id);
                else
                    $model = new PersonEventModel($id);
                $data = $model->get();
                
                // Peut mettre à jour?
                if($type == self::VISIBILITY_PUBLIC)
                {
                    $mmodel = new MemberModel();
                    $membership = $mmodel->getMembersByFilter(array(
                        'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                        'id_plateforme' =>  $data['id_plateforme']
                    ));
                    if(empty($membership))
                        return '';
                    $id_membre = $membership[0]['id_membre'];
                    
                    if($data['int_adder'] == $_SESSION[SessionController::ID_PERSONNE])
                    {
                        if(!UserPermissionModel::can($id_membre, 'CAN_EDIT_OWN_EVENTS'))
                            return 0; 
                    }
                    else
                    {
                        if(!UserPermissionModel::can($id_membre, 'CAN_EDIT_OTHERS_EVENTS'))
                            return 0; 
                    }
                }
                elseif($type == self::VISIBILITY_PRIVATE)
                {
                    if($data['int_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                        return '';
                }
                
                return $model->set(array(
                   'dt_begin'   => date('Y-m-d H:i:s', strtotime($_POST['sdate'])),
                    'dt_end'    =>  date('Y-m-d H:i:s', strtotime($_POST['edate'])) 
                ));
                break;
            case 'delete_event':
                $id = $_POST['id'];
                $type = $_POST['type'];
                if($type == self::VISIBILITY_PUBLIC)
                    $model = new PlatformEventModel($id);
                else
                    $model = new PersonEventModel($id);
                
                $data = $model->get();
                
                
                // Peut supprimer?
                if($type == self::VISIBILITY_PUBLIC)
                {
                    $mmodel = new MemberModel();
                    $membership = $mmodel->getMembersByFilter(array(
                        'id_personne'    =>  $_SESSION[SessionController::ID_PERSONNE],
                        'id_plateforme' =>  $data['id_plateforme']
                    ));
                    if(empty($membership))
                        return '';
                    $id_membre = $membership[0]['id_membre'];
                    
                    if($data['int_adder'] == $_SESSION[SessionController::ID_PERSONNE])
                    {
                        if(!UserPermissionModel::can($id_membre, 'CAN_REMOVE_OWN_EVENTS'))
                            return 0; 
                    }
                    else
                    {
                        if(!UserPermissionModel::can($id_membre, 'CAN_REMOVE_OTHERS_EVENTS'))
                            return 0; 
                    }
                    NotificationModel::fireNotif(NotificationModel::NOTIF_ON_DELETE_EVENT, 'Evenement supprimé: '.$data['tx_titre']);
                }
                elseif($type == self::VISIBILITY_PRIVATE)
                {
                    if($data['int_adder'] != $_SESSION[SessionController::ID_PERSONNE])
                        return '';
                }
                return $model->remove();
                break;
            case 'get_edit_link':
                return 'request.php?action=EventEdit&id='.$_POST['id'].'&type='.$_POST['type'];
                break;
            
            default:
                return '';
                break;
        }
    }

}
