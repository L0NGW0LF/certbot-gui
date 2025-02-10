<?php
if (isset($_GET['cert_path']) && isset($_GET['key_path'])) {
    $cert_path = escapeshellarg(trim($_GET['cert_path']));
    $key_path = escapeshellarg(trim($_GET['key_path']));
    $tempDir = sys_get_temp_dir();
    $cert_tempFile = tempnam($tempDir, 'cert_');
    $key_tempFile = tempnam($tempDir, 'key_');

    // Usa sudo per copiare i file
    $cert_command = "sudo cp $cert_path $cert_tempFile";
    $key_command = "sudo cp $key_path $key_tempFile";
    $cert_output = [];
    $key_output = [];
    $cert_return_var = 0;
    $key_return_var = 0;
    exec($cert_command, $cert_output, $cert_return_var);
    exec($key_command, $key_output, $key_return_var);

    // Debug: stampa di debug nella console del browser
    echo "<script>console.log('Cert command: " . addslashes($cert_command) . "');</script>";
    echo "<script>console.log('Cert output: " . addslashes(implode('\n', $cert_output)) . "');</script>";
    echo "<script>console.log('Cert return var: $cert_return_var');</script>";
    
    echo "<script>console.log('Key command: " . addslashes($key_command) . "');</script>";
    echo "<script>console.log('Key output: " . addslashes(implode('\n', $key_output)) . "');</script>";
    echo "<script>console.log('Key return var: $key_return_var');</script>";

    if ($cert_return_var === 0 && $key_return_var === 0 && file_exists($cert_tempFile) && file_exists($key_tempFile)) {
        // Estrai il nome del dominio dal percorso del certificato
        $domain = basename(dirname(trim($_GET['cert_path'])));

        // Imposta gli header per il download dei file
        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"$domain.zip\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        // Crea un file zip temporaneo
        $zip = new ZipArchive();
        $zipFile = tempnam($tempDir, 'zip_');
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($cert_tempFile, basename($_GET['cert_path']));
            $zip->addFile($key_tempFile, basename($_GET['key_path']));
            $zip->close();

            // Leggi il contenuto del file zip e invialo come download
            readfile($zipFile);

            // Elimina i file temporanei
            unlink($cert_tempFile);
            unlink($key_tempFile);
            unlink($zipFile);
        } else {
            echo "Impossibile creare il file zip.";
        }
        exit;
    } else {
        echo "Copia dei file fallita o file temporaneo non trovato.";
    }
} else {
    echo "Percorsi dei file non specificati.";
}
?>