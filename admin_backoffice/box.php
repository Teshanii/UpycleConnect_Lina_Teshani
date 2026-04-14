<?php 
// 1. Inclusion du header (Gère la sécurité Admin et les ressources)
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion de l'Inventaire des Box</h2>
            <div class="badge bg-light text-dark p-2">Session : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <p class="text-muted">
            Configurez ici les points de dépôt physiques. Selon le sujet, ces box permettent aux citoyens de déposer des objets validés.
        </p>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white p-3 border-0 shadow-sm">
                    <small>Total des Box configurées</small>
                    <h3 id="stat-total">...</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white p-3 border-0 shadow-sm">
                    <small>État général du réseau</small>
                    <h3 id="stat-etat">Opérationnel</h3>
                </div>
            </div>
        </div>

        <div class="card mb-4 p-3 shadow-sm border-0">
            <h5 class="mb-3">Ajouter une nouvelle box de collecte</h5>
            <form id="f-box">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="small text-muted">Adresse (Quartier ou Rue)</label>
                        <input type="text" id="adresse" class="form-control" placeholder="Ex: Paris 10ème - Rue La Fayette" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Capacité Maximale</label>
                        <input type="number" id="capacite" class="form-control" value="20" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary-upcycle mt-3">Enregistrer le conteneur</button>
            </form>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Box</th>
                            <th>Localisation</th>
                            <th>Capacité Max</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API = "http://localhost:8080/api/box";

        // CHARGEMENT DE LA LISTE DES BOX
        async function load() {
            try {
                const res = await fetch(API);
                const data = await res.json();
                const tbody = document.getElementById("corps");
                tbody.innerHTML = "";

                if (data && data.length > 0) {
                    document.getElementById("stat-total").innerText = data.length;
                    data.forEach(b => {
                        tbody.innerHTML += `<tr>
                            <td>#${b.id}</td>
                            <td><strong>${b.adresse}</strong></td>
                            <td><span class="badge bg-secondary">${b.capacite_max} objets</span></td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm" onclick="del(${b.id})">Supprimer</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='4' class='text-center'>Aucun conteneur configuré.</td></tr>";
                    document.getElementById("stat-total").innerText = "0";
                }
            } catch (err) {
                console.error("Erreur API Go:", err);
            }
        }

        // CRÉATION D'UNE BOX (Appel POST)
        document.getElementById("f-box").onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                adresse: document.getElementById("adresse").value,
                capacite_max: parseInt(document.getElementById("capacite").value)
            };
            
            const response = await fetch(API, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            if(response.ok) {
                e.target.reset();
                load();
            }
        };

        // SUPPRESSION (Appel DELETE)
        async function del(id) {
            if(confirm("Confirmer la suppression de ce point de dépôt ?")) {
                const res = await fetch(`${API}/${id}`, { method: 'DELETE' });
                if(res.ok) {
                    load();
                } else {
                    alert("Erreur : Ce conteneur est lié à des dépôts en cours.");
                }
            }
        }

        load();
    </script>
</body>
</html>