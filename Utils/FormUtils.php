<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormUtils
 *
 * @author videony
 */
class FormUtils {
    
    /**
     * 
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     */
    static function stringBox($name, $title, $val = '', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_STRING###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Crée une zone de texte.
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param String $height Hauteur de la zone de texte (ex: 300px)
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function textAreaBox($name, $title, $val = '', $height = '100px', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_TEXTAREA###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'height'              =>  $height,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Crée une zone de texte formaté 
     * /!\ Nécessite la librairie sceditor.js
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param String $height Hauteur de la zone de texte (ex: 300px)
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function textAreaBoxFormated($name, $title, $val = '', $height = '100px', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_FORMATED_TEXTAREA###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'height'              =>  $height,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Crée un champ de type date
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function dateBox($name, $title, $val = '', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_DATE###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title. ' (jj-mm-aaaa)',
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    static function dateTimeBox($name, $title, 
            $dateVal = '', $hrVal = 'HH', $mnVal = '00', 
            $dateError = FALSE, $hourError = FALSE, $minError = FALSE, 
            $reasons = array())
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_DATETIME###');
       
        if(!empty($reasons))
        {
            $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
            $reasonstemplate = GenerateUtils::subPart($part, '###SUB_EXPLAIN###');
            foreach($reasons as $key=>$reason)
            {
                $reasons[$key] = array('reason_text' => $reason);
            }
            $reasonstemplate = GenerateUtils::generateRS($reasonstemplate, $reasons);
            $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reasonstemplate));
        }
        else
        {
            $reasonpart = '';
        }
       
       $part = GenerateUtils::replaceStrings($part, array(
            'dt_setting_name'        =>  'dt_'.$name,
            'hr_setting_name'        =>  'hr_'.$name,
            'mn_setting_name'        =>  'mn_'.$name,
            'setting_description' =>  $title. ' (jj-mm-aaaa)',
            'setting_value'       =>  $dateVal,
            'DT_ERROR_CLASS'         =>  ($dateError==TRUE?'field_error':''),
            'HR_ERROR_CLASS'         =>  ($hourError==TRUE?'field_error':''),
            'MN_ERROR_CLASS'         =>  ($minError==TRUE?'field_error':''),
            'HR_'.$hrVal.'_SELECTED'    =>  'selected',
            'MN_'.$mnVal.'_SELECTED'    =>  'selected',
            'sub_explain'                =>  $reasonpart
       ));
       return GenerateUtils::replaceStrings($part, array(
            '###HR_HH_SELECTED###' => '', 
            '###HR_00_SELECTED###' => '', 
            '###HR_01_SELECTED###' => '', 
            '###HR_02_SELECTED###' => '', 
            '###HR_03_SELECTED###' => '', 
            '###HR_04_SELECTED###' => '', 
            '###HR_05_SELECTED###' => '', 
            '###HR_06_SELECTED###' => '', 
            '###HR_07_SELECTED###' => '', 
            '###HR_08_SELECTED###' => '', 
            '###HR_09_SELECTED###' => '', 
            '###HR_10_SELECTED###' => '', 
            '###HR_11_SELECTED###' => '', 
            '###HR_12_SELECTED###' => '', 
            '###HR_13_SELECTED###' => '', 
            '###HR_14_SELECTED###' => '', 
            '###HR_15_SELECTED###' => '', 
            '###HR_16_SELECTED###' => '', 
            '###HR_17_SELECTED###' => '', 
            '###HR_18_SELECTED###' => '', 
            '###HR_19_SELECTED###' => '', 
            '###HR_20_SELECTED###' => '', 
            '###HR_21_SELECTED###' => '', 
            '###HR_22_SELECTED###' => '', 
            '###HR_23_SELECTED###' => '', 
            '###MN_MM_SELECTED###' => '', 
            '###MN_00_SELECTED###' => '', 
            '###MN_05_SELECTED###' => '', 
            '###MN_10_SELECTED###' => '', 
            '###MN_15_SELECTED###' => '', 
            '###MN_20_SELECTED###' => '', 
            '###MN_25_SELECTED###' => '', 
            '###MN_30_SELECTED###' => '', 
            '###MN_35_SELECTED###' => '', 
            '###MN_40_SELECTED###' => '', 
            '###MN_45_SELECTED###' => '', 
            '###MN_50_SELECTED###' => '', 
            '###MN_55_SELECTED###' => '' 
       ));
       
    }
    /**
     * Champ de type password
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @param type $hash Le contenu sera hashé par javascript avant l'envoi du formulaire si mis à TRUE
     * @return type
     */
    static function passwordBox($name, $title, $val = '', $error = FALSE, $reason = '', $hash = TRUE)
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_PASSWORD###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart,
            'hash'                =>  ($hash == TRUE)?'hash':''
       ));
    }
    /**
     * Champ de type email
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function mailBox($name, $title, $val = '', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_EMAIL###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Champ de type telephone
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function telBox($name, $title, $val = '', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_TEL###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Champ numérique
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param Integer $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function intBox($name, $title, $val = 0, $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_INT###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    static function hiddenBox($name, $val) 
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_HIDDEN###');
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_value'       =>  $val,
       ));
    }
    /**
     * Case à cocher
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait à côté de la case à cocher en texte clair)
     * @param Boolean $val Valeur par défaut (TRUE = coché)
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     * @return type
     */
    static function boolBox($name, $title, $val = FALSE, $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_BOOLEAN###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
	   else
	   {
			$reasonpart = '';
	   }
       return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'checked'       =>  ($val==TRUE?'checked':''),
            'ERROR_CLASS'         =>  ($error==TRUE?'bool_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    static function selectBox($name, $title, $vals, $defaultIndex = 0, $error = FALSE, $reason = '')
    {
        $template = GenerateUtils::getTemplate('form');
        $part = GenerateUtils::subPart($template, '###SUB_WIDGET_SELECT###');
        if($reason != '')
        {
            $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
            $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
        }
        else
        {
            $reasonpart = '';
        }
        $markers = array();
        foreach($vals as $marker=>$val)
        {
            $markers[] = array(
                '###VALUE###' => $marker, 
                '###VALUETEXT###' => $val,
                '###SELECTED###'    =>  ($marker == $defaultIndex)?'selected':''
            );
        }
        $part = GenerateUtils::generateRS($part, $markers);
        return GenerateUtils::replaceStrings($part, array(
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'ERROR_CLASS'         =>  ($error==TRUE?'bool_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * 
     * @param String $name Nom (ID) du champ de type texte
     * @param String $title Titre (ce qui apparait au-dessus du champ en texte clair)
     * @param String $val Valeur par défaut
     * @param boolean $error Sera marqué comme champ contenant une faute si à TRUE
     * @param String $reason Raison pour laquelle le champ est marqué comme faux. Ne fonctionne que si $error = FALSE
     */
    static function captcha($name, $title, $val = '', $error = FALSE, $reason = '')
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_CAPTCHA###');
       if($reason != '')
       {
           $reasonpart = GenerateUtils::subPart($template, '###SUB_ERROR_REASON###');
           $reasonpart = GenerateUtils::replaceStrings($reasonpart, array('reason' => $reason));
       }
        else
        {
                     $reasonpart = '';
        }
       return GenerateUtils::replaceStrings($part, array(
            'timestamp'           =>    time(),
            'setting_name'        =>  $name,
            'setting_description' =>  $title,
            'setting_value'       =>  $val,
            'ERROR_CLASS'         =>  ($error==TRUE?'field_error':''),
            'sub_explain'         =>  $reasonpart
       ));
    }
    /**
     * Zone de texte décrivant un message d'erreur.
     * @param string $error_message Message d'erreur
     * @return type
     */
    static function errorBox($error_message = NULL)
    {
        if($error_message == NULL)
            $error_message = 'Un ou plusieurs champs semblent avoir été complétés de manière incorrecte. Veuillez rectifier '
                . 'ce qui est en rouge. Vous pouvez survoler ou cliquer sur les petites astérisques oranges pour connaitre la raison de l\'erreur.';
        $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_ERROR_MESSAGE###');
       return GenerateUtils::replaceStrings($part, array(
            'error_message'        =>  $error_message));
    }
    /**
     * Zone de texte décrivant un message de réussite
     * @param string $success_message Message de réussite
     * @return type
     */
    static function successBox($success_message = NULL)
    {
        if($success_message == NULL)
            $success_message = 'Formulaire envoyé avec succès.';
        $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_SUCCESS_MESSAGE###');
       return GenerateUtils::replaceStrings($part, array(
            'success_message'        =>  $success_message));
    }
    /**
     * Zone de texte décrivant un message informationnel
     * @param type $message Information à communiquer
     * @return type
     */
    static function infoBox($message)
    {
       $template = GenerateUtils::getTemplate('form');
       $part = GenerateUtils::subPart($template, '###SUB_WIDGET_INFO_MESSAGE###');
       return GenerateUtils::replaceStrings($part, array(
            'info_message'        =>  $message));
    }
    
}
