<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion du Catalogue des Prestations</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Définissez ici les services officiels d'UpcycleConnect. [cite_start]Ce catalogue permet aux artisans de proposer des prestations régulières et de qualité aux citoyens[cite: 13, 82].
        </p>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Ajouter / Modifier une prestation</h5>
            <form id="f-pre">
                <input type="hidden" id="edit-id">
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="small text-muted">Nom du service</label>
                        <input type="text" id="nom" class="form-control" placeholder="ex: Restauration de meuble ancien" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Prix de base (€)</label>
                        <input type="number" step="0.01" id="prix" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Description (Sera visible sur le catalogue)</label>
                        <input type="text" id="desc" class="form-control" placeholder="Détails sur l'offre de service">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" id="btn-ok" class="btn btn-primary-upcycle">Enregistrer la prestation</button>
                    <button type="button" class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Service proposé</th>
                            <th>Prix Indicatif</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/prestations";

        // CHARGEMENT (Appel API Go)
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    data.forEach(p => {
                        tbody.innerHTML += `
                            <tr>
                                <td>#${p.id}</td>
                                <td><strong>${p.nom}</strong></td>
                                <td><span class="badge bg-light text-dark border">${p.prix.toFixed(2)} €</span></td>
                                <td class="small text-muted fst-italic">${p.desc || '-'}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-1" onclick="edit(${p.id}, '${p.nom.replace(/'/g, "\\'")}', ${p.prix}, '${(p.desc || '').replace(/'/g, "\\'")}')">Modifier</button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="del(${p.id})">Suppr.</button>
                                </td>
                            </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='5' class='text-center py-4'>Aucune prestation dans le catalogue.</td></tr>";
                }
            } catch (err) {
                console.error("Erreur de liaison API:", err);
            }
        }

        // ENREGISTREMENT (POST ou PUT)
        document.getElementById("f-pre").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            const payload = {
                nom: document.getElementById("nom").value,
                prix: parseFloat(document.getElementById("prix").value),
                desc: document.getElementById("desc").value
            };

            const response = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if(response.ok) {
                resetForm();
                load();
            }
        };

        function edit(id, nom, prix, desc) {
            document.getElementById("edit-id").value = id;
            document.getElementById("nom").value = nom;
            document.getElementById("prix").value = prix;
            document.getElementById("desc").value = desc;
            document.getElementById("form-title").innerText = "Modifier la prestation #" + id;
            document.getElementById("btn-ok").innerText = "Mettre à jour le catalogue";
        }

        async function del(id) {
            if (confirm("Supprimer cette offre du catalogue ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) load();
            }
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-pre").reset();
            document.getElementById("form-title").innerText = "Ajouter / Modifier une prestation";
            document.getElementById("btn-ok").innerText = "Enregistrer la prestation";
        }

        load();
    </script>
</body>
</html>