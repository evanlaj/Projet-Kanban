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

if(!$is_public) {

  if (!isset($_SESSION['username'])) {
    $response->code = 1;  // projet privé
    $response->msg  = "Le projet est privé et l'utilisateur n'est pas connecté";
    echo(json_encode($response));
    exit();
  }
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

$query = $connexion->prepare('SELECT * FROM acces WHERE projet=? AND utilisateur=?');
$query->bind_param('is', $project_id, $user);
$query->execute();

$query_result = $query->get_result();
$query->close();

$user_has_access = ($query_result->num_rows != 0);

if(!$is_public && !$user_is_owner && !$user_has_access) {
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


$query = $connexion->prepare("SELECT id, affectation, description, DATE_FORMAT(date_limite, '%e/%c/%Y') as limite, colonne FROM tache WHERE projet=?");
$query->bind_param('i', $project_id);
$query->execute();

$query_result = $query->get_result();
$query->close();

$response->code = 0;
$response->msg  = "La requête s'est déroulée comme prévu";
$response->access = ($user_is_owner || $user_has_access);
$response->result  = $query_result->fetch_all(MYSQLI_ASSOC);

echo(json_encode($response));
exit();

?>
