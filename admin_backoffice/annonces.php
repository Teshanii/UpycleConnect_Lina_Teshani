<?php 

include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Validation & Modération des Annonces</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Vous devez vérifier le contenu des annonces de dons ou ventes avant validation[cite: 71]. 
            Toute action (Approbation/Refus) déclenche une notification automatique vers le particulier[cite: 103].
        </p>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Annonce</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAnnonce" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détail de l'annonce #<span id="display-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="f-annonce">
                        <input type="hidden" id="edit-id">
                        <div class="mb-3">
                            <label class="small text-muted">Titre de l'objet</label>
                            <input type="text" id="titre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted">Description complète</label>
                            <textarea id="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted">Catégorie</label>
                                <select id="categorie" class="form-select">
                                    <option value="Bois">Bois</option>
                                    <option value="Textile">Textile</option>
                                    <option value="Plastique">Plastique</option>
                                    <option value="Métal">Métal</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted">Type d'offre</label>
                                <select id="type" class="form-select">
                                    <option value="Don">Don gratuit</option>
                                    <option value="Vente">Vente (Commission Upcycle)</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" onclick="del()">Supprimer / Refuser</button>
                    <button type="button" class="btn btn-primary-upcycle" onclick="saveChanges()">Enregistrer & Valider</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = "http://localhost:8080/api/annonces";
        let modalCtrl = new bootstrap.Modal(document.getElementById('modalAnnonce'));

        async function load() {
            const res = await fetch(API);
            const data = await res.json();
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (data) {
                data.forEach(a => {
                    const badgeStatut = a.statut_validation === 1 
                        ? '<span class="badge bg-success">En ligne</span>' 
                        : '<span class="badge bg-warning text-dark">⌛ Attente</span>';

                    tbody.innerHTML += `<tr>
                        <td>#${a.id}</td>
                        <td><strong>${a.titre}</strong></td>
                        <td>${a.auteur}</td>
                        <td>${badgeStatut}</td>
                        <td class="text-center">
                            <button class="btn btn-info btn-sm text-white" onclick="viewDetails(${a.id}, '${a.titre}', '${a.auteur}')">Consulter / Modifier</button>
                        </td>
                    </tr>`;
                });
            }
        }

        // Préparer la modal avec les données (Simulé pour l'exemple étudiant)
        function viewDetails(id, titre, auteur) {
            document.getElementById("edit-id").value = id;
            document.getElementById("display-id").innerText = id;
            document.getElementById("titre").value = titre;
            modalCtrl.show();
        }

        // ENREGISTRER, VALIDER ET NOTIFIER (Réflexion Experte) 
        async function saveChanges() {
            const id = document.getElementById("edit-id").value;
            const payload = {
                id: parseInt(id),
                titre: document.getElementById("titre").value,
                statut_validation: 1 // On valide en même temps que l'on enregistre
            };

            const res = await fetch(`${API}/valider/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                alert("Annonce validée. Notification envoyée au particulier via OneSignal.");
                modalCtrl.hide();
                load();
            }
        }

        async function del() {
            const id = document.getElementById("edit-id").value;
            if (confirm("Refuser cette annonce ? L'utilisateur recevra une notification de refus.")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.ok) {
                    modalCtrl.hide();
                    load();
                }
            }
        }

        load();
    </script>
</body>
</html>