<?php

session_start();

//require 'debug.inc.php';
require 'bado.inc.php';

$connexion = connexion_mysql();

$project_id = $_POST["id"];

$desc = $_POST["desc"];
$affect = $_POST["user"];
$date = $_POST["date"];
if(!empty($date)) $date = $date." 08:00:00";

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

if(strlen($desc) < 4)
{
  $response->code = 1;
  $response->msg  = "La description du projet doit faire 4 caractères min.";
  echo(json_encode($response));
  exit();
}

/*###########################################################
#                     TEST AFFECTATION INPUT                #
###########################################################*/

if(!empty($affect)) {
  if(strcmp($affect, $owner) != 0)
  {
    $query = $connexion->prepare('SELECT * FROM acces WHERE projet=? AND utilisateur=?');
    $query->bind_param('is', $project_id, $affect);
    $query->execute();

    $query_result = $query->get_result();
    $query->close();

    if($query_result->num_rows == 0) {
      $response->code = 2;  // projet privé
      $response->msg  = "L'utilisateur affecté à la tâche n'a pas accès au projet";
      echo(json_encode($response));
      exit();
    }
  }
}

/*###########################################################
#                      CREATION REUSSIE                    #
###########################################################*/

if(empty($affect)) $affect = NULL;
if(empty($date)) $date = NULL;

$query = $connexion->prepare('INSERT INTO tache (projet, affectation, description, date_limite, colonne) VALUES (?, ?, ?, ?, 1)');

$query->bind_param("isss", $project_id, $affect, $desc, $date);
$query->execute();

$id_task = $connexion->insert_id;
$query->close();

$current_date = date("Y-m-d H:i:s");

$query = $connexion->prepare('UPDATE projet SET derniere_maj=? WHERE id=?');
$query->bind_param("si", $current_date, $project_id);
$query->execute();
$query->close();

$response->code = 0; //creation réussie
$response->task_id = $id_task;
$response->msg  = "La tâche a été créée avec succès";

echo(json_encode($response));
exit();

?>
