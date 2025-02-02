<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadati della pagina -->
    <meta charset="UTF-8"> <!-- Codifica dei caratteri -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ottimizzazione per dispositivi mobili -->
    <title>Certbot </title> <!-- Titolo della pagina -->
    <link rel="stylesheet" href="create_certificate.css"> <!-- Collegamento al file CSS -->
</head>
<body>

    <!-- Contenitore principale -->
    <div class="container">
    <a href="../index.php">
            <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF"  
            width="40" 
            height="40">
    </a>

        <!-- Titolo della pagina -->
        <h1>Crea Certificato </h1><br>
        <!-- Form per l'input utente -->
        <form method="POST" action="processCertificate/process.php">
            <!-- Campo per il dominio -->
            <label for="domain">Dominio:</label>
            <input type="text" id="domain" name="domain" placeholder="Inserisci il dominio" required>

            <!-- Campo per l'email -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Inserisci l'email" required>

            <!-- Pulsanti per alternare tra Webroot e ServerWeb -->
            <div class="toggle-container">
                <button type="button" class="toggle-btn active" data-target="webroot">Webroot</button>
                <button type="button" class="toggle-btn" data-target="server">ServerWeb</button>
            </div>

            <!-- Sezione Webroot (visibile di default) -->
            <div id="webroot">
                <label for="webroot-input">Percorso Webroot:</label><br>
                <input type="text" id="webroot-input" name="webroot" placeholder="Inserisci il percorso webroot" required>
            </div>

            <!-- Sezione ServerWeb (nascosta di default) -->
            <div id="server" class="hidden">
                <label for="server-select">Seleziona il server web:</label><br>
                <label for="server-hint">(NB: Bisogna inserire il serverName nella configurazione Site di interesse)</label><br>
                <select id="server-select" name="server">
                    <option value="apache">Apache</option>
                    <option value="nginx">Nginx</option>
                    <!-- <option value="lighttpd">Lighttpd</option>
                    <option value="caddy">Caddy</option> -->
                </select>
            </div>


            <!-- Checkbox del Rinnovo automatico -->
            <div class="renew">
                <input type="checkbox" id="renewCheckbox" name="renew">
                <label for="renewCheckbox"></label>
                <span>Rinnovo automatico certificato</span>
            </div>


            <!-- Checkbox del Test certificato -->
            <div class="test">
                <input type="checkbox" id="testCheckbox" name="test">
                <label for="testCheckbox"></label>
                <span>Test richiesta certificato</span>
            </div>
          

            <!-- Pulsante di invio del form -->
            <button type="submit">Esegui Certbot</button>
        </form>
    </div>

    <!-- Collegamento al file JavaScript -->
    <script src="create_certificate.js"></script>
</body>
</html>