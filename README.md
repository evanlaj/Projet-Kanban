# Projet Kanban

## README FR

Ce projet a été réalisé dans le cadre du cours de Langage Web de M1 Informatique (GIL) à l'Université de Rouen.

Cette application web permet de gérer des projets selon le modèle Kanban.
Les sources de l'application sont fournies avec un script d'installation.
L'application, les commentaires dans le code ainsi que le script d'installation sont en français uniquement.

### Installation

Le serveur de l'application web peut être déployé automatiquement sur une machine en utilisant le script d'installation.
Il est essentiel d'avoir les droits d'administration de la machine pour déployer l'application.
Toutes les dépendances seront automatiquement installées si elle ne sont pas présentes sur la machine.
L'installation devrait fonctionner sur de nombreuse distribution Linux mais n'a été testée que sur Ubuntu 20.04.3 LTS.

```bash
sudo ./install.sh
```

Note : il est préférable d'avoir une base de données MySQL préalablement configurée et vide.
Si l'initialisation de la base de données échoue, il est possible de trouver une description des tables de la base de données dans le fichier ```/src/sql_init/init.sql```

***

## README EN

This project was made as part of the Web Language lessons of the Software Engineering Master at the university of Rouen.

This web application allows users to manage project using Kanban boards.
The sources of the application are provided as well as an installation script.
The application, comments in the code and instructions in the installation scripts are in french only.

### Installation

The server for the web application can be automatically deployed on a new machine by using the installation script.
To do so it is necessary to have admin privileges on said machine.
All the dependencies will be automatically installed if they are not already installed.
The installation should work on a lot of Linux distributions but was only tested on Ubuntu 20.04.3 LTS.

```bash
sudo ./install.sh
```

Note : it is preferrable to already have an already configured empty MySQL database.
If the database initialization fails, a description of all the tables of the database is available in ```/src/sql_init/init.sql```
