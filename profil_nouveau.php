<!DOCTYPE html>
<html>

<?php
  require 'config/config.php';
  require 'functions/database.fn.php';
  require 'functions/functions.fn.php';
  require 'functions/adb.fn.php';
  require 'templates/_head.php';
  //require 'classes/Tablette.php';
  require 'classes/Application.php';
  $pdo = getPDOLink($config);
?>

<body>

  <?php

    include 'templates/_navbar_profil.php';

    if (count($_POST)>0) {

      //debug($_POST);

      $nom = str_secure($_POST["nom"]);
      $applications = explode("***",$_POST["lesApplis"]);
      array_pop($applications);

      print "<div class='container mt-4'>";



      //  Vérifier si un profil de même nom existe déja
      $sql="SELECT nom_profil FROM profil WHERE nom_profil = '$nom'";
      if (!$res=$pdo->query($sql)) {
        print "<div class='alert alert-danger' role='alert'>";
        print "Erreur d'accès à la BDD";
        print "</div>";
        die();
      } else {
        if ($res->rowCount()==0) {
          //  On peut créer le profil
          $sql = "INSERT INTO profil (nom_profil) VALUES ('$nom')";
          if (!$res=$pdo->query($sql)) {
            print "<div class='alert alert-danger' role='alert'>";
            print "Erreur lors de la création du profil dans la table PROFIL";
            print "</div>";
            die();
          } else {
            $idProfil = $pdo->lastInsertId();
            foreach($applications as $app) {
              $sql = "INSERT INTO app_profil (id_application, id_profil) VALUES ('$app','$idProfil')";
              if (!$res=$pdo->query($sql)) {
                print "<div class='alert alert-danger' role='alert'>";
                print "Erreur lors de la création du profil (application : $app)";
                print "</div>";
                die();
              }
            }
            print "<div class='alert alert-success' role='alert'>";
            print "Le profil ".$_POST['nom']." contenant ".count($applications)." applications a été créé";
            print "</div>";
          }
        } else {
          print "<div class='alert alert-danger' role='alert'>";
          print "Erreur : il y a déja un profil avec ce nom (". $_POST['nom'].")";
          print "</div>";
        }
      }

      print "</div>"; // class = container

    } else {
      $bibliotheque = getBibliotheque($pdo);
      ?>
      <div class="container mt-4">

        <h4 class="mt-4 mb-4">Création d'un profil</h4>

        <form id="nouveauProfil" method="post" action="">
          <div class="form-group">
           <label for="nom">Nom du profil : </label>
           <input type="text" class="form-control" id="nom" name="nom" />
          </div>
          <p>
           <span class="badge badge-pill badge-primary" id="nbSel">0</span>
           applications contenues dans le profil
          </p>
          <input type="hidden" name="lesApplis" id="lesApplis" />
          Ajouter les applications au profil puis
          <button type="button" id="btValide" class="btn btn-primary">
            Créer le profil
          </button>

          <div class="row mt-4">
            <div class="col-12 mt-3 mb-3">
              Sélectionner les applications à ajouter au profil :
            </div>
            <?php
              foreach($bibliotheque as $application) {
                $id = "app_".$application->getId();
                print "<div class='col-3 mt-2'>";
                print "<div class='py-2 px-2 appliBiblio' id='$id'>";
                print $application->getNom();
                print "</div>";
                print "</div>";
              }
            ?>
          </div>
        </form>

        <!-- Modal -->
      	<div class="modal" id="message" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      	  <div class="modal-dialog">
      		<div class="modal-content">
      		  <div class="modal-header">
              <h5 class="modal-title">Erreurs !</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="fermerModal">
        			  <span aria-hidden="true">&times;</span>
        			</button>
      		  </div>
      		  <div class="modal-body" id="infoMessage">
            </div>
      		</div>
      	  </div>
      	</div>


      </div>

      <?php
    }
  ?>

  <script src="templates/js/jquery-3.5.1.min.js"></script>
  <script src="templates/js/bootstrap.min.js"></script>
  <script src="templates/js/profil.js"></script>

</body>
</html>
