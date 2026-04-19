<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Modération des Utilisateurs</h2>
            <div class="badge bg-light text-dark p-2">Session : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">Gérez les comptes, surveillez les scores d'impact et modérez les accès au système.</p>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Gestion du profil</h5>
            <form id="f-user">
                <input type="hidden" id="edit-id">
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="small text-muted">Nom & Prénom</label>
                        <div class="input-group">
                            <input type="text" id="nom" class="form-control" placeholder="Nom" required>
                            <input type="text" id="pre" class="form-control" placeholder="Prénom" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Email</label>
                        <input type="email" id="mail" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Rôle système</label>
                        <select id="role" class="form-select" required></select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Statut Compte</label>
                        <select id="est_actif" class="form-select">
                            <option value="1">Actif (Autorisé)</option>
                            <option value="0">Banni (Bloqué)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Vérification Mail</label>
                        <select id="est_verifie" class="form-select">
                            <option value="1">Validé</option>
                            <option value="0">En attente</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label class="small text-muted">Score Upcycling</label>
                        <input type="number" id="score" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">ID Push (OneSignal)</label>
                        <input type="text" id="onesignal" class="form-control" readonly placeholder="Géré par l'application mobile">
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Nouveau mot de passe (optionnel)</label>
                        <input type="password" id="mdp" class="form-control" placeholder="Laisser vide pour garder l'actuel">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary-upcycle">Appliquer les changements</button>
                    <button type="button" class="btn btn-link text-muted" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>

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
                            <th>Email</th>
                            <th>Compte</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/users";

        // Chargement des utilisateurs
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";
                
                if (data && data.length > 0) {
                    data.forEach(u => {
                        const badgeActif = u.est_actif === 1 
                            ? '<span class="badge bg-success">Actif</span>' 
                            : '<span class="badge bg-danger">Banni</span>';
                        
                        const badgeMail = u.est_verifie === 1 
                            ? '<span class="text-success">✔ Vérifié</span>' 
                            : '<span class="text-warning">⌛ Attente</span>';

                        tbody.innerHTML += `<tr>
                            <td>#${u.id}</td>
                            <td><strong>${u.nom} ${u.pre}</strong></td>
                            <td>${u.mail}</td>
                            <td><span class="badge bg-info text-dark">${u.role}</span></td>
                            <td><span class="fw-bold">${u.score_upcycling} pts</span></td>
                            <td>${badgeMail}</td>
                            <td>${badgeActif}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" onclick="edit(${u.id},'${u.nom}','${u.pre}','${u.mail}',${u.id_role},${u.est_actif},${u.score_upcycling},'${u.onesignal_id}',${u.est_verifie})">Modifier</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="del(${u.id})">Supprimer</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='8' class='text-center'>Aucun membre trouvé.</td></tr>";
                }
            } catch (err) { console.error("Erreur API:", err); }
        }

        // Chargement des rôles pour le menu déroulant
        async function loadRoles() {
            const res = await fetch("http://localhost:8080/api/roles");
            const roles = await res.json();
            const select = document.getElementById("role");
            roles.forEach(r => { select.innerHTML += `<option value="${r.id}">${r.lib}</option>`; });
        }

        // Enregistrement des données (POST ou PUT)
        document.getElementById("f-user").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            
            const payload = {
                nom: document.getElementById("nom").value,
                pre: document.getElementById("pre").value,
                mail: document.getElementById("mail").value,
                mdp: document.getElementById("mdp").value,
                id_role: parseInt(document.getElementById("role").value),
                est_actif: parseInt(document.getElementById("est_actif").value),
                score_upcycling: parseInt(document.getElementById("score").value),
                onesignal_id: document.getElementById("onesignal").value,
                est_verifie: parseInt(document.getElementById("est_verifie").value)
            };

            const res = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            // Gestion de la sécurité Admin renvoyée par Go
            if (res.status === 403) {
                alert("Sécurité : Impossible de modifier un compte Administrateur via cette interface.");
            } else if (res.ok) {
                resetForm(); load();
            }
        };

        // Suppression d'un utilisateur
        async function del(id) {
            if (confirm("Supprimer ce membre ? S'il est Admin, l'action sera bloquée par le serveur de sécurité.")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.status === 403) alert("Interdit : On ne peut pas supprimer un Administrateur.");
                else load();
            }
        }

        // Pré-remplissage du formulaire pour édition
        function edit(id, nom, pre, mail, roleId, actif, score, os, verif) {
            document.getElementById("edit-id").value = id;
            document.getElementById("nom").value = nom;
            document.getElementById("pre").value = pre;
            document.getElementById("mail").value = mail;
            document.getElementById("role").value = roleId;
            document.getElementById("est_actif").value = actif;
            document.getElementById("score").value = score;
            document.getElementById("onesignal").value = (os === "undefined" || os === "null") ? "" : os;
            document.getElementById("est_verifie").value = verif;
            document.getElementById("form-title").innerText = "Modifier l'utilisateur #" + id;
        }

        // Réinitialisation du formulaire
        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-user").reset();
            document.getElementById("form-title").innerText = "Gestion du profil";
        }

       
        load(); loadRoles();
    </script>
</body>
</html>