
function initializeButtons() {

  let checkboxList = document.getElementsByClassName("checkbox-selection");

  for(let elem of checkboxList) {
    elem.addEventListener("click", function(e) {
      check(elem);
    });
  }

  let optionList = document.getElementsByClassName("ordre-option");

  for(let elem of optionList) {
    elem.addEventListener("click", function(e) {
      select(elem);
    });
  }

}

function check(elem) {
  elem.blur();
  if(elem.getAttribute("checked")) elem.removeAttribute("checked");
  else elem.setAttribute("checked", true);

  updateSelection();
}

function select(elem) {
  elem.blur();

  let selected = document.getElementById("ordre-selected-option");
  selected.innerHTML = elem.innerHTML;
  selected.setAttribute("value", elem.value);

  updateSelection();
}

function updateSelection() {
  let req = new XMLHttpRequest();

  let public;
  let private;
  let invited;

  if(document.getElementById("toggle-projets-publiques")) {
    public = (document.getElementById("toggle-projets-publiques").getAttribute("checked") == "true");
    private = (document.getElementById("toggle-projets-prives").getAttribute("checked") == "true");
    invited = (document.getElementById("toggle-projets-associes").getAttribute("checked") == "true");

    if(!public && !private && !invited)
    {
      check(document.getElementById("toggle-projets-prives"));
      return;
    }
  }
  else {
    public  = true;
    private = false;
    invited = false;
  }

  let order = document.getElementById("ordre-selected-option").getAttribute("value");

  let params="public="+public+"&private="+private+"&invited="+invited+"&order="+order;

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(response["msg"]);
        console.log(response);

        let grilleProjet = document.getElementById("grille-projets");
        grilleProjet.innerHTML = "";

        for(let projet of response["result"])
        {
          console.log(projet);

          link = document.createElement("a");
          link.setAttribute("href", "projet-"+projet["id"]);

          divProjet = document.createElement("div");
          divProjet.className = "projet";

          divTitre = document.createElement("div");
          divTitre.className = "projet-title";
          divTitre.appendChild(document.createTextNode(projet["nom"]));

          divProprio = document.createElement("div");
          divProprio.className = "projet-owner";
          divProprio.appendChild(document.createTextNode(projet["proprietaire"]));

          divLine = document.createElement("div");
          divLine.className = "line";

          divTaches = document.createElement("div");
          divTaches.className = "projet-task-done";
          divTaches.appendChild(document.createTextNode((projet["nb_taches_finies"]==null?"0":projet["nb_taches_finies"]) + " sur " + projet["nb_taches"]));

          divProgression = document.createElement("div");
          divProgression.className = "projet-progress";
          divBarre = document.createElement("div");
          divBarre.className = "projet-progress-bar";
          if (projet["nb_taches"] == 0 || projet["nb_taches_finies"] == null)
            divBarre.style = "width : 0";
          else
            divBarre.style = "width : " + (projet["nb_taches_finies"]*100/projet["nb_taches"]) +"%";

          divProgression.appendChild(divBarre);

          divDate = document.createElement("div");
          divDate.className = "projet-dates";

          divDateCreation = document.createElement("div");
          divDateCreation.className = "projet-creation";
          divDateCreation.appendChild(document.createTextNode(projet["creation"]));

          divDerniereMaj = document.createElement("div");
          divDerniereMaj.className = "projet-derniere-maj";
          divDerniereMaj.appendChild(document.createTextNode(projet["maj"]));

          divDate.appendChild(divDateCreation);
          divDate.appendChild(divDerniereMaj);

          divProjet.appendChild(divTitre);
          divProjet.appendChild(divProprio);
          divProjet.appendChild(divLine);
          divProjet.appendChild(divTaches);
          divProjet.appendChild(divProgression);
          divProjet.appendChild(divDate);

          link.appendChild(divProjet);
          grilleProjet.appendChild(link);
        }
      }
  };

  req.open("POST", "PHP/selection_projets.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}


function addProjectColumn() {

  document.getElementsByClassName("add-project-column")[0].blur();

  let input = document.getElementsByClassName("grille-columns-input")[0];

  let newCol = document.createElement("div");
  newCol.className = "project-column";

  let name = document.createElement("input");
  name.setAttribute("type", "text");
  name.setAttribute("placeholder", "e.g. Tâches compliquées...");
  name.setAttribute("autocomplete", "off");
  name.setAttribute("spellcheck", "off");
  name.setAttribute("maxlength", 32);
  name.className = "col-input";

  let cross = document.createElement("button");
  cross.className = "project-column-cross";

  cross.addEventListener("click", function(e) {
    newCol.remove();
  });

  newCol.appendChild(name);
  newCol.appendChild(cross);

  let lastCol = document.getElementsByClassName("last-column")[0];

  input.insertBefore(newCol, lastCol);
}

function createProject() {
  let req = new XMLHttpRequest();

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(req.responseText);
        console.log(response);

        document.getElementById("modal-error").innerHTML = response["msg"];

        if(response["project_id"] != null) window.location = '/projet-'+response["project_id"];
      }
  };

  let name = document.getElementById("project-name-input").value;
  let public = document.getElementById("project-is-public").checked;

  let params="name="+name+"&public="+public+"&columns[]=Stories";

  colList = document.getElementsByClassName("col-input");

  for(let elem of colList) params+= "&columns[]=" + elem.value;
  params+="&columns[]=Tâches terminées";

  console.log(params);

  req.open("POST", "PHP/creer_projet.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}
