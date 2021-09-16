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
    <title>Projets | Projet Kanban</title>
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

    <div id="liste-projet">

      <div id ="liste-projet-bar">
        <div id="title">Liste des projets</div>
        <div id="bar-boutons">
          <?php if (isset($_SESSION['username'])) { ?>
            <button id="nouveau-projet" class="bar-bouton" tooltip="Créer un nouveau projet" onclick="showModal('nouveau-projet')">+</button>

            <div id="toggle-selection">
              <button id="toggle-projets-publiques" class="checkbox-selection" tooltip="Projets publiques">
                <?php echo file_get_contents("IMG/public.svg"); ?>
              </button>
              <button id="toggle-projets-prives" class="checkbox-selection" checked="true" tooltip="Projets privés">
                <?php echo file_get_contents("IMG/lock.svg"); ?>
              </button>
              <button id="toggle-projets-associes" class="checkbox-selection"tooltip="Projets auxquels vous participez">
                <?php echo file_get_contents("IMG/associate.svg"); ?>
              </button>
            </div>
          <?php } ?>
          <div id="select-ordre" tabindex=0>
            <div id="ordre-selected-option" value="az">Ordre alphabétique A-Z</div>
            <button class="ordre-option" value="az">Ordre alphabétique A-Z</button>
            <button class="ordre-option" value="za">Ordre alphabétique Z-A</button>
            <button class="ordre-option" value="crear">Ordre de création (+récent)</button>
            <button class="ordre-option" value="creaa">Ordre de création (+ancien)</button>
            <button class="ordre-option" value="majr">Ordre de mise à jour (+récent)</button>
            <button class="ordre-option" value="maja">Ordre de mise à jour (+ancien)</button>
          </div>
        </div>
      </div>

      <div id="grille-projets"></div>

    </div>

    <script src="JS/projets.js"></script>
    <script src="JS/modals.js"></script>
    <script type="text/javascript">
      initializeButtons();
      updateSelection();
    </script>

  </body>

</html>
