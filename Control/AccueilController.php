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
class AccueilController implements BodyController{
  
    private $css = array(
        'css/ConnectedAccueil.css',
        'css/Accueil.css'
    );
    private $js = array(
        'js/jquery.als-1.7.min.js',
       'js/Accueil.js'
    );
    
    public function canAccess() {
        return TRUE;
    }
    public function getTitle() {
        return ConfigUtils::get('website.title');
    }

    

    public function getContent() {
        return GenerateUtils::replaceStrings(GenerateUtils::getTemplate('Accueil'), array(
            'instance_name'  => ConfigUtils::get('website.title')
        ));
    }

    public function getJS() {
        return $this->js;
    }
    public function getCSS() {
        return $this->css;
    }

    public function handlePostRequest() {
        
    }

}
