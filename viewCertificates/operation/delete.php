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
                 * delete.php
                 * Elimina il certificato associato a un determinato dominio utilizzando Certbot.
                 */

                // Recupera il dominio dalla query string
                if (!isset($_GET['domain'])) {
                    echo "Dominio non specificato.";
                    exit;
                }

                $domain = trim($_GET['domain']); 

                // Esegui il comando per eliminare il certificato
                // '--cert-name' specifica il certificato da eliminare
                $command = "sudo certbot delete --cert-name {$domain} --non-interactive 2>&1";
                $output = shell_exec($command);

                // Verifica l'output e fornisci un feedback
                // L'uscita tipica in caso di successo non è particolarmente chiara; si può cercare
                // stringhe specifiche per verificare la riuscita, o semplicemente mostrare l'output
                if (empty($output)) {
                    // Se l'output è vuoto, cerchiamo di dare un messaggio di conferma
                    echo "Certificato per il dominio {$_GET['domain']} eliminato (o non presente).";
                } else {
                    echo "Risultato dell'eliminazione del certificato per il dominio {$_GET['domain']}:<br>";
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