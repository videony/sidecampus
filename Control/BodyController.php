<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Interface pour être un "body" sur l'application web.
 * @author videony
 */
interface BodyController {
    //put your code here
    public function getContent(); // HTML généré
    public function getTitle(); // Titre de la page
    public function getCSS(); // Tableau de tous les fichiers CSS à inclure
    public function getJS(); // Tableau de tous les fichiers JS à inclure
    public function canAccess(); // Retourne true ou false, suivant si l'accès est permis ou non
    public function handlePostRequest(); // En cas de requête Ajax
}

?>
