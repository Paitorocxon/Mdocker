document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('content'); // Rechte Seite (Renderer)
    const navTree = document.getElementById('navTree'); // Navigation

    if (!content || !navTree) {
        console.error("Missing elements in DOM: 'content' or 'navTree'.");
        return;
    }

    // Dynamische Einrückung basierend auf data-depth
    document.querySelectorAll('[data-depth]').forEach((el) => {
        const depth = el.getAttribute('data-depth');
        el.style.setProperty('--depth', depth);
    });

    // Dynamisches Laden von Dateien bei Klick auf Links
    navTree.addEventListener('click', (e) => {
        const target = e.target;

        // Prüfen, ob es sich um einen Link handelt
        if (target.tagName === 'A') {
            e.preventDefault();

            const fileUrl = target.getAttribute('href'); // Datei-URL

            // Asynchroner API-Aufruf
            fetch(fileUrl + '&xhr=true') // XHR-Endpunkt
                .then((response) => {
                    if (!response.ok) throw new Error(`Failed to load file: ${fileUrl}`);
                    return response.text();
                })
                .then((data) => {
                    content.innerHTML = data; // Inhalt aktualisieren
                    highlightCodeBlocks(); // Code-Highlighting aufrufen
                    history.pushState(null, '', fileUrl); // URL ändern
                })
                .catch((err) => {
                    console.error(err);
                    content.innerHTML = `<p style="color:red;">Error loading file: ${err.message}</p>`;
                });
        }
    });

    // Event-Listener für das Aufklappen von Ordnern
    navTree.addEventListener('click', (e) => {
        const target = e.target;

        // Prüfen, ob es sich um einen Ordner handelt (folder-Klasse)
        if (target.classList.contains('folder')) {
            e.preventDefault();

            const childList = target.querySelector('ul');

            // Umschalten des Sichtbarkeitsstatus von Unterordnern
            if (childList) {
                childList.style.display = childList.style.display === 'none' ? 'block' : 'none';
            }
        }
    });

    // Unterstützt Browser Vor-/Zurück-Buttons
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        const file = params.get('file') || 'index.md';
        const fileUrl = `?file=${file}`;

        fetch(fileUrl + '&xhr=true')
            .then((response) => {
                if (!response.ok) throw new Error(`Failed to load file: ${fileUrl}`);
                return response.text();
            })
            .then((data) => {
                content.innerHTML = data; // Inhalt neu laden
                highlightCodeBlocks(); // Code-Highlighting aufrufen
            })
            .catch((err) => {
                console.error(err);
                content.innerHTML = `<p style="color:red;">Error loading file: ${err.message}</p>`;
            });
    });

    // Highlight.js aufrufen
    function highlightCodeBlocks() {
        if (typeof hljs !== 'undefined') {
            hljs.highlightAll();
        } else {
            console.warn("Highlight.js ist nicht geladen.");
        }
    }
});
