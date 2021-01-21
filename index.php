<!DOCTYPE html>
<html>
<?php

session_start();

require 'config/config.php';
require 'functions/database.fn.php';
require 'functions/functions.fn.php';
require 'functions/adb.fn.php';
require 'templates/_head.php';
require 'classes/Tablette.php';
require 'classes/Application.php';

$pdo = getPDOLink($config);

//debug($_SESSION);
?>

<body>

  <?php
    include 'templates/_navbar.php';

    if(count($_POST)>0) {
      //debug($_POST);
      $leParc = explode("***",$_POST["leParc"]);
      array_pop($leParc);
      //debug($leParc);
      switch ($_POST["choix"]) {
        case 'inst_app':
          foreach($leParc as $tablette) {
            $res = installer_application($pdo,$tablette,$_POST["application"]);
            debug($res);
            if ($res === true) {
              $_SESSION['message'][$tablette] = "<p class='alert alert-success'>Application installée</p>";
            } else {
              $_SESSION['message'][$tablette] = "<p class='alert alert-danger'>$res</p>";
            }
          }
          header('Location: index.php');
          break;

        case 'sup_app':
          foreach($leParc as $tablette) {
            desinstaller_application($pdo,$tablette,$_POST["application"]);
          }
          header('Location: index.php');
          break;

        case 'inst_pro':
          $rapport = installer_profil($pdo,$leParc,$_POST['profil']);
          print "<div class='container mt-4 mb-4'>";
          print $rapport;
          print "</div>";
          //debug($rapport);
          break;

        case 'sup_pro':
          $rapport = desinstaller_profil($pdo,$leParc,$_POST['profil']);
          print "<div class='container mt-4 mb-4'>";
          print $rapport;
          print "</div>";
          break;

        case 'env_fichier':
          envoyerFichier($leParc,$_FILES);
          header('Location: index.php');
          break;

        case 'rec_fichier':
          recupFichiers($leParc);
          header('Location: index.php');
          break;

        default: // photo
          recupPhotos($leParc);
          header('Location: index.php');
          break;
      }

    } else {



    $parc = getTablettes();
    //debug($parc);
    $bibliotheque = getBibliotheque($pdo);
    $profils = getProfils($pdo);
  ?>




  <div class="container mt-4">
    <div class="row">

      <div class="col-sm-8" id="principal">
  			<div class="row">

          <?php
            foreach ($parc as $tablette) {
              print "<div class='col-sm-3 mt-2 mb-2'>";
              print "<div class='card tablette'>";
              print "<div class='card-body'>";
              print "<p class='card-title nomTablette'>";
              print $tablette->getNumSerie();
              print "</p>";
              print "<span class='bouton'>";
              print $tablette->getStatutBouton();
              print "</span>";
              print "<p class='card-text mt-2' zoneMessage>";
              if (isset($_SESSION["message"][$tablette->getNumSerie()]))  {
                print $_SESSION["message"][$tablette->getNumSerie()];
                unset($_SESSION["message"][$tablette->getNumSerie()]);
              } else {
                print "";
              }
              print "</p>";

              print "<div class='row'>";

              print "<div class='col batterie'>";
              print "<span class='batterie_icone'>";
              print $tablette->getBatterieIcone();
              print "</span>";
              print "<span class='batterie_texte'>";
              print " ".$tablette->getBatterie();
              print "</span>";
              print "</div>";

              print "<div class='col'>";
              print "<span class='connexion'>";
              print $tablette->getConnexion();
              print "</span>";
              print "</div>";

              print "</div>";

              print "</div>"; //  card-body
              print "</div>"; //  card
              print "</div>"; //  col-sm-3
            }
           ?>

        </div> <!-- row -->
      </div> <!-- col-sm-8 #principal -->

      <div class="col-sm-4" id="menu">

          <div class="col-sm-12 mt-3">
            <span class="badge badge-pill badge-primary" id="nbSel">
              123
            </span>
             appareil(s) sélectionné(s)
          </div>

          <hr />

          <form method="post" action="" enctype="multipart/form-data">

            <div class="form-group">
              <label for="application">Application</label>
              <select class="form-control" id="application" name="application">
                <?php
                  foreach ($bibliotheque as $application) {
                    print "<option value='".$application->getId()."'>";
                    print $application->getNom();
                    print "</option>";

                  }
                ?>
              </select>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="choix" id="instApp" value="inst_app" checked />
              <label class="form-check-label" for="instApp">
                Installer/déployer
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="choix" id="suppApp" value="sup_app">
              <label class="form-check-label" for="suppApp">
                Désinstaller/supprimer
              </label>
            </div>

            <hr />

            <div class="form-group">
              <label for="application">Profil</label>
              <select class="form-control" id="profil" name="profil">
                <?php
                  foreach ($profils as $profil) {
                    print "<option value='".$profil['id_profil']."'>";
                    print $profil['nom_profil'];
                    print "</option>";
                  }
                ?>
              </select>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="choix" id="instpro" value="inst_pro">
              <label class="form-check-label" for="instPro">
                Installer/déployer
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="choix" id="suppPro" value="sup_pro">
              <label class="form-check-label" for="suppPro">
                Désinstaller
              </label>
            </div>

            <hr />

            <div class="form-check">
              <input class="form-check-input" type="radio" name="choix" id="envFichier" value="env_fichier">
              <label class="form-check-label" for="envFichier">
                Envoyer un fichier
              </label>
            </div>

            <div class="input-group mb-3 mt-2">
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="fichier" name="fichier" />
                <label class="custom-file-label" for="fichier">Sélectionner le fichier</label>
              </div>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" name="choix" id="recFichier" value="rec_fichier">
              <label class="form-check-label" for="recFichier">
                Récupérer les fichiers
              </label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="radio" name="choix" id="photos" value="photo">
              <label class="form-check-label" for="photo">
                Récupérer le contenu de la galerie
              </label>
            </div>


            <hr />

            <div class="text-right mt-4 mb-4">
              <input type="hidden" id="leParc" name="leParc" />
              <input type="button" class="btn btn-primary" id="btValide" value="Valider" />
              <!-- Button trigger modal -->
              <!--
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#valider">
                Valider
              </button>
              -->
            </div>

          </form>

      </div> <!-- col-smd-4 #menu -->

    </div> <!-- row -->
  </div> <!-- container-->



  <!-- Modal -->
  <div class="modal" id="valider" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="toto">Save changes</button>
        </div>
      </div>
    </div>
  </div>

  <?php } ?>

  <script src="templates/js/jquery-3.5.1.min.js"></script>
  <script src="templates/js/bootstrap.min.js"></script>
  <script src="templates/js/app.js"></script>
</body>
</html>
