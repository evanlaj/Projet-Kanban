<?php

require 'bado.inc.php';

$connexion = connexion_mysql();

session_start();
$user = $_SESSION["username"];
$user = $connexion->real_escape_string($user);

$public  = ($_POST["public"] == "true");
$private = ($_POST["private"] == "true");
$invited = ($_POST["invited"] == "true");

$order = $_POST["order"];

$query = "SELECT projet.id, projet.nom, projet.proprietaire, DATE_FORMAT(date_creation, '%e/%c/%Y') as creation, DATE_FORMAT(derniere_maj, '%e/%c/%Y') as maj, COUNT(tache.id) as nb_taches, nb_taches_finies FROM projet LEFT JOIN tache ON projet.id = tache.projet LEFT JOIN (SELECT COUNT(tache.id) as nb_taches_finies, projet as id FROM tache INNER JOIN (SELECT projet as id, MAX(colonne.position) as colonne_finale FROM colonne GROUP BY projet) as projet_colonne ON tache.projet = projet_colonne.id WHERE colonne = colonne_finale GROUP BY projet) as taches_finies_par_projet ON projet.id = taches_finies_par_projet.id ";

if ($invited)
  $query .= "LEFT JOIN acces ON projet.id = acces.projet ";

$query .= "WHERE ";

if ($public)
  $query .= "public=TRUE ";

if ($public && $private)
  $query .= "OR ";

if ($private)
  $query .= "proprietaire='".$user."' ";

if (($public || $private) && $invited)
  $query .= "OR ";

if ($invited)
  $query .= "utilisateur='".$user."' ";

$query .= "GROUP BY projet.id ";

$query .= "ORDER BY ";

switch ($order) {

  case "az":
    $query .= "projet.nom ASC";
    break;

  case "za":
    $query .= "projet.nom DESC";
    break;

  case "crear":
    $query .= "projet.id DESC";
    break;

  case "creaa":
    $query .= "projet.id ASC";
    break;

  case "majr":
    $query .= "projet.derniere_maj DESC";
    break;

  case "maja":
    $query .= "projet.derniere_maj ASC";
    break;
}

$result = $connexion->query($query);

if(!$result) {
  $response->code = 1;
  $response->msg  = "La requête n'a renvoyé aucun résultat";

  echo(json_encode($response));
  $result->close();
  exit();
}

$response->code = 0;
$response->msg  = "La requête s'est déroulée comme prévu";
$response->result  = $result->fetch_all(MYSQLI_ASSOC);

echo(json_encode($response));
$result->close();
exit();

?>
