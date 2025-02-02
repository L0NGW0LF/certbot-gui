// Attende che il DOM sia completamente caricato
document.addEventListener("DOMContentLoaded", function () {
    // Seleziona gli elementi del DOM
    const toggleButtons = document.querySelectorAll('.toggle-btn'); // Pulsanti toggle
    const webrootDiv = document.getElementById('webroot'); // Sezione Webroot
    const serverDiv = document.getElementById('server'); // Sezione ServerWeb
    const webrootInput = document.getElementById('webroot-input'); // Input Webroot
    const serverSelect = document.getElementById('server-select'); // Menu a tendina ServerWeb
    document.getElementById('server-select').value = "";

    // Imposta lo stato iniziale
    webrootDiv.classList.remove('hidden'); // Mostra la sezione Webroot
    serverDiv.classList.add('hidden'); // Nasconde la sezione ServerWeb
    webrootInput.setAttribute('required', 'true'); // Rende obbligatorio l'input Webroot
    serverSelect.removeAttribute('required'); // Rimuove l'obbligatorietà dal menu a tendina

    // Aggiunge event listeners ai pulsanti toggle
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Rimuove la classe "active" da tutti i pulsanti
            toggleButtons.forEach(btn => btn.classList.remove('active'));

            // Aggiunge la classe "active" al pulsante cliccato
            button.classList.add('active');

            // Mostra/nascondi le sezioni in base al pulsante selezionato
            const target = button.dataset.target;
            if (target === 'webroot') {
                document.getElementById('server-select').value = "";
                webrootDiv.classList.remove('hidden'); // Mostra la sezione Webroot
                serverDiv.classList.add('hidden'); // Nasconde la sezione ServerWeb
                webrootInput.setAttribute('required', 'true'); // Rende obbligatorio l'input Webroot
                serverSelect.removeAttribute('required'); // Rimuove l'obbligatorietà dal menu a tendina
            } else if (target === 'server') {
                document.getElementById('webroot-input').value = "";
                document.getElementById('server-select').value = "apache";
                serverDiv.classList.remove('hidden'); // Mostra la sezione ServerWeb
                webrootDiv.classList.add('hidden'); // Nasconde la sezione Webroot
                serverSelect.setAttribute('required', 'true'); // Rende obbligatorio il menu a tendina
                webrootInput.removeAttribute('required'); // Rimuove l'obbligatorietà dall'input Webroot
            }
        });
    });
});