<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Certbot</title>
    <link rel="stylesheet" href="index.css"> <!-- Collegamento al file CSS -->

</head>
<body>
    <div class="container">
        <img 
        src="https://www.svgrepo.com/show/353542/certbot.svg"
        width="300" 
        height="300">
        <p>Scegli un'opzione:</p>
        <div class="button-container">
            <!-- Pulsante per creare un nuovo certificato -->
            <a href="createCertificate/create_certificate.php" class="btn">Crea un Nuovo Certificato</a>
            <!-- Pulsante per visualizzare i certificati attivi -->
            <a href="viewCertificates/view_certificates.php" class="btn">Visualizza Certificati Attivi</a>
            <a href="showSites/show_sites.php" class="btn">Gestisci i Sites</a>
        </div>
    </div>
</body>
</html>