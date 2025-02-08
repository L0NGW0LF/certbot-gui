
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certbot Command Runner</title>
    <link rel="stylesheet" href="index.css"> <!-- Collegamento al file CSS -->
</head>
<body>
    <div class="container">
        <h1>Remote Certbot</h1>

      <!-- Input per il dominio -->
        <div class="input-group">
            <label for="domain">Domain:</label>
            <input type="text" id="domain" name="domain" placeholder="es: example.com" required>
        </div>


        <div class="input-group">
            <button id="runButton" class="btn">Run Certbot</button>
            <button id="stopButton" class="btn">Stop</button>
            <button id="pressEnter" class="action-button btn">Press Enter</button>
        </div>
        <div id="output"></div>
    </div>

    <script>
        let eventSource;
        let waitingForEnter = false;

        document.getElementById('runButton').addEventListener('click', function() {
            const domain = document.getElementById('domain').value;
            if (!domain) {
                alert('Please enter a domain');
                return;
            }

            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML = 'Running...\n';

            eventSource = new EventSource('test1.php?domain=' + encodeURIComponent(domain));
            eventSource.onmessage = function(event) {
                outputDiv.innerHTML += event.data + '\n';
                outputDiv.scrollTop = outputDiv.scrollHeight;

                // Show the Press Enter button when DNS record needs to be configured
                if (event.data.includes('[DNS TXT Record Details]') || 
                    event.data.includes('Please configure this DNS TXT record')) {
                    document.getElementById('pressEnter').style.display = 'inline-block';
                    waitingForEnter = true;
                }
            };

            eventSource.onerror = function() {
                outputDiv.innerHTML += '\n[ERROR] An error occurred while running the command.\n';
                eventSource.close();
            };
        });

        document.getElementById('stopButton').addEventListener('click', function() {
            if (eventSource) {
                eventSource.close();
                const outputDiv = document.getElementById('output');
                outputDiv.innerHTML += '\n[INFO] Command execution stopped by user.\n';
            }
        });

        document.getElementById('pressEnter').addEventListener('click', function() {
            if (waitingForEnter) {
                const outputDiv = document.getElementById('output');
                fetch('sender_enter.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            outputDiv.innerHTML += '[INFO] Continuing with certificate verification...\n';
                            document.getElementById('pressEnter').style.display = 'none';
                            waitingForEnter = false;
                        } else {
                            outputDiv.innerHTML += '[ERROR] ' + data.message + '\n';
                        }
                        outputDiv.scrollTop = outputDiv.scrollHeight;
                    })
                    .catch(error => {
                        outputDiv.innerHTML += '[ERROR] Failed to send Enter command: ' + error + '\n';
                        outputDiv.scrollTop = outputDiv.scrollHeight;
                    });
            }
        });
    </script>
</body>
</html>
