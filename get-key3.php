<?php

/********************************************************************
**
** CREATION David Arneau - AD-WAIBE - WWW.AD-WAIBE.FR
** Adaptation du Copyright initial : http://servuc.fr/blog/?p=110
** Fonctionne sous easyPhp + multi-droits
**
*********************************************************************/

require_once 'OvhApi.php'; // pour récupérer les clefs 

// Droits d'accès à l'API
$l_droits = json_encode (array( 
        array('method' => 'POST',   'path' => '/*'), 
		array('method' => 'GET',    'path' => '/*'),
		array('method' => 'PUT',    'path' => '/*'),
		array('method' => 'DELETE', 'path' => '/*')
    ));

$l_header = array(         
	'Content-Type:application/json',         
	'X-Ovh-Application:'.MY_AK     
	);
	
$curl = curl_init("https://eu.api.ovh.com/1.0/auth/credential");    
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);     
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");     
curl_setopt($curl, CURLOPT_HTTPHEADER, $l_header);     
curl_setopt($curl, CURLOPT_POSTFIELDS,'{"accessRules": '.$l_droits.'}');     
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($curl);  
   

echo "Voici votre consumerKey, copiez-là dans OvhApi.php : ";     
echo preg_replace("/^.*(\"consumerKey\":\")([A-Za-z0-9]+).*$/" , "$2" , $result);     
$l_url = "https://eu.api.ovh.com/auth/?credentialToken=" . preg_replace("/^.*(credentialToken=)([A-Za-z0-9]+).*$/" , "$2" , $result);     
echo "<br/><br/>Ensuite, vous devez activer votre compte et donner une durée de validation de votre clef (Choisissez Unlimited si c'est sans limite de durée) - CLIQUEZ ICI : <a target='_blank' href='$l_url'>$l_url</a>";
	

?>