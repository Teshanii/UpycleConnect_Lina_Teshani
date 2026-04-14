<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Supervision du Forum</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            [cite_start]En tant qu'Administrateur Général, vous supervisez l'intégralité des échanges[cite: 94]. 
            [cite_start]Vous pouvez contrôler le travail de modération des salariés, restaurer des messages ou purger la base de données[cite: 92, 95].
        </p>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 150px;">Auteur</th>
                            <th>Contenu du message</th>
                            <th style="width: 180px;">Visibilité Publique</th>
                            <th class="text-center" style="width: 250px;">Actions de supervision</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/messages";

        // CHARGEMENT DES MESSAGES (Appel API Go)
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    data.forEach(m => {
                        const isModere = m.est_modere === 1;
                        const badgeStatut = isModere 
                            ? '<span class="badge bg-danger">Masqué (Par Modération)</span>' 
                            : '<span class="badge bg-success">Visible sur le Forum</span>';
                        
                        // RÉFLEXION EXPERTE : L'Admin peut masquer OU restaurer (contrairement au salarié)
                        const btnToggle = isModere 
                            ? `<button class="btn btn-outline-success btn-sm me-1" onclick="changerStatut(${m.id}, 0)">Réactiver</button>` 
                            : `<button class="btn btn-warning btn-sm me-1" onclick="changerStatut(${m.id}, 1)">Masquer</button>`;

                        tbody.innerHTML += `<tr>
                            <td>#${m.id}</td>
                            <td><strong>${m.auteur}</strong></td>
                            <td class="text-wrap fst-italic text-muted">"${m.contenu}"</td>
                            <td>${badgeStatut}</td>
                            <td class="text-center">
                                ${btnToggle}
                                <button class="btn btn-danger btn-sm" onclick="supprimerDefinitif(${m.id})">Purger</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='5' class='text-center py-4'>Aucun message à superviser pour le moment.</td></tr>";
                }
            } catch (error) {
                console.error("Erreur technique API:", error);
            }
        }

        // ACTION DE MODÉRATION / RESTAURATION (Appel PUT /api/messages/{id})
        async function changerStatut(id, nouvelEtat) {
            const action = nouvelEtat === 1 ? "masquer" : "réactiver";
            if(confirm(`Voulez-vous ${action} ce message ?`)) {
                // On envoie le nouvel état (0 ou 1) à l'API Go
                const res = await fetch(`${API}/${id}`, { 
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ est_modere: nouvelEtat })
                });
                if(res.ok) load();
            }
        }

        // SUPPRESSION TOTALE (Appel DELETE /api/messages/{id})
        async function supprimerDefinitif(id) {
            if(confirm("Action critique : Supprimer définitivement ce message de la base de données ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) load();
            }
        }

        load();
    </script>
</body>
</html>