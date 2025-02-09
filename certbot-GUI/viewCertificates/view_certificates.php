<?php
/**
 * Questo script esegue "certbot certificates", ne acquisisce l'output
 * e mostra le informazioni principali in una semplice tabella HTML.
 */
$output = shell_exec('sudo certbot certificates 2>&1');
if (!$output) {
    // Gestione di un eventuale errore o output vuoto
    echo "Impossibile eseguire 'certbot certificates' o nessun output disponibile.";
    exit;
}
// Suddividiamo l'output in righe
$lines = explode("\n", $output);
// Array per memorizzare i certificati trovati
$certificates = [];
$currentCert = [];
// Cerchiamo le righe significative
foreach ($lines as $line) {
    $line = trim($line);
    // Inizia un nuovo certificato
    if (strpos($line, 'Certificate Name:') === 0) {
        // Se esiste un certificato in corso di lettura, lo salviamo prima di iniziare il nuovo
        if (!empty($currentCert)) {
            $certificates[] = $currentCert;
        }
        // Avviamo un nuovo array di info per il certificato
        $currentCert = [
            'name' => substr($line, strlen('Certificate Name:'))
        ];
    } elseif (strpos($line, 'Domains:') === 0) {
        $currentCert['domains'] = substr($line, strlen('Domains:'));
    } elseif (strpos($line, 'Expiry Date:') === 0) {
        // L'output di certbot potrebbe contenere data, ora e stato di validità
        $currentCert['expiry_date'] = substr($line, strlen('Expiry Date:'));
    } elseif (strpos($line, 'Certificate Path:') === 0) {
        $currentCert['cert_path'] = substr($line, strlen('Certificate Path:'));
    } elseif (strpos($line, 'Private Key Path:') === 0) {
        $currentCert['key_path'] = substr($line, strlen('Private Key Path:'));
    }
}

function getCertDirectory($domain)
{
    return "/etc/letsencrypt/live/" . $domain;
}

function openDirectory($path)
{
    $directory = escapeshellarg($path);
    shell_exec("sudo xdg-open $directory ");
}
// Se l'ultimo certificato non è stato ancora aggiunto all'array
if (!empty($currentCert)) {
    $certificates[] = $currentCert;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certbot </title> <!-- Titolo della pagina -->
    <link rel="stylesheet" href="view_certificates.css"> <!-- Collegamento al file CSS -->

</head>

<body>
    <div class="container">
        <a href="../index.php">
            <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF" width="40"
                height="40">
        </a>
        <h1>Lista dei Certificati Gestiti da Certbot</h1>
        <?php if (empty($certificates)): ?>
            <p>Nessun certificato trovato.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome Certificato</th>
                            <th>Domini</th>
                            <th>Data Scadenza</th>
                            <th>Percorso Certificato</th>
                            <th>Percorso Chiave Privata</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td><?php echo htmlentities($cert['name'] ?? ''); ?></td>
                                <td><?php echo htmlentities($cert['domains'] ?? ''); ?></td>
                                <td><?php echo htmlentities($cert['expiry_date'] ?? ''); ?></td>
                                <td><?php echo htmlentities($cert['cert_path'] ?? ''); ?></td>
                                <td><?php echo htmlentities($cert['key_path'] ?? ''); ?></td>
                                <td>
                                    <a href="operation/renew.php?domain=<?php echo urlencode($cert['domains']); ?>">Rinnova</a>
                                    |
                                    <a href="operation/delete.php?domain=<?php echo urlencode($cert['domains']); ?>">Elimina</a>
                                    |
                                    <?php
                                    $cert_dir = getCertDirectory(trim($cert['domains']));
                                    if (isset($_GET['open']) && $_GET['open'] === $cert['domains']) {
                                        openDirectory($cert_dir);
                                        // Redirect to remove the 'open' parameter
                                        header('Location: view_certificates.php');
                                        exit;
                                    }
                                    ?>
                                    <a href="?open=<?php echo urlencode($cert['domains']); ?>">Apri Directory</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>