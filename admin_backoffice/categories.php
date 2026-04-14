<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion du Catalogue de Matériaux</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Définissez ici les codes techniques des catégories. [cite_start]Ces codes permettent aux artisans de filtrer les matériaux par type[cite: 13, 18]. 
            [cite_start]Les traductions se configurent dans l'onglet <strong>Langues</strong>[cite: 113].
        </p>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Ajouter / Modifier une catégorie technique</h5>
            <form id="f-cat">
                <input type="hidden" id="edit-id">
                <div class="row g-3 align-items-center mt-1">
                    <div class="col-md-8">
                        <label class="small text-muted">Code de référence (Clé unique sans espaces)</label>
                        <input type="text" id="nom" class="form-control" placeholder="Ex: BOIS, TEXTILE, METAL_FERREUX..." required>
                    </div>
                    <div class="col-md-4 mt-4">
                        <button type="submit" class="btn btn-primary-upcycle w-100">Enregistrer dans le SI</button>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-link btn-sm text-muted" onclick="resetForm()">Annuler la modification</button>
                </div>
            </form>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 100px;">ID</th>
                            <th>Code de Référence (Clé technique)</th>
                            <th class="text-center" style="width: 250px;">Actions de gestion</th>
                        </tr>
                    </thead>
                    <tbody id="corps">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/categories";

        // CHARGEMENT DES CATÉGORIES (GET /api/categories)
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    data.forEach(c => {
                        tbody.innerHTML += `
                            <tr>
                                <td>#${c.id}</td>
                                <td><code class="text-success fw-bold">${c.nom}</code></td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm me-2" onclick="edit(${c.id}, '${c.nom}')">Modifier</button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="del(${c.id})">Supprimer</button>
                                </td>
                            </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='3' class='text-center py-4'>Aucune catégorie configurée.</td></tr>";
                }
            } catch (err) {
                console.error("Erreur technique API:", err);
            }
        }

        // ENREGISTREMENT (POST ou PUT)
        document.getElementById("f-cat").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            const payload = {
                nom: document.getElementById("nom").value.toUpperCase().replace(/\s+/g, '_')
            };

            const response = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            if(response.ok) {
                resetForm();
                load();
            } else {
                alert("Erreur : Ce code technique existe déjà ou est invalide.");
            }
        };

        // ÉDITION
        function edit(id, nom) {
            document.getElementById("edit-id").value = id;
            document.getElementById("nom").value = nom;
            document.getElementById("form-title").innerText = "Modifier la référence #" + id;
            document.getElementById("nom").focus();
        }

        // SUPPRESSION (DELETE /api/categories/{id})
        async function del(id) {
            if (confirm("Supprimer cette catégorie ? Cela pourrait impacter le filtrage des annonces en cours.")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) {
                    load();
                } else {
                    alert("Impossible : Cette catégorie est utilisée par des objets dans l'inventaire.");
                }
            }
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-cat").reset();
            document.getElementById("form-title").innerText = "Ajouter / Modifier une catégorie technique";
        }

        load();
    </script>
</body>
</html>