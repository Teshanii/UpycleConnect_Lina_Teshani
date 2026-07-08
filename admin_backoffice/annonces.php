<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Annonces</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-warning btn-sm me-2" onclick="filtrer('attente')"> En attente</button>
            <button class="btn btn-success btn-sm me-2" onclick="filtrer('validee')"> Validées</button>
            <button class="btn btn-danger btn-sm me-2" onclick="filtrer('refusee')"> Refusées</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrer('toutes')">Toutes</button>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal détail annonce -->
    <div class="modal fade" id="modalAnnonce" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Annonce #<span id="display-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Titre :</strong> <span id="modal-titre"></span></p>
                    <p><strong>Auteur :</strong> <span id="modal-auteur"></span></p>
                    <p><strong>Description :</strong> <span id="modal-description"></span></p>
                    <p><strong>Catégorie :</strong> <span id="modal-categorie"></span></p>
                    <p><strong>Type :</strong> <span id="modal-type"></span></p>
                    <p><strong>Prix :</strong> <span id="modal-prix"></span>€</p>
                    <div id="modal-photo"></div>

                    <hr>
                    <!-- Champ motif de refus -->
                    <div id="zone-motif">
                        <label class="form-label text-danger">Motif de refus </label>
                        <textarea id="motif-refus" class="form-control" rows="2" placeholder="Ex: Objet non conforme..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" onclick="supprimer()"> Supprimer</button>
                    <button type="button" class="btn btn-warning" onclick="refuser()"> Refuser</button>
                    <button type="button" class="btn btn-success" onclick="valider()"> Valider</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = "/api/annonces";
        let modalCtrl = new bootstrap.Modal(document.getElementById('modalAnnonce'));
        let toutesLesAnnonces = []; 

        
        async function load() {
            const res = await fetch(API);
            toutesLesAnnonces = await res.json();
            afficher(toutesLesAnnonces);
        }

        
        function afficher(data) {
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucune annonce.</td></tr>';
                return;
            }

            data.forEach(a => {
                let badge;
                if (a.statut_validation === 1) {
                    badge = '<span class="badge bg-success"> Validée</span>';
                } else if (a.statut_validation === 2) {
                    badge = '<span class="badge bg-danger"> Refusée</span>';
                } else {
                    badge = '<span class="badge bg-warning text-dark"> En attente</span>';
                }

                tbody.innerHTML += `<tr>
                    <td>#${a.id}</td>
                    <td><strong>${a.titre}</strong></td>
                    <td>${a.auteur || 'Inconnu'}</td>
                    <td>${badge}</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm text-white" onclick='ouvrir(${JSON.stringify(a)})'>Voir</button>
                    </td>
                </tr>`;
            });
        }

        
        function filtrer(type) {
            if (type === 'toutes') return afficher(toutesLesAnnonces);
            if (type === 'attente') return afficher(toutesLesAnnonces.filter(a => a.statut_validation === 0));
            if (type === 'validee') return afficher(toutesLesAnnonces.filter(a => a.statut_validation === 1));
            if (type === 'refusee') return afficher(toutesLesAnnonces.filter(a => a.statut_validation === 2));
        }

        
        function ouvrir(a) {
            document.getElementById("display-id").innerText = a.id;
            document.getElementById("modal-titre").innerText = a.titre;
            document.getElementById("modal-auteur").innerText = a.auteur || 'Inconnu';
            document.getElementById("modal-description").innerText = a.description || '-';
            document.getElementById("modal-categorie").innerText = a.categorie || '-';
            document.getElementById("modal-type").innerText = a.type_offre || '-';
            document.getElementById("modal-prix").innerText = a.prix || '0';
            document.getElementById("motif-refus").value = '';

            
        
            if (a.photo) {
                document.getElementById("modal-photo").innerHTML = `<img src="/${a.photo}" style="max-width:100%; border-radius:8px;" class="mt-2">`;
            } else {
                document.getElementById("modal-photo").innerHTML = '<p class="text-muted small">Pas de photo.</p>';
            }

            modalCtrl.show();
        }

        // Valider une annonce
        async function valider() {
            const id = document.getElementById("display-id").innerText;
            if (confirm("Valider cette annonce ?")) {
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
            if (!motif) {
                alert("Veuillez entrer un motif de refus.");
                return;
            }
            if (confirm("Refuser cette annonce ?")) {
                await fetch(`${API}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ statut_validation: 2, motif_refus: motif })
                });
                modalCtrl.hide();
                load();
            }
        }

        
        async function supprimer() {
            const id = document.getElementById("display-id").innerText;
            if (confirm("Supprimer définitivement cette annonce ?")) {
                await fetch(`${API}/${id}`, { method: 'DELETE' });
                modalCtrl.hide();
                load();
            }
        }

        load();
    </script>
</body>
</html>