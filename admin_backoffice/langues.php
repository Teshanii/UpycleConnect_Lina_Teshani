<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Internationalisation (I18n)</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            [cite_start]Conformément aux exigences de la Mission 1, cette interface permet de rendre la plateforme multilingue dynamiquement[cite: 113]. 
            [cite_start]Une fois une langue activée ici, le système permet de traduire les catégories, les services et les conseils[cite: 89, 102].
        </p>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 class="mb-3">Activer une nouvelle langue</h5>
            <form id="f-lang">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="small text-muted">Code ISO (2 lettres)</label>
                        <input type="text" id="code" class="form-control" placeholder="ex: es, en, de" maxlength="2" required style="text-transform: lowercase;">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Nom de la langue (Libellé)</label>
                        <input type="text" id="nom" class="form-control" placeholder="ex: Espagnol, Anglais..." required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-upcycle w-100">Ajouter au système</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <h5 class="mb-3">Langues configurées dans le SI</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 150px;">Code ISO</th>
                            <th>Libellé complet</th>
                            <th class="text-center">Statut système</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/langues";

        // CHARGEMENT DES LANGUES (Appel API Go)
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    data.forEach(l => {
                        tbody.innerHTML += `<tr>
                            <td><span class="badge bg-secondary font-monospace p-2">${l.code.toUpperCase()}</span></td>
                            <td><strong>${l.nom}</strong></td>
                            <td class="text-center"><span class="badge bg-success">● Actif</span></td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger btn-sm" onclick="del(${l.id})">Désactiver</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='4' class='text-center py-4'>Aucune langue additionnelle configurée.</td></tr>";
                }
            } catch (err) {
                console.error("Erreur technique de liaison API:", err);
            }
        }

        // AJOUT D'UNE LANGUE (POST /api/langues)
        document.getElementById("f-lang").onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                code: document.getElementById("code").value.toLowerCase(),
                nom: document.getElementById("nom").value
            };

            const response = await fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            if(response.ok) {
                e.target.reset();
                load();
            } else {
                alert("Erreur : Ce code ISO est déjà enregistré.");
            }
        };

        // SUPPRESSION (DELETE /api/langues/{id})
        async function del(id) {
            if(confirm("Désactiver cette langue ? Les traductions associées resteront en base mais ne seront plus affichées.")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) load();
            }
        }

        load();
    </script>
</body>
</html>