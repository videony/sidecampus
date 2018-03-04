START TRANSACTION;

-- Divers
DELETE FROM platform_events WHERE id_plateforme = 35;
DELETE FROM platform_message WHERE id_plateforme = 35;
DELETE FROM demande WHERE id_plateforme = 35;
DELETE FROM notif_plateforme WHERE id_plateforme = 35;

-- Forum
DELETE FROM forum_posts WHERE id_topic IN(SELECT id_topic FROM forum_topics WHERE plateforme_id = 35);
DELETE FROM forum_topics WHERE plateforme_id = 35;
DELETE FROM forum_categories WHERE plateforme_id = 35;

-- Membership
DELETE FROM user_permission WHERE id_membre IN(SELECT id_membre FROM membre WHERE id_plateforme = 35);
DELETE FROM user_setting WHERE id_membre IN(SELECT id_membre FROM membre WHERE id_plateforme = 35);
DELETE FROM membre WHERE id_plateforme = 35;

-- Resolve plateforme par défaut + catégorie
UPDATE personne AS p1
SET id_plateforme = (SELECT MAX(id_plateforme) FROM membre WHERE id_personne = p1.id_pers)
WHERE id_plateforme = 35;
UPDATE personne SET int_categorie = 1002 WHERE id_plateforme IS NULL;

-- Plateforme en elle-même
DELETE FROM plateforme WHERE id_plateforme = 35;

COMMIT;

-- /!\ Ne pas oublier les fichiers + folder

