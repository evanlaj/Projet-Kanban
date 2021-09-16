<?php

require 'bado.inc.php';

$connexion = connexion_mysql();

$user = $_POST["username"];
$pass = $_POST["password"];

$query = $connexion->prepare('SELECT mdp FROM utilisateur WHERE nom=?');

$query->bind_param("s", $user);
$query->execute();

$query_result = $query->get_result();
$result = $query_result->fetch_assoc();
$query->close();

if (!$result)
{
  $response->code = 1;  //pas d'utilisateur
  $response->msg  = "Cet utilisateur n'existe pas";
}
else if (!password_verify($pass, $result['mdp']))
{
  $response->code = 2; //mot de passe incorrect
  $response->msg  = "Le mot de passe est incorrect";
}
else
{
  $response->code = 0; //connexion réussie
  $response->msg  = "L'utilisateur s'est connecté avec succès";
  session_start();
  $_SESSION["username"] = $user;
}

echo(json_encode($response));
exit();

?>
