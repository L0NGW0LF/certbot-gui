<?php
session_start();

// Variabili per il risultato
$output = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_POST['domain'];
    $email = $_POST['email'];
    $webroot = $_POST['webroot'];
    $server = $_POST['server']; 
    $renew = isset($_POST['renew']); 
    $test = isset($_POST['test']); 

    // Verifica che tutti i campi obbligatori siano stati compilati
    if (empty($domain) || empty($email)) {
        $output = "Tutti i campi sono obbligatori!";
        $error = true;
    } else {
        // Costruisci il comando Certbot
        if (empty($server)) {
            if ($test) {
                $command = "sudo certbot certonly --dry-run -d $domain --webroot -w $webroot --non-interactive";
            } else {
                $command = "sudo certbot certonly --non-interactive --agree-tos --email $email -d $domain --webroot -w $webroot";
            }
        } else {
            if ($test) {
                $command = "sudo certbot certonly --dry-run -d $domain --$server --non-interactive";
            } else {
                $command = "sudo certbot --$server --non-interactive --agree-tos --email $email -d $domain";
            }
        }

        $command .= " 2>&1";
        $output = shell_exec($command);

        if (strpos($output, "failed") !== false || strpos($output, "error") !== false) {
            $error = true;
        } else {
            if ($renew) {
                $timerName = "certbot-renew-$domain";
                $serviceFilePath = "/etc/systemd/system/$timerName.service";
                $timerFilePath = "/etc/systemd/system/$timerName.timer";
                
                $serviceFileContent = "[Unit]\nDescription=Renew Certbot certificate for $domain\n\n[Service]\nType=oneshot\nExecStart=/usr/bin/certbot renew --force-renewal --cert-name $domain\n";
                
                if (file_put_contents($serviceFilePath, $serviceFileContent) === false) {
                    $output .= "\nErrore nella scrittura del file di servizio.";
                    $error = true;
                }
                
                $timerFileContent = "[Unit]\nDescription=Run Certbot renew every 45 days for $domain\n\n[Timer]\nOnUnitActiveSec=3888000\nPersistent=true\n\n[Install]\nWantedBy=timers.target\n";
                
                if (file_put_contents($timerFilePath, $timerFileContent) === false) {
                    $output .= "\nErrore nella scrittura del file di timer.";
                    $error = true;
                }
                
                if (!$error) {
                    shell_exec("sudo systemctl daemon-reload 2>&1");
                    $enableOutput = shell_exec("sudo systemctl enable $timerName.timer 2>&1");
                    $startOutput = shell_exec("sudo systemctl start $timerName.timer 2>&1");
                    
                    if (strpos($enableOutput, "failed") !== false || strpos($startOutput, "failed") !== false) {
                        $output .= "\nErrore durante l'abilitazione o l'avvio del timer.";
                        $output .= "\nEnable Output: $enableOutput";
                        $output .= "\nStart Output: $startOutput";
                        $error = true;
                    }
                }
            }
        }
    }
}
?>
