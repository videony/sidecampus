create database sidecampus;
use sidecampus;

-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le :  Dim 11 fév. 2018 à 17:24
-- Version du serveur :  10.1.14-MariaDB
-- Version de PHP :  5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Structure de la table `annonces`
--

CREATE TABLE `annonces` (
  `id_annonce` int(11) NOT NULL,
  `tx_titre` varchar(100) NOT NULL,
  `tx_description` varchar(1000) NOT NULL,
  `dt_posted` datetime NOT NULL,
  `int_price` decimal(4,2) NOT NULL,
  `cd_active` int(1) NOT NULL DEFAULT '1',
  `cd_boosted` int(1) NOT NULL DEFAULT '0',
  `id_personne` int(11) NOT NULL,
  `cd_sold` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `annonces_reponses`
--

CREATE TABLE `annonces_reponses` (
  `id_ar` int(11) NOT NULL,
  `id_annonce` int(11) NOT NULL,
  `tx_reponse` varchar(1000) NOT NULL,
  `tx_email` varchar(100) NOT NULL,
  `dt_answered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tx_nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `ip_address` varchar(16) COLLATE utf8_bin NOT NULL,
  `dt_blocked` datetime NOT NULL,
  `tx_reason` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT 'Tentative frauduleuse sur mot de passe',
  `id_block` int(11) NOT NULL,
  `cd_active` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `demande`
--

CREATE TABLE `demande` (
  `id_demande` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL,
  `id_personne` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `message` varchar(250) COLLATE utf8_bin NOT NULL,
  `dt_demande` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Demandes d''ajout à la plateforme';

-- --------------------------------------------------------

--
-- Structure de la table `file`
--

CREATE TABLE `file` (
  `id_file` int(11) NOT NULL,
  `id_folder` int(11) DEFAULT NULL,
  `tx_name` varchar(100) COLLATE utf8_bin NOT NULL,
  `tx_extension` varchar(10) COLLATE utf8_bin NOT NULL,
  `tx_commentaire` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `id_adder` int(11) NOT NULL,
  `dt_ajout` datetime NOT NULL,
  `int_size` int(11) NOT NULL,
  `tx_encoded_name` varchar(110) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `file_external_authorization`
--

CREATE TABLE `file_external_authorization` (
  `id_authorization` int(11) NOT NULL,
  `id_file` int(11) NOT NULL,
  `dt_perished` datetime NOT NULL,
  `tx_key` varchar(40) NOT NULL,
  `tx_type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `folder`
--

CREATE TABLE `folder` (
  `id_folder` int(11) NOT NULL,
  `id_plateforme` int(11) DEFAULT NULL,
  `id_parent_folder` int(11) DEFAULT NULL,
  `id_adder` int(11) NOT NULL,
  `tx_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `dt_ajout` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `forum_categories`
--

CREATE TABLE `forum_categories` (
  `id_categorie` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plateforme_id` int(11) NOT NULL,
  `date_last_post` datetime DEFAULT NULL,
  `nom_categorie` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id_post` int(11) NOT NULL,
  `id_topic` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `post` varchar(5000) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id_topic` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `plateforme_id` int(11) NOT NULL,
  `date_last_post` datetime DEFAULT NULL,
  `nom_topic` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `first_post` varchar(5000) NOT NULL,
  `id_categorie` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `login_history`
--

CREATE TABLE `login_history` (
  `id_login_history` int(11) NOT NULL,
  `ts_login` datetime NOT NULL,
  `id_pers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `membre`
--

CREATE TABLE `membre` (
  `id_membre` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL,
  `id_personne` int(11) NOT NULL,
  `actif` int(1) NOT NULL,
  `hierarchie` int(2) NOT NULL,
  `compteurNotif` int(4) NOT NULL DEFAULT '0',
  `dt_join` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `mot_cle`
--

CREATE TABLE `mot_cle` (
  `tx_mot` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `notif_plateforme`
--

CREATE TABLE `notif_plateforme` (
  `id_notif_plateforme` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `texte` varchar(300) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `date` datetime NOT NULL,
  `id_plateforme` int(11) NOT NULL,
  `tx_link` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `permission`
--

CREATE TABLE `permission` (
  `permission_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `admin_default` int(1) NOT NULL,
  `moderator_default` int(1) NOT NULL,
  `member_default` int(1) NOT NULL,
  `section` varchar(30) COLLATE utf8_bin NOT NULL,
  `tx_description` varchar(100) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `permission`
--

INSERT INTO `permission` (`permission_name`, `admin_default`, `moderator_default`, `member_default`, `section`, `tx_description`) VALUES
('CAN_ACCEPT_USER', 1, 1, 1, 'Gestion de la plateforme', 'Accepter un utilisateur en demande d\'admission'),
('CAN_ADD_FILES', 1, 1, 1, 'Fichiers', 'Ajouter des fichiers sur la plateforme'),
('CAN_ADD_FOLDER', 1, 1, 1, 'Fichiers', 'Créer des dossiers sur la plateforme'),
('CAN_ADD_PUBLIC_EVENT', 1, 1, 1, 'Calendrier', 'Ajouter un événement public sur le calendrier de la plateforme'),
('CAN_BAN_USER', 1, 1, 0, 'Gestion de la plateforme', 'Bannir un utilisateur de la plateforme'),
('CAN_BLOCK_USER', 1, 1, 0, 'Gestion de la plateforme', 'Bloquer un utilisateur de la plateforme'),
('CAN_CREATE_CATEGORY', 1, 1, 1, 'Forum', 'Créer une catégorie de sujet dans le forum'),
('CAN_CREATE_NEW_MESSAGE', 1, 1, 1, 'Forum', 'Créer un nouveau message dans le formu'),
('CAN_CREATE_SUBJECT', 1, 1, 1, 'Forum', 'Créer un sujet dans le forum'),
('CAN_EDIT_OTHERS_CATEGORY', 1, 1, 0, 'Forum', 'Editer les catégories créées par d\'autres personnes'),
('CAN_EDIT_OTHERS_EVENTS', 1, 1, 0, 'Calendrier', 'Editer les évènements publics créés par d\'autres de la plateforme'),
('CAN_EDIT_OTHERS_FILES_COMMENTS', 1, 1, 0, 'Fichiers', 'Editer les commentaires des fichiers des autres'),
('CAN_EDIT_OTHERS_MESSAGE', 1, 1, 0, 'Forum', 'Editer les messages créés par d\'autres personnes'),
('CAN_EDIT_OTHERS_SUBJECT', 1, 1, 0, 'Forum', 'Editer les sujets créés par d\'autres personnes'),
('CAN_EDIT_OWN_CATEGORY', 1, 1, 0, 'Forum', 'Editer le titre de sa propre catégorie'),
('CAN_EDIT_OWN_EVENTS', 1, 1, 1, 'Calendrier', 'Editer ses propres évènements'),
('CAN_EDIT_OWN_FILES_COMMENTS', 1, 1, 1, 'Fichiers', 'Editer les commentaires de ses fichiers'),
('CAN_EDIT_OWN_MESSAGE', 1, 1, 1, 'Forum', 'Editer son propre message'),
('CAN_EDIT_OWN_SUBJECT', 1, 1, 1, 'Forum', 'Editer son propre sujet'),
('CAN_EDIT_PLATFORM', 1, 0, 0, 'Gestion de la plateforme', 'Editer la plateforme.'),
('CAN_EDIT_RIGHTS', 1, 0, 0, 'Gestion de la plateforme', 'Editer les droits des membres de la plateforme.'),
('CAN_MOVE_OTHERS_FILES', 1, 1, 0, 'Fichiers', 'Déplacer ou copier les fichiers des autres dans d\'autres dossiers'),
('CAN_MOVE_OWN_FILES', 1, 1, 1, 'Fichiers', 'Peut déplacer ou copier ses fichiers dans d\'autres dossiers'),
('CAN_REJECT_USER', 1, 1, 0, 'Gestion de la plateforme', 'Ejecter un utilisateur de la plateforme'),
('CAN_REMOVE_OTHERS_CATEGORY', 1, 1, 0, 'Forum', 'Supprimer des catégories créées par d\'autre dans le forum.'),
('CAN_REMOVE_OTHERS_EVENTS', 1, 1, 0, 'Calendrier', 'Supprimer des événements public créés par d\'autres de la plateforme'),
('CAN_REMOVE_OTHERS_FILES', 1, 1, 0, 'Fichiers', 'Supprimer des fichiers d\'autres personnes sur la plateforme.'),
('CAN_REMOVE_OTHERS_FOLDER', 1, 1, 0, 'Fichiers', 'Supprimer des dossiers d\'autres personnes sur la plateforme.'),
('CAN_REMOVE_OTHERS_MESSAGES', 1, 1, 0, 'Forum', 'Supprimer des messages écrits par d\'autres sur le forum.'),
('CAN_REMOVE_OTHERS_PLATFORM_MESSAGES', 1, 1, 0, 'Gestion de la plateforme', 'Supprimer les messages à la plateforme des autres utilisateurs'),
('CAN_REMOVE_OTHERS_SUBJECT', 1, 1, 0, 'Forum', 'Supprimer des sujets créés par d\'autres sur le forum.'),
('CAN_REMOVE_OWN_CATEGORY', 1, 1, 0, 'Forum', 'Supprimer ses catégories dans le forum'),
('CAN_REMOVE_OWN_EVENTS', 1, 1, 1, 'Calendrier', 'Supprimer ses évènements'),
('CAN_REMOVE_OWN_FILES', 1, 1, 1, 'Fichiers', 'Supprimer ses fichiers'),
('CAN_REMOVE_OWN_FOLDER', 1, 1, 1, 'Fichiers', 'Supprimer ses dossiers'),
('CAN_REMOVE_OWN_MESSAGE', 1, 1, 1, 'Forum', 'Supprimer ses messages sur le forum.'),
('CAN_REMOVE_OWN_SUBJECT', 1, 1, 1, 'Forum', 'Supprimer ses sujets sur le forum.'),
('CAN_RENAME_OTHERS_FILES ', 1, 1, 0, 'Fichiers', 'Renommer les fichiers ou dossiers des autres'),
('CAN_RENAME_OWN_FILES', 1, 1, 1, 'Fichiers', 'Renommer ses fichiers ou dossiers'),
('CAN_REVOKE_MODERATOR', 1, 0, 0, 'Gestion de la plateforme', 'Révoquer le droit de modérateur'),
('CAN_SEE_FILES', 1, 1, 1, 'Fichiers', 'Voir les fichiers de la plateforme'),
('CAN_SEE_MESSAGES', 1, 1, 1, 'Forum', 'Voir les messages du forum'),
('CAN_SEE_OTHERS_PROFILE', 1, 1, 1, 'Gestion de la plateforme', 'Voir les profils des autres membres de la plateforme'),
('CAN_SEE_PUBLIC_EVENTS', 1, 1, 1, 'Calendrier', 'Voir les événements publics de la plateforme'),
('CAN_SET_ADMIN', 1, 0, 0, 'Gestion de la plateforme', 'Nommer quelqu\'un administrateur'),
('CAN_SET_MEMBER', 1, 0, 0, 'Gestion de la plateforme', 'Révoquer le droit d\'administateur ou modérateur'),
('CAN_SET_MODERATOR', 1, 1, 0, 'Gestion de la plateforme', 'Nommer quelqu\'un modérateur'),
('CAN_UNBAN_USER', 1, 1, 0, 'Gestion de la plateforme', 'Débannir un utilisateur de la plateforme'),
('CAN_UNBLOCK_USER', 1, 1, 0, 'Gestion de la plateforme', 'Débloquer un utilisateur '),
('CAN_WRITE_PLATFORM_MESSAGE', 1, 1, 1, 'Gestion de la plateforme', 'Ecrire un message à la plateforme');

--
-- Structure de la table `personal_file`
--

CREATE TABLE `personal_file` (
  `id_file` int(11) NOT NULL,
  `id_folder` int(11) DEFAULT NULL,
  `tx_name` varchar(100) COLLATE utf8_bin NOT NULL,
  `tx_extension` varchar(10) COLLATE utf8_bin NOT NULL,
  `tx_commentaire` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `id_adder` int(11) NOT NULL,
  `dt_ajout` datetime NOT NULL,
  `int_size` int(11) NOT NULL,
  `tx_encoded_name` varchar(110) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `personal_folder`
--

CREATE TABLE `personal_folder` (
  `id_folder` int(11) NOT NULL,
  `id_personne` int(11) DEFAULT NULL,
  `id_parent_folder` int(11) DEFAULT NULL,
  `id_adder` int(11) NOT NULL,
  `tx_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `dt_ajout` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `personne`
--

CREATE TABLE `personne` (
  `id_pers` int(11) NOT NULL,
  `tx_nom` varchar(50) COLLATE utf8_bin NOT NULL,
  `tx_prenom` varchar(50) COLLATE utf8_bin NOT NULL,
  `tx_login` varchar(50) COLLATE utf8_bin NOT NULL,
  `tx_mdp` varchar(50) COLLATE utf8_bin NOT NULL,
  `tx_email` varchar(50) COLLATE utf8_bin NOT NULL,
  `tx_gsm` varchar(15) COLLATE utf8_bin DEFAULT NULL,
  `int_categorie` int(11) NOT NULL DEFAULT '1001',
  `tx_msg_perso` varchar(150) COLLATE utf8_bin DEFAULT NULL,
  `dt_naissance` date DEFAULT NULL,
  `id_plateforme` int(11) DEFAULT NULL,
  `confirm_code` varchar(50) COLLATE utf8_bin NOT NULL,
  `confirm` int(1) NOT NULL DEFAULT '0',
  `enabled` int(1) NOT NULL DEFAULT '1',
  `img_profile_pic` blob,
  `tx_profile_pic` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `tx_login_code` varchar(20) COLLATE utf8_bin NOT NULL,
  `reinit_pwd_sent` int(1) NOT NULL DEFAULT '0',
  `dt_last_cookie_set` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dt_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nb_annonces` int(11) NOT NULL DEFAULT '0',
  `popup_news_to_see` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `person_events`
--

CREATE TABLE `person_events` (
  `id_person_event` int(11) NOT NULL,
  `tx_titre` varchar(30) COLLATE utf8_bin NOT NULL,
  `tx_description` varchar(200) COLLATE utf8_bin NOT NULL,
  `int_adder` int(11) NOT NULL,
  `dt_begin` datetime NOT NULL,
  `dt_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `plateforme`
--

CREATE TABLE `plateforme` (
  `id_plateforme` int(11) NOT NULL,
  `tx_nom` varchar(100) COLLATE utf8_bin NOT NULL,
  `dt_created` date NOT NULL,
  `annee` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `section` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `cp` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `ecole` varchar(100) COLLATE utf8_bin NOT NULL,
  `descriptif` varchar(500) COLLATE utf8_bin DEFAULT NULL,
  `visible` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `platform_events`
--

CREATE TABLE `platform_events` (
  `id_platform_event` int(11) NOT NULL,
  `tx_titre` varchar(30) COLLATE utf8_bin NOT NULL,
  `tx_description` varchar(200) COLLATE utf8_bin NOT NULL,
  `int_adder` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL,
  `dt_begin` datetime NOT NULL,
  `dt_end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `platform_message`
--

CREATE TABLE `platform_message` (
  `id_message` int(11) NOT NULL,
  `tx_message` varchar(500) NOT NULL,
  `id_writer` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Structure de la table `seance_bloque`
--

CREATE TABLE `seance_bloque` (
  `id_seance_bloque` int(11) NOT NULL,
  `id_semaine_bloque` int(11) NOT NULL,
  `tx_sujet` varchar(50) NOT NULL,
  `jour_semaine` varchar(10) NOT NULL,
  `heure_debut` int(2) NOT NULL,
  `heure_fin` int(2) NOT NULL,
  `tx_color` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `semaine_bloque`
--

CREATE TABLE `semaine_bloque` (
  `id_semaine_bloque` int(11) NOT NULL,
  `id_personne` int(11) NOT NULL,
  `dt_begin` date NOT NULL,
  `dt_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `setting`
--

CREATE TABLE `setting` (
  `setting_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `admin_default` int(1) DEFAULT NULL,
  `moderator_default` int(1) DEFAULT NULL,
  `member_default` int(1) DEFAULT NULL,
  `section` varchar(30) COLLATE utf8_bin NOT NULL,
  `tx_description` varchar(100) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Déchargement des données de la table `setting`
--

INSERT INTO `setting` (`setting_name`, `admin_default`, `moderator_default`, `member_default`, `section`, `tx_description`) VALUES
('NOTIF_ON_ACCEPT_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur est accepté à réjoindre la plateforme'),
('NOTIF_ON_BAN_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lors du banniment d\'un utilisateur'),
('NOTIF_ON_BLOCK_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur est bloqué.'),
('NOTIF_ON_CHANGE_PERM_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsque les permissions d\'un utilisateur a été changé'),
('NOTIF_ON_DELETE_CATEGORY', 1, 0, 0, 'Forum', 'Recevoir une notifiation lors de la suppression d\'une catégorie'),
('NOTIF_ON_DELETE_EVENT', 1, 1, 0, 'Calendrier', 'Recevoir une notification lors de la suppression d\'un nouvel évènement'),
('NOTIF_ON_DELETE_FILE', 1, 0, 0, 'Fichiers', 'Recevoir une notification lors de la suppression d\'un fichier'),
('NOTIF_ON_DELETE_FOLDER', 1, 0, 0, 'Fichiers', 'Recevoir une notification lors de la suppression d\'un dossier'),
('NOTIF_ON_DELETE_MESSAGE', 1, 0, 0, 'Forum', 'Recevoir une notifiation lors de la suppression d\'un message'),
('NOTIF_ON_DELETE_SUBJECT', 1, 0, 0, 'Forum', 'Recevoir une notifiation lors de la suppression d\'un sujet'),
('NOTIF_ON_EJECT_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lors de l\'expulsion d\'un utilisateur'),
('NOTIF_ON_NEW_ASK_TO_JOIN', 1, 1, NULL, 'Gestion de la plateforme', 'Recevoir une notification lors d\'une nouvelle demande d\'une personne pour rejoindre la plateforme'),
('NOTIF_ON_NEW_CATEGORY', 1, 1, 0, 'Forum', 'Recevoir une notification lors d\'une nouvelle catégorie'),
('NOTIF_ON_NEW_EVENT', 1, 1, 1, 'Calendrier', 'Recevoir une notification lors de l\'ajout d\'un nouvel évènement'),
('NOTIF_ON_NEW_FILE', 1, 1, 1, 'Fichiers', 'Recevoir une notification lors de l\'ajout d\'un nouveau fichier'),
('NOTIF_ON_NEW_FOLDER', 1, 1, 0, 'Fichiers', 'Recevoir une notification lors de l\'ajout d\'un nouveau dossier'),
('NOTIF_ON_NEW_MESSAGE', 1, 1, 1, 'Forum', 'Recevoir une notification lors d\'un nouveau message'),
('NOTIF_ON_NEW_SUBJECT', 1, 1, 0, 'Forum', 'Recevoir une notification lors d\'un nouveau sujet'),
('NOTIF_ON_PLATFORM_EDIT', 1, 1, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsque la plateforme a été modifiée'),
('NOTIF_ON_SET_ADMIN', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur a été nommé administrateur'),
('NOTIF_ON_SET_MEMBER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur a été nommé membre'),
('NOTIF_ON_SET_MODERATOR', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur a été nommé modérateur'),
('NOTIF_ON_UNBAN_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lors du débanniment d\'un utilisateur'),
('NOTIF_ON_UNBLOCK_USER', 1, 0, NULL, 'Gestion de la plateforme', 'Recevoir une notification lorsqu\'un utilisateur est débloqué.');

--
-- Structure de la table `todo`
--

CREATE TABLE `todo` (
  `id_todo` int(11) NOT NULL,
  `dt_deadline` date NOT NULL,
  `tx_todo` varchar(200) NOT NULL,
  `id_personne` int(11) NOT NULL,
  `position` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `user_permission`
--

CREATE TABLE `user_permission` (
  `id_up` int(11) NOT NULL,
  `id_membre` int(11) NOT NULL,
  `permission_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `value` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `user_setting`
--

CREATE TABLE `user_setting` (
  `id_us` int(11) NOT NULL,
  `id_membre` int(11) NOT NULL,
  `setting_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `value` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annonces`
--
ALTER TABLE `annonces`
  ADD PRIMARY KEY (`id_annonce`);

--
-- Index pour la table `annonces_reponses`
--
ALTER TABLE `annonces_reponses`
  ADD PRIMARY KEY (`id_ar`);

--
-- Index pour la table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id_block`);

--
-- Index pour la table `demande`
--
ALTER TABLE `demande`
  ADD PRIMARY KEY (`id_demande`),
  ADD UNIQUE KEY `UNIQUE_DEMANDE` (`id_plateforme`,`id_personne`);

--
-- Index pour la table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`id_file`),
  ADD UNIQUE KEY `UNIQUE_FILE_IN_FOLDER` (`id_folder`,`tx_name`);

--
-- Index pour la table `file_external_authorization`
--
ALTER TABLE `file_external_authorization`
  ADD PRIMARY KEY (`id_authorization`);

--
-- Index pour la table `folder`
--
ALTER TABLE `folder`
  ADD PRIMARY KEY (`id_folder`),
  ADD UNIQUE KEY `UNIQUE_FOLDER_NAME` (`id_plateforme`,`id_parent_folder`,`tx_name`);

--
-- Index pour la table `forum_categories`
--
ALTER TABLE `forum_categories`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Index pour la table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id_post`);

--
-- Index pour la table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id_topic`);

--
-- Index pour la table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id_login_history`);

--
-- Index pour la table `membre`
--
ALTER TABLE `membre`
  ADD PRIMARY KEY (`id_membre`),
  ADD UNIQUE KEY `UNIQUE_MEMBERSHIP` (`id_personne`,`id_plateforme`);

--
-- Index pour la table `mot_cle`
--
ALTER TABLE `mot_cle`
  ADD PRIMARY KEY (`tx_mot`);

--
-- Index pour la table `notif_plateforme`
--
ALTER TABLE `notif_plateforme`
  ADD PRIMARY KEY (`id_notif_plateforme`);

--
-- Index pour la table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`permission_name`),
  ADD UNIQUE KEY `UNIQUE_PERMISSION` (`permission_name`);

--
-- Index pour la table `personal_file`
--
ALTER TABLE `personal_file`
  ADD PRIMARY KEY (`id_file`),
  ADD UNIQUE KEY `UNIQUE_FILE_IN_FOLDER` (`id_folder`,`tx_name`);

--
-- Index pour la table `personal_folder`
--
ALTER TABLE `personal_folder`
  ADD PRIMARY KEY (`id_folder`),
  ADD UNIQUE KEY `UNIQUE_FOLDER_NAME` (`id_personne`,`id_parent_folder`,`tx_name`);

--
-- Index pour la table `personne`
--
ALTER TABLE `personne`
  ADD PRIMARY KEY (`id_pers`),
  ADD UNIQUE KEY `tx_login` (`tx_login`);

--
-- Index pour la table `person_events`
--
ALTER TABLE `person_events`
  ADD PRIMARY KEY (`id_person_event`);

--
-- Index pour la table `plateforme`
--
ALTER TABLE `plateforme`
  ADD PRIMARY KEY (`id_plateforme`);

--
-- Index pour la table `platform_events`
--
ALTER TABLE `platform_events`
  ADD PRIMARY KEY (`id_platform_event`);

--
-- Index pour la table `platform_message`
--
ALTER TABLE `platform_message`
  ADD PRIMARY KEY (`id_message`);

--
-- Index pour la table `seance_bloque`
--
ALTER TABLE `seance_bloque`
  ADD PRIMARY KEY (`id_seance_bloque`);

--
-- Index pour la table `semaine_bloque`
--
ALTER TABLE `semaine_bloque`
  ADD PRIMARY KEY (`id_semaine_bloque`);

--
-- Index pour la table `setting`
--
ALTER TABLE `setting`
  ADD PRIMARY KEY (`setting_name`),
  ADD UNIQUE KEY `UNIQUE_PERMISSION` (`setting_name`);

--
-- Index pour la table `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`id_todo`);

--
-- Index pour la table `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`id_up`),
  ADD UNIQUE KEY `UNIQUE_PERMISSION` (`id_membre`,`permission_name`);

--
-- Index pour la table `user_setting`
--
ALTER TABLE `user_setting`
  ADD PRIMARY KEY (`id_us`),
  ADD UNIQUE KEY `UNIQUE_PERMISSION` (`id_membre`,`setting_name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annonces`
--
ALTER TABLE `annonces`
  MODIFY `id_annonce` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `annonces_reponses`
--
ALTER TABLE `annonces_reponses`
  MODIFY `id_ar` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id_block` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT pour la table `demande`
--
ALTER TABLE `demande`
  MODIFY `id_demande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2724;
--
-- AUTO_INCREMENT pour la table `file`
--
ALTER TABLE `file`
  MODIFY `id_file` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5075;
--
-- AUTO_INCREMENT pour la table `file_external_authorization`
--
ALTER TABLE `file_external_authorization`
  MODIFY `id_authorization` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6060;
--
-- AUTO_INCREMENT pour la table `folder`
--
ALTER TABLE `folder`
  MODIFY `id_folder` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=910;
--
-- AUTO_INCREMENT pour la table `forum_categories`
--
ALTER TABLE `forum_categories`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT pour la table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT pour la table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id_topic` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT pour la table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id_login_history` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81860;
--
-- AUTO_INCREMENT pour la table `membre`
--
ALTER TABLE `membre`
  MODIFY `id_membre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3769;
--
-- AUTO_INCREMENT pour la table `mot_cle`
--
ALTER TABLE `mot_cle`
  MODIFY `tx_mot` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `notif_plateforme`
--
ALTER TABLE `notif_plateforme`
  MODIFY `id_notif_plateforme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12100;
--
-- AUTO_INCREMENT pour la table `personal_file`
--
ALTER TABLE `personal_file`
  MODIFY `id_file` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pour la table `personal_folder`
--
ALTER TABLE `personal_folder`
  MODIFY `id_folder` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT pour la table `personne`
--
ALTER TABLE `personne`
  MODIFY `id_pers` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2864;
--
-- AUTO_INCREMENT pour la table `person_events`
--
ALTER TABLE `person_events`
  MODIFY `id_person_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;
--
-- AUTO_INCREMENT pour la table `plateforme`
--
ALTER TABLE `plateforme`
  MODIFY `id_plateforme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
--
-- AUTO_INCREMENT pour la table `platform_events`
--
ALTER TABLE `platform_events`
  MODIFY `id_platform_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
--
-- AUTO_INCREMENT pour la table `platform_message`
--
ALTER TABLE `platform_message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT pour la table `seance_bloque`
--
ALTER TABLE `seance_bloque`
  MODIFY `id_seance_bloque` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `semaine_bloque`
--
ALTER TABLE `semaine_bloque`
  MODIFY `id_semaine_bloque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pour la table `todo`
--
ALTER TABLE `todo`
  MODIFY `id_todo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;
--
-- AUTO_INCREMENT pour la table `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `id_up` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190887;
--
-- AUTO_INCREMENT pour la table `user_setting`
--
ALTER TABLE `user_setting`
  MODIFY `id_us` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90942;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
