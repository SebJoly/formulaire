<?php
require './php/Formulaire.php';
use formulaire\php\Formulaire;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Demo Plugin Formulaire</title>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
		
		<!-- jQuery -->
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<!-- jQuery UI -->
		<script src="./lib/jquery-ui/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.min.css">
		<link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.structure.min.css">
		<link rel="stylesheet" href="./lib/jquery-ui/jquery-ui.theme.min.css">
		<!-- formulaire -->
		<script src="./lib/formulaire.js"></script>
    </head>
	<body>
		<div id="test" class="col-md-offset-3 col-md-6 col-sm-10">
			<h1 style="text-align: center;">Formulaire de test</h1><br />
			<?php
			$form = new Formulaire();
			$form->init("localhost","mysql","formulaire","etudiant2","root","admin");
			$form->addField("id", "ID");
			$form->setRequired("id",false);
			$form->addField("prenom","Prénom");
			$form->addField("nom","Nom");
			$form->addField("datenaiss","Date de naissance");
			//$form->addField("niveau","Niveau");
			$form->addField("age","Age");
			$form->setRequired("age",false);
			$form->addField("alternant","Alternance ?");
			$form->addField("commentaire","Commentaire");
			$form->loadValuesFromIndex("id","22");
			//$form->isUpdateForm("id","22");
			$form->show();
			?>
		</div>
	</body>
</html>