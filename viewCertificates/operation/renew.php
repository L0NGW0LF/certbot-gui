<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard Certbot</title>
        <link rel="stylesheet" href="../../createCertificate/processCertificate/process.css"> <!-- Collegamento al file CSS -->
        </head>
    <body>
        <div class="container">
            <h1>Esito Certbot </h1><br><br><br>
            <p>
                <?php
                /**
                 * renew.php
                 * Rinnova il certificato associato a un determinato dominio utilizzando Certbot.
                 */

                // Recupera il dominio dalla query string e rimuove gli spazi non necessari
                if (!isset($_GET['domain']) || trim($_GET['domain']) === '') {
                    echo "Dominio non specificato.";
                    exit;
                }

                $domain = trim($_GET['domain']); 

                // Esegui il comando per rinnovare il certificato
                // Con l'opzione --force-renewal forza il rinnovo anche se non necessario.
                // Utilizza '--cert-name' per indicare il nome del certificato, 
                // che di solito corrisponde a uno dei domini configurati.
                $command = "sudo certbot renew --cert-name {$domain} --force-renewal 2>&1";

                // Esegui il comando
                $output = shell_exec($command);

                // Verifica l'output e fornisci un feedback
                if (strpos($output, 'Congratulations') !== false) {
                    echo "Certificato per il dominio {$_GET['domain']} rinnovato con successo.";
                } else {
                    echo "Errore durante il rinnovo del certificato per il dominio {$_GET['domain']}:<br>";
                    echo nl2br(htmlentities($output));
                }
                ?>
            </p><br><br><br>
            <a href="../../index.php">
                <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF"  
                    width="40" 
                    height="40">
                </img>
             </a>
        </div>
    </body>
</html>
