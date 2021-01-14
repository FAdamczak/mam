<!DOCTYPE html>
<html>

<?php
  require 'config/config.php';
  require 'functions/database.fn.php';
  require 'functions/functions.fn.php';
  require 'functions/adb.fn.php';
  require 'templates/_head.php';
  require 'classes/Application.php';
  $pdo = getPDOLink($config);
?>

<body>

  <?php
    include 'templates/_navbar_application.php';

    if (count($_POST)>0) {
      //debug($_POST);

      $id=$_POST["idAppli"];

      //  Suppression de l'application dans les profils
      $sql = "DELETE FROM app_profil WHERE id_application = '$id'";

      if (!$res = $pdo->query($sql)) {
        print "<p class='alert alert-danger mx-3 my-3 mt-4' role='alert'>";
        print "ERREUR : Un problème est survenu lors de la suppression dans la base de données";
        print "<br />(suppression de l'application dans les app_profil)";
        print "</p>";
        die();
      }

      //  Suppression de l'application dans la base de donnée
      $sql = "DELETE FROM application WHERE id_application = '$id'";

      if (!$res = $pdo->query($sql)) {
        print "<p class='alert alert-danger mx-3 my-3 mt-4' role='alert'>";
        print "ERREUR : Un problème est survenu lors de la suppression dans la base de données";
        print "</p>";
        die();
      }

      print "<p class='alert alert-success mx-3 my-3 mt-4' role='alert'>";
      print "L'application a été supprimée";
      print "</p>";


    }
    else {
        $bibliotheque = getBibliotheque($pdo);
      ?>

      <div class="container">
        <h4 class="mt-4 mb-4">Supprimer une application</h4>

        <form id="leForm" method="post" action="">

          <div class="form-group">
            <label for="idAppli">Sélectionner l'application à supprimer</label>
            <select class="custom-select" id="idAppli" name="idAppli">
              <?php
                foreach ($bibliotheque as $application) {
                  print "<option value='".$application->getId()."'>";
                  print $application->getNom();
                  print "</option>";
                }
              ?>
            </select>
          </div>

          <button type="button" class="btn btn-danger mt-4" id="btSupprimer">SUPPRIMER</button>

        </form>
      </div>

      <div class="modal" tabindex="-1" id="message">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <p>
              Confirmer la suppression de l'application<br />
              Les profils seront éventuellement modifiés.
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
            <button type="button" id="confirmDelete" class="btn btn-danger">Confirmer</button>
          </div>
        </div>
      </div>
    </div>

      <?php } ?>

      <script src="templates/js/jquery-3.5.1.min.js"></script>
      <script src="templates/js/bootstrap.min.js"></script>
      <script src="templates/js/application.js"></script>

    </body>

  </html>
