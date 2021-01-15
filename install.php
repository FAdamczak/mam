<!DOCTYPE html>
<html>

  <?php

  require 'templates/_head.php';

  if (count($_POST) > 0) {
    //  Installation de la bdd
    //
    /*
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
    */


    // Contact avec la base de donnée
    echo "<p>Tentative de connexion à la base de données ..........";
    try {
      $db = "mysql:host=".$_POST["dbHost"].";dbname=".$_POST["dbName"];
      $dbh = new PDO($db, $_POST["dbUsername"], $_POST["dbPassword"]);
    } catch (PDOException $e) {
        print "<br />Erreur !: " . $e->getMessage();
    }
    echo "<b>Connexion réussie</b></p>";


    // Création des tables
    echo "<p>Création des tables dans la base de données....</p>";

    echo "<p>Table APPLICATION...... ";
    $sql = "CREATE TABLE `application` (
      `id_application` tinyint(4) NOT NULL,
      `nom_application` varchar(50) NOT NULL,
      `fichier_application` varchar(500) NOT NULL,
      `package_application` varchar(250) DEFAULT NULL,
      `taille_application` varchar(50) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    executeSql($dbh,$sql);
    executeSql($dbh,"ALTER TABLE `application` ADD PRIMARY KEY (`id_application`)");
    executeSql($dbh, "ALTER TABLE `application`MODIFY `id_application` tinyint(4) NOT NULL AUTO_INCREMENT");
    print "<b>OK</b></p>";

    echo "<p>Table PROFILS...... ";
    $sql="CREATE TABLE `profil` (
      `id_profil` tinyint(4) NOT NULL,
      `nom_profil` varchar(250) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    executeSql($dbh,$sql);
    executeSql($dbh,"ALTER TABLE `profil`ADD PRIMARY KEY (`id_profil`);");
    executeSql($dbh,"ALTER TABLE `application` MODIFY `id_application` tinyint(4) NOT NULL AUTO_INCREMENT;");
    print "<b>OK</b></p>";

    echo "<p>Table app_profil..... ";
    $sql = "CREATE TABLE `app_profil` (
        `id_application` tinyint(4) NOT NULL,
        `id_profil` tinyint(4) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    executeSql($dbh,$sql);
    print "<b>OK</b></p>";


    // Création du fichier de configuration
    echo "<p>Création du fichier de configuration...............";

    if (preg_match("#Win#", getenv("HTTP_USER_AGENT"))) {
      $os = "Windows";
    } else {
      $os = "Linux";
    }

    $filename = "config/config_model.php";
    $dest = "config/config.php";

    $handle = fopen($filename, "r");
    if ($handle === false) {
      die("Erreur lors de la l'ouverture du temmplate de configuration");
    }
    $data = fread($handle, filesize($filename));
    if ($data  === false) {
      die("Erreur lors de la lecture du template de configuration");
    }
    fclose($handle);

    $data = str_replace("{{dbhost}}", $_POST['dbHost'],$data);
    $data = str_replace("{{dbname}}", $_POST['dbName'],$data);
    $data = str_replace("{{dbusername}}", $_POST['dbUsername'],$data);
    $data = str_replace("{{dbpassword}}", $_POST['dbPassword'],$data);

    if($os == 'linux') {
      $data = str_replace("{{prefixe}}", "",$data);
    } else {
      $data = str_replace("{{prefixe}}", "platform-tools",$data);
    }

    $fp = fopen($dest, 'w');
    if ($fp  === false) {
      die("Erreur lors de la création du fichier de configuration");
    }
    if (fwrite($fp,$data) === false) {
      die("Erreur lors de l'écriture du fichier de configuration");
    }
    fclose($fp);
    echo "<b>OK</b>";

    //  Création des dossiers nécessaires
    $folders = [
  		"DOCUMENTS",
  		"FICHIERS",
  		"FICHIERS". DIRECTORY_SEPARATOR ."APK",
  		"FICHIERS". DIRECTORY_SEPARATOR ."BIBLIOTHEQUE",
  		"FICHIERS". DIRECTORY_SEPARATOR ."SAUVEGARDE",
  		"PHOTOS"
  	];
    echo "<p>Création du fichier de configuration...............</p>";
    foreach($folders as $folder) {

  		print "<p>";
  		print "$folder<br>";
  		if (is_dir($folder)) {
  			print "Ce dossier existe déja";
  		}
  		else {
  			$dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".
            DIRECTORY_SEPARATOR.$folder;
        //print_r($dest);
  			if (mkdir($dest)) {
  				//chmod($dest,777);
  				print "OK";
  			} else {
  				print "Erreur";
  			}
  		}
  		print "</p>";
  	}



    print "<h3>INSTALLATION TERMINÉE</h3>";
    print "<a href='index.php'>Aller sur l'application</a>";


  } else {
    ?>
    <div class="container">
      <h3>Installation de l'application</h3>
      <form method="post" action="">

        <div class="form-group">
          <label for="dbHost">Adresse serveur (localhost)</label>
          <input type="text" class="form-control" id="dbHost" name="dbHost" required />
        </div>

        <div class="form-group">
          <label for="dbName">Nom de la base de données</label>
          <input type="text" class="form-control" id="dbName" name="dbName" required />
        </div>

        <div class="form-group">
          <label for="dbUser">Nom d'utilisateur</label>
          <input type="text" class="form-control" id="dbUsername" name="dbUsername" required />
        </div>

        <div class="form-group">
          <label for="dbPassword">Mot de passe</label>
          <input type="password" class="form-control" id="dbPassword" name="dbPassword" />
        </div>

        <!--
        <div class="form-check">
          <input class="form-check-input" type="radio" name="typeInstall" id="bien" value="linux" checked>
          <label class="form-check-label" for="bien">
            Installation sur Linux <i class="fa fa-smile-o" aria-hidden="true"></i>
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="typeInstall" id="moche" value="windows">
          <label class="form-check-label" for="moche">
            Installation sur W$ <i class="fa fa-frown-o" aria-hidden="true"></i>
          </label>
        </div>
        -->
        <div class="mt-4">
          <input type="submit" class="btn btn-primary" value="Installation" />
        </div>

      </form>
    </div>
    <?php
  }

  ?>

  <script src="templates/js/jquery-3.5.1.min.js"></script>
  <script src="templates/js/bootstrap.min.js"></script>

 </html>

<?php
  function executeSql($db,$sql) {
    try {
      $res = $db->query($sql);
    } catch (PDOException $e) {
        print "<br />Erreur !: " . $e->getMessage();
        die();
    }
    //var_dump($res);
  }

?>
