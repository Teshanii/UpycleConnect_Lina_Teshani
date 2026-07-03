<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Modération des projets</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <div class="alert alert-light border small mb-4">
            Vue d'ensemble des projets d'upcycling publiés dans la galerie communautaire. Vous pouvez supprimer un projet inapproprié.
        </div>

        <!-- KPIs -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Total projets</small>
                    <h4 id="kpi-total" class="fw-bold" style="color:var(--primary-green);">0</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Sponsorisés</small>
                    <h4 id="kpi-sponsorises" class="fw-bold" style="color:#f4a261;">0</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                    <small class="text-muted">Terminés</small>
                    <h4 id="kpi-termines" class="fw-bold" style="color:#198754;">0</h4>
                </div>
            </div>
        </div>

        <!-- Filtre par ville -->
        <div class="card p-3 shadow-sm border-0 mb-3">
            <input type="text" id="filtre-ville" class="form-control form-control-sm" style="max-width:300px;"
                   placeholder="Filtrer par ville..." onkeyup="afficher()">
        </div>

        <!-- Tableau projets -->
        <div class="card p-3 shadow-sm border-0 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Créateur</th>
                            <th>Ville</th>
                            <th>Statut</th>
                            <th>Sponsorisé</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = 'http://localhost:8080/api/projets';
        let tousProjets = [];

        async function load() {
            const res = await fetch(API);
            tousProjets = await res.json() || [];
            afficher();
            majKPIs();
        }

        function majKPIs() {
            document.getElementById('kpi-total').innerText = tousProjets.length;
            document.getElementById('kpi-sponsorises').innerText = tousProjets.filter(p => p.est_sponsorise == 1).length;
            document.getElementById('kpi-termines').innerText = tousProjets.filter(p => p.statut === 'termine').length;
        }

        function afficher() {
            const filtreVille = document.getElementById('filtre-ville').value.toLowerCase();
            const tbody = document.getElementById('corps');
            tbody.innerHTML = '';

            let liste = tousProjets;
            if (filtreVille) {
                liste = liste.filter(p => (p.ville || '').toLowerCase().includes(filtreVille));
            }

            if (liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Aucun projet.</td></tr>';
                return;
            }

            liste.forEach(p => {
                const badgeStatut = p.statut === 'termine'
                    ? '<span class="badge bg-success">Terminé</span>'
                    : '<span class="badge bg-secondary">En cours</span>';

                const badgeSponsor = p.est_sponsorise == 1
                    ? '<span class="badge bg-warning text-dark">⭐ Oui</span>'
                    : '<span class="text-muted">-</span>';

                tbody.innerHTML += '<tr>' +
                    '<td>#' + p.id + '</td>' +
                    '<td><strong>' + p.titre + '</strong></td>' +
                    '<td>' + p.createur + '</td>' +
                    '<td>' + (p.ville || '-') + '</td>' +
                    '<td>' + badgeStatut + '</td>' +
                    '<td>' + badgeSponsor + '</td>' +
                    '<td class="text-center">' +
                    '<a href="../projet_detail.php?id=' + p.id + '" target="_blank" class="btn btn-outline-secondary btn-sm me-1">Voir</a>' +
                    '<button class="btn btn-outline-danger btn-sm" onclick="supprimer(' + p.id + ')">Supprimer</button>' +
                    '</td>' +
                    '</tr>';
            });
        }

        async function supprimer(id) {
            if (!confirm('Supprimer ce projet ainsi que ses étapes et participants ?')) return;
            const res = await fetch(API + '/' + id, { method: 'DELETE' });
            if (res.ok) load();
            else alert('Erreur lors de la suppression.');
        }

        load();
    </script>
</body>
</html>