<!DOCTYPE html>
<html>

<?php
  require 'config/config.php';
  require 'functions/database.fn.php';
  require 'functions/functions.fn.php';
  require 'functions/adb.fn.php';
  require 'templates/_head.php';
  require 'classes/Tablette.php';
  $pdo = getPDOLink($config);
?>

<body>

  <?php
    include 'templates/_navbar_application.php';

    if (count($_POST)>0) {
      //debug($_POST);
      //debug($_FILES);

      print "<div class='container mt-4'>";


      //  Upload du fichier .APK
      $dest = __DIR__ ."/FICHIERS/BIBLIOTHEQUE/";
      $fileInfo = new SplFileInfo($_FILES['fichier']['name']);
      //  On vérifie l'extension qui doit être .apk
      if (strtoupper($fileInfo->getExtension()) != "APK") {
        print "<p class='alert alert-danger' role='alert'>";
        print "ERREUR : le fichier sélectionné n'est pas un fichier .APK";
        print "</p>";
        die();
      }
      // Upload du fichier
      $newFile = $_FILES['fichier']['name'];

      if (!move_uploaded_file($_FILES['fichier']['tmp_name'],$dest.$newFile)){
        print "<p class='alert alert-danger' role='alert'>";
        print "ERREUR : Un problème est survenu lors de l'enregistrement du fichier APK";
        print "</p>";
        die();
      }

      // Enregistrement du fichier dans la BDD
      $nom = $_POST['nomApplication'];
      $taille = $_FILES['fichier']['size'];
      $sql = "INSERT INTO application ";
      $sql.="(nom_application,fichier_application,package_application,taille_application) ";
      $sql.="VALUES ('$nom','$newFile','','$taille')";
      //debug($sql);
      if (!$res = $pdo->query($sql)) {
        print "<p class='alert alert-danger mx-3 my-3 mt-4' role='alert'>";
        print "ERREUR : Un problème est survenu lors de l'enregistrement dans la base de données";
        print "</p>";
        die();
      }

      print "<p class='alert alert-success mx-3 my-3 mt-4' role='alert'>";
      print "L'application a été enregistrée dans la bibliothèque";
      print "</p>";

      print "</div>"; // class=container

    } else {
  ?>


  <div class="container">
    <h4 class="mt-4 mb-4">Ajout d'une nouvelle application</h4>
    <form method="post" action="" enctype="multipart/form-data">

      <div class="form-group">
        <label for="nomApplication">Nom de l'application dans la bibliothèque</label>
        <input type="text" class="form-control" id="nomApplication" name="nomApplication" required />
      </div>

      <div class="form-group">
        <label for="fichier">Fichier .apk correspondant</label>
        <div class="custom-file">
          <input type="file" class="custom-file-input" id="fichier" name="fichier" required />
          <label class="custom-file-label" for="inputGroupFile04"></label>
        </div>
      </div>

      <input type="submit" class="btn btn-primary" value="Valider" />


    </form>
  </div>

  <?php } ?>

  <script src="templates/js/jquery-3.5.1.min.js"></script>
  <script src="templates/js/bootstrap.min.js"></script>
  <script src="templates/js/application.js"></script>


</body>

</html>
