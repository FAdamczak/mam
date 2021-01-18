<?php

class Tablette {

  private $numSerie;
  private $statut;
  private $nom;
  private $statutBouton;
  private $batterie;
  private $batterieIcone;
  private $connexion;

  public function __construct($id, $statut) {
    $this->numSerie = $id;
    $this->statut = $statut;

    $this->setNom();
    $this->setStatutBouton($statut);
    $this->setBatterie();
    $this->setConnexion();
  }


  public function setStatutBouton($statut) {
    //debug($statut);
    switch ($statut) {
      case 'device':
        $url = "tablettes/visu_tablette.php?id=".$this->numSerie;
        $bouton = "<a href='$url' class='btn btn-primary idTablette'>";
        $bouton.= $this->nom."</a>";
      break;
      case 'unauthorized':
        $bouton = "<a href='#' class='btn btn-secondary idTablette'>";
        $bouton.= $this->nom."unauthorized</a>";
      break;
      case 'offline':
        $bouton = "<a href='#' class='btn btn-warning idTablette'>";
        $bouton.= "offline</a>";
      break;
      default:
        $boutons = "<a href='#' class='btn btn-secondary'>Problème inconnu</a>";
        break;
    }
    $this->statutBouton = $bouton;
  }

  public function setNom() {
    if ($this->statut == "device") {
      $this->nom = getNomTablette($this->numSerie);
    } else {
      $this->nom="";
    }
  }

  public function setBatterie() {
    if ($this->statut == "device") {
      $charge = getBatterie($this->numSerie);
      $this->batterie = $charge."%";

      if ($charge < "10") {
        $this->batterieIcone = "<i class='fa fa-battery-empty' aria-hidden='true'></i>";
      }
      if (($charge >= "10") && ($charge < "40")) {
        $this->batterieIcone = "<i class='fa fa-battery-quarter' aria-hidden='true'></i>";
      }
      if (($charge >= "40") && ($charge < "60")) {
        $this->batterieIcone = "<i class='fa fa-battery-half' aria-hidden='true'></i>";
      }
      if (($charge >="60") && ($charge < "80")) {
        $this->batterieIcone = "<i class='fa fa-battery-three-quarters' aria-hidden='true'></i>";
      }
      if ($charge > "80") {
        $this->batterieIcone = "<i class='fa fa-battery-full' aria-hidden='true'></i>";
      }
    } else {
      $this->batterie = "";
      $this->batterieIcone="";
    }
  }

  public function setConnexion() {
    if ($this->statut == "device") {
      if (isConnecte($this->numSerie)) {
        $this->connexion = "<i class='fa fa-wifi' aria-hidden='true'></i>";
      } else {
        $this->connexion = "<i class='fa fa-ban' aria-hidden='true'></i>";
      }
    } else {
      $this->connexion = "";
    }
  }

  public function getNumSerie() {
    return $this->numSerie;
  }

  public function getStatut() {
    return $this->statut;
  }

  public function getStatutBouton() {
    return $this->statutBouton;
  }

  public function getBatterie() {
    return $this->batterie;
  }

  public function getBatterieIcone() {
    return $this->batterieIcone;
  }

  public function getConnexion() {
    return $this->connexion;
  }
}

 ?>
