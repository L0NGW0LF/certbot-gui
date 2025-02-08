<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function send_message($message)
{
    echo "data: {$message}\n\n";
    ob_flush();
    flush();
}

function validateDomain($domain) {
    return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
}

function runCertbotCommand($domain)
{
    if (!validateDomain($domain)) {
        send_message("[ERROR] Invalid domain format");
        return false;
    }

    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];

    // Set a reasonable timeout
    set_time_limit(300); // 5 minutes timeout

    $process = proc_open("sudo certbot certonly --agree-tos --manual --preferred-challenges dns -d " . escapeshellarg($domain), $descriptorspec, $pipes);

    if (!is_resource($process)) {
        send_message("[ERROR] Failed to start the process");
        return false;
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $stdin = $pipes[0];
    $stdout = $pipes[1];
    $stderr = $pipes[2];

    $buffer = '';
    $error_buffer = '';
    $complete_output = '';
    $start_time = time();
    $timeout = 300; // 5 minutes timeout

    while ((!feof($stdout) || !feof($stderr)) && (time() - $start_time < $timeout)) {
        $read = [$stdout, $stderr];
        $write = null;
        $except = null;
        
        if (stream_select($read, $write, $except, 1) === false) {
            break;
        }

        foreach ($read as $stream) {
            if ($stream === $stdout) {
                $content = fgets($stdout);
                if ($content !== false) {
                    $complete_output .= $content;
                    $buffer .= $content;
                    
                    if (strpos($buffer, "\n") !== false) {
                        send_message(trim($buffer));
                        $buffer = '';
                    }
                }
            } elseif ($stream === $stderr) {
                $content = fgets($stderr);
                if ($content !== false) {
                    $error_buffer .= $content;
                    if (trim($error_buffer) !== '') {
                        send_message("[ERROR] " . trim($error_buffer));
                        $error_buffer = '';
                    }
                }
            }
        }

        if (strpos($complete_output, '_acme-challenge') !== false && strpos($complete_output, 'with the following value:') !== false) {
            $lines = explode("\n", $complete_output);
            $record_name = '';
            $record_value = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '_acme-challenge') !== false && empty($record_name)) {
                    $record_name = $line;
                } else if (!empty($record_name) && !empty($line) && strpos($line, '_acme-challenge') === false && strpos($line, 'with the following value:') === false) {
                    $record_value = $line;
                    break;
                }
            }
            
            if (!empty($record_name) && !empty($record_value)) {
                send_message("[DNS TXT Record Details]");
                send_message("Name: " . $record_name);
                send_message("Value: " . $record_value);
                send_message("[INFO] Please configure this DNS TXT record and press Enter when ready...");
                
                // Wait for user input
                while (!file_exists('/tmp/certbot_input') || trim(file_get_contents('/tmp/certbot_input')) === '') {
                    usleep(100000); // Sleep for 0.1 seconds
                    if (time() - $start_time >= $timeout) {
                        send_message("[ERROR] Timeout waiting for user input");
                        break;
                    }
                }
                
                if (file_exists('/tmp/certbot_input')) {
                    send_message("[INFO] Processing DNS verification...");
                    fwrite($stdin, "\n");
                    file_put_contents('/tmp/certbot_input', '');
                    $complete_output = '';
                }
            }
        }

        if (strpos($buffer, 'Press Enter to continue') !== false) {
            send_message("[INFO] Waiting for user to press Enter...");
            // Wait for input file to be modified
            while (!file_get_contents('/tmp/certbot_input') && (time() - $start_time < $timeout)) {
                usleep(100000); // Sleep for 0.1 seconds
            }
            
            send_message("[SUCCESS] Enter key pressed successfully!");
            fwrite($stdin, "\n");
            file_put_contents('/tmp/certbot_input', ''); // Clear the input file
            $buffer = '';
        }
    }

    // Clean up
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }

    $return_value = proc_close($process);
    send_message("Command returned: $return_value");
    return $return_value === 0;
}

if (isset($_GET['domain'])) {
    $domain = trim($_GET['domain']);
    send_message("Domain received: $domain");
    runCertbotCommand($domain);
} else {
    send_message("No domain provided.");
}

?>

<script>
document.getElementById('pressEnter').addEventListener('click', function() {
    if (waitingForEnter) {
        fetch('send_enter.php')
            .then(response => response.text())
            .then(data => {
                console.log('Enter signal sent:', data);
                document.getElementById('pressEnter').style.display = 'none';
                waitingForEnter = false;
            })
            .catch(error => {
                console.error('Error sending Enter:', error);
                const outputDiv = document.getElementById('output');
                outputDiv.innerHTML += '[ERROR] Failed to send Enter command: ' + error + '\n';
            });
    }
});
</script>