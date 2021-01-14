<?php

function getPDOLink($config) {
  try {
    $dsn = 'mysql:dbname='.$config['database'].';host='.$config['host'].
    ';charset=utf8';
    return new PDO($dsn,$config['username'],$config['password']);
  } catch (PDOException $e) {
    print '<pre>';
    var_dump($e->getMessage());
    print '</pre>';
    exit ('ERREUR DE CONNEXION A LA BDD');
  }
}

?>
