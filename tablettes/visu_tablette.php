<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title></title>
  <meta name="viewport" content="width=device-width">
  <link rel="stylesheet" type="text/css" href="../templates/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="../templates/css/fontawesome-all.min.css" />
  <!--<link rel="stylesheet" type="text/css" href="templates/css/styles.css" />-->
  <link rel="stylesheet" type="text/css" href="../templates/css/tablette.css" />
</head>

<body>

  <?php
    session_start();

    require '../config/config.php';
    require '../functions/database.fn.php';
    require '../functions/functions.fn.php';
    require '../functions/adb.fn.php';

    if(count($_POST)>0) {

      print "traitement";
      debug($_POST);

      switch($_POST['operation']) {
        case "DESINSTALLER" :
          $res = hardDesinstalle($_POST["param"],$_POST["idTablette"]);
          $message = "<p class='alert alert-primary'>Résultat de la désinstallation : ";
          $message.= $res;
          $message.="</p>";
          $_SESSION['message'] = $message;
          $url = "Location: visu_tablette.php?id=".$_POST['idTablette'];
          header($url);
          //debug($res);
          break;

        case "RECUPAPK" :
          $res=saveApkFromPackage(trim($_POST['param']),$_POST['idTablette']);
          $message = "<p class='alert alert-primary'>Résultat de la sauvegarde du fichier APK : ";
          $message.= $res;
          $message.= "</p>";
          $_SESSION['message'] = $message;
          $url = "Location: visu_tablette.php?id=".$_POST['idTablette'];
          header($url);
          //debug($res);
          break;

        case "SAUVEGARDE" :
          $res = backupTablette($_POST['idTablette']);
          $url = "Location: ../index.php";
          header($url);
          break;

        case "RESTORE" :
          debug("Restauration ");
          $source = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".
                    DIRECTORY_SEPARATOR."FICHIERS".DIRECTORY_SEPARATOR.
                    "SAUVEGARDES".DIRECTORY_SEPARATOR.$_POST["param"];
          $cmd = "restore ".$source;
          debug($cmd);
          $res = getAdbS($_POST["idTablette"],$cmd);
          $url = "Location: ../index.php";
          header($url);
          break;

        case "ETEINDRE" :
          eteindreTablette($_POST['idTablette']);
          header('Location: ../index.php');
          break;

        case "REBOOT" :
          rebootTabeltte($_POST["idTablette"]);
          header('Location: ../index.php');
          break;

        case "REINITIALISER" :
          $cmd = "recovery --wipe_data";
          $res = getAdbS($_POST['idTablette'],$cmd);
          debug($res);
          $url = "Location: ../index.php";
          //header($url);
          break;
        /*
        case "RING" :
          debug("faire sonner la tablette");
          break;
        */

      }




    } else {

    $id = $_GET['id'];
    $info = array();

    //  La tablette est-elle connectée à internet ?
    $connexion = isConnecte($id);

    // Recherche des informations système
    $info["nom"] = getNomTablette($id);
    $info["modele"] = getModele($id);
    $info["version"] = getVersion($id);

    //  Recherches des informations matérielle
    $info["batterie"] = getBatterie($id);
    $freeDisk = getFreeDisk($id);
    $info["freeDisk1"] = $freeDisk["valeur"];
    $info["freeDisk2"] = $freeDisk["pourcent"];

    // Recherche des informations relatives à la connexion
    if ($connexion) {
      $adresses = getAdresseIp($id);
      $info["ip"] = $adresses["ip"];
      $info["mac"] = $adresses["mac"];
      $info["borne"] = getBorne($id);
      //$info["mac"] = getAdresseMac($id);
    }
    //debug($info);

    $pdo = getPDOLink($config);
    $packages_biblio = getPackageBibliotheque($pdo);
    $packages_tablette = getPackageTablette($id);
    sort($packages_tablette,SORT_STRING);

    //debug($packages_tablette);
    //debug($packages_biblio);


  ?>

  <div class="container mt-4 mb-4">

    <div class="row mt-3 mb-3 mx-2">
      <a href="../index.php">
        <i class="fas fa-home icone"></i>
      </a>
    </div>

    <div class="row">
      <div class="col-12">
        <?php
          if (isset($_SESSION['message'])) {
            print $_SESSION['message'];
            unset($_SESSION['message']);
          }
         ?>
      </div>
    </div>

    <div class="row">
      <div class="col-4">
        <div class="px-2 py-2 info_generale alert alert-primary">
          <ul>
            <li><?= "Id : ".$id; ?></li>
            <li><?= "Nom : ".$info["nom"]; ?></li>
            <li><?= "Modèle : ".$info["modele"]; ?></li>
            <li><?= "Android : ".$info["version"]; ?></li>
          </ul>
        </div>
      </div>

      <div class="col-4">
        <div class="px-2 py-2 info_systeme alert alert-info">
          <ul>
            <li><?= "Espace disque disponible : ".$info["freeDisk1"]; ?></li>
            <li><?= "Soit : ".$info["freeDisk2"]." %"; ?></li>
            <li><?= "Batterie : ".$info["batterie"]." % "; ?></li>
          </ul>
        </div>
      </div>

      <div class="col-4">
        <div class="px-2 py-2 info_connexion alert alert-secondary">
          <?php if (!$connexion) {
            print "Non connectée";
          } else { ?>
            <ul>
              <li><?= "IP : ".$info["ip"]; ?></li>
              <li><?= "MAC: ".$info["mac"]; ?></li>
              <li><?= "Borne : ".$info["borne"]; ?></li>
            </ul>
          <?php } ?>
        </div>
      </div>
    </diV>

    <div class="row">
      <div class="col-sm-8 mt-3 mb-3">
        <div class="px-2 py-2 liste_appli">
          Applications installées :
          <select name="applications" id="applications">
            <?php
              foreach($packages_tablette as $package) {
                if (in_array(trim($package), $packages_biblio)) {
                  $affiche = "*** ".$package;
                } else {
                  $affiche = $package;
                }
                print "<option value='".$package."'>";
                print $affiche;
                print "</option>";
              }
            ?>
          </select>
          <small  class="form-text text-muted">*** Package = l'application est
            dans la bibliothèque</small>
        </div>
      </div>
      <div class="col-sm-2 py-4">
        <button type="button" class="btn btn-primary" id="bt_desinstalle">
          Désinstaller
        </button>
      </div>
      <div class="col-sm-2 py-4">
        <button type="button" class="btn btn-primary" id="bt_recupApk">
          Récupérer APK
        </button>
      </div>

    </div> <!-- row -> liste les packages installés + 2 boutons -->
    <?php /*
    <div class="row">
      <div class="col-6 mt-3 mb-3">
        <div class="px-2 py-2 liste_appli">
          Applications installées :
          <select name="applications" id="applications">
            <?php
              foreach($packages_tablette as $package) {
                if (in_array(trim($package), $packages_biblio)) {
                  $affiche = "*** ".$package;
                } else {
                  $affiche = $package;
                }
                print "<option value='".$package."'>";
                print $affiche;
                print "</option>";
              }
            ?>
          </select>
          <small  class="form-text text-muted">*** Package = l'application est
            dans la bibliothèque</small>
        </div>

        <button type="button" class="btn btn-primary" id="bt_desinstalle">
          Désinstaller
        </button>
        <button type="button" class="btn btn-primary" id="bt_recupApk">
          Récupérer APK
        </button>

      </div>
      */ ?>
      <div class="row">
        <div class="col-sm-2 mb-3 mt-3">
          <button type="button" class="btn btn-primary" id="bt_sauvegarge">Sauvegarder</button>
        </div>
        <div class="col-sm-2 mb-3 mt-3">
          <button type="button" class="btn btn-primary" id="bt_restore">Restaurer</button>

        </div>
        <div class="col-sm-2 mb-3 mt-3">
          <button type="button" class="btn btn-primary" id="bt_eteindre">Éteindre</button>
        </div>
        <div class="col-sm-2 mb-3 mt-3">
          <button type="button" class="btn btn-primary" id="bt_reboot">Redémarrer</button>
        </div>
        <div class="col-sm-2 mb-3 mt-3">
          <button type="button" class="btn btn-primary" id="bt_reinit">Réinitialiser</button>
        </div>
        <div class="col-sm-2 mb-3 mt-3">
          <!-- Place pour un futur bouton => "recup Nova Launcher config"
          <button type="button" class="btn btn-primary" id="bt_reinit">Réinitialiser</button>
          -->
        </div>
      </div> <!-- rom -> 6 boutons -->

      <div class="row">
        <div class="col-sm-8 mx-2 mb-3">
          <div class="mt-1" id="divRestore">
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="fichierRestore"
              lang="fr" />
              <label class="custom-file-label" for="fichierRestore">Fichier à restaurer</label>
            </div>
            <div class="input-group-append mt-2">
              <button class="btn btn-outline-secondary" type="button" id="bt_valideRestore">Valider</button>
            </div>
          </div>
        </div>
        <div class="col">
          &nbsp;
        </div>
      </div>
      <?php /*
      <div class="col-6 mt-3 mb-3">
        <div class="row">
          <div class="col-6 mt-1 mb-1">
            <button type="button" class="btn btn-primary" id="bt_sauvegarge">Sauvegarder</button>
          </div>
          <div class="col-6 mt-1 mb-1">
            <div>
              <button type="button" class="btn btn-primary" id="bt_restore">Restaurer</button>
            </div>
            <div class="mt-1" id="divRestore">
              <div class="custom-file">
                  <input type="file" class="custom-file-input" id="fichierRestore"
                   lang="fr" />
                  <label class="custom-file-label" for="fichierRestore">Fichier à restaurer</label>
                </div>
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary" type="button" id="bt_valideRestore">Valider</button>
                </div>
            </div>
          </div>
          <div class="col-6 mt-1 mb-1">
            <button type="button" class="btn btn-primary" id="bt_eteindre">Éteindre</button>
          </div>
          <div class="col-6 mt-1 mb-1">
            <button type="button" class="btn btn-primary" id="bt_reboot">Redémarrer</button>
          </div>
          <div class="col-6 mt-1 mb-1">
            <button type="button" class="btn btn-primary" id="bt_reinit">Réinitialiser</button>
          </div>
          <!--
          <div class="col-6 mt-1 mb-1">
            <button type="button" class="btn btn-primary" id="bt_ring">Sonner</button>
          </div>
          -->
        </div>
      </div>
      */ ?>

    </div>

    <form id="theForm" method="post" action="">
      <input type="hidden" id="idTablette" name="idTablette" value="<?= $id ?>" />
      <input type="hidden" id="operation" name="operation" />
      <input type="hidden" id="param" name="param" />
    </form>

  </div>

  <script src="../templates/js/jquery-3.5.1.min.js"></script>
  <script src="../templates/js/bootstrap.min.js"></script>
  <script src="../templates/js/tablette.js"></script>
</body>

</html>

<?php

}

?>
