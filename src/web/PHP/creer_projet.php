<?php

session_start();

if (!isset($_SESSION['username']) || !isset($_POST["name"]) || !isset($_POST["public"]) || !isset($_POST["columns"])) {
  $response->code = 10;  // utilisateur non connecté
  $response->msg  = "L'utilisateur n'est pas connecté";
  echo(json_encode($response));
  exit();
}

require 'bado.inc.php';

$connexion = connexion_mysql();

$user = $_SESSION["username"];

$name = $_POST["name"];
$public = (int) $_POST["public"];
$columns = $_POST["columns"];

$current_date = date("Y-m-d H:i:s");

/*###########################################################
#                     TEST SYNTAXE INPUT                    #
###########################################################*/

if(strlen($name) < 4)
{
  $response->code = 1;
  $response->msg  = "Le nom du projet doit faire 4 caractères min.";
  echo(json_encode($response));
  exit();
}

for ($i = 0; $i < count($columns); $i++) {
  $column = $columns[$i];
  if(strlen($column) < 4)
  {
    $response->code = 2;
    $response->msg  = "Le nom des colonnes doit faire 4 caractères min.";
    echo(json_encode($response));
    exit();
  }
  for ($j = $i+1; $j < count($columns); $j++) {
    $col2 = $columns[$j];
    if (strcmp($column, $col2) == 0) {
      $response->code = 3;
      $response->msg  = "Le nom des colonnes doit être différent";
      echo(json_encode($response));
      exit();
    }
  }
}

/*###########################################################
#                      CREATION REUSSIE                    #
###########################################################*/

  $query = $connexion->prepare('INSERT INTO projet (nom, public, proprietaire, date_creation, derniere_maj) VALUES (?,?,?,?,?)');

  $query->bind_param("sisss", $name, $public, $user, $current_date, $current_date);
  $query->execute();

  $id_project = $connexion->insert_id;

  $query->close();

  for ($i = 0; $i < count($columns); $i++) {
    $name = $columns[$i];
    $pos  = $i+1;

    $query_col = $connexion->prepare('INSERT INTO colonne (projet, nom, position) VALUES (?,?,?)');
    $query_col->bind_param("isi", $id_project, $name, $pos);
    $query_col->execute();
  }
    $query_col->close();

  $response->code = 0; //connexion réussie
  $response->project_id = $id_project;
  $response->msg  = "Le projet a été créé avec succès";

  echo(json_encode($response));
  exit();

?>
