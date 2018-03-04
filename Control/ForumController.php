<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForumController
 *
 */
class ForumController implements BodyController{
  
    private $css = array(
        'css/form.css',
        'css/Tables.css',
        'css/Forum.css'
    );
    private $js = array(
        'js/form.js',
       'js/jquery.tablesorter.min.js',
        'js/jquery.tablesorter.widgets.js',
        'js/Table.js',
       'js/Forum.js'
    );
    
    public function canAccess() {
        return isset($_SESSION[SessionController::ID_MEMBRE]);
    }
    public function getTitle() {
        return 'Forum';
    }

    

    public function getContent() {


        $template = GenerateUtils::getTemplate('Forum');

        $model = new ForumCategoriesModel($_SESSION[SessionController::PLATFORM_ID]);

        //Récuperer sujets en fonction de la plateforme
        $categories = $model->getForumCategoriesByFilterWithUserName(array(                
            'plateforme_id' => $_SESSION[SessionController::PLATFORM_ID]
        ));

        foreach($categories as $key=>$data)
        {
            $categories[$key]['date_last_post']  = date('d-m-Y H:i:s', strtotime($categories[$key]['date_last_post']));
            if($data['topic_cnt'] == null)
                $categories[$key]['topic_cnt'] = 0;
        }
        
        
        $template = GenerateUtils::generateRS($template, $categories);
        
         
        
        //permissions
        $template = $this::setPermissions($template, $categories);        

        return $template;  

//        }
        
        
    }
    
    public function setPermissions($template, $categories){        
        $permissionmodel = new UserPermissionModel();

        $resultCan = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_CREATE_CATEGORY');

        if(empty($resultCan)){
            $template = GenerateUtils::emptyPart($template, "IF_CAN_CREATE_CATEGORY");
        }
        
        
        $resultDelOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OWN_CATEGORY');
        $resultDelOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_REMOVE_OTHERS_CATEGORY');

        $resultEditOwn = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OWN_CATEGORY');
        $resultEditOthers = $permissionmodel->can($_SESSION[SessionController::ID_MEMBRE], 'CAN_EDIT_OTHERS_CATEGORY');
        
        foreach($categories as $key=>$data)
        {
            if($categories[$key]['user_id'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultDelOwn))
                    $template = GenerateUtils::emptyPart($template, "CATDEL".$categories[$key]['id_categorie']);
            }
            if($categories[$key]['user_id'] != $_SESSION[SessionController::ID_MEMBRE])
            {
                 if(empty($resultDelOthers))
                    $template = GenerateUtils::emptyPart($template, "CATDEL".$categories[$key]['id_categorie']);
            }
            if($categories[$key]['user_id'] == $_SESSION[SessionController::ID_MEMBRE])
            {
                if(empty($resultEditOwn))
                    $template = GenerateUtils::emptyPart($template, "CATED".$categories[$key]['id_categorie']);
            }
            if($categories[$key]['user_id'] != $_SESSION[SessionController::ID_MEMBRE])
            {
                 if(empty($resultEditOthers))
                    $template = GenerateUtils::emptyPart($template, "CATED".$categories[$key]['id_categorie']);
            }           
        }
        
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
                    case 'delete_category': 
                        $category_model = new ForumCategoriesModel();                
                        if(!($category_model->createdBy($_SESSION[SessionController::ID_MEMBRE],$_POST['id'])))
                        {
                            //He did not created the topic
                            if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OTHERS_CATEGORY")))
                                $return = FALSE;
                            else{
                                $category_model = new ForumCategoriesModel($_POST['id']);
                                $data = $category_model->get();
                                $return = $category_model->remove();
                            }
                        } else{ 
                            if(!(UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], "CAN_REMOVE_OWN_CATEGORY")))
                                $return = FALSE; 
                            else{
                                $category_model = new ForumCategoriesModel($_POST['id']);
                                $data = $category_model->get();
                                $return = $category_model->remove();
                            }
                        }
                       
                        if($return == TRUE){                            
                            echo 'OK';
                            NotificationModel::fireNotif(NotificationModel::NOTIF_ON_DELETE_CATEGORY,
                                    'Catégorie supprimée: '.$data['nom_categorie']);
                        }                            
                        else
                            echo 'KO';
                        break;            

//                    case 'edit_category':  
//                        $model = new ForumCategoriesModel($_POST['id']);
//                        $return = new ForumCategoriesEditController().getContent();
//                        if($return == TRUE)
//                            echo 'OK';
//                        else
//                            echo 'KO';
//                        break;   
                }
    }
    
}




