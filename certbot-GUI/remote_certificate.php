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
        <a href="index.php">
            <img class="back" src="https://img.icons8.com/?size=100&id=85498&format=png&color=FFFFFF"  
            width="40" 
            height="40">
        </a>
        <h1>Remote Certbot</h1>

        <!-- Input per il dominio -->
        <div class="input-group">
            <label for="domain">Domain:</label>     
            <input type="text" id="domain" name="domain" placeholder="es: example.com" required><br><br><br>
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" placeholder="es: prova@email.com" required>
        </div>


        <div class="input-group">
            <button id="runButton" class="btn">Run Certbot</button>
            <button id="stopButton" class="btn">Stop Certbot</button>
            <button id="toggleOutput" class="btn">Show Output</button><br><br>
        </div>
        <div id="output" class="input-group"></div><br>
        <div class="output-name-value">
            <h2 for="domain" id="txtName"></h2>
            <h2 for="domain" id="txtValue"></h2>
        </div><br>

        <button id="pressEnter" class="action-button btn">Press Enter</button>

    </div>

    <script>
        let eventSource;
        let waitingForEnter = false;

        document.getElementById('runButton').addEventListener('click', function () {
            const domain = document.getElementById('domain').value;
            const email = document.getElementById('email').value;

            if (!domain || !email) {
                alert('Please enter both domain and email');
                return;
            }

            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML = 'Running...\n';

            // Modifica l'URL per includere sia il dominio che l'email
            eventSource = new EventSource('remote_process.php?domain=' + encodeURIComponent(domain) +
                '&email=' + encodeURIComponent(email));
            eventSource.onmessage = function (event) {
                const outputDiv = document.getElementById('output');
                outputDiv.innerHTML += event.data + '\n';
                outputDiv.scrollTop = outputDiv.scrollHeight;

                // Extract Name and Value from the output
                if (event.data.startsWith('Name:')) {
                    const txtName = document.getElementById('txtName');
                    txtName.textContent = event.data;
                }
                if (event.data.startsWith('Value:')) {
                    const txtValue = document.getElementById('txtValue');
                    txtValue.textContent = event.data;
                }

                // Show the Press Enter button when DNS record needs to be configured
                if (event.data.includes('[DNS TXT Record Details]') ||
                    event.data.includes('Please configure this DNS TXT record')) {
                    document.getElementById('pressEnter').style.display = 'inline-block';
                    waitingForEnter = true;
                }
            };

            eventSource.onerror = function () {
                outputDiv.innerHTML += '\n[ERROR] An error occurred while running the command.\n';
                eventSource.close();
            };
        });

        document.getElementById('stopButton').addEventListener('click', function () {
            if (eventSource) {
                eventSource.close();
                const outputDiv = document.getElementById('output');
                outputDiv.innerHTML += '\n[INFO] Command execution stopped by user.\n';
            }
        });

        document.getElementById('pressEnter').addEventListener('click', function () {
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

        document.getElementById('toggleOutput').addEventListener('click', function() {
            const outputDiv = document.getElementById('output');
            const toggleButton = document.getElementById('toggleOutput');
            
            if (outputDiv.classList.contains('show')) {
                outputDiv.classList.remove('show');
                toggleButton.textContent = 'Show Output';
            } else {
                outputDiv.classList.add('show');
                toggleButton.textContent = 'Hide Output';
            }
        });
    </script>
</body>

</html>