#!/bin/bash

# ESEGUIRE QUESTO PRIMA DI AVVIARE LO SCRIPT: chmod +x setup.sh

# Funzione per richiedere l'input dell'utente con convalida
ask_user() {
    local prompt="$1"
    local input

    while true; do
        read -p "$prompt" input
        if [[ "$input" == "s" || "$input" == "n" ]]; then
            echo "$input"
            break
        else
            echo "Risposta non valida. Inserisci 's' per sì o 'n' per no."
        fi
    done
}

# Chiedi all'utente se desidera installare Apache
install_apache=$(ask_user "Vuoi installare Apache? (s/n): ")

# Se l'utente desidera installare Apache
if [[ "$install_apache" == "s" ]]; then
    

    # Installa Apache e PHP
    sudo apt update
    sudo apt install -y apache2
    sudo apt install -y php libapache2-mod-php
    sudo apt install -y php-mysql php-xml php-mbstring php-cli php-cgi php-fpm


    # Cambia il proprietario delle directory di configurazione di Apache
    sudo chown -R www-data:www-data /etc/apache2/sites-available/
    sudo chmod -R 755 /etc/apache2/sites-available/
fi

# Chiedi all'utente se desidera installare Nginx
install_nginx=$(ask_user "Vuoi installare Nginx? (s/n): ")

# Se l'utente desidera installare Nginx
if [[ "$install_nginx" == "s" ]]; then
    

    # Installa Nginx e PHP-FPM
    sudo apt update
    sudo apt install -y nginx
    sudo apt install -y php-fpm


    # Cambia il proprietario delle directory di configurazione di Nginx
    sudo chown -R www-data:www-data /etc/nginx/sites-available/
    sudo chmod -R 755 /etc/nginx/sites-available/
fi

# Aggiungi regole a sudoers per www-data
echo "www-data ALL=(ALL) NOPASSWD: /usr/bin/certbot" | sudo tee -a /etc/sudoers
echo "www-data ALL=(ALL) NOPASSWD: /bin/cp /etc/letsencrypt/live/*" | sudo tee -a /etc/sudoers


# Installa Python 3, Certbot
sudo apt install -y python3 python3-venv libaugeas0 

# Crea un ambiente virtuale per Certbot
sudo python3 -m venv /opt/certbot/

# Aggiorna pip e installa Certbot
sudo /opt/certbot/bin/pip install --upgrade pip
sudo /opt/certbot/bin/pip install certbot certbot-apache certbot-nginx

# Crea un link simbolico per Certbot
sudo ln -s /opt/certbot/bin/certbot /usr/bin/certbot

# Permette all'utente www-data di eseguire routine di rinnovo
sudo chown www-data:www-data /etc/systemd/system/
sudo chmod 775 /etc/systemd/system/

echo "Installazione eseguita con successo."