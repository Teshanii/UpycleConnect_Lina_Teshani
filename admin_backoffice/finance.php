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

        <!-- Alerte demandes de remboursement -->
        <div id="alerte-remboursements"></div>

        <!-- KPIs -->
        <div class="row mb-4 g-3">
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Total encaissé</small>
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
                    <small class="text-muted">Revenus prestations</small>
                    <h4 id="kpi-prestations" class="fw-bold" style="color:#6f42c1;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Commissions ventes</small>
                    <h4 id="kpi-commissions" class="fw-bold" style="color:#f4a261;">0.00 €</h4>
                </div>
            </div>
            <div class="col-md">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
                    <small class="text-muted">Total remboursé</small>
                    <h4 id="kpi-rembourses" class="fw-bold text-danger">0.00 €</h4>
                </div>
            </div>
        </div>

        <!-- Graphique CA mensuel -->
        <div class="card p-3 shadow-sm border-0 mb-4">
            <h5 class="mb-3">Chiffre d'affaires par mois</h5>
            <canvas id="graphCA" height="80"></canvas>
        </div>

        <!-- Top ateliers + Top clients -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="mb-3"> Top 5 ateliers les plus rentables</h6>
                    <ul class="list-group list-group-flush" id="top-ateliers"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 shadow-sm border-0 h-100">
                    <h6 class="mb-3"> Top 5 clients</h6>
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
                        <option value="refund_pending">Remboursement demandé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filtre-type" class="form-select form-select-sm" onchange="filtrer()">
                        <option value="">Tous les types</option>
                        <option value="atelier">Atelier</option>
                        <option value="prestation">Prestation</option>
                        <option value="commission">Commission</option>
                        <option value="abonnement">Abonnement</option>
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
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>

        <!-- Demandes de remboursement -->
        <div class="card p-3 shadow-sm border-0">
            <h5 class="mb-3 text-danger">⚠ Demandes de remboursement en attente</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Référence</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps-remboursements"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const API = 'http://localhost:8080/api/transactions';
        const API_USERS = 'http://localhost:8080/api/users';
        const API_EVENTS = 'http://localhost:8080/api/evenements';
        let toutesTransactions = [];
        let tousUsers = [];
        let tousEvenements = [];
        let graphInstance = null;

        async function load() {
            const [resT, resU, resE] = await Promise.all([
                fetch(API),
                fetch(API_USERS),
                fetch(API_EVENTS)
            ]);
            toutesTransactions = await resT.json() || [];
            tousUsers = await resU.json() || [];
            tousEvenements = await resE.json() || [];

            afficher(toutesTransactions);
            mettreAJourKPIs(toutesTransactions);
            afficherRemboursements(toutesTransactions);
            dessinerGraphique(toutesTransactions);
            afficherTopAteliers(toutesTransactions);
            afficherTopClients(toutesTransactions);
            afficherAlerte(toutesTransactions);
        }

        function getNomUser(idUser) {
            const u = tousUsers.find(u => u.id === idUser);
            return u ? u.pre + ' ' + u.nom : 'Inconnu';
        }

        function afficher(liste) {
            const tbody = document.getElementById('corps');
            tbody.innerHTML = '';

            document.getElementById('nb-transactions').innerText = liste.length + ' transaction(s) affichée(s)';

            if (!liste || liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Aucune transaction.</td></tr>';
                return;
            }

            liste.forEach(t => {
                const badge = t.statut === 'succeeded'
                    ? '<span class="badge bg-success">Réussi</span>'
                    : t.statut === 'refunded'
                    ? '<span class="badge bg-warning text-dark">Remboursé</span>'
                    : t.statut === 'refund_pending'
                    ? '<span class="badge bg-danger">Remb. demandé</span>'
                    : `<span class="badge bg-secondary">${t.statut}</span>`;

                const btnRembourser = t.statut === 'succeeded'
                    ? `<button class="btn btn-outline-warning btn-sm" onclick="rembourser(${t.id}, '${t.ref_stripe}')">Rembourser</button>`
                    : '';

                tbody.innerHTML += `<tr>
                    <td>#${t.id}</td>
                    <td>${t.date ? t.date.split('T')[0] : '-'}</td>
                    <td>${getNomUser(t.id_user)}</td>
                    <td><span class="text-capitalize">${t.type || 'atelier'}</span></td>
                    <td><code class="small">${t.ref_stripe || '-'}</code></td>
                    <td><strong>${t.montant.toFixed(2)} €</strong></td>
                    <td>${badge}</td>
                    <td class="text-center">${btnRembourser}</td>
                </tr>`;
            });
        }

        function afficherRemboursements(liste) {
            const tbody = document.getElementById('corps-remboursements');
            const demandes = liste.filter(t => t.statut === 'refund_pending');

            if (!demandes || demandes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Aucune demande en attente.</td></tr>';
                return;
            }

            demandes.forEach(t => {
                tbody.innerHTML += `<tr>
                    <td>#${t.id}</td>
                    <td>${t.date ? t.date.split('T')[0] : '-'}</td>
                    <td>${getNomUser(t.id_user)}</td>
                    <td><strong>${t.montant.toFixed(2)} €</strong></td>
                    <td><code class="small">${t.ref_stripe || '-'}</code></td>
                    <td class="text-center">
                        <button class="btn btn-success btn-sm me-1" onclick="validerRemboursement(${t.id}, '${t.ref_stripe}')">Valider</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refuserRemboursement(${t.id})">Refuser</button>
                    </td>
                </tr>`;
            });
        }

        function afficherAlerte(liste) {
            const nbDemandes = liste.filter(t => t.statut === 'refund_pending').length;
            const div = document.getElementById('alerte-remboursements');

            if (nbDemandes > 0) {
                div.innerHTML = `<div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <span>⚠ <strong>${nbDemandes}</strong> demande(s) de remboursement en attente</span>
                    <a href="#corps-remboursements" class="btn btn-sm btn-warning">Voir</a>
                </div>`;
            } else {
                div.innerHTML = '';
            }
        }

        function mettreAJourKPIs(liste) {
            let total = 0, ateliers = 0, prestations = 0, commissions = 0, rembourses = 0;

            // Mois actuel et précédent pour la comparaison
            const aujourdhui = new Date();
            const moisActuel = aujourdhui.toISOString().substring(0, 7);
            const moisPrecedent = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth() - 1, 1).toISOString().substring(0, 7);

            let totalMoisActuel = 0;
            let totalMoisPrecedent = 0;

            liste.forEach(t => {
                if (t.statut === 'succeeded') {
                    total += t.montant;
                    // Ventilation par type
                    if (t.type === 'prestation') prestations += t.montant;
                    else if (t.type === 'commission') commissions += t.montant;
                    else ateliers += t.montant; // atelier par défaut

                    if (t.date && t.date.startsWith(moisActuel)) totalMoisActuel += t.montant;
                    if (t.date && t.date.startsWith(moisPrecedent)) totalMoisPrecedent += t.montant;
                }
                if (t.statut === 'refunded') rembourses += t.montant;
            });

            document.getElementById('kpi-total').innerText = total.toFixed(2) + ' €';
            document.getElementById('kpi-ateliers').innerText = ateliers.toFixed(2) + ' €';
            document.getElementById('kpi-prestations').innerText = prestations.toFixed(2) + ' €';
            document.getElementById('kpi-commissions').innerText = commissions.toFixed(2) + ' €';
            document.getElementById('kpi-rembourses').innerText = rembourses.toFixed(2) + ' €';

            // Évolution mois actuel vs mois précédent
            const evol = document.getElementById('kpi-total-evol');
            if (totalMoisPrecedent > 0) {
                const pct = ((totalMoisActuel - totalMoisPrecedent) / totalMoisPrecedent * 100).toFixed(1);
                if (pct >= 0) {
                    evol.innerHTML = '<span class="text-success">▲ ' + pct + '% vs mois dernier</span>';
                } else {
                    evol.innerHTML = '<span class="text-danger">▼ ' + pct + '% vs mois dernier</span>';
                }
            } else if (totalMoisActuel > 0) {
                evol.innerHTML = '<span class="text-success">▲ Nouveau ce mois-ci</span>';
            }
        }

        function dessinerGraphique(liste) {
            const parMois = {};
            liste.forEach(t => {
                if (t.statut === 'succeeded' && t.date) {
                    const mois = t.date.substring(0, 7);
                    parMois[mois] = (parMois[mois] || 0) + t.montant;
                }
            });

            const labels = Object.keys(parMois).sort();
            const valeurs = labels.map(m => parMois[m]);

            if (graphInstance) graphInstance.destroy();

            const ctx = document.getElementById('graphCA').getContext('2d');
            graphInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'CA mensuel (€)',
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

        function afficherTopAteliers(liste) {
            const ateliers = {};
            liste.forEach(t => {
                if (t.statut === 'succeeded' && t.type !== 'commission' && t.type !== 'abonnement' && t.type !== 'prestation') {
                    const cle = 'Atelier ' + t.montant.toFixed(2) + '€';
                    ateliers[cle] = (ateliers[cle] || 0) + t.montant;
                }
            });

            const top = Object.entries(ateliers)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            const ul = document.getElementById('top-ateliers');
            ul.innerHTML = '';
            if (top.length === 0) {
                ul.innerHTML = '<li class="list-group-item text-muted">Aucune donnée.</li>';
                return;
            }
            top.forEach(([nom, total], i) => {
                ul.innerHTML += `<li class="list-group-item d-flex justify-content-between">
                    <span><strong>${i + 1}.</strong> ${nom}</span>
                    <strong class="text-primary">${total.toFixed(2)} €</strong>
                </li>`;
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
                ul.innerHTML += `<li class="list-group-item d-flex justify-content-between">
                    <span><strong>${i + 1}.</strong> ${nom}</span>
                    <strong style="color:var(--primary-green);">${total.toFixed(2)} €</strong>
                </li>`;
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
            if (type) res = res.filter(t => (t.type || 'atelier') === type);
            if (debut) res = res.filter(t => t.date && t.date >= debut);
            if (fin) res = res.filter(t => t.date && t.date <= fin + 'T23:59:59');

            afficher(res);
            mettreAJourKPIs(res);
            dessinerGraphique(res);
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

        async function validerRemboursement(id, ref) {
            if (!confirm('Valider ce remboursement ?')) return;
            const res = await fetch('../particulier/remboursement.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ref: ref, id_transaction: id })
            });
            const data = await res.json();
            if (data.ok) { alert('Remboursement validé.'); load(); }
            else alert(data.error || 'Erreur.');
        }

        async function refuserRemboursement(id) {
            if (!confirm('Refuser cette demande ?')) return;
            await fetch(API + '/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ statut: 'succeeded' })
            });
            load();
        }

        function exporterCSV() {
            if (!toutesTransactions || toutesTransactions.length === 0) {
                alert('Aucune transaction à exporter.');
                return;
            }
            let csv = 'ID,Date,Client,Type,Reference,Montant,Statut\n';
            toutesTransactions.forEach(t => {
                csv += `${t.id},${t.date ? t.date.split('T')[0] : ''},${getNomUser(t.id_user)},${t.type || 'atelier'},${t.ref_stripe || ''},${t.montant.toFixed(2)},${t.statut}\n`;
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