<?php

  session_start();
  $project_id = $_GET["id"];

?>

<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css"/>
  <link rel="stylesheet" href="modals.css"/>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;700&display=swap" rel="stylesheet">
  <title>Projet | Projet Kanban</title>
</head>

  <body>

    <header>
      <nav>
        <a href="/"><button class="nav-button" onclick="">Accueil</button></a>
        <a href="projets"><button class="nav-button" onclick="">Projets</button></a>
        <?php if (!isset($_SESSION['username'])) { ?>
          <button class="nav-button" onclick="showModal('connexion')">Se connecter</button>
          <button class="nav-button" onclick="showModal('inscription')">S'inscrire</button>
        <?php  } else { ?>
          <a href="PHP/deconnexion"><button class="nav-button" onclick="">Se déconnecter</button></a>
        <?php } ?>
      </nav>
    </header>

    <div id ="nom-kanban-bar">
      <div id="title"></div>
      <div id="bar-boutons">
        <?php if (isset($_SESSION['username'])) { ?>
          <div id="ajouter-acces" class="bar-input">
            <input id="user-name-input"type=text placeholder="e.g. johndoe" autocomplete="off" spellcheck=false maxlength=16>
            <button id="add-access-button" class="bar-bouton" tooltip="Ajouter un membre au projet" onclick="createAccess()">
              <?php echo file_get_contents("IMG/associate.svg"); ?>
            </button>
          </div>
          <button id="nouvelle-tache" class="bar-bouton" tooltip="Créer une tâche" onclick="showModal('nouvelle-tache')">+</button>
        <?php } ?>
      </div>
    </div>

    <div id="grid"></div>

  </body>

  <script src="JS/kanban.js"></script>
  <script src="JS/modals.js"></script>

  <script type="text/javascript"> initializeApp(<?= $project_id ?>); </script>

</html>
