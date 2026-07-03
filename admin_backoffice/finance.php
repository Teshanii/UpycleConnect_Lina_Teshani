<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Finance & Transactions</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <div class="alert alert-light border small mb-4">
            <strong>Revenu plateforme</strong> = ateliers + abonnements + commissions sur les ventes.
            <strong>Trésorerie due</strong> = argent des ventes à reverser aux vendeurs.
        </div>

        <!-- KPIs -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Revenu plateforme</small>
                    <h4 id="kpi-total" class="fw-bold" style="color:var(--primary-green);">0.00 €</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Commissions</small>
                    <h4 id="kpi-commissions" class="fw-bold" style="color:#f4a261;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                    <small class="text-muted">Trésorerie due aux users</small>
                    <h4 id="kpi-tresorerie" class="fw-bold" style="color:#198754;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                    <small class="text-muted">Total remboursé</small>
                    <h4 id="kpi-rembourses" class="fw-bold text-danger">0.00 €</h4>
                </div>
            </div>
        </div>

        <!-- Filtres simples -->
        <div class="card p-3 shadow-sm border-0 mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <select id="filtre-statut" class="form-select form-select-sm" onchange="filtrer()">
                        <option value="">Tous les statuts</option>
                        <option value="succeeded">Réussi</option>
                        <option value="refunded">Remboursé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filtre-type" class="form-select form-select-sm" onchange="filtrer()">
                        <option value="">Tous les types</option>
                        <option value="atelier">Atelier</option>
                        <option value="abonnement">Abonnement</option>
                        <option value="prestation">Prestation</option>
                        <option value="objet">Objet</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tableau transactions -->
        <div class="card p-3 shadow-sm border-0 mb-4">
            <h5 class="mb-3">Transactions</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Commission</th>
                            <th>Statut</th>
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
        const API = 'http://localhost:8080/api/transactions';
        const API_USERS = 'http://localhost:8080/api/users';
        let toutesTransactions = [];
        let tousUsers = [];

        async function load() {
            const [resT, resU] = await Promise.all([fetch(API), fetch(API_USERS)]);
            toutesTransactions = await resT.json() || [];
            tousUsers = await resU.json() || [];
            afficher(toutesTransactions);
            mettreAJourKPIs(toutesTransactions);
        }

        function getNomUser(idUser) {
            const u = tousUsers.find(u => u.id === idUser);
            return u ? u.pre + ' ' + u.nom : 'Inconnu';
        }

        function libelleType(type) {
            if (type === 'atelier') return 'Atelier';
            if (type === 'abonnement') return 'Abonnement';
            if (type === 'prestation') return 'Prestation';
            if (type === 'objet') return 'Objet';
            return 'Paiement';
        }

        function afficher(liste) {
            const tbody = document.getElementById('corps');
            tbody.innerHTML = '';

            if (!liste || liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Aucune transaction.</td></tr>';
                return;
            }

            liste.forEach(t => {
                const badge = t.statut === 'succeeded'
                    ? '<span class="badge bg-success">Réussi</span>'
                    : t.statut === 'refunded'
                    ? '<span class="badge bg-warning text-dark">Remboursé</span>'
                    : '<span class="badge bg-secondary">' + t.statut + '</span>';

                let btnRembourser = '';
                if (t.statut === 'succeeded' && (t.type === 'atelier' || t.type === 'abonnement')) {
                    btnRembourser = '<button class="btn btn-outline-warning btn-sm" onclick="rembourser(' + t.id + ', \'' + t.ref_stripe + '\')">Rembourser</button>';
                }

                const commissionAffichee = (t.commission && t.commission > 0)
                    ? '<span style="color:#f4a261;">' + t.commission.toFixed(2) + ' €</span>'
                    : '<span class="text-muted">-</span>';

                tbody.innerHTML += '<tr>' +
                    '<td>#' + t.id + '</td>' +
                    '<td>' + (t.date ? t.date.split('T')[0] : '-') + '</td>' +
                    '<td>' + getNomUser(t.id_user) + '</td>' +
                    '<td>' + libelleType(t.type) + '</td>' +
                    '<td><strong>' + t.montant.toFixed(2) + ' €</strong></td>' +
                    '<td>' + commissionAffichee + '</td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="text-center">' + btnRembourser + '</td>' +
                    '</tr>';
            });
        }

        function mettreAJourKPIs(liste) {
            let ateliers = 0, abonnements = 0, commissions = 0, rembourses = 0;

            liste.forEach(t => {
                if (t.statut === 'succeeded') {
                    if (t.type === 'atelier') ateliers += t.montant;
                    else if (t.type === 'abonnement') abonnements += t.montant;
                    else if (t.type === 'objet' || t.type === 'prestation') commissions += (t.commission || 0);
                }
                if (t.statut === 'refunded') rembourses += t.montant;
            });

            const revenuPlateforme = ateliers + abonnements + commissions;

            document.getElementById('kpi-total').innerText = revenuPlateforme.toFixed(2) + ' €';
            document.getElementById('kpi-commissions').innerText = commissions.toFixed(2) + ' €';
            document.getElementById('kpi-rembourses').innerText = rembourses.toFixed(2) + ' €';

            // Trésorerie due = somme des soldes des particuliers et artisans
            let tresorerie = 0;
            tousUsers.forEach(u => {
                if (u.solde && u.id_role !== 1 && u.id_role !== 2) {
                    tresorerie += parseFloat(u.solde);
                }
            });
            document.getElementById('kpi-tresorerie').innerText = tresorerie.toFixed(2) + ' €';
        }

        function filtrer() {
            const statut = document.getElementById('filtre-statut').value;
            const type = document.getElementById('filtre-type').value;

            let res = toutesTransactions;
            if (statut) res = res.filter(t => t.statut === statut);
            if (type) res = res.filter(t => t.type === type);

            afficher(res);
            mettreAJourKPIs(res);
        }

        async function rembourser(id, ref) {
            if (!confirm('Rembourser cette transaction via Stripe ?')) return;
            const res = await fetch('../particulier/remboursement.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ref: ref, id_transaction: id })
            });
            const data = await res.json();
            if (data.ok) { alert('Remboursement effectué.'); load(); }
            else alert(data.error || 'Erreur.');
        }

        load();
    </script>
</body>
</html>