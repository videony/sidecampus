/**
Can be used to manually confirm a user who has not received the confirm email
*/
UPDATE personne SET confirm = 1, int_categorie = 1002
WHERE tx_email = 'email@domain.com';
