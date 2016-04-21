<?php


// Filtrage sur l'IP
$allow_all_ip=true;
// Liste des IP à autoriser si $allow_all_ip==false
$allowed_ip=array("");

// Autoriser la création de compte FTP pour les e-mails de la forme xxxxx@mondomaine.com (en précisant mondomaine.com ci-dessous)
$allowed_email_domain="mondomaine.com";

// Le préfixe des utilisateurs FTP imposé par OVH (à récupérer dans votre espace client)
$ovh_ftpuser_prefix="mondomainekm-"; // ne pas oublier le tiret à la fin




// Configuration des clés d'API OVH
// => Consultez le document Word fourni
$service_name="mondomaineduserviceftp.com"; // A COMPLETER (en general le nom de domaine du service FTP)
define("MY_AK", "A COMPLETER"); 	// Clef application 
define("MY_AS", "A COMPLETER"); 	// Secret key 
define("MY_CK", "A COMPLETER"); 	// Consumer key : à récupérer avec get-key3.php
