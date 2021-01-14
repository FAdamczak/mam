<?php

/**
 * Retourne toutes les applications de la bibliothèque par ordre alphabétique
 * @param  [PDO] $pdo []
 * @return [array]      [Tableau de classs Application]
 */
function getBibliotheque($pdo) {
  $sql = "SELECT * FROM application ORDER BY nom_application";
  $res = $pdo->query($sql);
  return $res->fetchAll(PDO::FETCH_CLASS,"Application");
}

/**
 * Retourne l'ensemble des profils par ordre alphabétique
 * @param  [PDO] $pdo []
 * @return [array]      [tableau associatif de profils]
 */
function getProfils($pdo) {
  $sql="SELECT * FROM profil ORDER BY nom_profil";
  $res = $pdo->query($sql);
  return $res->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne la liste des profils ET les applications contenues dans celui-ci
 * @param  [type] $pdo [description]
 * @return [Array]      [Tableau association+indexé => ["nomProfil"][nom_app1, ...]]
 */
function getProfilsEtAppli($pdo) {
  $sql = "SELECT * FROM profil ORDER BY nom_profil";
  $res = $pdo->query($sql);
  $profils = $res->fetchAll(PDO::FETCH_ASSOC);
  $tRes = [];
  foreach($profils as $profil) {
    $nom = $profil["nom_profil"];
    $sql = "SELECT A.nom_application FROM app_profil AP INNER JOIN application A ";
    $sql.= "ON AP.id_application = A.id_application ";
    $sql.= "WHERE AP.id_profil = '". $profil["id_profil"]."'";
    $res = $pdo->query($sql);
    $t = $res->fetchAll(PDO::FETCH_COLUMN);
    //debug($t);
    $tRes[$nom] = $t;
  }
  return $tRes;
}

/**
 * Retourne une instance de Application à  partir d'un id d'application
 * @param  [PDO] $pdo          []
 * @param  [Strng] $id         [Id d el'application]
 * @return [Application]       [Instance de la classe Application]
 */
function getAppli($pdo,$id) {
  $sql = "SELECT * FROM application WHERE id_application='$id'";
  $res = $pdo->query($sql);
  $res->setFetchMode(PDO::FETCH_CLASS, 'Application');
  return $res->fetch();
}

/**
 * Retourne la liste des packages CONNUS de la bibliothèque
 * @param  [type] $pdo []
 * @return [type]      [Array]
 */
function getPackageBibliotheque($pdo) {
  $sql = "SELECT package_application FROM application ORDER BY package_application";
  $res = $pdo->query($sql);
  $query = $res->fetchAll(PDO::FETCH_COLUMN);
  $packages = array();
  foreach ($query as $package) {
    if($package != "") {
        array_push($packages, $package);
    }
  }
  return $packages;
}

/**
 * Installe une application identifiée par son id dans la BDD sur une tablette
 * @param  [PDO] $pdo []]
 * @param  [String] $tablette [id de lta tablette]
 * @param  [String] $id       [id de l'application dans la table application]
 * @return [String/bool]      [true si l'installation s'est bien déroulée ou un message d'erreur]
 */
function installer_application($pdo, $tablette, $id) {
  // récupérer l'application
  $application = getAppli($pdo,$id);
  //debug($application);

  // si l'application n'a jamais été installée, il faut récupérer son package.
  // pour cela :
  // 1- faire la liste des packages présents sur la tablette AVANT Installation
  // 2- faire la liste des packages APRES installation
  // 3- le package installé est la différence entre les 2 listes
  // cqfd
  if ($application->getPackage()=="") {
    $avant = explode('package:',listePackage($tablette));
  }

  $apk = "FICHIERS/BIBLIOTHEQUE/".$application->getFichier();
  $res = InstallerApk($tablette,$apk);
  $pos = strpos($res,"Success");

  if ($pos === false) {
    $retourInstallation = $res;
  } else {
    if ($application->getPackage()=="") {
      $apres = explode('package:',listePackage($tablette));
      $nomPackage = array_diff($apres,$avant);
      foreach($nomPackage as $package) {
        $leNom = trim($package);
        //debug($leNom);
      }
      $id = $application->getId();
      $sql = "UPDATE application SET package_application = '$leNom' WHERE id_application='$id'";
      //debug($sql);
      $res_sql = $pdo->query($sql);
    }
    $retourInstallation = true;
  }
  return $retourInstallation;
}

/**
 * Désinstalle une application données sur une tablette donnée
 * @param  [PDO] $pdo             []
 * @param  [String] $tablette     [Id de la tablette]
 * @param  [String] $application  [Id de l'application dans la BDD]
 * @return [type]                 [Message balisé (<p>) : résulat de l'opération]
 */
function desinstaller_application($pdo,$tablette,$application) {
  //  Récupérer le package de l'application
  $sql = "SELECT package_application FROM application WHERE id_application = '$application'";
  $res = $pdo->query($sql);
  $tPackage = $res->fetch(PDO::FETCH_ASSOC);
  $package = $tPackage['package_application'];
  //$cmd = "shell pm uninstall ".$package;
  //$res = getAdbS($tablette, $cmd);
  $res = desinstallerAppli($tablette, $package);
  //debug($res);
  if ($res=="Success") {
    $_SESSION['message'][$tablette] = "<p class='alert alert-success'>Application désinstallée</p>";
  } else {
    $_SESSION['message'][$tablette] = "<p class='alert alert-danger'>Echec</p>";
  }
  //debug($res);
}

/**
 * Installa un profil
 * @param  [PDO] $pdo         []
 * @param  [Array] $parc      [Tableau d'id de tablettes]
 * @param  [String] $idProfil [Id du profil à dépolyer]
 * @return [String]           [Rapport mis en forme : résultats des opérations]
 */
function installer_profil($pdo,$parc,$idProfil) {
  $rapport = "";

  //  Rechercher le nom du profil et les appli associées
  $sql = "SELECT * FROM profil P, application A, app_profil AP ";
  $sql.= "WHERE P.id_profil = AP.id_profil ";
  $sql.= "AND A.id_application = AP.id_application ";
  $sql.= "AND P.id_profil = '$idProfil'";

  $res = $pdo->query($sql);
  $tRes = $res->fetchAll(PDO::FETCH_ASSOC);

  $nomProfil = $tRes[0]["nom_profil"];
  $rapport.= "<div class='rapport'>";

  $rapport.= "<div class='rapport-titre'>Installation du profil <b>$nomProfil</b></div>";

  foreach ($parc as $tablette) {
    $rapport.= "<div class='rapport-tablette px-1 py-1'>";
    $rapport.= "Tablette n° : ".$tablette;
    foreach ($tRes as $elt) {
      //debug($elt);
      $rapport.= "<div class='rapport-application px-3 py-1'>Installation de ".$elt['nom_application']." : ";
      //$src = "FICHIERS/BIBLIOTHEQUE/".$elt["fichier_application"];
      $ret = installer_application($pdo,$tablette, $elt["id_application"]);
      if ($ret === true) {
        $rapport.= "<span class='rapport-success'>Installation réussie</span>";
      } else {
        $rapport.= "<span class='rapport-echec'>Echec, ".$ret."</span>";
      }
      $rapport.="</div>"; //  class=rapport-application
    }
    $rapport.="</div>"; //  class=rapport-tablette
  }

  $rapport.="</div>"; // class = rapport

  //debug($rapport);

  return $rapport;
}

/**
 * Désintaller les applications d'un profil sur un ensemble de tablettes
 * @param  [PDO] $pdo       []
 * @param  [Array] $parc     [Tableau d'id de tablettes]
 * @param  [String] $idProfil [id du profil à désinstaller]
 * @return [String]           [Rapport mis en forme html : résultats des opérations]
 */
function desinstaller_profil($pdo,$parc,$idProfil) {
  $rapport = "";

  //  Rechercher le nom du profil et les appli associées
  $sql = "SELECT * FROM profil P, application A, app_profil AP ";
  $sql.= "WHERE P.id_profil = AP.id_profil ";
  $sql.= "AND A.id_application = AP.id_application ";
  $sql.= "AND P.id_profil = '$idProfil'";

  $res = $pdo->query($sql);
  $tRes = $res->fetchAll(PDO::FETCH_ASSOC);

  $nomProfil = $tRes[0]["nom_profil"];
  $rapport.= "<div class='rapport'>";

  $rapport.= "<div class='rapport-titre'>Désinstallation du profil <b>$nomProfil</b></div>";

  foreach ($parc as $tablette) {
    $rapport.= "<div class='rapport-tablette px-1 py-1'>";
    $rapport.= "Tablette n° : ".$tablette;
    foreach ($tRes as $elt) {
      //debug($elt);

      $rapport.= "<div class='rapport-application px-3 py-1'>Désinstallation de ".$elt['nom_application']." : ";

      if ($elt["package_application"]=="") {
        $rapport.= "<span class='rapport-echec'>Package inconnu, désinstallation impossible</span>";
      } else {
        //debug($elt['package_application']);
        $ret = desinstallerAppli($tablette, $elt['package_application']);
        if ($ret) {
          $rapport.= "<span class='rapport-success'>Désinstallation réussie</span>";
        } else {
          $rapport.= "<span class='rapport-echec'>Echec</span>";
        }
      }

      $rapport.="</div>"; //  class=rapport-application

    }
    $rapport.="</div>"; //  class=rapport-tablette
  }

  $rapport.="</div>"; // class = rapport

  return $rapport;
}

function envoyerFichier($parc,$files){
  debug($files);
  $nomFichier = $files['fichier']['name'];
  $source = $files['fichier']['tmp_name'];
  foreach($parc as $tablette) {
    //debug($tablette);
    $res = getAdbS($tablette,"shell mkdir sdcard/documents");
    $destination = "sdcard/documents/".$nomFichier;
    $cmd = "push ".$source." ".$destination;
    $res = getAdbS($tablette,$cmd);
    if (strpos($res,"pushed")!== false) {
      $_SESSION['message'][$tablette] = "<p class='alert alert-success'>Fichier reçu</p>";
    } else {
      $_SESSION['message'][$tablette] = "<p class='alert alert-danger'>Echec</p>";
    }
    //debug($res);
  }
}

function recupFichiers($parc) {
  foreach($parc as $tablette) {
    $dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".DIRECTORY_SEPARATOR
      ."DOCUMENTS".DIRECTORY_SEPARATOR.$tablette."_";
    //  On récupère la liste des fichiers contenus dnas le dossier documents de la tablette
    $res = getAdbS($tablette, "shell ls sdcard/documents");
    $tRes = explode("\n",$res);
    foreach($tRes as $fichier) {
      //debug($fichier);
      $nomTablette = trim($fichier);
      $nom = str_replace(" ","_",$nomTablette);
      $source = "sdcard/documents/\"".$nomTablette."\"";
      $destination = $dest.$nom;
      $cmd = "pull ".$source." ".$destination;
      //debug ($cmd);
      $res = getAdbS($tablette,$cmd);
    }
    $_SESSION['message'][$tablette] = "<p class='alert alert-primary'>Terminé</p>";
    //debug($res);
  }
}

function recupPhotos($parc) {

  foreach($parc as $tablette) {
    $dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".DIRECTORY_SEPARATOR
      ."PHOTOS".DIRECTORY_SEPARATOR.$tablette."_";
    //  On recupère la liste des photos dans le dossier /sdard/DCIM/camera
    $res = getAdbS($tablette,"shell ls /mnt/sdcard/DCIM/Camera");
    $tRes = explode("\n",$res);
    foreach($tRes as $fichier) {
      $nomTablette = trim($fichier);
      debug($nomTablette);
      $nom = str_replace(" ","_",$nomTablette);
      $source = "sdcard/DCIM/Camera/\"".$nomTablette."\"";
      $destination = $dest.$nom;
      $cmd = "pull ".$source." ".$destination;
      //debug ($cmd);
      $res = getAdbS($tablette,$cmd);
    }
    $_SESSION['message'][$tablette] = "<p class='alert alert-primary'>Terminé</p>";
  }
}


/**
 * Retourne la liste des packages contenus sur une tablette donnée
 * @param  [String] $tablette [Id de la tablette]
 * @return [Array]            [Tableau indexé des packages]
 */
function getPackageTablette($tablette) {
  $res = listePackage($tablette);
  $t_packages = explode("package:",$res);
  $packages = array();
  foreach($t_packages as $package) {
    if ($package !="") {
      array_push($packages,$package);
    }
  }
  return $packages;
}


/**
 * Sauvegarde sur l'ordinateur un fichier APK de la tablette
 * @param  [String] $package [nom du package dont il faut extraire l'apk]
 * @param  [String] $id      [id de la tablette]
 * @return [String]          [Résultat de la sauvegarde]
 */
function saveApkFromPackage($package,$id) {
  $cmd = "shell pm path ".$package;
  $res = getAdbS($id,$cmd);
  $pathApk = substr($res,8);
  $p = strrpos($pathApk,"/");
  $nomApk = substr($pathApk,$p+1);

  $dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".DIRECTORY_SEPARATOR.
          "FICHIERS".DIRECTORY_SEPARATOR."APK".DIRECTORY_SEPARATOR.$nomApk;

  //debug($pathApk);
  //debug($nomApk);
  //debug($dest);

  $cmd = "pull ".$pathApk." ".$dest;
  $res = getAdbS($id,$cmd);
  $tRes = explode("\n",$res);

  $infoRetour = $tRes[count($tRes)-1];

  return $infoRetour;
}


function backupTablette($id) {
  $today = time();
  $timeSauvegarde = date('Y_m_d_His', $today);
  $alea = rand(0,255);
  $nomSauvegarde = $timeSauvegarde."_".$alea.".ab";
  $dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."mam".DIRECTORY_SEPARATOR.
          "FICHIERS".DIRECTORY_SEPARATOR."SAUVEGARDES".DIRECTORY_SEPARATOR.$nomSauvegarde;

  $cmd = "backup -apk -shared -all -f ".$dest;
  $res = getAdbS($id,$cmd);
  return($res);
}

/************************************************************************/

function str_secure($string) {
    return trim(htmlspecialchars($string));
}

function debug($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

?>
