const CURSOR_STATES = {DEFAULT: 0, GRABBING: 1};

var currentState = CURSOR_STATES.DEFAULT;
var cursorPosTamp = {x: 0, y: 0};
var grabbedTask = null;
var lastStack = null;
var placeholder = null;
var taskList;
var stackList;
var projectId;

function initializeApp(id) {

  projectId = id;
  initializeDisplayStacks(id);
}

function initializeDisplayStacks(id) {
  let req = new XMLHttpRequest();

  let params="id="+id;

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(response["msg"]);
        console.log(response);

        if (response["code"] != 0) window.location = '/projets';

        let nomProjet = document.getElementById("title");
        nomProjet.innerHTML = response["project_name"];

        let grilleProjet = document.getElementById("grid");
        grilleProjet.innerHTML = "";

        for(let column of response["result"])
        {
          console.log(column);

          divColumn = document.createElement("div");
          divColumn.className = "column";

          divBoard = document.createElement("div");
          divBoard.className = "board";

          divBoardTitle = document.createElement("div");
          divBoardTitle.className = "board-title";
          divBoardTitle.appendChild(document.createTextNode(column["nom"]));

          divLine = document.createElement("div");
          divLine.className = "line";

          divStack = document.createElement("div");
          divStack.className = "stack";

          divBoard.appendChild(divBoardTitle);
          divBoard.appendChild(divLine);
          divBoard.appendChild(divStack);

          divColumn.appendChild(divBoard);

          grilleProjet.appendChild(divColumn);
        }

        initializeDisplayTasks(id);
      }
  };

  req.open("POST", "PHP/selection_colonne_projet.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

function initializeDisplayTasks(id) {
  let req = new XMLHttpRequest();

  let params="id="+id;

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(response["msg"]);
        console.log(response);

        stackList = document.getElementsByClassName('stack');

        for(let task of response["result"])
        {
          console.log(task);

          divTask = document.createElement("div");
          divTask.className = "task";
          divTask.setAttribute("task-id", task["id"]);

          divTaskDesc = document.createElement("div");
          divTaskDesc.className = "task-desc";
          divTaskDesc.appendChild(document.createTextNode(task["description"]));

          spanTaskUser = document.createElement("span");
          spanTaskUser.className = "task-user";
          if(task["affectation"] != null)
            spanTaskUser.appendChild(document.createTextNode("@" + task["affectation"]));
          else
            spanTaskUser.appendChild(document.createTextNode("Tâche libre"));

          spanTaskDate = document.createElement("span");
          spanTaskDate.className = "task-date";
          if(task["limite"] != null)
            spanTaskDate.appendChild(document.createTextNode(" - à rendre le " + task["limite"]));
          else
            spanTaskDate.appendChild(document.createTextNode(" - pas de date limite"));


          divTask.appendChild(divTaskDesc);
          divTask.appendChild(spanTaskUser);
          divTask.appendChild(spanTaskDate);

          stackList[task["colonne"]-1].appendChild(divTask);
        }

        taskList = document.getElementsByClassName('task');
        stackList = document.getElementsByClassName('stack');

        if(response["access"]) {
          initializeTasks();
          initializeStacks();
        }
        else document.getElementById("bar-boutons").remove();
      }
  };

  req.open("POST", "PHP/selection_tache_projet.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

function initializeTasks() {

  for (let task of taskList) {

    task.addEventListener('mousedown', function(e) {
      if(currentState == CURSOR_STATES.DEFAULT && e.which == 1)
      {
        grabTask(task, e);
      }

    });
  }

  document.addEventListener('mouseup', function(e) {
    if(currentState == CURSOR_STATES.GRABBING && e.which == 1)
    {
      dropTask();
    }
  });

  document.addEventListener('mousemove', function(e) {
    if(currentState == CURSOR_STATES.GRABBING)
    {
      offsetX = (e.pageX - cursorPosTamp.x);

      grabbedTask.style.transform = "rotateZ("+(Math.sign(offsetX))*4+"deg)";

      grabbedTask.style.left = grabbedTask.offsetLeft + offsetX + "px";
      grabbedTask.style.top = grabbedTask.offsetTop + (e.pageY - cursorPosTamp.y) + "px";
      cursorPosTamp = {x: e.pageX, y: e.pageY};
    }
  });
}

function initializeStacks() {

  for(let stack of stackList) {

    stack.addEventListener('mouseenter', function(e) {
      if(currentState == CURSOR_STATES.GRABBING && e.which == 1)
      {
        setLastStack(stack);
      }
    });
  }
}

function grabTask(task, e) {
  e.preventDefault();

  document.activeElement.blur();

  task.style.width = task.offsetWidth +"px";
  task.classList.add("grabbed");

  lastStack = task.parentNode;
  lastStack.classList.add("last-stack");

  grabbedTask = task;

  currentState = CURSOR_STATES.GRABBING;
  cursorPosTamp = {x: e.pageX, y: e.pageY};

  document.getElementsByTagName('body')[0].style.cursor = "grabbing";

  placeholder = document.createElement("div");
  placeholder.className = "task-placeholder first-placeholder";

  task.parentNode.insertBefore(placeholder, task.nextSibling);

  grabbedTask.style.left = grabbedTask.offsetLeft + (e.pageX - cursorPosTamp.x) + "px";
  grabbedTask.style.top = grabbedTask.offsetTop + (e.pageY - cursorPosTamp.y) - grabbedTask.parentNode.scrollTop + "px";

  //document.getElementById("new-task-button").parentNode.insertBefore(task, document.getElementById("new-task-button"));
}

function dropTask() {

  moveTask(grabbedTask.getAttribute("task-id"), lastStack);

  grabbedTask.classList.remove("grabbed");

  grabbedTask.style.left = "";
  grabbedTask.style.top = "";
  grabbedTask.style.width = "";
  grabbedTask.style.transform = "";

  lastStack.replaceChild(grabbedTask, placeholder);
  lastStack.classList.remove("last-stack");

  grabbedTask = null;
  lastStack = null;

  currentState = CURSOR_STATES.DEFAULT;
  document.getElementsByTagName('body')[0].style.cursor = "";

}

function setLastStack(stack) {

  if(stack != lastStack)
  {
    if(lastStack != null) lastStack.classList.remove("last-stack");
    lastStack = stack;
    stack.classList.add("last-stack");
    putPlaceholder();
  }
}

function putPlaceholder() {

  removePlaceholder(true);

  placeholder = document.createElement("div");
  placeholder.className = "task-placeholder";
  lastStack.appendChild(placeholder);
}

function removePlaceholder(animate) {

  let placeholderList = document.getElementsByClassName('task-placeholder');

  while(placeholderList[0])
  {
    if(animate) {
      let parent = placeholderList[0].parentNode;
      let animPlaceholder = document.createElement("div");
      animPlaceholder.classList = "task-placeholder-out";
      animPlaceholder.addEventListener('animationend', function(e) {
        parent.removeChild(animPlaceholder);
      });

      parent.replaceChild(animPlaceholder, placeholderList[0]);
    }
    else {
      placeholderList[0].parentNode.removeChild(placeholderList[0]);
    }
  }
}

function createTask() {

  let req = new XMLHttpRequest();

  let desc = document.getElementById("task-desc-input").value;
  let user = document.getElementById("task-user-input").value;
  let date = document.getElementById("task-date-input").value;

  let params="id="+projectId+"&desc="+desc+"&user="+user+"&date="+date;

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(req.responseText);
        console.log(response);

        document.getElementById("modal-error").innerHTML = response["msg"];

        if(response["code"] == 0) {
          divTask = document.createElement("div");
          divTask.className = "task";
          divTask.setAttribute("task-id", response["task_id"]);

          divTaskDesc = document.createElement("div");
          divTaskDesc.className = "task-desc";
          divTaskDesc.appendChild(document.createTextNode(desc));

          spanTaskUser = document.createElement("span");
          spanTaskUser.className = "task-user";
          if(user !== "")
            spanTaskUser.appendChild(document.createTextNode("@" + user));
          else
            spanTaskUser.appendChild(document.createTextNode("Tâche libre"));

          spanTaskDate = document.createElement("span");
          spanTaskDate.className = "task-date";
          if(date !== "")
            spanTaskDate.appendChild(document.createTextNode(" - à rendre le " + formateDate(date)));
          else
            spanTaskDate.appendChild(document.createTextNode(" - pas de date limite"));


          divTask.appendChild(divTaskDesc);
          divTask.appendChild(spanTaskUser);
          divTask.appendChild(spanTaskDate);

          divTask.addEventListener('mousedown', function(e) {
            if(currentState == CURSOR_STATES.DEFAULT && e.which == 1)
            {
              grabTask(divTask, e);
            }
          });

          stackList[0].appendChild(divTask);
        }
      }
  };

  console.log(params);

  req.open("POST", "PHP/creer_tache.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

function moveTask(taskId, lastStack) {

  let req = new XMLHttpRequest();

  let position = 0;
  for(stack of stackList) {
    position++;
    if (stack == lastStack) break;
  }

  if(position == 0) return;


  let params="id="+taskId+"&position="+position;

  console.log(params);

  req.open("POST", "PHP/deplacer_tache.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

function createAccess() {
  let req = new XMLHttpRequest();

  let user = document.getElementById("user-name-input").value;

  let params="id="+projectId+"&user="+user;

  document.getElementById("user-name-input").value = "";
  document.getElementById("add-access-button").blur();

  console.log(params);

  req.open("POST", "PHP/creer_acces.php", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

//petite fonction pour transformer les dates YYYY-mm-dd en d/m/YYYY
function formateDate(date) {
  newDate = date.split('-');
  return parseInt(newDate[2], 10) + "/" + parseInt(newDate[1], 10) + "/" + newDate[0];
}
