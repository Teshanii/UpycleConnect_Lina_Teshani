<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Suivi des Revenus & Transactions</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Suivez ici l'intégralité de l'activité financière. [cite_start]Les revenus proviennent des abonnements premium, des commissions sur ventes et des inscriptions aux ateliers[cite: 291, 301, 307].
        </p>

        <div class="row mb-4 g-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white p-3 border-0 shadow-sm">
                    <small>Total Collecté (Stripe)</small>
                    <h2 id="total-rev">0.00 €</h2>
                    [cite_start]<p class="small mb-0">Revenus validés et encaissés [cite: 108]</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white p-3 border-0 shadow-sm">
                    <small>Abonnements & Commissions</small>
                    <h3 id="total-comm">0.00 €</h3>
                    [cite_start]<p class="small mb-0">Particuliers et Professionnels [cite: 291, 301]</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white p-3 border-0 shadow-sm">
                    <small>Ventes Ateliers / Formations</small>
                    <h3 id="total-form">0.00 €</h3>
                    [cite_start]<p class="small mb-0">Inscriptions individuelles [cite: 307, 308]</p>
                </div>
            </div>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <h5 class="mb-3">Détail des flux via API Stripe</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Référence Transaction</th>
                            <th>Type de service</th>
                            <th>Montant TTC (20%)</th> <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/transactions";

        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";
                
                let totalEncaisse = 0;
                let totalAteliers = 0;
                let totalCommissions = 0;

                if (data && data.length > 0) {
                    data.forEach(t => {
                        const isSuccess = t.statut === 'succeeded' || t.statut === 'valide';
                        const badge = isSuccess 
                            ? '<span class="badge bg-success">Réussi</span>' 
                            : `<span class="badge bg-warning text-dark">${t.statut}</span>`;

                        if(isSuccess) {
                            totalEncaisse += t.montant;
                            // Réflexion experte : On simule la ventilation par type pour la démo
                            if(t.type === 'formation') totalAteliers += t.montant;
                            else totalCommissions += t.montant;
                        }

                        tbody.innerHTML += `<tr>
                            <td>${t.date.split('T')[0]}</td>
                            <td><code class="small">${t.ref_stripe}</code></td>
                            <td><span class="text-capitalize">${t.type || 'Abonnement'}</span></td>
                            <td><strong>${t.montant.toFixed(2)} €</strong></td>
                            <td>${badge}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='5' class='text-center py-4'>Aucun flux financier détecté.</td></tr>";
                }

                // Mise à jour des compteurs
                document.getElementById('total-rev').innerText = totalEncaisse.toFixed(2) + " €";
                document.getElementById('total-comm').innerText = totalCommissions.toFixed(2) + " €";
                document.getElementById('total-form').innerText = totalAteliers.toFixed(2) + " €";

            } catch (err) {
                console.error("Erreur technique finance:", err);
            }
        }
        
        load();
    </script>
</body>
</html>