<?php
// Path to the file you want to open
$file = '/path/to/your/file.pdf';

// Escape the file path to ensure it is safe to use in a shell command
$escapedFile = escapeshellarg($file);

// Command to open the file using xdg-open
$command = 'xdg-open ' . $escapedFile;

// Execute the command and capture the output and return status
exec($command . ' > /dev/null 2>&1 &', $output, $return_var);

if ($return_var === 0) {
    echo "The file has been opened successfully.";
} else {
    echo "Failed to open the file.";
}
?>