<?php
if(!defined('WEB_ROOT'))
    define('WEB_ROOT', '../');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once WEB_ROOT.'Model/DB.php';
/**
 * Description of SessionController
 *
 * @author videony
 * Manage user session.
 */
class SessionController {
    //put your code here
    const SET_DEFAULT = 1;
    const LOGIN = 2;
    const LOGOUT = 3;
    const NOACTION = 4;
    const JUST_INSTANTIATE = 5;
    
    const TIMEOUT_IN_MINUTES = 30;
    const LAST_ACTIVITY = "last_activity";
    
    const SELECTION_COPY        = 1;
    const SELECTION_CUT         = 2;
    const SELECTION_DOWNLOAD    = 3;
    
    const TWO_PER_LINE              = 'perline2';
    const THREE_PER_LINE            = 'perline3';
    
    const ID_PERSONNE               = "id_user";
    const ID_MEMBRE                 = "id_membre";
    const NOTIFICATION_MEMBER_ID  = "notification_member_id";
    const CATEGORIE                 = "categorie";
    const LAST_LOGIN_TRY            = "last_login_try";
    const ATTEMPTS                  = "attempts";
    const USERNAME                  = "login";
    const ENABLED                   = "enabled";
    const PLATFORM_ID               = "platform_id";
    const NOTIFICATION_PLATFORM_ID  = "notification_platform_id";
    const PLATFORM_NAME             = "platform_name";
    const HIERARCHIE                = "hierarchie";
    const CURRENT_FOLDER            = "current_folder";
    const CURRENT_PERSONAL_FOLDER   = "current_personal_folder";
    const FILE_SELECTION            = "file_selection";
    const HIST_FILE_SELECTION       = "hist_file_selection";
    const LAST_SELECTION_TYPE       = "last_selection_type";
    const NB_ELEMS_PER_LINE         = "nbperline";
    const CAPTCHA                   = "captcha";
    const RENEW_CAPTCHA             = "renew_captcha";
    const LOGIN_SALT                = "login_salt";
    const RAND_STRING               = 'randstring';
    const SESSION_CHANGE_REASON     = "change_reason";
    const HAS_SEEN_WHICH_PLATFORM   = "has_seen_which_platform";
    const HIDE_SIDE_MENU            = 'hide_side_menu';
    
    
    public function __construct($type = self::SET_DEFAULT)
    {
        // Regénération de l'id de session pour plus de sécurité
        //session_regenerate_id();

        // Timeout fixé pour plus de sécurité
        /*if(isset($_SESSION[self::LAST_ACTIVITY]) 
                && (time() - $_SESSION[self::LAST_ACTIVITY] > self::TIMEOUT_IN_MINUTES*60))
        {
            $this->destroyLoginSession();
        }*/
        $_SESSION[self::LAST_ACTIVITY] = time();
        if(!isset($_SESSION[SessionController::LAST_LOGIN_TRY]))
        {
            $_SESSION[SessionController::LAST_LOGIN_TRY] = '';
            $_SESSION[SessionController::ATTEMPTS] = 0;
        }
        if(!isset($_SESSION[self::LOGIN_SALT]))
            $_SESSION[self::LOGIN_SALT] = NULL;
        /*if(!isset($_SESSION[self::RENEW_CAPTCHA]))
            $_SESSION[self::RENEW_CAPTCHA] = TRUE;*/
        switch($type)
        {
            case self::SET_DEFAULT :
                $this->createDefaultSession();
                break;
            case self::LOGIN :
                $this->createLoginSession();
                break;
            case self::LOGOUT :
                $this->destroyLoginSession();
                break;
            case self::NOACTION :
            case self::JUST_INSTANTIATE:
                break;
            default :
                $this->createDefaultSession();
                break;
        }
        // Mise à jour de la session si besoin
        if($type != self::JUST_INSTANTIATE)
            $this->updateSession();
        // Changement de plateforme
        if(isset($_REQUEST['change_platform']))
        {
            $this->changePlatform($_REQUEST['platform']);
        }
    }
    public function createDefaultSession($login_if_can = TRUE)
	{
        if (session_status() == PHP_SESSION_NONE)
            session_start();
        if(!isset($_SESSION[SessionController::CATEGORIE]))
            $_SESSION[SessionController::CATEGORIE] = PageController::VISITOR;
        if(!isset($_SESSION[SessionController::ID_PERSONNE]) && isset($_COOKIE['sidecampus_login'])
                && $login_if_can)
                $this->createLoginSession();
    }
    /**
     * Mot de passe dans la base de données: sha1(sha1(mdp_en_clair).confirmcode)
     * Procédure de connexion:
     *  -> Le client entre son mot de passe sur la page web.
     *  -> Quand il envoie le formulaire, on récupère par ajax:
     *          - un salt (qui est enregistré dans la session)
     *          - le code de confirmation du client
     *  -> Le password envoyé est le hash sha1(sha1(sha1(mdp_en_clair).confirmcode).salt)
     *  -> Le serveur peut vérifier le hash envoyé.
     *  -> Une fois vérifié, le salt est remis à NULL pour éviter de pouvoir réutiliser le hash
     *  Avantages: 
     *      - Le mot de passe ne passe jamais en clair sur le réseau
     *      - Même avec le sel et le code de confirmation passant en clair sur le réseau, on ne 
     *          peut pas deviner le mot de passe.
     *      - Le hash n'est jamais pareil (ou très rarement), donc on ne peut pas réutiliser un 
     *          hash qu'on aurait sniffé pour se connecter sur un compte.
     *      - Les mots de passe égaux dans la base de données n'ont pas forcément le même hash
     * @throws Exception
     */
    public function createLoginSession()
    {
		if(isset($_SESSION[SessionController::ID_PERSONNE]))
			return;
		if(isset($_COOKIE['sidecampus_login']))
		{
			// Cookie existant
			$_REQUEST['login'] = $_COOKIE['sidecampus_login'];
			$_REQUEST['mdp'] = $_COOKIE['sidecampus_key'];
			$_SESSION[SessionController::LOGIN_SALT] = $_COOKIE['sidecampus_salt'];
		}
        if(isset($_REQUEST['login']))
        {
            $login = $_REQUEST['login'];
            $mdp = $_REQUEST['mdp'];

            $model = new PersonneModel();
            $infos = $model->getByLogin($login);
			
			// Limite à 1 mois
			//if(strtotime($infos['dt_last_cookie_set']) )

            if($infos == NULL || $infos['confirm'] == 0)
            {
                    throw new Exception('Cet utilisateur n\'existe pas ou n\'a pas encore été confirmé.');
            }
            else
            {	
                if($mdp == sha1($infos['tx_mdp'].$_SESSION[SessionController::LOGIN_SALT]))
                {
                    if($infos['enabled'] == 1)
                    {
                        // Login success
                        $this->setSession($infos);
						
						// Set cookie if necessary
						if(!isset($_COOKIE['sidecampus_login']) && isset($_REQUEST['remember_me']) && $infos['id_pers'] != 0)
						{
							setcookie('sidecampus_login', $login, time()+60*60*24*30);
							setcookie('sidecampus_key', $mdp, time()+60*60*24*30);
							setcookie('sidecampus_salt', $_SESSION[SessionController::LOGIN_SALT], time()+60*60*24*30);
						}
						
						// On change le salt pour la sécurité
						$_SESSION[SessionController::LOGIN_SALT] = NULL;
                        
                        // Insert into history
                        $query = "INSERT INTO login_history(ts_login, id_pers) VALUES(NOW(), :user)";
                        $result = DB::update($query, array(':user' => $_SESSION[SessionController::ID_PERSONNE]));
                        
                    }
                    else
                    {
                            throw new Exception('Cet utilisateur est désactivé dans le système.');
                    }
                }
				elseif(isset($_COOKIE['sidecampus_login']))
				{
					return;
				}
                else
                {
                    if($_SESSION[SessionController::ATTEMPTS] < 15 || !ConfigUtils::isIpBlockerEnabled())
                    {
                        if($_SESSION[SessionController::LAST_LOGIN_TRY] != $login)
                                $_SESSION[SessionController::ATTEMPTS] = 0;
                        $_SESSION[SessionController::ATTEMPTS]++;
                        $_SESSION[SessionController::LAST_LOGIN_TRY] = $login;
                        throw new Exception('Le mot de passe est incorrect');
                    }
                    else
                    {
                        $ipblocker = new IpBlockerModel();
                        $ipblocker->create($_SERVER['REMOTE_ADDR']);
                        throw new Exception('Nombre de tentatives dépassé. Le compte a été bloqué pour ce login.');
                    }
                }
            }
        }
    }
	public function getAttempt() {return $_SESSION[SessionController::ATTEMPTS];}
	public function getPreviousLogin() {return $_SESSION[SessionController::LAST_LOGIN_TRY];}
    public function destroyLoginSession()
    {
        $updating = null;
        $updating_rand = null;
        if(isset($_SESSION['UPDATING_PASSWORD']))
            $updating = $_SESSION['UPDATING_PASSWORD'];
        if(isset($_SESSION['UPDATING_PASSWORD_RANDOM']))
            $updating_rand = $_SESSION['UPDATING_PASSWORD_RANDOM'];

        session_destroy();
		
		// Delete all cookies
		setcookie('sidecampus_login', '', time()-3600);
		setcookie('sidecampus_key', '', time()-3600);
		setcookie('sidecampus_salt', '', time()-3600);
		
        $this->createDefaultSession(FALSE);
        
        if($updating != null)
            $_SESSION['UPDATING_PASSWORD'] = $updating;
        if($updating_rand != null)
            $_SESSION['UPDATING_PASSWORD_RANDOM'] = $updating_rand;
    }
    public static function updatePersonInfo($personID)
    {
        $person = new PersonneModel($personID);
        $data = $person->get();
        $_SESSION[SessionController::ID_PERSONNE] = $personID;
        $_SESSION[SessionController::USERNAME] = $data['tx_login'];
        if(!isset($_SESSION[SessionController::CURRENT_PERSONAL_FOLDER]))
        {
            $model = new PersonalFolderModel();
            $folder = $model->getDefaultFolder($_SESSION[SessionController::ID_PERSONNE]);
            $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER] = $folder['id_folder'];
        }
        $_SESSION[SessionController::ENABLED] = $data['enabled'];
    }
    public function changePlatform($platformID)
    {
        if(!isset($_SESSION[SessionController::ID_PERSONNE]))
            return;
        // Changement des infos de la plateforme
        $_SESSION[SessionController::PLATFORM_ID] = $platformID;
        $_SESSION[SessionController::NOTIFICATION_PLATFORM_ID] = $platformID;
        $_SESSION[SessionController::HAS_SEEN_WHICH_PLATFORM] = FALSE;
        $platformModel = new PlatformModel($platformID);
        $pinfos = $platformModel->get();
        $_SESSION[SessionController::PLATFORM_NAME] = $pinfos['tx_nom'];
        // Changement des infos du membre
        $memberModel = new MemberModel();
        $membre = $memberModel->getMembersByFilter(array(
            'id_plateforme' =>  $platformID,
            'id_personne'   =>  $_SESSION[SessionController::ID_PERSONNE]
        ));
        if(empty($membre))
            return;
        if($membre[0]['actif'] == 0)
            return FALSE;
        $_SESSION[SessionController::ID_MEMBRE] = $membre[0]['id_membre'];
        $_SESSION[SessionController::NOTIFICATION_MEMBER_ID] = $membre[0]['id_membre'];
        $_SESSION[SessionController::HIERARCHIE] = $membre[0]['hierarchie'];
        if($membre[0]['hierarchie'] == MemberModel::ADMIN)
        {
            $_SESSION[SessionController::CATEGORIE] = PageController::ADMIN_PLATFORM;
        }
        elseif($membre[0]['hierarchie'] == MemberModel::MODERATOR)
        {
            $_SESSION[SessionController::CATEGORIE] = PageController::ADMIN_PLATFORM;
        }
        else
        {
            $_SESSION[SessionController::CATEGORIE] = PageController::MEMBER_DEFAULT_PLATFORM;
        }
        $foldermodel = new FolderModel();
        $folderdata = $foldermodel->getDefaultFolder($platformID);
        $_SESSION[SessionController::CURRENT_FOLDER] = $folderdata['id_folder'];
        $_SESSION[SessionController::NB_ELEMS_PER_LINE] = self::THREE_PER_LINE;
    }
    public function addSelection($ids, $type = self::SELECTION_COPY)
    {
        $_SESSION[self::FILE_SELECTION] = $ids;
        $_SESSION[self::LAST_SELECTION_TYPE] = $type;
    }
    public function clearSelection()
    {
		if(isset($_SESSION[self::FILE_SELECTION]))
			$_SESSION[self::HIST_FILE_SELECTION] = $_SESSION[self::FILE_SELECTION];
        $_SESSION[self::FILE_SELECTION] = array();
        $_SESSION[self::LAST_SELECTION_TYPE] = self::SELECTION_COPY;
    }
    public function clearSessionVars()
    {
        foreach($_SESSION as $key=>$sess)
        {
            unset($key);
        }
    }
    public function setSession($infos)
    {
        $_SESSION[SessionController::ID_PERSONNE] = $infos['id_pers'];
        $_SESSION[SessionController::USERNAME] = $infos['tx_login'];
        $_SESSION[SessionController::ENABLED] = $infos['enabled'];
        $_SESSION[SessionController::CATEGORIE] = $infos['int_categorie'];
        $_SESSION[SessionController::RAND_STRING] = $infos['tx_login_code'];
        $model = new PersonalFolderModel();
        $folder = $model->getDefaultFolder($_SESSION[SessionController::ID_PERSONNE]);
        $_SESSION[SessionController::CURRENT_PERSONAL_FOLDER] = $folder['id_folder'];

        // Default platform
        if($infos['int_categorie'] == PageController::MEMBER_DEFAULT_PLATFORM)
        {
            $_SESSION[SessionController::HIDE_SIDE_MENU] = FALSE;
            $this->changePlatform($infos['id_plateforme']);
        }
    }
    
    /**
     * Update session if:
     *      - has changed category
     *      - has been blocked on active platform
     */
    public function updateSession()
    {
        if(!isset($_SESSION[SessionController::USERNAME]))
            return;
		$this->updatePersonInfo($_SESSION[SessionController::ID_PERSONNE]);
        $model = new PersonneModel();
        $infos = $model->getByLogin($_SESSION[SessionController::USERNAME]);
        // Check si pas bloqué
        if($_SESSION[SessionController::CATEGORIE] == PageController::MEMBER_DEFAULT_PLATFORM
                || $_SESSION[SessionController::CATEGORIE] == PageController::ADMIN_PLATFORM)
        {
            $message = null;
            $membermodel = new MemberModel($_SESSION[SessionController::ID_MEMBRE]);
            $member = $membermodel->get();
            if($member['actif'] == 0)
            {
                // A été bloqué => on refait la session
                $message = "Vous avez été bloqué sur '".$_SESSION[SessionController::PLATFORM_NAME]
                            ."' par un admin ou modérateur. Vous ne pourrez y accéder que quand l'un deux"
                        . " vous débloquera";
                $this->clearSessionVars();
                $this->setSession($infos);
                $_SESSION[SessionController::SESSION_CHANGE_REASON] = $message;
            }
            elseif($member['hierarchie'] < $_SESSION[SessionController::HIERARCHIE]) 
            {
                // A été déchu
                $text_status = null;
                switch($member['hierarchie'])
                {
                    case MemberModel::MEMBER:
                        $text_status = "membre";
                        break;
                    case MemberModel::MODERATOR:
                        $text_status = "modérateur";  
                        break;
                }
                $platform = $_SESSION[SessionController::PLATFORM_ID];
                $infos['id_plateforme'] = $platform;
                $message = "Vous avez perdu vos privilèges. Vous êtes maintenant ".$text_status;
                $this->clearSessionVars();
                $this->setSession($infos);
                $_SESSION[SessionController::SESSION_CHANGE_REASON] = $message;
            }
            elseif($member['hierarchie'] > $_SESSION[SessionController::HIERARCHIE]) 
            {
                // A été promu
                $text_status = null;
                switch($member['hierarchie'])
                {
                    case MemberModel::ADMIN:
                        $text_status = "administrateur";
                        break;
                    case MemberModel::MODERATOR:
                        $text_status = "modérateur";  
                        break;
                }
                $platform = $_SESSION[SessionController::PLATFORM_ID];
                $infos['id_plateforme'] = $platform;
                $message = "Vous avez été promu. Vous êtes maintenant ".$text_status;
                $this->clearSessionVars();
                $this->setSession($infos);
                $_SESSION[SessionController::SESSION_CHANGE_REASON] = $message;
            }
        }
        // Si pas été bloqué, on regarde si la catégorie a changé
        elseif($infos['int_categorie'] != $_SESSION[SessionController::CATEGORIE])
        {
            $message = null;
            if($_SESSION[SessionController::CATEGORIE] == PageController::MEMBER_NO_DEFAULT_PLATFORM
                    && $infos['int_categorie'] == PageController::MEMBER_DEFAULT_PLATFORM)
            {
                $platformmodel = new PlatformModel($infos['id_plateforme']);
                $platformdata = $platformmodel->get();
                $message = "Vous avez désormais accès à ".$platformdata['tx_nom'];
            }
            $this->clearSessionVars();
            $this->setSession($infos);
            if($message != null)
                $_SESSION[SessionController::SESSION_CHANGE_REASON] = $message;
        }
            
        
    }
   
}

?>
