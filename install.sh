#!/bin/bash

################################################################################
#                                                                              #
#                    INSTALLATION DES PAQUETS NECESSAIRES                      #
#                                                                              #
################################################################################


#installe la dernière version d'apache2 si le paquet n'est pas installé
apache2_installed=$(dpkg-query -W --showformat='${Status}\n' apache2|grep "installed")

printf "Verification d'apache2 : $apache2_installed\n"
if [ "" = "$apache2_installed" ]; then
  printf "le paquet apache2 est introuvable ; installation du paquet\n"
  sudo apt install apache2 -y
fi

printf "\n"
#configuration du pare-feu si ufw est installé
ufw_installed=$(dpkg-query -W --showformat='${Status}\n' ufw|grep "installed")
printf "Verification d'ufw : $ufw_installed\n"
if  [ "" != "$ufw_installed" ]; then
  printf "le paquet ufw est installé ; configuration du pare-feu\n"
  sudo ufw allow in "Apache Full"
fi

printf "\n"
#installe la dernière version de php si le paquet n'est pas installé
php_installed=$(dpkg-query -W --showformat='${Status}\n' php|grep "installed")
printf "Verification de php : $php_installed\n"
if [ "" = "$php_installed" ]; then
  printf "le paquet php est introuvable ; installation du paquet\n"
  sudo apt install php -y
fi

#installe la dernière version de libapache2-mod-php si le paquet n'est pas installé
php_mod_installed=$(dpkg-query -W --showformat='${Status}\n' libapache2-mod-php|grep "installed")
printf "Verification de libapache2-mod-php : $php_mod_installed\n"
if [ "" = "$php_mod_installed" ]; then
  printf "le paquet libapache2-mod-php est introuvable ; installation du paquet\n"
  sudo apt install libapache2-mod-php -y
fi

#installe la dernière version de php-mysql si le paquet n'est pas installé
php_mysql_installed=$(dpkg-query -W --showformat='${Status}\n' php-mysql|grep "installed")
printf "Verification de php-mysql : $php_mysql_installed\n"
if [ "" = "$php_mysql_installed" ]; then
  printf "le paquet php-mysql est introuvable ; installation du paquet\n"
  sudo apt install php-mysql -y
fi

#installe la dernière version de mysql-client si le paquet n'est pas installé
mysql__client_installed=$(dpkg-query -W --showformat='${Status}\n' mysql-client|grep "installed")
printf "Verification de mysql-client : $mysql__client_installed\n"
if [ "" = "$mysql__client_installed" ]; then
  printf "le paquet mysql-client est introuvable ; installation du paquet\n"
  sudo apt install mysql-client -y
fi

printf "\n"

################################################################################
#                                                                              #
#           CREATION ET CONFIGURATION DE L'HOTE VIRTUEL APACHE2                #
#                                                                              #
################################################################################

printf "Creation de l'hôte virtuel pour le site web Post In\n"
printf "Veuillez entrer le nom de l'hôte virtuel (dossier) : "

# Creation du dossier contenant les fichiers de l'hote virtuel
read nom_hote_virtuel
sudo mkdir /var/www/$nom_hote_virtuel
while [ $? -ne 0 ] ; do
  printf "Une erreur est survenue lors de la creation de l'hote virtuel (le nom entré est probablement incorrect)\n"
  printf "Veuillez entrer le nom de l'hôte virtuel (dossier) : "

  read nom_hote_virtuel
  sudo mkdir /var/www/$nom_hote_virtuel
done

#Recuperation des données de l'hote virtuel

#Port
regex_port='^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$'
printf "Veuillez entrer le port à attributer à l'hôte virtuel (par défaut 80) : "
read port
port=${port:-80}
while [[ ! $port =~ $regex_port ]] ; do
    unset port
    printf "La valeur entrée est incorrecte ($port) \n"
    printf "Veuillez entrer le port à attributer à l'hôte virtuel (par défaut 80) : "
    read port
    port=${port:-80}
done

printf "Vérification de la configuration d'apache2 pour le port $port\n"
listen_port=$(sudo cat /etc/apache2/ports.conf | grep "^Listen $port\$")
if [ "" = "$listen_port" ]; then
  printf "Aucune configuration trouvée pour le port $port ; mise sur écoute du port $port\n"
  sudo printf "\n\nListen $port" >> /etc/apache2/ports.conf
fi

#Adresse mail de l'admin web
printf "Veuillez entrer l'adresse mail de l'administrateur web (par défaut webmaster@localhost) : "
read mail_admin
mail_admin=${mail_admin:-"webmaster@localhost"}

#Creation du fichier de configuration de l'hote virtuel
printf "\nCréation du fichier de configuration de l'hôte virtuel\n"
sudo touch /etc/apache2/sites-available/$nom_hote_virtuel.conf

sudo printf "<VirtualHost *:$port>\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\tServerAdmin $mail_admin\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\tDocumentRoot /var/www/$nom_hote_virtuel/web\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\tErrorLog \${APACHE_LOG_DIR}/error.log\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\tCustomLog \${APACHE_LOG_DIR}/access.log combined\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\t<Directory /var/www/$nom_hote_virtuel/web>\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\t\tAllowOverride All\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\t\tRequire all granted\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "\t</Directory>\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf
sudo printf "</VirtualHost>\n" >> /etc/apache2/sites-available/$nom_hote_virtuel.conf

sudo a2ensite $nom_hote_virtuel
sudo a2dissite 000-default

#activation du module de RewriteEngine d'apache2
sudo a2enmod rewrite

sudo cp -r src/web /var/www/$nom_hote_virtuel/web

sudo systemctl reload apache2

################################################################################
#                                                                              #
#             CREATION ET/OU CONFIGURATION DE LA BASE DE DONNEES               #
#                                                                              #
################################################################################

printf "\n"
printf "Veuillez entrer l'adresse de la base de données (par défaut localhost) : "
read adresse_bado
adresse_bado=${adresse_bado:-"localhost"}

if [ "localhost" = "$adresse_bado" ]; then
  printf "Adresse de la base de données définie sur localhost\n\n"
  #installe la dernière version de mysql-server si le paquet n'est pas installé
  mysql_server_installed=$(dpkg-query -W --showformat='${Status}\n' mysql-server|grep "installed")
  printf "Verification de mysql-server : $mysql_server_installed\n"
  if [ "" = "$mysql_server_installed" ]; then
    printf "le paquet mysql-server est introuvable ; installation du paquet\n"
    sudo apt install mysql-server -y

    printf "Pour la suite de la configuration de la base de donnée, veuillez utiliser l'utilisateur \"root\" et laisser le champ de mot de passe vide\n"
    printf "Il est très fortement recommandé d'accepter de créer un nouvel utilisateur pour le serveur web lorsque l'option sera proposée"
  fi
fi

printf "\nInitialisation de la base de données\n"

printf "\nVeuillez entrer les identifiants de connexion à la base de données\n"
printf "(Assurez-vous que ces identifiants permettent de créer de nouvelles bases de données si vous souhaitez créer la base de données durant l'installation)\n"
printf "(Assurez-vous que ces identifiants permettent de créer de nouveaux utilisateurs si vous souhaitez que le serveur web se connecte avec d'autres identifiants)\n"
printf "Nom d'utilisateur : "

read nom_user_db

printf "Mot de passe : "

read -s mdp_db

printf "\n\nVeuillez entrer le nom de la base de données (par défaut $nom_hote_virtuel""_db) : "
read nom_bado
nom_bado=${nom_bado:-"$nom_hote_virtuel""_db"}
mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -e "CREATE DATABASE IF NOT EXISTS $nom_bado"

printf "Voulez-vous créer un utilisateur pour le serveur web (o/n) : "
read creer_utilisateur

regex_on='^(o|n)$'

while [[ ! $creer_utilisateur =~ $regex_on ]] ; do
  printf "Veuillez répondre par o (oui) ou n (non)\n"
  printf "Voulez-vous créer un utilisateur pour le serveur web (o/n) ?\n"
  printf "(Si vous ne créez pas d'utilisateur, les identifiants entrés pour se connecter à la base de données seront également ceux utilisés par le serveur web)\n"
  read creer_utilisateur
done

if [ $creer_utilisateur = "o" ] ; then

  nom_serv_db="$nom_hote_virtuel""_user"
  mdp_serv_db=""

  chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
  for i in {1..20} ; do
    mdp_serv_db=$mdp_serv_db"${chars:RANDOM%${#chars}:1}"
  done

  mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -e "CREATE USER '$nom_serv_db'@'%' IDENTIFIED WITH mysql_native_password BY '$mdp_serv_db'"
  mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -e "GRANT DELETE, INSERT, SELECT, UPDATE ON $nom_bado.* TO '$nom_serv_db'@'%'"

  mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -e "CREATE USER '$nom_serv_db'@'localhost' IDENTIFIED WITH mysql_native_password BY '$mdp_serv_db'"
  mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -e "GRANT DELETE, INSERT, SELECT, UPDATE ON $nom_bado.* TO '$nom_serv_db'@'localhost'"

fi

printf "Création des tables de la base de données\n"
mysql -u $nom_user_db -p$mdp_db -h $adresse_bado -D $nom_bado < src/sql_init/init.sql

if [ -z "$nom_serv_db" ] ; then
  $nom_serv_db=$nom_user_db
  $mdp_serv_db=$mdp_db
fi

sudo mkdir /var/www/$nom_hote_virtuel/sql
sudo touch /var/www/$nom_hote_virtuel/sql/id_connexion
sudo printf "$nom_serv_db $mdp_serv_db $nom_bado $adresse_bado" > /var/www/$nom_hote_virtuel/sql/id_connexion
