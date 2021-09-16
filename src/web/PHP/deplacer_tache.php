<?php

session_start();

require 'debug.inc.php';
require 'bado.inc.php';

$connexion = connexion_mysql();

$task_id = $_POST["id"];

$position = $_POST["position"];

$query = $connexion->prepare('SELECT projet FROM tache WHERE id=?');
$query->bind_param('i', $task_id);
$query->execute();

$query_result = $query->get_result();
$query->close();

$project_id = $query_result->fetch_array()['projet'];

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
#                    DEPLACEMENT REUSSIE                    #
###########################################################*/

$query = $connexion->prepare('UPDATE tache SET colonne=? WHERE id=?');
$query->bind_param("si", $position, $task_id);
$query->execute();
$query->close();

$current_date = date("Y-m-d H:i:s");

$query = $connexion->prepare('UPDATE projet SET derniere_maj=? WHERE id=?');
$query->bind_param("si", $current_date, $project_id);
$query->execute();
$query->close();

$response->code = 0; //creation réussie
$response->msg  = "La tâche a été déplacée avec succès";

echo(json_encode($response));
exit();

?>
