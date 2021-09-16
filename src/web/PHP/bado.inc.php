<?php

function connexion_mysql() {
  $identifiants = file_get_contents("../../sql/id_connexion"); // récuperer le contenu d'un fichier en dehors du serveur web pour obtenir les identifiants sécures de la base de données.
  $user = explode(" ", $identifiants)[0];
  $pass = explode(" ", $identifiants)[1];
  $db   = trim(explode(" ", $identifiants)[2]);

  $connexion = new mysqli("localhost", $user, $pass, $db);
  return $connexion;
}

?>
