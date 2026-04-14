<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Événements & Ateliers</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Conformément à la Mission 1, vous validez les ateliers créés par les salariés avant leur publication. 
            [cite_start]Les tarifs doivent être compris entre 20€ et 100€ selon le barème de l'entreprise[cite: 88, 307, 309].
        </p>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Planifier ou modifier un atelier</h5>
            <form id="f-ev">
                <input type="hidden" id="edit-id">
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="small text-muted">Titre de la formation</label>
                        <input type="text" id="titre" class="form-control" placeholder="Ex: Initiation Upcycling Bois" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Date et Heure</label>
                        <input type="datetime-local" id="date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Prix (€)</label>
                        <input type="number" step="0.01" id="prix" class="form-control" placeholder="20-100" required>
                    </div>
                    <div class="col-md-1">
                        <label class="small text-muted">Places</label>
                        <input type="number" id="place" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Animateur (Salarié)</label>
                        <select id="anim" class="form-select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-upcycle">Enregistrer l'événement</button>
                    <button type="button" class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Atelier / Formation</th>
                            <th>Date prévue</th>
                            <th>Tarif</th>
                            <th>Animateur</th>
                            <th>Statut</th>
                            <th class="text-center">Contrôle Admin</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/evenements";

        // CHARGEMENT DES ÉVÉNEMENTS
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    data.forEach(e => {
                        const isValidated = e.statut_validation === 1;
                        const badge = isValidated 
                            ? '<span class="badge bg-success">Validé & Publié</span>' 
                            : '<span class="badge bg-warning text-dark">Attente de validation</span>';
                        
                        // Action de validation visible seulement si non validé [cite: 101]
                        const btnValider = !isValidated 
                            ? `<button class="btn btn-success btn-sm me-1" onclick="valider(${e.id})">Approuver</button>` 
                            : "";

                        tbody.innerHTML += `
                            <tr>
                                <td>#${e.id}</td>
                                <td><strong>${e.titre}</strong><br><small class="text-muted">${e.place} places disponibles</small></td>
                                <td>${e.date.replace("T", " ")}</td>
                                <td><span class="fw-bold">${e.prix}€</span></td>
                                <td><span class="badge bg-info text-dark">${e.anim}</span></td>
                                <td>${badge}</td>
                                <td class="text-center">
                                    ${btnValider}
                                    <button class="btn btn-warning btn-sm" onclick="edit(${e.id},'${e.titre}','${e.date}',${e.prix},${e.place},${e.id_anim})">Éditer</button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="del(${e.id})">Supprimer</button>
                                </td>
                            </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='7' class='text-center py-4'>Aucun événement enregistré dans le SI.</td></tr>";
                }
            } catch (err) { console.error("Erreur API Go:", err); }
        }

        // VALIDATION ADMIN (PUT /api/evenements/valider/{id})
        async function valider(id) {
            if(confirm("En validant, cet atelier sera ouvert aux réservations payantes pour les particuliers.")) {
                const res = await fetch(`${API}/valider/${id}`, { method: 'PUT' });
                if(res.ok) load();
            }
        }

        // CHARGER LES ANIMATEURS (Filtre par rôle Salarié)
        async function loadAnimateurs() {
            const res = await fetch("http://localhost:8080/api/users");
            const users = await res.json();
            const select = document.getElementById("anim");
            select.innerHTML = '<option value="">Choisir un salarié...</option>';
            users.forEach(u => {
                // Seuls les Salariés (Rôle 2) ou Admin peuvent animer 
                if(u.id_role === 1 || u.id_role === 2) {
                    select.innerHTML += `<option value="${u.id}">${u.nom} ${u.pre}</option>`;
                }
            });
        }

        // ENREGISTREMENT (POST ou PUT)
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
            if(res.ok) { resetForm(); load(); }
        };

        function edit(id, titre, date, prix, place, animId) {
            document.getElementById("edit-id").value = id;
            document.getElementById("titre").value = titre;
            document.getElementById("date").value = date.replace(" ", "T");
            document.getElementById("prix").value = prix;
            document.getElementById("place").value = place;
            document.getElementById("anim").value = animId;
            document.getElementById("form-title").innerText = "Modifier l'événement #" + id;
        }

        async function del(id) {
            if (confirm("Supprimer définitivement cet événement ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) load();
            }
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-ev").reset();
            document.getElementById("form-title").innerText = "Planifier un nouvel atelier";
        }

        load();
        loadAnimateurs();
    </script>
</body>
</html>