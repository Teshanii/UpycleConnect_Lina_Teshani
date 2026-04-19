<?php include 'includes/header.php'; ?>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="content">
        <h2>Supervision des Dépôts en Box</h2>
        <p class="text-muted">Validez les demandes et attribuez un code d'ouverture.</p>

        <div class="card p-3 shadow-sm border-0">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Citoyen</th>
                        <th>Description de l'objet</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="corps-demandes"></tbody>
            </table>
        </div>
    </div>

    <script>
        async function load() {
            const res = await fetch("http://localhost:8080/api/demandes_box");
            const data = await res.json();
            const tbody = document.getElementById("corps-demandes");
            tbody.innerHTML = "";

            data.forEach(d => {
                tbody.innerHTML += `<tr>
                    <td>Utilisateur #${d.id_user}</td>
                    <td>${d.description}</td>
                    <td><span class="badge bg-warning">En attente</span></td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="valider(${d.id})">Envoyer Code</button>
                    </td>
                </tr>`;
            });
        }
        load();
    </script>
</body>