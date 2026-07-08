<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Utilisateurs</h2>
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-primary-upcycle btn-sm" onclick="nouveauUser()">+ Ajouter un utilisateur</button>
                <div class="badge bg-light text-dark p-2">Session : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
            </div>
        </div>

        <!-- Formulaire création / modification -->
        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Modifier un utilisateur</h5>
            <form id="f-user">
                <input type="hidden" id="edit-id">
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="small text-muted">Nom</label>
                        <input type="text" id="nom" class="form-control" placeholder="Nom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Prénom</label>
                        <input type="text" id="pre" class="form-control" placeholder="Prénom" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Email</label>
                        <input type="email" id="mail" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Rôle</label>
                        <select id="role" class="form-select" required></select>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="small text-muted">Statut compte</label>
                        <select id="est_actif" class="form-select">
                            <option value="1">Actif</option>
                            <option value="0">Banni</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Vérification email</label>
                        <select id="est_verifie" class="form-select">
                            <option value="1">Vérifié</option>
                            <option value="0">En attente</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Score upcycling</label>
                        <input type="number" id="score" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Mot de passe</label>
                        <input type="password" id="mdp" class="form-control" placeholder="Obligatoire pour création">
                    </div>
                </div>
                <div id="msg-form" class="mt-2"></div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-upcycle">Enregistrer</button>
                    <button type="button" class="btn btn-link text-muted" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>

        <!-- Filtres et recherche -->
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" id="recherche" class="form-control" placeholder="Rechercher par nom ou email..." oninput="filtrer()">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary btn-sm w-100" onclick="filtrer('tous')">Tous</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success btn-sm w-100" onclick="filtrer('actifs')">Actifs</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-danger btn-sm w-100" onclick="filtrer('bannis')">Bannis</button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-warning btn-sm w-100" onclick="filtrer('non-verifies')">Non vérifiés</button>
            </div>
        </div>

        <!-- Compteur + export -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted small" id="nb-affiches"></span>
            <button class="btn btn-success btn-sm" onclick="exporterCSV()">Exporter CSV</button>
        </div>

        <!-- Tableau -->
        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Score</th>
                            <th>Email vérifié</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "/api/users";
        let tousLesUsers = [];
        let filtreActif = 'tous';

        async function load() {
            try {
                const res = await fetch(API);
                tousLesUsers = await res.json();
                filtrer(filtreActif);
            } catch (err) {
                console.error("Erreur API:", err);
            }
        }

        function afficher(data) {
            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";
            document.getElementById('nb-affiches').innerText = (data ? data.length : 0) + ' utilisateur(s) affiché(s)';

            if (!data || data.length === 0) {
                tbody.innerHTML = "<tr><td colspan='8' class='text-center text-muted'>Aucun utilisateur.</td></tr>";
                return;
            }

            data.forEach(u => {
                const badgeActif = u.est_actif === 1
                    ? '<span class="badge bg-success">Actif</span>'
                    : '<span class="badge bg-danger">Banni</span>';

                const badgeMail = u.est_verifie === 1
                    ? '<span class="text-success">Vérifié</span>'
                    : '<span class="text-warning">En attente</span>';

                const btnBan = u.est_actif === 1
                    ? `<button class="btn btn-outline-danger btn-sm me-1" onclick="toggleBan(${u.id}, 0)">Bannir</button>`
                    : `<button class="btn btn-outline-success btn-sm me-1" onclick="toggleBan(${u.id}, 1)">Débannir</button>`;

                tbody.innerHTML += `<tr>
                    <td>#${u.id}</td>
                    <td><strong>${u.nom} ${u.pre}</strong></td>
                    <td>${u.mail}</td>
                    <td><span class="badge bg-info text-dark">${u.role}</span></td>
                    <td><strong>${u.score_upcycling} pts</strong></td>
                    <td>${badgeMail}</td>
                    <td>${badgeActif}</td>
                    <td class="text-center">
                        ${btnBan}
                        <button class="btn btn-warning btn-sm me-1" onclick="edit(${u.id},'${u.nom}','${u.pre}','${u.mail}',${u.id_role},${u.est_actif},${u.score_upcycling},${u.est_verifie})">Modifier</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="del(${u.id})">Supprimer</button>
                    </td>
                </tr>`;
            });
        }

        function filtrer(type) {
            if (type) filtreActif = type;
            var terme = document.getElementById('recherche').value.toLowerCase();
            var resultats = tousLesUsers || [];

            if (filtreActif === 'bannis') resultats = resultats.filter(u => u.est_actif === 0);
            if (filtreActif === 'actifs') resultats = resultats.filter(u => u.est_actif === 1);
            if (filtreActif === 'non-verifies') resultats = resultats.filter(u => u.est_verifie === 0);

            if (terme) {
                resultats = resultats.filter(u =>
                    (u.nom + ' ' + u.pre).toLowerCase().includes(terme) ||
                    u.mail.toLowerCase().includes(terme)
                );
            }

            afficher(resultats);
        }

        async function toggleBan(id, statut) {
            var action = statut === 0 ? 'Bannir' : 'Débannir';
            if (confirm(action + ' cet utilisateur ?')) {
                var user = tousLesUsers.find(u => u.id === id);
                if (!user) return;
                const res = await fetch(`${API}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nom: user.nom,
                        pre: user.pre,
                        mail: user.mail,
                        id_role: user.id_role,
                        est_actif: statut,
                        score_upcycling: user.score_upcycling,
                        est_verifie: user.est_verifie
                    })
                });
                if (res.status === 403) {
                    alert("Impossible de modifier un Administrateur.");
                } else {
                    load();
                }
            }
        }

        async function loadRoles() {
            const res = await fetch("/api/roles");
            const roles = await res.json();
            const select = document.getElementById("role");
            select.innerHTML = '';
            roles.forEach(r => {
                select.innerHTML += `<option value="${r.id}">${r.lib}</option>`;
            });
        }

        document.getElementById("f-user").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            const mdp = document.getElementById("mdp").value;

            
            if (!id && !mdp) {
                document.getElementById('msg-form').innerHTML = '<div class="alert alert-danger">Le mot de passe est obligatoire pour créer un utilisateur.</div>';
                return;
            }

            const payload = {
                nom: document.getElementById("nom").value,
                pre: document.getElementById("pre").value,
                mail: document.getElementById("mail").value,
                mdp: mdp,
                id_role: parseInt(document.getElementById("role").value),
                est_actif: parseInt(document.getElementById("est_actif").value),
                score_upcycling: parseInt(document.getElementById("score").value),
                est_verifie: parseInt(document.getElementById("est_verifie").value)
            };

            
            const res = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.status === 403) {
                alert("Impossible de modifier un Administrateur.");
            } else if (res.status === 409 || !res.ok) {
                const data = await res.json();
                document.getElementById('msg-form').innerHTML = '<div class="alert alert-danger">' + (data.error || 'Erreur.') + '</div>';
            } else {
                resetForm();
                load();
            }
        };

        async function del(id) {
            if (confirm("Supprimer définitivement cet utilisateur ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.status === 403) alert("Impossible de supprimer un Administrateur.");
                else if (res.status === 409) alert("Cet utilisateur est lié à des annonces ou événements.");
                else load();
            }
        }

        function edit(id, nom, pre, mail, roleId, actif, score, verif) {
            document.getElementById("edit-id").value = id;
            document.getElementById("nom").value = nom;
            document.getElementById("pre").value = pre;
            document.getElementById("mail").value = mail;
            document.getElementById("role").value = roleId;
            document.getElementById("est_actif").value = actif;
            document.getElementById("score").value = score;
            document.getElementById("est_verifie").value = verif;
            document.getElementById("mdp").value = '';
            document.getElementById("mdp").placeholder = "Laisser vide pour garder l'actuel";
            document.getElementById('msg-form').innerHTML = '';
            document.getElementById("form-title").innerText = "Modifier l'utilisateur #" + id;
            window.scrollTo(0, 0);
        }

        function nouveauUser() {
            resetForm();
            document.getElementById("form-title").innerText = "Ajouter un utilisateur";
            document.getElementById("mdp").placeholder = "Obligatoire pour création";
            window.scrollTo(0, 0);
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-user").reset();
            document.getElementById('msg-form').innerHTML = '';
            document.getElementById("form-title").innerText = "Modifier un utilisateur";
            document.getElementById("mdp").placeholder = "Laisser vide pour garder l'actuel";
        }

        function exporterCSV() {
            if (!tousLesUsers || tousLesUsers.length === 0) {
                alert("Aucun utilisateur à exporter.");
                return;
            }
            var csv = 'ID,Nom,Prenom,Email,Role,Score,Statut,Email verifie\n';
            tousLesUsers.forEach(function(u) {
                csv += u.id + ',' + u.nom + ',' + u.pre + ',' + u.mail + ',' + u.role + ',' + u.score_upcycling + ',' + (u.est_actif === 1 ? 'Actif' : 'Banni') + ',' + (u.est_verifie === 1 ? 'Verifie' : 'En attente') + '\n';
            });
            var blob = new Blob([csv], { type: 'text/csv' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'utilisateurs_upcycleconnect.csv';
            a.click();
            URL.revokeObjectURL(url);
        }

        load();
        loadRoles();
    </script>
</body>
</html>