<?php
session_start();

// Gestisci la visualizzazione (Apache o Nginx)
if (isset($_GET['view'])) {
    $_SESSION['view'] = $_GET['view'];
}
$view = isset($_SESSION['view']) ? $_SESSION['view'] : 'apache';

// Percorso della cartella in base alla visualizzazione selezionata
$directory = ($view == 'nginx') ? '/etc/nginx/sites-available/' : '/etc/apache2/sites-available/';

// Funzione per verificare se il percorso è una cartella
if (is_dir($directory)) {
    $files = scandir($directory);
} else {
    die("La directory non esiste.");
}

// Funzione per visualizzare il contenuto del file
function viewFileContent($filePath) {
    if (file_exists($filePath)) {
        return htmlspecialchars(file_get_contents($filePath));
    } else {
        return "File non trovato.";
    }
}

// Funzione per salvare il contenuto del file
function saveFileContent($filePath, $content) {
    if (is_writable($filePath)) {
        file_put_contents($filePath, $content);
        return "File salvato con successo.";
    } else {
        return "Errore: Il file non è scrivibile.";
    }
}

// Funzione per creare un nuovo file
function createNewFile($filePath) {
    if (!file_exists($filePath)) {
        if (is_writable(dirname($filePath))) {
            file_put_contents($filePath, ""); // Crea un file vuoto
            return "File creato con successo.";
        } else {
            return "Errore: La directory non è scrivibile.";
        }
    } else {
        return "Il file esiste già.";
    }
}

// Funzione per rimuovere un file
function removeFile($filePath) {
    if (file_exists($filePath)) {
        if (is_writable($filePath)) {
            unlink($filePath);
            return "File rimosso con successo.";
        } else {
            return "Errore: Il file non è scrivibile.";
        }
    } else {
        return "Errore: Il file non esiste.";
    }
}

// Controlla se è stato selezionato un file
if (isset($_GET['file'])) {
    $selectedFile = $_GET['file'];
    $filePath = $directory . '/' . basename($selectedFile);
    $fileContent = viewFileContent($filePath);
}

// Controlla se è stata richiesta la rimozione di un file
if (isset($_GET['remove'])) {
    $fileToRemove = $_GET['remove'];
    $filePath = $directory . '/' . basename($fileToRemove);
    $message = removeFile($filePath);
    // Aggiorna l'elenco dei file dopo la rimozione
    $files = scandir($directory);
}

// Controlla se è stato inviato il modulo di modifica
if (isset($_POST['save'])) {
    $selectedFile = $_POST['file'];
    $filePath = $directory . '/' . basename($selectedFile);
    $newContent = $_POST['content'];
    $message = saveFileContent($filePath, $newContent);
    $fileContent = viewFileContent($filePath);
}

// Controlla se è stato inviato il modulo per creare un nuovo file
if (isset($_POST['create'])) {
    $newFileName = $_POST['new_file_name'];
    $newFilePath = $directory . '/' . basename($newFileName);
    $message = createNewFile($newFilePath);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco File</title>
</head>
<link rel="stylesheet" href="show_sites.css"> <!-- Collegamento al file CSS -->

<body>

<div class="container">
    <a href="../index.php">
            <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF"  
            width="40" 
            height="40">
    </a>
    <h1>Elenco dei file in <?php echo htmlspecialchars($directory); ?></h1>
    
    <!-- Pulsante per cambiare visualizzazione -->
    <form method="get">
        <button type="submit" name="view" value="apache">Visualizza Apache</button>
        <button type="submit" name="view" value="nginx">Visualizza Nginx</button>
    </form>

    <?php if (count($files) > 2): // Controlla se ci sono file (escludendo '.' e '..') ?>
        <div class="table-wrapper">
            <table border="1" >
                <tr>
                    <th>Nome File</th>
                    <th>Visualizza</th>
                    <th>Rimuovi</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <?php if ($file != '.' && $file != '..'): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file); ?></td>
                            <td>
                                <a href="?file=<?php echo urlencode($file); ?>">Visualizza</a>
                            </td>
                            <td>
                                <a href="?remove=<?php echo urlencode($file); ?>" onclick="return confirm('Sei sicuro di voler rimuovere questo file?');">Rimuovi</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>                
    <?php else: ?>
        <p>No Site attivi trovati.</p>
    <?php endif; ?>

    <?php if (isset($fileContent)): ?>
        <h2>Contenuto del file: <?php echo htmlspecialchars($selectedFile); ?></h2>
        <form method="post">
            <textarea class="table-wrapper" name="content" rows="20" cols="100"><?php echo $fileContent; ?></textarea><br>
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($selectedFile); ?>">
            <input type="submit" name="save" value="Salva">
        </form>
        <?php if (isset($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <h2>Crea un nuovo file di configurazione</h2>
    <form method="post">
        <label for="new_file_name">Nome del nuovo file (es: nuovo_sito.conf):</label>
        <input type="text" name="new_file_name" id="new_file_name" required><br>
        <input type="submit" name="create" value="Crea">
    </form>
    <?php if (isset($message) && !isset($fileContent)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <div>
</body>
</html>