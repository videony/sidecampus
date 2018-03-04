<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CGUController
 *
 * @author videony
 */
class CGUController implements BodyController{
  
    private $css = array(
        
    );
    private $js = array(
       
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return 'Conditions générales d\'utilisation';
    }

    

    public function getContent() {
        return GenerateUtils::getTemplate('CGU');
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
