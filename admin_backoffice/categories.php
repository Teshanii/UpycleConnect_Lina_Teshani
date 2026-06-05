<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Catégories</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Stats -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid var(--primary-green) !important;">
                    <small class="text-muted">Total catégories</small>
                    <h3 id="kpi-total" class="fw-bold" style="color:var(--primary-green);">0</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                    <small class="text-muted">Catégorie la + utilisée</small>
                    <h5 id="kpi-top" class="fw-bold text-primary">-</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm" style="border-left: 4px solid #f4a261 !important;">
                    <small class="text-muted">Total annonces</small>
                    <h3 id="kpi-annonces" class="fw-bold" style="color:#f4a261;">0</h3>
                </div>
            </div>
        </div>

        <!-- Formulaire ajout/modification -->
        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 id="form-title">Ajouter / Modifier une catégorie</h5>
            <form id="f-cat">
                <input type="hidden" id="edit-id">
                <div class="row g-3 align-items-center mt-1">
                    <div class="col-md-8">
                        <label class="small text-muted">Code de référence (sans espaces)</label>
                        <input type="text" id="nom" class="form-control" placeholder="Ex: BOIS, TEXTILE..." required>
                    </div>
                    <div class="col-md-4 mt-4">
                        <button type="submit" class="btn btn-primary-upcycle w-100">Enregistrer</button>
                    </div>
                </div>
                <div class="mt-2">
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
                            <th>Code</th>
                            <th class="text-center">Nb d'annonces</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/categories";
        const API_ANNONCES = "http://localhost:8080/api/annonces";
        let toutesAnnonces = [];

        async function load() {
            // Charger les annonces pour compter
            const resA = await fetch(API_ANNONCES);
            toutesAnnonces = await resA.json() || [];

            // Charger les catégories
            const res = await fetch(API);
            const data = await res.json() || [];

            const tbody = document.getElementById("corps");
            tbody.innerHTML = "";

            if (data.length === 0) {
                tbody.innerHTML = "<tr><td colspan='4' class='text-center text-muted py-3'>Aucune catégorie.</td></tr>";
                return;
            }

            let totalAnnonces = 0;
            let topNom = '-';
            let topCount = 0;

            data.forEach(c => {
                // Compter les annonces dans cette catégorie
                const nb = toutesAnnonces.filter(a => a.categorie === c.nom).length;
                totalAnnonces += nb;
                if (nb > topCount) { topCount = nb; topNom = c.nom; }

                tbody.innerHTML += `<tr>
                    <td>#${c.id}</td>
                    <td><code class="text-success fw-bold">${c.nom}</code></td>
                    <td class="text-center"><span class="badge bg-light text-dark border">${nb}</span></td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm me-1" onclick="edit(${c.id}, '${c.nom}')">Modifier</button>
                        <button class="btn btn-outline-danger btn-sm" onclick="del(${c.id})">Supprimer</button>
                    </td>
                </tr>`;
            });

            // Mise à jour des KPIs
            document.getElementById("kpi-total").innerText = data.length;
            document.getElementById("kpi-top").innerText = topCount > 0 ? topNom + ' (' + topCount + ')' : 'Aucune';
            document.getElementById("kpi-annonces").innerText = totalAnnonces;
        }

        document.getElementById("f-cat").onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit-id").value;
            const payload = { nom: document.getElementById("nom").value.toUpperCase().replace(/\s+/g, '_') };

            const res = await fetch(id ? `${API}/${id}` : API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) { resetForm(); load(); }
            else alert("Erreur : ce code existe déjà.");
        };

        function edit(id, nom) {
            document.getElementById("edit-id").value = id;
            document.getElementById("nom").value = nom;
            document.getElementById("form-title").innerText = "Modifier la catégorie #" + id;
        }

        async function del(id) {
            if (confirm("Supprimer cette catégorie ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if (res.ok) load();
            }
        }

        function resetForm() {
            document.getElementById("edit-id").value = "";
            document.getElementById("f-cat").reset();
            document.getElementById("form-title").innerText = "Ajouter / Modifier une catégorie";
        }

        load();
    </script>
</body>
</html>