<!DOCTYPE html>
<html>

<?php
  require 'config/config.php';
  require 'functions/database.fn.php';
  require 'functions/functions.fn.php';
  require 'functions/adb.fn.php';
  require 'templates/_head.php';
  //require 'classes/Tablette.php';
  //require 'classes/Application.php';

  $pdo = getPDOLink($config);

?>

  <body>
    <?php
      include 'templates/_navbar_profil.php';
      $profils = getProfilsEtAppli($pdo);
      //$profils = getProfils($pdo);
    ?>
    <div class="container">

      <h4 class="mt-4 mb-4">Liste des profils existants</h4>

      <div class="row">
        <?php
          /*
          foreach($profils as $profil) {
            print "<div class='col-3 mt-2'>";
            print "<p class='py-2 px-2 appliBiblio'>";
            print $profil["nom_profil"];
            print "</p>";
            print "</div>";
          }
          */

          foreach ($profils as $key=>$value) {
            //debug($key);
            $applis = "";
            foreach ($value as $nom) {
              $applis.=$nom."<br>";
            }
            //debug($applis);

            print "<div class='col-3 mt-2'>";
              print "<div class='card-body encadre'>";
              print "<h5 class='card-title'>$key</h5>";
              print "<p class='card-text'>$applis</p>";
              print "</div>";
            print "</div>";

          }

        ?>
      </div>

    </div> <!-- container -->

    <script src="templates/js/jquery-3.5.1.min.js"></script>
    <script src="templates/js/bootstrap.min.js"></script>
    <script src="templates/js/profil.js"></script>

  </body>


</html>
