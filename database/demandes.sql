SELECT plateforme.id_plateforme, plateforme.tx_nom, personne.id_pers, personne.tx_prenom, personne.tx_nom
FROM demande, plateforme, personne
WHERE demande.id_plateforme = plateforme.id_plateforme
AND demande.id_personne = personne.id_pers