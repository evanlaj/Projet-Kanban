<?php

session_start();

//require 'debug.inc.php';
require 'bado.inc.php';

$connexion = connexion_mysql();

$project_id = $_POST["id"];

$affect = $_POST["user"];

/*###########################################################
#                      TEST PROPRIETAIRE                    #
###########################################################*/

if (!isset($_SESSION['username'])) {
  $response->code = 10;  // utilisateur non connecté
  $response->msg  = "L'utilisateur n'est pas connecté";
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

/*###########################################################
#                          TEST ACCES                       #
###########################################################*/

if(!$user_is_owner) {

  $query = $connexion->prepare('SELECT * FROM acces WHERE projet=? AND utilisateur=?');
  $query->bind_param('is', $project_id, $user);
  $query->execute();

  $query_result = $query->get_result();
  $query->close();

  if($query_result->num_rows == 0) {
    $response->code = 11;  // projet privé
    $response->msg  = "Le projet est privé et l'utilisateur n'a pas accès à ce projet";
    echo(json_encode($response));
    exit();
  }
}

/*###########################################################
#                     TEST SYNTAXE INPUT                    #
###########################################################*/

if(!preg_match("/^([A-Za-z0-9\-\_]{4,16})$/", $affect))
{
  $response->code = 1;  //utilisateur existe déjà
  $response->msg  = "Le nom d'utilisateur entré n'est pas valide";
  echo(json_encode($response));
  exit();
}

/*###########################################################
#                   TEST UTILISATEUR EXISTE                 #
###########################################################*/

$query = $connexion->prepare('SELECT nom FROM utilisateur WHERE nom=?');
$query->bind_param('s', $affect);
$query->execute();

$query_result = $query->get_result();
$query->close();

if($query_result->num_rows == 0) {
  $response->code = 2;
  $response->msg  = "L'utilisateur n'existe pas";
  echo(json_encode($response));
  exit();
}

/*###########################################################
#                      CREATION REUSSIE                    #
###########################################################*/

$query = $connexion->prepare('INSERT INTO acces (projet, utilisateur) VALUES (?, ?)');

$query->bind_param("is", $project_id, $affect);
$query->execute();

$query->close();

$response->code = 0; //creation réussie
$response->msg  = "L'accès a été créé avec succès";

echo(json_encode($response));
exit();

?>
