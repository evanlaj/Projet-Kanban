<?php

require 'bado.inc.php';

$connexion = connexion_mysql();

$user = $_POST["username"];
$pass = $_POST["password"];
$conf = $_POST["confirm"];

/*###########################################################
#                     TEST SYNTAXE INPUT                    #
###########################################################*/

if(!preg_match("/^([A-Za-z0-9\-\_]{4,16})$/", $user))
{
  $response->code = 2;  //utilisateur existe déjà
  $response->msg  = "Le nom d'utilisateur entré n'est pas valide";
  echo(json_encode($response));
  exit();
}

if(strlen($pass) < 8)
{
  $response->code = 4; //mot de passe trop court
  $response->msg  = "Le mot de passe doit faire 8 caractères min.";
  echo(json_encode($response));
  exit();
}

if (strcmp($pass, $conf) != 0)
{
  $response->code = 3; //mot de passe différents
  $response->msg  = "Les mot de passes entrés sont différents";
  echo(json_encode($response));
  exit();
}

/*###########################################################
#                    TEST NOM D'UTILISATEUR                 #
###########################################################*/

$query = $connexion->prepare('SELECT nom FROM utilisateur WHERE nom=?');

$query->bind_param("s", $user);
$query->execute();

$query_result = $query->get_result();
$result = $query_result->fetch_assoc();
$query->close();

if ($result)
{
  $response->code = 1;  //utilisateur existe déjà
  $response->msg  = "Le nom d'utilisateur entré est déjà utilisé";
  echo(json_encode($response));
  exit();
}

/*###########################################################
#                    INSCRIPTION REUSSIE                    #
###########################################################*/

  $hash = password_hash($pass, PASSWORD_ARGON2ID);

  $query = $connexion->prepare('INSERT INTO utilisateur VALUES (?,?)');

  $query->bind_param("ss", $user, $hash);
  $query->execute();
  $query->close();


  $response->code = 0; //connexion réussie
  $response->msg  = "L'inscription a été réalisée avec succès";

  echo(json_encode($response));
  exit();

?>
