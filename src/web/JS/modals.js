function showModal(modalName) {

  let blurs = document.getElementsByClassName("blur");

  if (blurs.length > 0) hideModal();

  let blur = document.createElement("div");

  let req = new XMLHttpRequest();

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
         blur.innerHTML = req.responseText;

         cross = document.createElement("button");
         cross.className = "modal-cross";
         cross.addEventListener("click", function(e) { hideModal(); });

         modal = blur.getElementsByClassName("modal")[0];
         modal.appendChild(cross);
      }
  };

  req.open("GET", modalName+".html", true);
  req.send();

  blur.className = "blur";
  blur.addEventListener("click", function(e) {
    if(e.target == this) hideModal();
  });

  body = document.getElementsByTagName("BODY")[0];
  body.classList.add("no-scroll");
  body.appendChild(blur);
}

function hideModal() {
  let blur = document.getElementsByClassName("blur")[0];

  blur.classList.add("blur-out");

  blur.getElementsByClassName("modal")[0].classList.add("modal-out");

  blur.addEventListener("animationend", function(e) {
    if(e.target == this) blur.remove();
  });

  body.classList.remove("no-scroll");
}

function login() {

  let req = new XMLHttpRequest();

  let username = encodeURIComponent(document.getElementById("username-input").value);
  let password = encodeURIComponent(document.getElementById("password-input").value);

  let params="username="+username+"&password="+password

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(response);

        if (response["code"] != 0) document.getElementById("modal-error").innerHTML = response["msg"];
        else document.location.reload();
      }
  };

  req.open("POST", "PHP/connexion", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}

function signup() {
  let req = new XMLHttpRequest();

  let username = encodeURIComponent(document.getElementById("username-input").value);
  let password = encodeURIComponent(document.getElementById("password-input").value);
  let confirm  = encodeURIComponent(document.getElementById("confirm-input").value);

  let params="username="+username+"&password="+password+"&confirm="+confirm;

  req.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {

        let response = JSON.parse(req.responseText);

        console.log(req.responseText);
        console.log(response);

        document.getElementById("modal-error").innerHTML = response["msg"];
      }
  };

  req.open("POST", "PHP/inscription", true);
  req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  req.send(params);
}
