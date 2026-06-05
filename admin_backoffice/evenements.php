<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Événements & Ateliers</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Filtres -->
        <div class="mb-3">
            <button class="btn btn-warning btn-sm me-2" onclick="filtrer('attente')">En attente</button>
            <button class="btn btn-success btn-sm me-2" onclick="filtrer('valide')">Validés</button>
            <button class="btn btn-danger btn-sm me-2" onclick="filtrer('refuse')">Refusés</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrer('tous')">Tous</button>
        </div>

        <!-- Formulaire création/modification -->
        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Planifier un nouvel atelier</h5>
            <form id="f-ev">
                <input type="hidden" id="edit-id">
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="small text-muted">Titre</label>
                        <input type="text" id="titre" class="form-control" placeholder="Ex: Initiation Upcycling Bois" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Date et Heure</label>
                        <input type="datetime-local" id="date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Prix (€)</label>
                        <input type="number" step="0.01" id="prix" class="form-control" required>
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">Places</label>
                        <input type="number" id="place" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Animateur</label>
                        <select id="anim" class="form-select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-upcycle">Enregistrer</button>
                    <button type="button" class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>

        <!-- Tableau -->
        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Atelier</th>
                            <th>Date</th>
                            <th>Tarif</th>
                            <th>Inscrits</th>
                            <th>Animateur</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>

        <!-- Modal refus -->
        <div class="modal fade" id="modalRefus" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Refuser l'atelier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="refus-id">
                        <label class="form-label text-danger">Motif du refus (obligatoire)</label>
                        <textarea id="motif-refus" class="form-control" rows="3" placeholder="Ex: Contenu non conforme..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" onclick="confirmerRefus()">Refuser</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal liste des inscrits -->
        <div class="modal fade" id="modalInscrits" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Inscrits — <span id="inscrits-titre"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <input type="text" id="recherche-inscrits" class="form-control" placeholder="Rechercher un inscrit..." oninput="filtrerInscrits()">
                        </div>
                        <table class="table table-hover" id="table-inscrits">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody id="corps-inscrits"></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-success" onclick="exporterCSV()">Exporter CSV</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = "http://localhost:8080/api/evenements";
        let tousLesEvents = [];
        let tousLesInscrits = [];
        let titreEventEnCours = '';
        let modalRefus = new bootstrap.Modal(document.getElementById('modalRefus'));
        let modalInscrits = new bootstrap.Modal(document.getElementById('modalInscrits'));

        async function load() {
            const res = await fetch(API);
            tousLesEvents = await res.json();
            afficher(tousLesEvents);
        }

        function afficher(data) {
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (!data || data.length === 0) {
                tbody.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>Aucun événement.</td></tr>";
                return;
            }

            data.forEach(e => {
                const badge = e.statut_validation === 1
                    ? '<span class="badge bg-success">Valide</span>'
                    : e.statut_validation === 2
                    ? `<span class="badge bg-danger">Refuse</span>${e.motif_refus ? '<br><small class="text-danger">' + e.motif_refus + '</small>' : ''}`
                    : '<span class="badge bg-warning text-dark">En attente</span>';

                const btnValider = e.statut_validation !== 1
                    ? `<button class="btn btn-success btn-sm me-1" onclick="valider(${e.id})">Approuver</button>`
                    : "";

                const inscrits = `${e.nb_inscrits} / ${e.place}`;

                tbody.innerHTML += `
                    <tr>
                        <td>#${e.id}</td>
                        <td><strong>${e.titre}</strong></td>
                        <td>${e.date ? e.date.replace("T", " ") : '-'}</td>
                        <td>${e.prix}€</td>
                        <td><span class="badge bg-info text-dark">${inscrits}</span></td>
                        <td>${e.anim}</td>
                        <td>${badge}</td>
                        <td class="text-center">
                            ${btnValider}
                            <button class="btn btn-info btn-sm me-1 text-white" onclick="voirInscrits(${e.id}, '${e.titre}')">Inscrits</button>
                            <button class="btn btn-danger btn-sm me-1" onclick="ouvrirRefus(${e.id})">Refuser</button>
                            <button class="btn btn-warning btn-sm me-1" onclick="edit(${e.id},'${e.titre}','${e.date}',${e.prix},${e.place},${e.id_anim})">Editer</button>
                            <button class="btn btn-outline-danger btn-sm" onclick="del(${e.id})">Supprimer</button>
                        </td>
                    </tr>`;
            });
        }

        function filtrer(type) {
            if (type === 'tous') return afficher(tousLesEvents);
            if (type === 'attente') return afficher(tousLesEvents.filter(e => e.statut_validation === 0));
            if (type === 'valide') return afficher(tousLesEvents.filter(e => e.statut_validation === 1));
            if (type === 'refuse') return afficher(tousLesEvents.filter(e => e.statut_validation === 2));
        }

        async function valider(id) {
            if (confirm("Valider cet atelier ? Il sera visible pour les particuliers.")) {
                const res = await fetch(`${API}/valider/${id}`, { method: 'PUT' });
                if (res.ok) load();
            }
        }

        function ouvrirRefus(id) {
            document.getElementById('refus-id').value = id;
            document.getElementById('motif-refus').value = '';
            modalRefus.show();
        }

        async function confirmerRefus() {
            const id = document.getElementById('refus-id').value;
            const motif = document.getElementById('motif-refus').value.trim();
            if (!motif) {
                alert("Veuillez entrer un motif de refus.");
                return;
            }
            if (confirm("Confirmer le refus de cet atelier ?")) {
                await fetch(`${API}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ motif_refus: motif })
                });
                modalRefus.hide();
                load();
            }
        }

        // Voir la liste des inscrits
        async function voirInscrits(idEvent, titre) {
            titreEventEnCours = titre;
            document.getElementById('inscrits-titre').innerText = titre;
            document.getElementById('recherche-inscrits').value = '';

            const res = await fetch(`http://localhost:8080/api/inscrits-evenement/${idEvent}`);
            tousLesInscrits = await res.json();
            afficherInscrits(tousLesInscrits);
            modalInscrits.show();
        }

        function afficherInscrits(liste) {
            const tbody = document.getElementById('corps-inscrits');
            tbody.innerHTML = '';

            if (!liste || liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Aucun inscrit.</td></tr>';
                return;
            }

            liste.forEach(u => {
                tbody.innerHTML += `<tr>
                    <td>${u.nom}</td>
                    <td>${u.pre}</td>
                    <td>${u.mail}</td>
                </tr>`;
            });
        }

        // Filtrer les inscrits par nom
        function filtrerInscrits() {
            const terme = document.getElementById('recherche-inscrits').value.toLowerCase();
            const resultats = tousLesInscrits.filter(u =>
                u.nom.toLowerCase().includes(terme) ||
                u.pre.toLowerCase().includes(terme) ||
                u.mail.toLowerCase().includes(terme)
            );
            afficherInscrits(resultats);
        }

        // Exporter la liste en CSV
        function exporterCSV() {
            if (!tousLesInscrits || tousLesInscrits.length === 0) {
                alert("Aucun inscrit a exporter.");
                return;
            }

            // Créer le contenu CSV
            let csv = 'Nom,Prenom,Email\n';
            tousLesInscrits.forEach(u => {
                csv += `${u.nom},${u.pre},${u.mail}\n`;
            });

            // Télécharger le fichier
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'inscrits_' + titreEventEnCours.replace(/ /g, '_') + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        }

        document.getElementById("f-ev").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            const payload = {
                titre: document.getElementById("titre").value,
                date: document.getElementById("date").value,
                prix: parseFloat(document.getElementById("prix").value),
                place: parseInt(document.getElementById("place").value),
                id_anim: parseInt(document.getElementById("anim").value)
            };

            const res = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.ok) { resetForm(); load(); }
        };

        function edit(id, titre, date, prix, place, animId) {
            document.getElementById("edit-id").value = id;
            document.getElementById("titre").value = titre;
            document.getElementById("date").value = date ? date.replace(" ", "T") : '';
            document.getElementById("prix").value = prix;
            document.getElementById("place").value = place;
            document.getElementById("anim").value = animId;
            document.getElementById("form-title").innerText = "Modifier l'événement #" + id;
        }

        async function del(id) {
            if (confirm("Supprimer définitivement cet événement ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.ok) load();
            }
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-ev").reset();
            document.getElementById("form-title").innerText = "Planifier un nouvel atelier";
        }

        async function loadAnimateurs() {
            const res = await fetch("http://localhost:8080/api/users");
            const users = await res.json();
            const select = document.getElementById("anim");
            select.innerHTML = '<option value="">Choisir un salarie...</option>';
            users.forEach(u => {
                if (u.id_role === 1 || u.id_role === 2) {
                    select.innerHTML += `<option value="${u.id}">${u.nom} ${u.pre}</option>`;
                }
            });
        }

        load();
        loadAnimateurs();
    </script>
</body>
</html>