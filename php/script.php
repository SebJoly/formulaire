<?php

header('Content-Type: text/text');

require './Formulaire.php';
use formulaire\php\Formulaire;

$currentTime = time();
if($dossier = opendir('../temp')) {
	while(false !== ($fichier = readdir($dossier))) {
		if (($currentTime-$fichier > 24*3600) && $fichier-0 != $_POST['formID']) {
			unlink('../temp/' . $fichier);
		}
	}
}

if (isset($_POST['formID']) && isset($_POST['mode'])) {
	$path = '../temp/' . $_POST['formID'];
	
	if (file_exists($path)) {
		$form = unserialize(file_get_contents($path));
		
		// Insertion dans la table
		if($_POST['mode'] == 'insert') {
 			$status = $form->insert($_POST['values']);
		}
		// Update
		elseif ($_POST['mode'] == 'update') {
			$status = $form->update($_POST['values']);
		}
		
		echo "Resultat : " . $status;
	}else{
		throw new Exception();
	}
}
?>