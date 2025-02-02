document.addEventListener("DOMContentLoaded", function () {
    // Seleziona gli elementi del DOM
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    const webrootDiv = document.getElementById('webroot');
    const serverDiv = document.getElementById('server');
    const webrootInput = document.getElementById('webroot-input');
    const serverSelect = document.getElementById('server-select');
    document.getElementById('server-select').value = "";

    // Imposta lo stato iniziale
    webrootDiv.classList.remove('hidden');
    serverDiv.classList.add('hidden');
    webrootInput.setAttribute('required', 'true');
    serverSelect.removeAttribute('required');

    // Aggiunge event listeners ai pulsanti toggle
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            toggleButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Mostra/nascondi le sezioni in base al pulsante selezionato
            const target = button.dataset.target;
            if (target === 'webroot') {
                document.getElementById('server-select').value = "";
                webrootDiv.classList.remove('hidden');
                serverDiv.classList.add('hidden');
                webrootInput.setAttribute('required', 'true');
                serverSelect.removeAttribute('required');
            } else if (target === 'server') {
                document.getElementById('webroot-input').value = "";
                document.getElementById('server-select').value = "apache";
                serverDiv.classList.remove('hidden');
                webrootDiv.classList.add('hidden');
                serverSelect.setAttribute('required', 'true');
                webrootInput.removeAttribute('required');
            }
        });
    });
});