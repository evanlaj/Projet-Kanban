<?php session_start(); ?>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="modals.css"/>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;700&display=swap" rel="stylesheet">
    <title>Accueil | Projet Kanban</title>
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

    <h1>Projet Kanban</h1>
    <div id="accueil-cta">
      <?php if (!isset($_SESSION['username'])) { ?>
        <button id="bouton-connexion" class="bouton-accueil" onclick="showModal('connexion')">Se connecter</button>
        <button id="bouton-inscription" class="bouton-accueil" onclick="showModal('inscription')">S'inscrire</button>
      <?php  } else { ?>
        <a href="projets"><button id="bouton-projets" class="bouton-accueil">Voir les projets</button></a>
      <?php } ?>
    </div>

    <script src="JS/modals.js"></script>

  </body>

</html>
