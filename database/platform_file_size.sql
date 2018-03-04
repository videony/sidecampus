select sizes.id_plateforme, p1.tx_nom, sizes.size
FROM
	(select id_plateforme, sum(int_size)/1000000 as size
    FROM file, folder
    where file.id_folder = folder.id_folder
    group by id_plateforme ) as sizes, plateforme as p1
WHERE p1.id_plateforme = sizes.id_plateforme