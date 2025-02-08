
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certbot Command Runner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
        .input-group {
            margin-bottom: 20px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .input-group button {
            padding: 10px 20px;
            font-size: 16px;
        }
        #output {
            margin-top: 20px;
            white-space: pre-wrap;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            height: 300px;
            overflow-y: scroll;
        }
        .service-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-left: 10px;
        }
        .service-button:hover {
            background-color: #45a049;
        }
        .action-button {
            background-color: #008CBA;
            color: white;
            border: none;
            margin-left: 10px;
        }
        .action-button:hover {
            background-color: #007B9A;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Run Certbot Command</h1>
        <div class="input-group">
            <label for="domain">Domain:</label>
            <input type="text" id="domain" name="domain" required>
        </div>
        <div class="input-group">
            <button id="runButton">Run Certbot</button>
            <button id="stopButton">Stop</button>
            <button id="pressEnter" class="action-button">Press Enter</button>
            <button id="restartApache" class="service-button">Restart Apache</button>
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

        document.getElementById('restartApache').addEventListener('click', function() {
            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML += '[INFO] Restarting Apache...\n';

            fetch('restart_apache.php')
                .then(response => response.text())
                .then(data => {
                    outputDiv.innerHTML += data + '\n';
                    outputDiv.scrollTop = outputDiv.scrollHeight;
                })
                .catch(error => {
                    outputDiv.innerHTML += '[ERROR] Failed to restart Apache: ' + error + '\n';
                });
        });
    </script>
</body>
</html>
