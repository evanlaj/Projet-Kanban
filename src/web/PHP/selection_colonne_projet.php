<?php

//require 'debug.php';

require 'bado.inc.php';

$connexion = connexion_mysql();

session_start();

$project_id = $_POST["id"];

/*###########################################################
#                     TEST PROJET PUBLIC                    #
###########################################################*/

$query = $connexion->prepare('SELECT public FROM projet WHERE id=?');
$query->bind_param('i', $project_id);
$query->execute();

$query_result = $query->get_result();
$query->close();

$is_public = $query_result->fetch_array()['public'];

/*###########################################################
#                      TEST PROPRIETAIRE                    #
###########################################################*/

$user_is_owner = 0;

if(!$is_public) {

  if (!isset($_SESSION['username'])) {
    $response->code = 1;  // projet privé
    $response->msg  = "Le projet est privé et l'utilisateur n'est pas connecté";
    echo(json_encode($response));
    exit();
  }

  $user = $_SESSION["username"];
  $user = $connexion->real_escape_string($user);

  $query = $connexion->prepare('SELECT proprietaire FROM projet WHERE id=?');
  $query->bind_param('i', $project_id);
  $query->execute();

  $query_result = $query->get_result();
  $query->close();

  $owner = $query_result->fetch_array()['proprietaire'];

  $user_is_owner = (strcmp($owner, $user) == 0);
}

/*###########################################################
#                          TEST ACCES                       #
###########################################################*/

if(!$is_public && !$user_is_owner) {

  $query = $connexion->prepare('SELECT * FROM acces WHERE projet=? AND utilisateur=?');
  $query->bind_param('is', $project_id, $user);
  $query->execute();

  $query_result = $query->get_result();
  $query->close();

  if($query_result->num_rows == 0) {
    $response->code = 2;  // projet privé
    $response->msg  = "Le projet est privé et l'utilisateur n'a pas accès à ce projet";
    echo(json_encode($response));
    exit();
  }
}

/*###########################################################
#                           REQUETE                         #
###########################################################*/

$query = $connexion->prepare('SELECT nom FROM projet WHERE id=?');
$query->bind_param('i', $project_id);
$query->execute();

$query_result = $query->get_result();
$query->close();

$response->project_name = $query_result->fetch_array()['nom'];

$query = $connexion->prepare('SELECT nom, position FROM colonne WHERE projet=? ORDER BY position ASC');
$query->bind_param('i', $project_id);
$query->execute();

$query_result = $query->get_result();
$query->close();

$response->code = 0;
$response->msg  = "La requête s'est déroulée comme prévu";
$response->result = $query_result->fetch_all(MYSQLI_ASSOC);

echo(json_encode($response));
exit();

?>
