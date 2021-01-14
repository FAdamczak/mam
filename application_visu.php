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
    include 'templates/_navbar_application.php';
    $bibliotheque = getBibliotheque($pdo);
    //debug($bibliotheque);

  ?>

  <div class="container mt-3">
    <div class="row">

        <?php
          foreach($bibliotheque as $application) {
            print "<div class='col-3 mt-2'>";
            print "<p class='py-2 px-2 appliBiblio'>";
            print $application->getNom();
            print "</p>";
            print "</div>";
          }

        ?>

    </div> <!-- row -->
  </div> <!-- container -->

  <script src="templates/js/jquery-3.5.1.min.js"></script>
  <script src="templates/js/bootstrap.min.js"></script>
  <script src="templates/js/application.js"></script>

</body>

</html>
