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

        <!-- Petit texte explicatif pour comprendre les chiffres -->
        <div class="alert alert-light border small mb-4">
            <strong>Comment lire ce tableau de bord :</strong>
            Le <em>Revenu plateforme</em> est l'argent qui revient réellement à UpcycleConnect = revenus des ateliers + abonnements Premium + commissions (7% prélevés sur les ventes entre utilisateurs).
            La <em>Trésorerie due</em> est l'argent des ventes qui appartient aux vendeurs (particuliers et artisans) et que la plateforme doit leur reverser.
        </div>

        <!-- KPIs -->
        <div class="row mb-4 g-3">
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Revenu plateforme</small>
                    <h4 id="kpi-total" class="fw-bold" style="color:var(--primary-green);">0.00 €</h4>
                    <small id="kpi-total-evol" class="text-muted"></small>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                    <small class="text-muted">Revenus ateliers</small>
                    <h4 id="kpi-ateliers" class="fw-bold text-primary">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                    <small class="text-muted">Revenus abonnements</small>
                    <h4 id="kpi-abonnements" class="fw-bold" style="color:#6f42c1;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Commissions (7%)</small>
                    <h4 id="kpi-commissions" class="fw-bold" style="color:#f4a261;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                    <small class="text-muted">Trésorerie due aux users</small>
                    <h4 id="kpi-tresorerie" class="fw-bold" style="color:#198754;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                    <small class="text-muted">Total remboursé</small>
                    <h4 id="kpi-rembourses" class="fw-bold text-danger">0.00 €</h4>
                </div>
            </div>
        </div>

        <!-- Graphique revenu plateforme par mois -->
        <div class="card p-3 shadow-sm border-0 mb-4">
            <h5 class="mb-3">Revenu plateforme par mois</h5>
            <canvas id="graphCA" height="80"></canvas>
        </div>

        <!-- Répartition des revenus + Top clients -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="mb-3">Répartition des revenus de la plateforme</h6>
                    <canvas id="graphRepartition" height="180"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="mb-3">Top 5 clients (montant payé)</h6>
                    <ul class="list-group list-group-flush" id="top-clients"></ul>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-3 shadow-sm border-0 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" id="recherche" class="form-control form-control-sm" placeholder="Rechercher (client, réf)..." oninput="filtrer()">
                </div>
                <div class="col-md-2">
                    <select id="filtre-statut" class="form-select form-select-sm" onchange="filtrer()">
                        <option value="">Tous les statuts</option>
                        <option value="succeeded">Réussi</option>
                        <option value="refunded">Remboursé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filtre-type" class="form-select form-select-sm" onchange="filtrer()">
                        <option value="">Tous les types</option>
                        <option value="atelier">Atelier</option>
                        <option value="abonnement">Abonnement</option>
                        <option value="prestation">Prestation</option>
                        <option value="objet">Objet</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="filtre-debut" class="form-control form-control-sm" onchange="filtrer()">
                </div>
                <div class="col-md-2">
                    <input type="date" id="filtre-fin" class="form-control form-control-sm" onchange="filtrer()">
                </div>
                <div class="col-md-1 text-end">
                    <button class="btn btn-sm btn-success" onclick="exporterCSV()">CSV</button>
                </div>
            </div>
        </div>

        <!-- Tableau transactions -->
        <div class="card p-3 shadow-sm border-0 mb-4">
            <h5 class="mb-3">Toutes les transactions</h5>
            <p class="small text-muted" id="nb-transactions"></p>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Référence Stripe</th>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const API = 'http://localhost:8080/api/transactions';
        const API_USERS = 'http://localhost:8080/api/users';
        let toutesTransactions = [];
        let tousUsers = [];
        let graphCA = null;
        let graphRepartition = null;

        // On charge les transactions et les users en parallèle
        async function load() {
            const [resT, resU] = await Promise.all([
                fetch(API),
                fetch(API_USERS)
            ]);
            toutesTransactions = await resT.json() || [];
            tousUsers = await resU.json() || [];

            afficher(toutesTransactions);
            mettreAJourKPIs(toutesTransactions);
            dessinerGraphiqueMois(toutesTransactions);
            dessinerRepartition(toutesTransactions);
            afficherTopClients(toutesTransactions);
        }

        // On retrouve le nom d'un user à partir de son id
        function getNomUser(idUser) {
            const u = tousUsers.find(u => u.id === idUser);
            return u ? u.pre + ' ' + u.nom : 'Inconnu';
        }

        // On traduit le type technique en libellé lisible
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

            document.getElementById('nb-transactions').innerText = liste.length + ' transaction(s) affichée(s)';

            if (!liste || liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">Aucune transaction.</td></tr>';
                return;
            }

            liste.forEach(t => {
                const badge = t.statut === 'succeeded'
                    ? '<span class="badge bg-success">Réussi</span>'
                    : t.statut === 'refunded'
                    ? '<span class="badge bg-warning text-dark">Remboursé</span>'
                    : '<span class="badge bg-secondary">' + t.statut + '</span>';

                // Le bouton rembourser n'apparaît que pour les paiements réussis
                // et seulement pour les paiements à la plateforme (atelier, abonnement)
                let btnRembourser = '';
                if (t.statut === 'succeeded' && (t.type === 'atelier' || t.type === 'abonnement')) {
                    btnRembourser = '<button class="btn btn-outline-warning btn-sm" onclick="rembourser(' + t.id + ', \'' + t.ref_stripe + '\')">Rembourser</button>';
                }

                // On affiche la commission seulement si elle existe (ventes objet/prestation)
                const commissionAffichee = (t.commission && t.commission > 0)
                    ? '<span style="color:#f4a261;">' + t.commission.toFixed(2) + ' €</span>'
                    : '<span class="text-muted">-</span>';

                tbody.innerHTML += '<tr>' +
                    '<td>#' + t.id + '</td>' +
                    '<td>' + (t.date ? t.date.split('T')[0] : '-') + '</td>' +
                    '<td>' + getNomUser(t.id_user) + '</td>' +
                    '<td>' + libelleType(t.type) + '</td>' +
                    '<td><code class="small">' + (t.ref_stripe || '-') + '</code></td>' +
                    '<td><strong>' + t.montant.toFixed(2) + ' €</strong></td>' +
                    '<td>' + commissionAffichee + '</td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="text-center">' + btnRembourser + '</td>' +
                    '</tr>';
            });
        }

        function mettreAJourKPIs(liste) {
            let ateliers = 0, abonnements = 0, commissions = 0, rembourses = 0;

            // Pour comparer le mois actuel et le mois précédent
            const aujourdhui = new Date();
            const moisActuel = aujourdhui.toISOString().substring(0, 7);
            const moisPrecedent = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 1, 1).toISOString().substring(0, 7);
            let totalMoisActuel = 0, totalMoisPrecedent = 0;

            liste.forEach(t => {
                if (t.statut === 'succeeded') {
                    // On calcule le revenu RÉEL de la plateforme selon le type
                    let revenuLigne = 0;
                    if (t.type === 'atelier') {
                        ateliers += t.montant;
                        revenuLigne = t.montant;
                    } else if (t.type === 'abonnement') {
                        abonnements += t.montant;
                        revenuLigne = t.montant;
                    } else if (t.type === 'objet' || t.type === 'prestation') {
                        // Sur une vente entre utilisateurs, seule la commission (7%) revient à la plateforme
                        commissions += (t.commission || 0);
                        revenuLigne = (t.commission || 0);
                    }

                    if (t.date && t.date.startsWith(moisActuel)) totalMoisActuel += revenuLigne;
                    if (t.date && t.date.startsWith(moisPrecedent)) totalMoisPrecedent += revenuLigne;
                }
                if (t.statut === 'refunded') rembourses += t.montant;
            });

            // Revenu réel de la plateforme = ateliers + abonnements + commissions
            const revenuPlateforme = ateliers + abonnements + commissions;

            document.getElementById('kpi-total').innerText = revenuPlateforme.toFixed(2) + ' €';
            document.getElementById('kpi-ateliers').innerText = ateliers.toFixed(2) + ' €';
            document.getElementById('kpi-abonnements').innerText = abonnements.toFixed(2) + ' €';
            document.getElementById('kpi-commissions').innerText = commissions.toFixed(2) + ' €';
            document.getElementById('kpi-rembourses').innerText = rembourses.toFixed(2) + ' €';

            // Trésorerie due aux users = somme des soldes des particuliers et artisans
            // (on exclut l'admin id_role 1 et le salarié id_role 2 : ils n'ont pas de portefeuille)
            let tresorerie = 0;
            tousUsers.forEach(u => {
                if (u.solde && u.id_role !== 1 && u.id_role !== 2) {
                    tresorerie += parseFloat(u.solde);
                }
            });
            document.getElementById('kpi-tresorerie').innerText = tresorerie.toFixed(2) + ' €';

            // Évolution du revenu plateforme mois actuel vs mois précédent
            const evol = document.getElementById('kpi-total-evol');
            if (totalMoisPrecedent > 0) {
                const pct = ((totalMoisActuel - totalMoisPrecedent) / totalMoisPrecedent * 100).toFixed(1);
                evol.innerHTML = pct >= 0
                    ? '<span class="text-success">▲ ' + pct + '% vs mois dernier</span>'
                    : '<span class="text-danger">▼ ' + pct + '% vs mois dernier</span>';
            } else if (totalMoisActuel > 0) {
                evol.innerHTML = '<span class="text-success">▲ Nouveau ce mois-ci</span>';
            } else {
                evol.innerHTML = '';
            }
        }

        // Graphique : revenu plateforme par mois (barres)
        function dessinerGraphiqueMois(liste) {
            const parMois = {};
            liste.forEach(t => {
                if (t.statut === 'succeeded' && t.date) {
                    const mois = t.date.substring(0, 7);
                    // On ne compte que le revenu réel plateforme
                    let revenuLigne = 0;
                    if (t.type === 'atelier' || t.type === 'abonnement') revenuLigne = t.montant;
                    else if (t.type === 'objet' || t.type === 'prestation') revenuLigne = (t.commission || 0);
                    parMois[mois] = (parMois[mois] || 0) + revenuLigne;
                }
            });

            const labels = Object.keys(parMois).sort();
            const valeurs = labels.map(m => parMois[m]);

            if (graphCA) graphCA.destroy();
            const ctx = document.getElementById('graphCA').getContext('2d');
            graphCA = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenu plateforme (€)',
                        data: valeurs,
                        backgroundColor: 'rgba(45, 106, 79, 0.7)',
                        borderColor: '#2d6a4f',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Graphique : répartition des revenus de la plateforme (camembert)
        function dessinerRepartition(liste) {
            let ateliers = 0, abonnements = 0, commissions = 0;
            liste.forEach(t => {
                if (t.statut === 'succeeded') {
                    if (t.type === 'atelier') ateliers += t.montant;
                    else if (t.type === 'abonnement') abonnements += t.montant;
                    else if (t.type === 'objet' || t.type === 'prestation') commissions += (t.commission || 0);
                }
            });

            if (graphRepartition) graphRepartition.destroy();
            const ctx = document.getElementById('graphRepartition').getContext('2d');
            graphRepartition = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Ateliers', 'Abonnements', 'Commissions'],
                    datasets: [{
                        data: [ateliers, abonnements, commissions],
                        backgroundColor: ['#0d6efd', '#6f42c1', '#f4a261']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        function afficherTopClients(liste) {
            const clients = {};
            liste.forEach(t => {
                if (t.statut === 'succeeded') {
                    const nom = getNomUser(t.id_user);
                    clients[nom] = (clients[nom] || 0) + t.montant;
                }
            });

            const top = Object.entries(clients)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            const ul = document.getElementById('top-clients');
            ul.innerHTML = '';
            if (top.length === 0) {
                ul.innerHTML = '<li class="list-group-item text-muted">Aucune donnée.</li>';
                return;
            }
            top.forEach(([nom, total], i) => {
                ul.innerHTML += '<li class="list-group-item d-flex justify-content-between">' +
                    '<span><strong>' + (i + 1) + '.</strong> ' + nom + '</span>' +
                    '<strong style="color:var(--primary-green);">' + total.toFixed(2) + ' €</strong>' +
                    '</li>';
            });
        }

        function filtrer() {
            const recherche = document.getElementById('recherche').value.toLowerCase();
            const statut = document.getElementById('filtre-statut').value;
            const type = document.getElementById('filtre-type').value;
            const debut = document.getElementById('filtre-debut').value;
            const fin = document.getElementById('filtre-fin').value;

            let res = toutesTransactions;

            if (recherche) {
                res = res.filter(t => {
                    const nom = getNomUser(t.id_user).toLowerCase();
                    const ref = (t.ref_stripe || '').toLowerCase();
                    return nom.includes(recherche) || ref.includes(recherche);
                });
            }
            if (statut) res = res.filter(t => t.statut === statut);
            if (type) res = res.filter(t => t.type === type);
            if (debut) res = res.filter(t => t.date && t.date >= debut);
            if (fin) res = res.filter(t => t.date && t.date <= fin + 'T23:59:59');

            afficher(res);
            mettreAJourKPIs(res);
            dessinerGraphiqueMois(res);
            dessinerRepartition(res);
        }

        // Remboursement direct par l'admin (depuis le tableau)
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

        function exporterCSV() {
            if (!toutesTransactions || toutesTransactions.length === 0) {
                alert('Aucune transaction à exporter.');
                return;
            }
            let csv = 'ID,Date,Client,Type,Reference,Montant,Commission,Statut\n';
            toutesTransactions.forEach(t => {
                csv += t.id + ',' +
                    (t.date ? t.date.split('T')[0] : '') + ',' +
                    getNomUser(t.id_user) + ',' +
                    libelleType(t.type) + ',' +
                    (t.ref_stripe || '') + ',' +
                    t.montant.toFixed(2) + ',' +
                    (t.commission || 0).toFixed(2) + ',' +
                    t.statut + '\n';
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'transactions_upcycleconnect.csv';
            a.click();
            URL.revokeObjectURL(url);
        }

        load();
    </script>
</body>
</html>