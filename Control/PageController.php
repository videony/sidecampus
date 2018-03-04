<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

/**
 * Description of IndexController
 *
 * @author videony
 */
class PageController {
    //put your code here
    const ALL = 0;
    const BANNER = 1;
    const MENU = 2;
    const BODY = 3;
    const FOOTER = 4;
    
    const VISITOR                       = 1000;
    const MEMBER_NOT_CONFIRMED          = 1001;
    const MEMBER_NO_DEFAULT_PLATFORM    = 1002;
    const MEMBER_DEFAULT_PLATFORM       = 1003;
    const ADMIN_PLATFORM                = 1004;
    
    private $type;
    private $bodycontroller;
    
    private $default_js = array (
        'js/jquery.js',
        'js/jquery-ui/ui/jquery.ui.core.js',
        'js/jquery-ui.custom.min.js',
        'js/jquery-ui/ui/jquery.ui.widget.js',
	'js/fancybox/jquery.fancybox-1.3.4.js',
        'js/jquery-ui/ui/jquery.ui.position.js',
        'js/jquery-ui/ui/jquery.ui.tooltip.js',
        'js/jquery-ui/ui/jquery.ui.effect.js',
        'js/jquery-ui/ui/jquery.ui.effect-explode.js',
        'js/jquery-ui/ui/jquery.ui.effect-fade.js',
        'js/jquery-ui/ui/jquery.ui.effect-slide.js',
        'js/jquery-ui/ui/jquery.ui.button.js',
        'js/jquery-ui/ui/jquery.ui.effect-blind.js',
        'js/jquery-ui/ui/jquery.ui.datepicker.js',
        'js/jquery.mCustomScrollbar.concat.min.js',
        'js/jquery.noty.packaged.min.js',
        'js/sha1.js',
        'js/jquery.tooltipster.min.js',
        'js/jquery.magnific-popup.js',
        'js/Page.js'
    );
    private $default_css = array(
        'css/cascade/css/cascade/production/build-full.min.css',
        'css/jquery-ui.min.css',
		'js/fancybox/jquery.fancybox-1.3.4.css',
		'js/jquery-ui/themes/redmond/jquery.ui.all.css',
		'js/jquery-ui/themes/redmond/jquery.ui.core.css',
        'css/jquery.mCustomScrollbar.css',
        'css/tooltip-themes/tooltipster-light.css',
        'css/magnific-popup.css',
        'css/UserMan.css',
        'css/cascade/css/site.css',
        'css/main.css'
    );
    
    
    private $menu_items = array(
        self::VISITOR => array(
            array('menu_item_id' => 'Accueil', 'menu_item_text' =>  'ACCUEIL'),
            //array('menu_item_id' => 'Demo', 'menu_item_text' =>  'DEMONSTRATION'),
            array('menu_item_id' => 'Contact', 'menu_item_text' =>  'CONTACT'),
            array('menu_item_id' => 'About', 'menu_item_text' =>  'A PROPOS')
        ),
        self::MEMBER_NOT_CONFIRMED => array(
            array('menu_item_id' => 'Accueil', 'menu_item_text' =>  'ACCUEIL'),
            //array('menu_item_id' => 'Demo', 'menu_item_text' =>  'DEMONSTRATION'),
            array('menu_item_id' => 'Contact', 'menu_item_text' =>  'CONTACT'),
            array('menu_item_id' => 'About', 'menu_item_text' =>  'A PROPOS')
        ),
        self::MEMBER_NO_DEFAULT_PLATFORM => array(
            array('menu_item_id' => 'Tuto', 'menu_item_text' =>  'MANUEL D\'UTILISATION'),
            array('menu_item_id' => 'Calendar', 'menu_item_text' =>  'CALENDRIER'),
            array('menu_item_id' => 'Todo', 'menu_item_text' =>  'NOTES'),
            array('menu_item_id' => 'PlatformList', 'menu_item_text' =>  'PLATEFORMES')
        ),
        self::MEMBER_DEFAULT_PLATFORM => array(
            array('menu_item_id' => 'Files', 'menu_item_text' =>  'FICHIERS'),
            array('menu_item_id' => 'Calendar', 'menu_item_text' =>  'CALENDRIER'),
            array('menu_item_id' => 'Forum', 'menu_item_text' =>  'FORUM'),
            array('menu_item_id' => 'Todo', 'menu_item_text' =>  'NOTES'),
            array('menu_item_id' => 'PlatformList', 'menu_item_text' =>  'PLATEFORMES')
        ),
        self::ADMIN_PLATFORM => array(
            array('menu_item_id' => 'Files', 'menu_item_text' =>  'FICHIERS'),
            array('menu_item_id' => 'Calendar', 'menu_item_text' =>  'CALENDRIER'),
            array('menu_item_id' => 'Forum', 'menu_item_text' =>  'FORUM'),
            array('menu_item_id' => 'Todo', 'menu_item_text' =>  'NOTES'),
            array('menu_item_id' => 'PlatformList', 'menu_item_text' =>  'PLATEFORMES')
        )
      );
    private $icon_items = array(
        self::VISITOR => array(
            
        ),
        self::MEMBER_NOT_CONFIRMED => array(
            
        ),
        self::MEMBER_NO_DEFAULT_PLATFORM => array(
            array('menu_item_id' => 'Disconnect', 'img' =>  'door_in.png', 'description' => 'Se déconnecter')
            
        ),
        self::MEMBER_DEFAULT_PLATFORM => array(
            'home'   =>  array('menu_item_id' => 'ConnectedAccueil', 'img' =>  'house.png', 'description' => 'Accueil'),
            'changep'   =>  array('menu_item_id' => 'PlatformConnected', 'img' =>  'bricks.png', 'description' => 'Changer de plateforme'),
            'editp'     =>  array('menu_item_id' => 'PlatformMan', 'img' =>  'brick_edit.png', 'description' => 'Gérer la plateforme'),
            'asking'    =>  array('menu_item_id' => 'PlatformAskingUsers', 'img' =>  'hourglass.png', 'description' => 'Utilisateurs en demande d\'admission'),
            'editu'     =>  array('menu_item_id' => 'UserMan', 'img' =>  'group.png', 'description' => 'Utilisateurs de la plateforme'),
            'logout'    =>  array('menu_item_id' => 'Disconnect', 'img' =>  'door_in.png', 'description' => 'Se déconnecter')
       ),
        self::ADMIN_PLATFORM => array(
            'home'   =>  array('menu_item_id' => 'ConnectedAccueil', 'img' =>  'house.png', 'description' => 'Accueil'),
            'changep'   =>  array('menu_item_id' => 'PlatformConnected', 'img' =>  'bricks.png', 'description' => 'Changer de plateforme'),
            'editp'     =>  array('menu_item_id' => 'PlatformMan', 'img' =>  'brick_edit.png', 'description' => 'Gérer la plateforme'),
            'editu'     =>  array('menu_item_id' => 'UserMan', 'img' =>  'group.png', 'description' => 'Utilisateurs de la plateforme'),
            'asking'    =>  array('menu_item_id' => 'PlatformAskingUsers', 'img' =>  'hourglass.png', 'description' => 'Utilisateurs en demande d\'admission'),
            'logout'    =>  array('menu_item_id' => 'Disconnect', 'img' =>  'door_in.png', 'description' => 'Se déconnecter')
        )
      );
    
    /**
     * Construit la page. Détermine quelle bodycontroller appeler
     * @param type $type
     */
    public function __construct($type = self::ALL) 
    {
        $this->type = $type;
        
        // Action par défaut
        if(!isset($_REQUEST['action']) || !class_exists($_REQUEST['action'].'Controller'))
        {
            $_REQUEST['action'] = $this->getDefaultAction();
        }
        // Classe à appeler
        $class = $_REQUEST['action'].'Controller';
        if(in_array('BodyController', class_implements($class)))
            $this->bodycontroller = new $class();
        else
            $this->bodycontroller = NULL;
    }
    /**
     * Action par défaut suivant le type d'utilisateur du site.
     * @return string
     */
    public function getDefaultAction()
    {
        if($_SESSION[SessionController::CATEGORIE] == self::VISITOR)
            return 'Accueil';
        elseif($_SESSION[SessionController::CATEGORIE] == self::MEMBER_NOT_CONFIRMED)
            return 'Accueil';
        elseif($_SESSION[SessionController::CATEGORIE] == self::MEMBER_NO_DEFAULT_PLATFORM)
            return 'Accueil';
        elseif($_SESSION[SessionController::CATEGORIE] == self::MEMBER_DEFAULT_PLATFORM)
            return 'ConnectedAccueil';
        elseif($_SESSION[SessionController::CATEGORIE] == self::ADMIN_PLATFORM)
            return 'ConnectedAccueil';
        else
            return 'Accueil';
    }
    /**
     * Génère le HTML de la page
     * @return type
     */
    public function display()
    {
        //$_SESSION['r'] = $_SESSION['r'].method();
        switch($this->type)
        {
            case self::ALL :
                $page_template = $this->getPageTemplate();
                $page_template = $this->generateBody($page_template);
                $page_template = $this->generateBanner($page_template);
                $page_template = $this->generateMenu($page_template);
                $page_template = $this->generateFooter($page_template);
                $page_template = $this->generateCSS($page_template);
                $page_template = $this->generateJS($page_template);
                return $page_template;
                break;
        }
    }
    /**
     * Génère la bannière de la page ainsi que les icônes du dessus sur base $this->icon_items
     * @param type $template
     * @return type
     */
    private function generateBanner($template)
    {
        $markers = array();
        $markers['IF_NEED_PLATFORM_LINK'] = FALSE;
        $markers['###IF_PLATFORM_TITLE###'] = FALSE;
        $markers['###IF_CREATE_PLATFORM###'] = ConfigUtils::isPlaformCreateEnabled();
        
        if(isset($_SESSION[SessionController::USERNAME]))
        {
            $markers['###IF_CONNECTED###'] = TRUE;
            $markers['###IF_NOT_CONNECTED_MENU###'] = FALSE;
            $markers['###IF_NOT_CONNECTED###'] = FALSE;
            $markers['###USER###'] = $_SESSION[SessionController::USERNAME];
            $markers['###MAIN_TITLE###'] = ConfigUtils::get('website.title');
            $markers['###ID_PERSONNE###'] = $_SESSION[SessionController::ID_PERSONNE];
            $markers['###TIMESTAMP###'] = time();
            
            $markers['utilisateur'] = strtoupper($_SESSION[SessionController::USERNAME]);
            if(isset($_SESSION[SessionController::PLATFORM_NAME]))
            {
                $markers['###PLATFORM_NAME###'] = strtoupper($_SESSION[SessionController::PLATFORM_NAME]);
                $markers['###PLATFORM_ID###'] = $_SESSION[SessionController::PLATFORM_ID];
                $markers['###IF_PLATFORM###'] = TRUE;
                $markers['###IF_PLATFORM_TITLE###'] = TRUE;
                $markers['###IF_PLATFORM_MENU###'] = TRUE;
                
                // Cacher menu?
                if($_SESSION[SessionController::HIDE_SIDE_MENU])
                {
                    $markers['TRANSPARENT'] = 'slight-transparent';
                    $markers['ICON_SIDE_MENU'] = 'icon-angle-right';
                    $markers['INVISIBLE'] = 'force-invisible';
                }
                else
                {
                    $markers['TRANSPARENT'] = '';
                    $markers['ICON_SIDE_MENU'] = 'icon-angle-left';
                    $markers['INVISIBLE'] = '';
                }
                
                if(strlen($_SESSION[SessionController::PLATFORM_NAME]) < 30)
                        $markers['plateforme'] = $_SESSION[SessionController::PLATFORM_NAME];
                else
                        $markers['plateforme'] = substr($_SESSION[SessionController::PLATFORM_NAME], 0, 30).'...';
                // Notifications
                $markers['nb_notifs'] = $nbnotifs = NotificationModel::getNbNotif();
                $markers['nb_total_notifs'] = NotificationModel::getTotalNbNotif();
                $notifsarray = NotificationModel::getNotifsOfMembre($markers['nb_notifs']);
                $compteur = 1;
                foreach($notifsarray as $notifkey=>$notifdata)
                {
                    $notifsarray[$notifkey]['notif_date'] = date('d-m-Y H:i', strtotime($notifdata['notif_date']));
                    $notifsarray[$notifkey]['notif_section'] = str_replace(' ', '_', $notifdata['notif_section']);
                    if($compteur > $nbnotifs)
                        $notifsarray[$notifkey]['is_read'] = "read";
                    else
                        $notifsarray[$notifkey]['is_read'] = "notread";
                    $compteur++;
                }
                $notifications = GenerateUtils::subPart($template, "###SUB_NOTIFICATIONS###");
                $notifications = GenerateUtils::generateRS($notifications, $notifsarray);
                $markers['sub_notifications'] = $notifications;
            }
            else
            {
                $markers['###IF_PLATFORM###'] = FALSE;
                $markers['###IF_PLATFORM_MENU###'] = FALSE;
                $markers['IF_NEED_PLATFORM_LINK'] = TRUE;
            }
            
        }
        else
        {
            $markers['###IF_CONNECTED###'] = FALSE;
            $markers['###IF_PLATFORM###'] = FALSE;
                $markers['###IF_PLATFORM_MENU###'] = FALSE;
            $markers['###IF_NOT_CONNECTED###'] = TRUE;
            $markers['###IF_PROFILE_PIC###'] = FALSE;
            
            $markers['###MAIN_TITLE###'] = ConfigUtils::get('website.title');
        }
        // Annonces
        $annmodel = new AnnonceModel();
        $anncount = $annmodel->getAnnoncesCount();
        if($anncount == 0)
        {
            $markers['if_annonces_count'] = FALSE;
        }
        else
        {
            $markers['nb_annonces'] = $anncount;
        }
        
        
        $markers['###HOME_PAGE###'] = "request.php?action=".$this->getDefaultAction();
        $markers['###LOGO_LOCATION###'] = ConfigUtils::get('website.logo');
        // Changement de paramètres session => notify de l'utilisateur

        if(isset($_SESSION[SessionController::SESSION_CHANGE_REASON]))
        {
            $markers['###IF_SESSION_MESSAGE###'] = TRUE;
            $markers['session_message'] = $_SESSION[SessionController::SESSION_CHANGE_REASON];
            unset($_SESSION[SessionController::SESSION_CHANGE_REASON]);
        }
        else
        {
            $markers['###IF_SESSION_MESSAGE###'] = FALSE;
        }
        
        // Icones en haut à gauche
        if($_SESSION[SessionController::CATEGORIE] == self::ADMIN_PLATFORM
                || $_SESSION[SessionController::CATEGORIE] == self::MEMBER_DEFAULT_PLATFORM)
        {
            // Droits
            // Permissions
            $permissionmodel = new UserPermissionModel();
            $norights = $permissionmodel->getUserPermissionByFilter(array(
               'id_membre'  =>  $_SESSION[SessionController::ID_MEMBRE],
                'value'     =>  0
            ));
            $template = GenerateUtils::cleanForRights($template, $norights);
        }
        $menu = GenerateUtils::subPart($template, '###SUB_ICONS###');
        $generated = GenerateUtils::generateRS($menu, $this->icon_items[$_SESSION[SessionController::CATEGORIE]]);
        $template = GenerateUtils::replaceSubPart($template, '###SUB_ICONS###', $generated);
        
        // Popup notif pour dire sur quelle plateforme on est
        if(!isset($_SESSION[SessionController::HAS_SEEN_WHICH_PLATFORM])
                || $_SESSION[SessionController::HAS_SEEN_WHICH_PLATFORM] === TRUE)
        {
            $markers['if_has_not_seen_which_platform'] = FALSE;
        }
        else
        {
            $_SESSION[SessionController::HAS_SEEN_WHICH_PLATFORM] = TRUE;
        }
        
        return GenerateUtils::replaceStrings($template, $markers);
    }
    /**
     * Génère le menu de la page sur base de $this->menu_items
     * @param type $template
     * @return type
     */
    private function generateMenu($template)
    {
        return $template;
        /*if(isset($_SESSION[SessionController::ID_MEMBRE])
                && !UserPermissionModel::can($_SESSION[SessionController::ID_MEMBRE], 'CAN_SEE_FILES'))
        {
            unset($this->menu_items[self::MEMBER_DEFAULT_PLATFORM][0]);
            unset($this->menu_items[self::ADMIN_PLATFORM][0]);
        }
        $menu = GenerateUtils::subPart($template, '###SUB_MENU###');
        $generated = GenerateUtils::generateRS($menu, $this->menu_items[$_SESSION[SessionController::CATEGORIE]]);
        return GenerateUtils::replaceSubPart($template, '###SUB_MENU###', $generated);*/
    }
    /**
     * Génère le corps. Celui-ci est pris en charge suivant l'action. 
     * Par exemple, si $_REQUEST['action'] = 'Accueil', il appellera AccueilController.
     * Si l'utilisateur n'a pas le droit de consulter cette classe, la page par défaut sera renvoyée.
     * @param type $template
     * @return type
     */
    private function generateBody($template)
    {
        if($this->bodycontroller != NULL)
        {
            if(!$this->bodycontroller->canAccess()) 
            {
                // Classe à appeler
                $class = $this->getDefaultAction().'Controller';
                $this->bodycontroller = new $class();
            }
            $markers['###SUB_BODY###'] = $this->bodycontroller->getContent();
            $markers['###HTML_PAGE###'] = $this->bodycontroller->getTitle();
            if(isset($_SESSION[SessionController::PLATFORM_ID]))
            {
                $nbnotifs = NotificationModel::getTotalNbNotif();
                if($nbnotifs > 0)
                    $markers['###HTML_PAGE###'] = "[".$nbnotifs."] ".$markers['###HTML_PAGE###'];
            }
            $markers['###WEB_ROOT###'] = WEB_ROOT;
            return GenerateUtils::replaceStrings($template, $markers);
        }
        else
        {
            return $template;
        }
    }
    /**
     * Génère le bas de page
     * @param type $template
     * @return type
     */
    private function generateFooter($template)
    {
        return $template;
    }
    /**
     * Génère la liste des fichiers CSS à inclure. (Défaut + ceux du bodycontroller)
     * @param type $template
     * @return type
     */
    private function generateCSS($template)
    {
        // 1 - GET ALL CSS FILES
        $cssfiles = array();
        foreach($this->default_css as $key=>$css)
        {
            $cssfiles[] = WEB_ROOT.$css;
        }
        if($this->bodycontroller != NULL)
        {
            $morecss = $this->bodycontroller->getCSS();
            foreach($morecss as $key=>$morecss)
            {
                $cssfiles[] = WEB_ROOT.$morecss;
            }
        }
        
        // 2 - GENERATE
        $html = GenerateUtils::subPart($template, '###SUB_CSS###');
        $html = GenerateUtils::generateRS($html, $cssfiles);
        return GenerateUtils::replaceSubPart($template, '###SUB_CSS###', $html);
    }
    /**
     * Génère la liste des fichiers Javascript à inclure. (Défaut + ceux du bodycontroller)
     * @param type $template
     * @return type
     */
    private function generateJS($template)
    {
        // 0 - DYNAMIC ADD
        if($_SESSION[SessionController::CATEGORIE] == self::MEMBER_DEFAULT_PLATFORM
                || $_SESSION[SessionController::CATEGORIE] == self::ADMIN_PLATFORM)
        {
            $this->default_js[] = 'js/notifications.js';
        }
        // 1 - GET ALL JS FILES
        $jsfiles = array();
        foreach($this->default_js as $key=>$js)
        {
            $jsfiles[] = WEB_ROOT.$js;
        }
        if($this->bodycontroller != NULL)
        {
            $morejs = $this->bodycontroller->getJS();
            foreach($morejs as $key=>$morejs)
            {
                $jsfiles[] = WEB_ROOT.$morejs;
            }
        }
        
        // 2 - GENERATE
        $html = GenerateUtils::subPart($template, '###SUB_JS###');
        $html = GenerateUtils::generateRS($html, $jsfiles);
        return GenerateUtils::replaceSubPart($template, '###SUB_JS###', $html);
    }
    /**
     * Template d'une page HTML du site
     * @return type
     */
    private function getPageTemplate()
    {
        return file_get_contents(WEB_ROOT.'View/Page.html');
    }
    /**
     * Page d'interdiction d'accès.
     * @return type
     */
    private function getForbiddenTemplate()
    {
            return file_get_contents(WEB_ROOT.'View/Forbidden.html');
    }
}

?>
