<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Supervision du Forum</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-success btn-sm me-2" onclick="filtrer('visible')">Visibles</button>
            <button class="btn btn-warning btn-sm me-2" onclick="filtrer('modere')">Masqués</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrer('tous')">Tous</button>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 140px;">Auteur</th>
                            <th style="width: 100px;">Type</th>
                            <th>Contenu</th>
                            <th style="width: 140px;">Date</th>
                            <th style="width: 140px;">Visibilité</th>
                            <th class="text-center" style="width: 230px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/messages";
        let tousMessages = [];

        async function load() {
            const res = await fetch(API);
            tousMessages = await res.json() || [];
            afficher(tousMessages);
        }

        function afficher(liste) {
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (!liste || liste.length === 0) {
                tbody.innerHTML = "<tr><td colspan='7' class='text-center text-muted py-3'>Aucun message.</td></tr>";
                return;
            }

            liste.forEach(m => {
                const isModere = m.est_modere === 1;
                const estReponse = m.id_message_parent > 0;

                const badgeStatut = isModere 
                    ? '<span class="badge bg-danger">Masqué</span>' 
                    : '<span class="badge bg-success">Visible</span>';

                const badgeType = estReponse
                    ? `<span class="badge bg-info text-dark">Réponse à #${m.id_message_parent}</span>`
                    : '<span class="badge bg-light text-dark border">Message</span>';

                const btnToggle = isModere 
                    ? `<button class="btn btn-outline-success btn-sm me-1" onclick="changerStatut(${m.id}, 0)">Réactiver</button>` 
                    : `<button class="btn btn-warning btn-sm me-1" onclick="changerStatut(${m.id}, 1)">Masquer</button>`;

                tbody.innerHTML += `<tr>
                    <td>#${m.id}</td>
                    <td><strong>${m.auteur}</strong></td>
                    <td>${badgeType}</td>
                    <td class="text-wrap fst-italic text-muted">"${m.contenu}"</td>
                    <td class="small">${m.date ? m.date.replace('T', ' ').substring(0, 16) : '-'}</td>
                    <td>${badgeStatut}</td>
                    <td class="text-center">
                        ${btnToggle}
                        <button class="btn btn-danger btn-sm" onclick="supprimerDefinitif(${m.id})">Purger</button>
                    </td>
                </tr>`;
            });
        }

        function filtrer(type) {
            if (type === 'tous') return afficher(tousMessages);
            if (type === 'visible') return afficher(tousMessages.filter(m => m.est_modere === 0));
            if (type === 'modere') return afficher(tousMessages.filter(m => m.est_modere === 1));
        }

        async function changerStatut(id, nouvelEtat) {
            const action = nouvelEtat === 1 ? "masquer" : "réactiver";
            if (confirm(`Voulez-vous ${action} ce message ?`)) {
                const res = await fetch(`${API}/${id}`, { 
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ est_modere: nouvelEtat })
                });
                if (res.ok) load();
            }
        }

        async function supprimerDefinitif(id) {
            if (confirm("Supprimer définitivement ce message (et ses réponses si c'en est un principal) ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.ok) load();
            }
        }

        load();
    </script>
</body>
</html>