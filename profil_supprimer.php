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

      $id=$_POST["idProfil"];

      $sql = "DELETE FROM app_profil WHERE id_profil = '$id'";
      if (!$res = $pdo->query($sql)) {
        print "<p class='alert alert-danger mx-3 my-3 mt-4' role='alert'>";
        print "ERREUR : Un problème est survenu lors de la suppression dans la base de données<br />";
        print "(Suppression du profil dans la table app_profil)";
        print "</p>";
        die();
      }

      $sql = "DELETE FROM profil WHERE id_profil = '$id'";
      if (!$res = $pdo->query($sql)) {
        print "<p class='alert alert-danger mx-3 my-3 mt-4' role='alert'>";
        print "ERREUR : Un problème est survenu lors de la suppression dans la base de données";
        print "</p>";
        die();
      }

      print "<p class='alert alert-success mx-3 my-3 mt-4' role='alert'>";
      print "Le profil a été supprimé";
      print "</p>";

    } else {
        $profils = getProfils($pdo);
      ?>


      <div class="container">

        <h4 class="mt-4 mb-4">Supprimer un profil</h4>

        <form id="leForm" method="post" action="">

          <div class="form-group">
            <label for="idAppli">Sélectionner le profil à supprimer</label>
            <select class="custom-select" id="idAppli" name="idProfil">
              <?php
                foreach ($profils as $profil) {
                  print "<option value='".$profil["id_profil"]."'>";
                  print $profil["nom_profil"];
                  print "</option>";
                }
              ?>
            </select>
          </div>

          <button type="button" class="btn btn-danger mt-4" id="btSupprimer">SUPPRIMER</button>

        </form>

        <div class="modal" tabindex="-1" id="message">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <p>
                Confirmer la suppression du profil
              </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
              <button type="button" id="confirmDelete" class="btn btn-danger">Confirmer</button>
            </div>
          </div>
        </div>
      </div>
      </div>

    <?php  }  ?>

    <script src="templates/js/jquery-3.5.1.min.js"></script>
    <script src="templates/js/bootstrap.min.js"></script>
    <script src="templates/js/profil.js"></script>

  </body>
</html>
