<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Prestations</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-warning btn-sm me-2" onclick="filtrer('attente')">En attente</button>
            <button class="btn btn-success btn-sm me-2" onclick="filtrer('valide')">Validées</button>
            <button class="btn btn-danger btn-sm me-2" onclick="filtrer('refuse')">Refusées</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrer('toutes')">Toutes</button>
        </div>

        <!-- Tableau -->
        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Créateur</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>

        <!-- Modal détail -->
        <div class="modal fade" id="modalPrestation" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Prestation #<span id="display-id"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Nom :</strong> <span id="modal-nom"></span></p>
                        <p><strong>Créateur :</strong> <span id="modal-createur"></span></p>
                        <p><strong>Prix :</strong> <span id="modal-prix"></span> €</p>
                        <p><strong>Description :</strong> <span id="modal-desc"></span></p>
                        <div id="modal-photo"></div>
                        <hr>
                        <label class="form-label text-danger">Motif de refus</label>
                        <textarea id="motif-refus" class="form-control" rows="2" placeholder="Ex: Prix trop élevé..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" onclick="supprimer()">Supprimer</button>
                        <button type="button" class="btn btn-warning" onclick="refuser()">Refuser</button>
                        <button type="button" class="btn btn-success" onclick="valider()">Valider</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = "http://localhost:8080/api/prestations";
        let toutesPrestations = [];
        let modalCtrl = new bootstrap.Modal(document.getElementById('modalPrestation'));

        async function load() {
            const res = await fetch(API);
            toutesPrestations = await res.json() || [];
            afficher(toutesPrestations);
        }

        function afficher(liste) {
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (!liste || liste.length === 0) {
                tbody.innerHTML = "<tr><td colspan='6' class='text-center text-muted py-3'>Aucune prestation.</td></tr>";
                return;
            }

            liste.forEach(p => {
                let badge;
                if (p.statut_validation === 1) badge = '<span class="badge bg-success">Validée</span>';
                else if (p.statut_validation === 2) badge = '<span class="badge bg-danger">Refusée</span>';
                else badge = '<span class="badge bg-warning text-dark">En attente</span>';

                tbody.innerHTML += `<tr>
                    <td>#${p.id}</td>
                    <td><strong>${p.nom}</strong></td>
                    <td>${p.createur || 'Inconnu'}</td>
                    <td>${p.prix.toFixed(2)} €</td>
                    <td>${badge}</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm text-white" onclick='ouvrir(${JSON.stringify(p)})'>Voir</button>
                    </td>
                </tr>`;
            });
        }

        function filtrer(type) {
            if (type === 'toutes') return afficher(toutesPrestations);
            if (type === 'attente') return afficher(toutesPrestations.filter(p => p.statut_validation === 0));
            if (type === 'valide') return afficher(toutesPrestations.filter(p => p.statut_validation === 1));
            if (type === 'refuse') return afficher(toutesPrestations.filter(p => p.statut_validation === 2));
        }

        function ouvrir(p) {
            document.getElementById("display-id").innerText = p.id;
            document.getElementById("modal-nom").innerText = p.nom;
            document.getElementById("modal-createur").innerText = p.createur || 'Inconnu';
            document.getElementById("modal-prix").innerText = p.prix.toFixed(2);
            document.getElementById("modal-desc").innerText = p.desc || '-';
            document.getElementById("motif-refus").value = '';

            if (p.photo) {
                document.getElementById("modal-photo").innerHTML = `<img src="http://localhost/${p.photo}" style="max-width:100%; border-radius:8px;" class="mt-2">`;
            } else {
                document.getElementById("modal-photo").innerHTML = '<p class="text-muted small">Pas de photo.</p>';
            }

            modalCtrl.show();
        }

        async function valider() {
            const id = document.getElementById("display-id").innerText;
            if (confirm("Valider cette prestation ?")) {
                await fetch(`${API}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ statut_validation: 1 })
                });
                modalCtrl.hide();
                load();
            }
        }

        async function refuser() {
            const id = document.getElementById("display-id").innerText;
            const motif = document.getElementById("motif-refus").value.trim();
            if (!motif) { alert("Veuillez entrer un motif de refus."); return; }

            if (confirm("Refuser cette prestation ?")) {
                await fetch(`${API}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ motif_refus: motif })
                });
                modalCtrl.hide();
                load();
            }
        }

        async function supprimer() {
            const id = document.getElementById("display-id").innerText;
            if (confirm("Supprimer définitivement cette prestation ?")) {
                await fetch(`${API}/${id}`, { method: 'DELETE' });
                modalCtrl.hide();
                load();
            }
        }

        load();
    </script>
</body>
</html>