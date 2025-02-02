<?php
session_start();

// Variabili per il risultato
$output = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_POST['domain'];
    $email = $_POST['email'];
    $webroot = $_POST['webroot'];
    $server = $_POST['server']; // Menu a tendina per scegliere il tipo di sistema
    $renew = isset($_POST['renew']); // Verifica se il checkbox è selezionato

    // Verifica che tutti i campi obbligatori siano stati compilati
    if (empty($domain) || empty($email)) {
        $output = "Tutti i campi sono obbligatori!";
        $error = true;
    } else {
        // Costruisci il comando Certbot
        if (empty($server)) {
            // Se il server non è specificato, usa il comando con webroot
            $command = "sudo certbot certonly --non-interactive --agree-tos --email $email -d $domain --webroot -w $webroot 2>&1";
        } else {
            // Seleziona il comando appropriato in base al server scelto
            $command = "sudo certbot --$server --non-interactive --agree-tos --email $email -d $domain 2>&1";
        }

        // Esegue il comando e cattura l'output
        $output = shell_exec($command);

        // Controlla se ci sono errori nel risultato
        if (strpos($output, "failed") !== false || strpos($output, "error") !== false) {
            $error = true;
        } else {
            // Se il rinnovo automatico è selezionato, crea un timer systemd
            if ($renew) {
                $timerName = "certbot-renew-$domain";
                $serviceFilePath = "/etc/systemd/system/$timerName.service";
                $timerFilePath = "/etc/systemd/system/$timerName.timer";

                // Crea il file di servizio
                $serviceFileContent = "[Unit]
Description=Renew Certbot certificate for $domain

[Service]
Type=oneshot
ExecStart=/usr/bin/certbot renew --force-renewal --cert-name $domain
";

                file_put_contents($serviceFilePath, $serviceFileContent);

                // Crea il file di timer
                $timerFileContent = "[Unit]
Description=Run Certbot renew every 45 days for $domain

[Timer]
OnCalendar=*-*-* *:*:0/3888000
Persistent=true

[Install]
WantedBy=timers.target
";

                file_put_contents($timerFilePath, $timerFileContent);

                // Abilita e avvia il timer
                shell_exec("sudo systemctl enable $timerName.timer");
                shell_exec("sudo systemctl start $timerName.timer");
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Certbot</title>
    <link rel="stylesheet" href="process.css"> <!-- Collegamento al file CSS -->
</head>

<body>
    <div class="container">
        <h1>Generatore Certificato SSL</h1>

        <!-- Risultato del comando Certbot -->
        <?php if (!empty($output)): ?>
            <div class="result <?php echo $error ? 'error' : 'success'; ?>">
                <h2><?php echo $error ? "Errore" : "Successo"; ?></h2>
                <pre><?php echo htmlspecialchars($output); ?></pre>
                <a href="../index.php">
                    <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF" width="40"
                        height="40">
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>