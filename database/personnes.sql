SELECT personne.tx_prenom, personne.tx_nom, plateforme.tx_nom, confirm
FROM `personne`, membre, plateforme 
WHERE personne.id_pers = membre.id_personne and plateforme.id_plateforme = membre.id_plateforme
UNION
SELECT pers.tx_prenom, pers.tx_nom, 'Aucune', confirm
FROM `personne`pers
WHERE NOT EXISTS(select * FROM demande where id_personne = pers.id_pers)
UNION
SELECT pers.tx_prenom, pers.tx_nom, 'En attente', confirm
FROM `personne`pers
WHERE EXISTS(select * FROM demande where id_personne = pers.id_pers)
ORDER BY 3, 4