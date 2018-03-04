SELECT plateforme.tx_nom, personne.tx_prenom, personne.tx_nom, personne.tx_email
FROM plateforme, membre, personne
WHERE plateforme.id_plateforme = membre.id_plateforme
AND membre.hierarchie = 3
AND personne.id_pers = membre.id_personne
AND plateforme.visible = 1