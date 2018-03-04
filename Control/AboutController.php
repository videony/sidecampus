<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AboutController
 *
 * @author videony
 */
class AboutController implements BodyController{
  
    private $css = array(
        
        'css/Accueil.css'
    );
    private $js = array(
       
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'A propos';
    }

    

    public function getContent() {
        return GenerateUtils::getTemplate('About');
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
            default:
                return '';
                break;
        }
    }

}
