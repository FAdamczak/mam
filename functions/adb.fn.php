<?php

/**
 * Retourne la liste des tablettes connectées à l'ordinateur
 *
 * @return Array  [0] : id de la tablette - [1] : son statut (device, unauthorized, offline,...)
 */
function getTablettes() {
  $l = strlen("List of devices attached");
  $parc = array();

  $res = trim(substr(getAdb("devices"),$l+1));
  // On récupère un tableau avec une ligne par device
  $t_res = explode("\n",$res);
  foreach($t_res as $ligne) {
    //  On récupére un tableau info avec :
    //  - [0] : l'id de la tablette
    //  - [1] : son statut (device, unauthorized, offline,...)
    $info = explode("\t",$ligne);
    //  S'il y a effectivement une tablette connectée
    if ($info[0] !="") {
      //debug($info);
      $t = new Tablette($info[0],$info[1]);
      array_push($parc, $t);
    }
  }
  return $parc;
}

/**
 * Retourne l'état de charge de la batterie d'une tablette (en %)
 *
 * @param [String] $idTablette : id de la tablette
 * @return [String] : en % état de charge de la tablette
 */
function getBatterie($idTablette) {
  if (PREFIXE_ADB== "") {
    $res = getAdbS($idTablette,"shell dumpsys battery | grep level");
  } else {
    $res = getAdbS($idTablette,"shell dumpsys battery | findstr level");
  }

  $charge = substr($res,strpos($res,": ")+2);
  return $charge;
}

/**
 * Retourne le nom d'une tablette
 *
 * @param [String] id de la tablette
 * @return [String]
 */
function getNomTablette($idTablette) {

  if (PREFIXE_ADB == "") {
    $suite1 = " | grep name";
    $suite2 = " | grep -A1 Local";
  } else {
    $suite1 = " | findstr name";
    $suite2 = " | findstr -A1 Local";
  }

  $cmd = "shell dumpsys bluetooth_manager".$suite1;
  $res = getAdbS($idTablette,$cmd);

  if ($res !="") {
    $tRes = explode("\n",$res);
    $nomTablette = trim(substr($tRes[0],6));
  } else {
    $cmd = "shell dumpsys bluetooth_manager".$suite2;
    $res = getAdbS($idTablette,$cmd);

    $p1 = strpos($res,"Name=")+strlen("Name=");
    $p2 = strpos($res,":");
    $nomTablette = substr($res, $p1, strlen($res)-$p2-strlen("Name="));
  }

  return $nomTablette;
}

/**
 * Retourne VRAI si une tablette est connectée à internet
 *
 * @param [String] id de la tablette
 * @return boolean
 */
function isConnecte($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell getprop | grep dhcp.wlan0.result");
  } else {
    $res = getAdbS($idTablette, "shell getprop | findstr dhcp.wlan0.result");
  }

  return strpos($res,"[ok]")>0;
}

/**
 * Installe un fichier apk sur une tablette
 *
 * @param [String] $tablette [id de la tablette]
 * @param [String] $apk      [chemin d'accès du fichier apk]
 *
 */
function InstallerApk($tablette,$apk) {
  $cmd = "install ".$apk;
  $res = getAdbS($tablette, $cmd);
  //debug($res);
  $pos = strpos($res,"Success");
  if ($pos === false) {
    $valRetour = substr(
      $res,
      strrpos($res,"[")+1,
      strrpos($res,"]")-strrpos($res,"[")-1
    );
  } else {
    $valRetour = "Success";
  }
  /*
  if (strpos($res,"Success")>0) {
    $valRetour = "Application installée";
  } else {
    $valRetour = substr(
      $res,
      strrpos($res,"[")+1,
      strrpos($res,"]")-strrpos($res,"[")-1
    );
  }
  */
  //debug($valRetour);
  return $valRetour;
}

/**
 * Désinstalle une appli identifiée par son package sur une tablette
 * @param  [String] $tablette [Id de la tablette]
 * @param  [String] $package  [Nom du packge à désinstaller]
 * @return [Boolean]          [Vrai si l'application a bien été désintallée]
 */
function desinstallerAppli($tablette, $package) {
  $cmd = "shell pm uninstall ".$package;
  $res = getAdbS($tablette, $cmd);
  $pos = strpos($res,"Success");
  ($pos === false) ? $ret = false : $ret = true;
  return $ret;
}

/**
 * Effectue le listing des packages contenus sur une tablette
 * @param  [String] $tablette [Id de la tablette]
 * @return [String]           [Liste des packages]
 */
function listePackage($tablette) {
  $cmd = "shell pm list package";
  $res = getAdbS($tablette, $cmd);
  return $res;
}

/**
 * Retourne l'espace disponible (en octet) sur une tablette donnée
 * @param  [String] $tablette [Id de la tablette]
 * @return [float]           [Espace disponible]
 */
function getFreeSpace($tablette) {
  $res = getAdbS($tablette,"shell df /data/media");
  //  On enlève tous les espaces de la chaine retournée
  $tRes = explode(" ",$res);
  //  On construit un tableau propre pour récupérer les différentes valeurs
  $tMem = [];
  $j = 0;
  for ($i=1; $i < count($tRes);++$i) {
    if($tRes[$i] !="") {
      $tMem[$j] = $tRes[$i];
      ++$j;
    }
  }
  //  Maintenant on sait que la mémoire dispo est en indice 6
  $mem = $tMem[6];
  //  On dispose maintenant d'une chaine de caractère qui se termine soit par
  //  M (méga) soit par G(giga) qu'il faut transformer en Octet
  $unite = substr($mem,strlen($mem)-1);
  $valeur = substr($mem,0,strlen($mem)-1);
  switch ($unite) {
    case 'M':
      $freeSpace = $valeur * 1000000;
      break;
    case 'G':
      $freeSpace = $valeur * 1000000000;
      break;
    default:
      debug($unite);
      break;
  }
  return $freeSpace;
}

function getAdresseIp($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell netcfg | grep wlan0");
  } else {
    $res = getAdbS($idTablette, "shell netcfg | findstr wlan0");
  }

  // extraction de l'adresse mac
  $tRes = explode(" ",$res);
  $mac = $tRes[count($tRes)-1];

  // extraction de l'adresse IP
  $posSlash = strpos($res,"/");
  $ch1 = substr($res,0,$posSlash);
  $pos2 = strrpos($ch1," ");
  $ip = substr($ch1,$pos2+1);

  $retour = array();
  $retour["ip"] = $ip;
  $retour["mac"] = $mac;
  //debug($retour);
  return($retour);
}

function getModele($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell cat /system/build.prop | grep ro.product.model");
  } else {
    $res = getAdbS($idTablette, "shell cat /system/build.prop | findstr ro.product.model");
  }
  $modele = substr($res,strpos($res,"=")+1);
  return $modele;
}

function getVersion($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell cat /system/build.prop | grep ro.build.version.release");
  } else {
    $res = getAdbS($idTablette, "shell cat /system/build.prop | findstr ro.build.version.release");
  }
  $modele = substr($res,strpos($res,"=")+1);
  return $modele;
}

function getBorne($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell dumpsys netstats | grep iface");
  } else {
    $res = getAdbS($idTablette, "shell dumpsys netstats | findstr iface");
  }

  //debug($res);
  $sousModele = substr($res,strrpos($res,"=")+2);
  $modele = substr($sousModele,0,strlen($sousModele)-3);
  return $modele;
}

function getFreeDisk($idTablette) {
  if (PREFIXE_ADB == "") {
    $res = getAdbS($idTablette, "shell dumpsys diskstats");
  } else {
    $res = getAdbS($idTablette, "shell dumpsys diskstats");
  }

  $tRes = explode("\n",$res);
  $data = $tRes[1];

  $pos1 = strpos($data,":")+1;
  $pos2 = strpos($data,"total")-1;
  $space1 = trim(substr($data,$pos1,$pos2-$pos1));

  $pos1 = strrpos($data,"=");
  $pos2 = strrpos($data,"%");
  $space2 =substr($data,$pos1+2,$pos2-$pos1-2);

  $freeSpace = array();
  $freeSpace["valeur"] = $space1;
  $freeSpace["pourcent"] = $space2;
  return $freeSpace;
}

/**
 * Reboote une tablette identifiée par son Id
 * @param  [String] $id [Id de la tablette]
 */
function rebootTabeltte($id) {
  $res = getAdbS($id,"reboot");
}

/**
 * Éteint une tablette identifiée par son id
 * @param  [String] $id [Id de la tablette]
 */
function eteindreTablette($id) {
  $res = getAdbS($id,"shell reboot -p");
}

function hardDesinstalle($package,$id) {
  $cmd = "shell pm uninstall --user 0 ".trim($package);
  $res = getAdbS($id,$cmd);
  return $res;
}
/****************************************************************************/
/*                                                                          */
/****************************************************************************/

/**
 * Envoi une commande ADB à toute les tablettes
 *
 * @param [String] $cmd : commande à envoyer
 * @return[String] résultat de la commande
 */
function getAdb($cmd) {
  $commande = PREFIXE_ADB."adb ".$cmd;
  $res = shell_exec($commande);
  //debug($res);
  return trim($res);
}

/**
 * Envoie une commande ADD à UNE tablette précise
 *
 * @param [String] $id : id de la tablette
 * @param [String] $cmd : commande à envoyer
 * @return [String] :  résultat de la commande
 */
function getAdbS($id, $cmd) {
  $commande = PREFIXE_ADB."adb -s ".$id." ".$cmd;
  //debug($commande);
  $res = shell_exec($commande);
  //debug($res);
  return trim($res);
}
