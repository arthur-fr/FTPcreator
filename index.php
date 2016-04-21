<?php
header('Content-Type: text/html; charset=utf-8');

require("config.php");


// Pour mémoire et debug : Doc OVH API => https://eu.api.ovh.com/console/#/hosting/web
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>FTP Creator</title>
	<style>
		html { background:  #08233e url(images/bg.jpg) no-repeat center center fixed; background-size: cover; }
		html,body{ margin:0; padding:0; font-family:'Open Sans', Arial; color: #ffffff; text-align: center; }
		#transp{ width: 100%; height: 100%; float: left; background: #08233e; opacity: 0.7; filter: alpha(opacity=70); position: absolute; z-index: -1; }
		#wrapper{ width: 940px; margin: 0 auto; }
		.logo{ width: 940px; float: left; margin-top: 100px; }
		.content{ width: 940px; float: left; }
	</style>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>
	<meta name="robots" content="noindex, nofollow">
</head>
<body>
	<div id="transp">
	</div>
	<div id="wrapper">
		<div class="logo"><img src="images/logo.png" title="logo" /></div>
		<div class="content">
			<p style="font-size:35px; font-weight:bold">Création d'espaces FTP à la demande</p>

<?php
if ($allow_all_ip===false && !in_array($_SERVER["REMOTE_ADDR"], $allow_from_ip)) {
?>
			<p style="font-size:20px;">Ce service n'est pas accessible depuis cette adresse IP.</p>
<?php
}elseif (!isset($_POST['email']) || !preg_match("`[a-z0-9-_.]*@".str_replace(".", "\.",$allowed_email_domain)."`i", $_POST['email'])) {
?>
			<p style="font-size:20px;">Merci d'indiquer votre adresse e-mail @<?php echo $allowed_email_domain; ?> pour recevoir des identifiants de connexion :</p>
			<form method="POST"><input name="email" placeholder="votre-email@<?php echo $allowed_email_domain; ?>" style="font-size:20px; width:300px; height:40px" required="required" type="email" pattern="[a-z0-9-_.]*@<?php echo str_replace(".", "\.",$allowed_email_domain); ?>" /><button type="submit" style="font-size:20px; height:50px">&raquo; Créer un espace FTP</button></form>
<?php
}else{
	require_once ("OvhApi.php");


	$ovh = new OvhApi(); 


	//$res = $ovh->get('/hosting/web');
	//var_dump($res);

	// On calcul l'id de l'utilisateur à créer 
	$id_min=100;
	$id_max=999;

	$dern_id=file_get_contents("../last_ftp_ID.txt");
	$id=(($dern_id+1) > $id_max) ? $id_min : $dern_id+1;

	$login=$ovh_ftpuser_prefix.$id;
	$mdp=randomPassword();

	$users = $ovh->get('/hosting/web/'.$service_name.'/user');
	//var_dump($users);

	if (in_array($login, $users)) {
		// Utilisateur déjà existant, on le supprime d'abord
		$res_delete = $ovh->delete('/hosting/web/'.$service_name.'/user/'.$login);
		//var_dump($res_delete);
		sleep(30);
	}
	// On supprime les 4 prochains utilisateurs pour prendre de l'avance
	$id_plus1=(($id+1) > $id_max) ? $id_min : $id+1;
	if (in_array($ovh_ftpuser_prefix.$id_plus1, $users)) {
		$res_delete = $ovh->delete('/hosting/web/'.$service_name.'/user/'.$ovh_ftpuser_prefix.$id_plus1);
		//var_dump($res_delete);
	}
	$id_plus2=(($id_plus1+1) > $id_max) ? $id_min : $id_plus1+1;
	if (in_array($ovh_ftpuser_prefix.$id_plus2, $users)) {
		$res_delete = $ovh->delete('/hosting/web/'.$service_name.'/user/'.$ovh_ftpuser_prefix.$id_plus2);
		//var_dump($res_delete);
	}
	$id_plus3=(($id_plus2+1) > $id_max) ? $id_min : $id_plus2+1;
	if (in_array($ovh_ftpuser_prefix.$id_plus3, $users)) {
		$res_delete = $ovh->delete('/hosting/web/'.$service_name.'/user/'.$ovh_ftpuser_prefix.$id_plus3);
		//var_dump($res_delete);
	}
	$id_plus4=(($id_plus3+1) > $id_max) ? $id_min : $id_plus3+1;
	if (in_array($ovh_ftpuser_prefix.$id_plus4, $users)) {
		$res_delete = $ovh->delete('/hosting/web/'.$service_name.'/user/'.$ovh_ftpuser_prefix.$id_plus4);
		//var_dump($res_delete);
	}


	//var_dump(">> ID A CREER : ".$id); die();

	$res = $ovh->post('/hosting/web/'.$service_name.'/user', array( 
			'home' => date("Y-m-d")."_".$login."_".uniqid(),
			'login' => $login,
			'password' => $mdp
		));

	if (@$res['status']=="todo") {
		print "<p style='font-size:20px;'>Votre espace FTP est en cours de création !<br />Vous allez recevoir un e-mail avec les informations de connexion dans quelques secondes.</p>";


		$headers = 'From: FTP creator <noreply@ftpcreator.github.com>' . "\r\n";
		$headers .='Content-Type: text/plain; charset="utf-8"'."\r\n"; // ici on envoie le mail au format texte encodé en UTF-8
		$headers .='Content-Transfer-Encoding: 8bit'."\r\n"; // ici on précise qu'il y a des caractères accentués
		mail($_POST['email'], "Création d'un espace FTP", "Bonjour,\n\nVoici les informations pour votre nouvel espace FTP :\n    Serveur FTP : ".$service_name."\n    Nom d'utilisateur : ".$login."\n    Mot de passe : ".$mdp."\n\n    Pour voir le contenu depuis un navigateur web :\n    ftp://".$login.":".$mdp."@".$service_name."\n\n/!\ IMPORTANT : Votre espace FTP est encore en cours de création et sera prêt environ 2 minutes après la réception de ce message !\n\nBonne journée !\nhttp://".$service_name."/", $headers);
		file_put_contents("../log_creation_ftp.log", file_get_contents("../log_creation_ftp.log")."[".date("Y-m-d H:i:s")."] ".$_POST['email']." ".$login.":".$mdp."\r\n");
		file_put_contents("../last_ftp_ID.txt", $id);		
	}elseif (@$res['message']=="You can not create the same user at the same time") {
		print "<p style='font-size:20px;'>Un espace FTP est déjà en cours de création, merci de réessayer d'ici 2 minutes...</p>";
	}else{
		var_dump($res);
	}
}
?>
		</div>
	</div>
</body>
</html>










<?php
/**** FONCTIONS ****/

function randomPassword($len = 8){
	if(($len%2)!==0){ // Length paramenter must be a multiple of 2
		$len=8;
	}
	$length=$len-2; // Makes room for the two-digit number on the end
	$conso=array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
	$vocal=array('a','e','i','o','u');
	$password='';
	srand ((double)microtime()*1000000);
	$max = $length/2;
	for($i=1; $i<=$max; $i++){
		$password.=$conso[rand(0,19)];
		$password.=$vocal[rand(0,4)];
	}
	$password.=rand(10,99);
	$newpass = $password;
	return $newpass;
}