<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Box & Demandes de Dépôt</h2>
            <div class="badge bg-light text-dark p-2">Session : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" onclick="afficherOnglet('demandes')" href="#">Demandes en attente</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="afficherOnglet('box')" href="#">Gérer les Box</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="afficherOnglet('casiers')" href="#">Gérer les Casiers</a>
            </li>
        </ul>

        <!-- ONGLET 1 : Demandes -->
        <div id="onglet-demandes">
            <div class="mb-3">
                <button class="btn btn-warning btn-sm me-2" onclick="filtrerDemandes('en_attente')">En attente</button>
                <button class="btn btn-success btn-sm me-2" onclick="filtrerDemandes('valide')">Validées</button>
                <button class="btn btn-danger btn-sm me-2" onclick="filtrerDemandes('refuse')">Refusées</button>
                <button class="btn btn-secondary btn-sm" onclick="filtrerDemandes('toutes')">Toutes</button>
            </div>

            <div class="card p-3 shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Utilisateur</th>
                                <th>Objet décrit</th>
                                <th>Box</th>
                                <th>Casier</th>
                                <th>Code</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="corps-demandes"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ONGLET 2 : Box -->
        <div id="onglet-box" style="display:none;">
            <div class="card mb-4 p-3 shadow-sm border-0">
                <h5>Ajouter une box</h5>
                <form id="f-box">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="small text-muted">Adresse</label>
                            <input type="text" id="adresse" class="form-control" placeholder="Ex: 174 rue La Fayette" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Ville</label>
                            <input type="text" id="ville" class="form-control" placeholder="Ex: Paris 11" required>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted">Capacité max</label>
                            <input type="number" id="capacite" class="form-control" value="20" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-upcycle mt-3">Enregistrer</button>
                </form>
            </div>

            <div class="card p-3 shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                           <tr>
                                <th>ID</th>
                                <th>Adresse</th>
                                <th>Ville</th>
                                <th>Capacité</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="corps-box"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ONGLET 3 : Casiers -->
        <div id="onglet-casiers" style="display:none;">
            <div class="card mb-4 p-3 shadow-sm border-0">
                <h5>Ajouter un casier</h5>
                <form id="f-casier">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted">Numéro du casier</label>
                            <input type="text" id="num-casier" class="form-control" placeholder="Ex: A1, B3..." required>
                        </div>
                        <div class="col-md-8">
                            <label class="small text-muted">Box</label>
                            <select id="id-box-casier" class="form-select" required>
                                <option value="">Choisir une box...</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-upcycle mt-3">Ajouter le casier</button>
                </form>
            </div>

            <div class="card p-3 shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Numéro</th>
                                <th>Statut</th>
                                <th>Box</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="corps-casiers"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal refus demande -->
        <div class="modal fade" id="modalRefus" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Refuser la demande</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="refus-id">
                        <label class="form-label text-danger">Motif du refus (obligatoire)</label>
                        <textarea id="motif-refus" class="form-control" rows="3" placeholder="Ex: Objet non conforme..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" onclick="confirmerRefus()">Refuser</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal code généré après validation -->
        <div class="modal fade" id="modalCode" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Demande validée</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="text-muted">Le particulier doit se rendre à :</p>
                        <h4 id="modal-casier" class="text-primary"></h4>
                        <p class="text-muted mt-3">Et utiliser ce code pour ouvrir le casier :</p>
                        <h1 id="modal-code" class="fw-bold" style="letter-spacing: 8px; color: var(--primary-green);"></h1>
                        <p class="text-muted small mt-2">Ce code a été enregistré et est visible par le particulier dans son espace.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Compris</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BOX = '/api/box';
        const API_DEMANDES = '/api/demandes_box';
        const API_CASIERS = '/api/casiers';

        let toutesLesDemandes = [];
        let toutesLesBox = [];
        let modalRefus = new bootstrap.Modal(document.getElementById('modalRefus'));
        let modalCode = new bootstrap.Modal(document.getElementById('modalCode'));

        
        function afficherOnglet(nom) {
            document.getElementById('onglet-demandes').style.display = 'none';
            document.getElementById('onglet-box').style.display = 'none';
            document.getElementById('onglet-casiers').style.display = 'none';
            document.getElementById('onglet-' + nom).style.display = 'block';
        }

        
        async function chargerDemandes() {
            const res = await fetch(API_DEMANDES);
            toutesLesDemandes = await res.json() || [];
            afficherDemandes(toutesLesDemandes);
        }

        function afficherDemandes(liste) {
            const tbody = document.getElementById('corps-demandes');
            tbody.innerHTML = '';

            if (!liste || liste.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Aucune demande.</td></tr>';
                return;
            }

            liste.forEach(d => {
                const badge = d.statut === 'valide'
                    ? '<span class="badge bg-success">Validée</span>'
                    : d.statut === 'refuse'
                    ? `<span class="badge bg-danger">Refusée</span>${d.motif_refus ? '<br><small class="text-danger">' + d.motif_refus + '</small>' : ''}`
                    : d.statut === 'depose'
                    ? '<span class="badge bg-info text-dark">Déposé</span>'
                    : d.statut === 'recupere'
                    ? '<span class="badge bg-primary">Récupéré</span>'
                    : '<span class="badge bg-warning text-dark">En attente</span>';

                const btnValider = d.statut === 'en_attente'
                    ? `<button class="btn btn-success btn-sm me-1" onclick="valider(${d.id}, ${d.id_box})">Valider</button>`
                    : '';

                const btnRefuser = d.statut === 'en_attente'
                    ? `<button class="btn btn-danger btn-sm" onclick="ouvrirRefus(${d.id})">Refuser</button>`
                    : '';

                tbody.innerHTML += `<tr>
                    <td>#${d.id}</td>
                    <td>${d.nom_user || '-'}</td>
                    <td>${d.description || '-'}</td>
                    <td>${d.adresse_box || '-'}</td>
                    <td>${d.numero_casier || '-'}</td>
                    <td><strong>${d.code_ouverture || '-'}</strong></td>
                    <td>${badge}</td>
                    <td>${d.date ? d.date.replace('T', ' ') : '-'}</td>
                    <td class="text-center">${btnValider} ${btnRefuser}</td>
                </tr>`;
            });
        }

        function filtrerDemandes(type) {
            if (type === 'toutes') return afficherDemandes(toutesLesDemandes);
            if (type === 'en_attente') return afficherDemandes(toutesLesDemandes.filter(d => d.statut === 'en_attente'));
            if (type === 'valide') return afficherDemandes(toutesLesDemandes.filter(d => d.statut === 'valide'));
            if (type === 'refuse') return afficherDemandes(toutesLesDemandes.filter(d => d.statut === 'refuse'));
        }

        async function valider(id, idBox) {
            if (confirm("Valider cette demande ? Un casier sera attribué automatiquement.")) {
                const res = await fetch(`${API_DEMANDES}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_box: idBox })
                });

                if (res.ok) {
                    const data = await res.json();
                    
                    document.getElementById('modal-code').innerText = data.code;
                    document.getElementById('modal-casier').innerText = 'Casier : ' + data.casier;
                    modalCode.show();
                    chargerDemandes();
                } else {
                    const err = await res.json();
                    alert(err.error || 'Erreur lors de la validation.');
                }
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
                alert('Veuillez entrer un motif de refus.');
                return;
            }
            await fetch(`${API_DEMANDES}/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ motif_refus: motif })
            });
            modalRefus.hide();
            chargerDemandes();
        }

        
        async function chargerBox() {
            const res = await fetch(API_BOX);
            toutesLesBox = await res.json() || [];
            const tbody = document.getElementById('corps-box');
            tbody.innerHTML = '';

            
            const sel = document.getElementById('id-box-casier');
            sel.innerHTML = '<option value="">Choisir une box...</option>';

            if (!toutesLesBox || toutesLesBox.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Aucune box.</td></tr>';
                return;
            }

            toutesLesBox.forEach(b => {
                tbody.innerHTML += `<tr>
                    <td>#${b.id}</td>
                    <td><strong>${b.adresse}</strong></td>
                    <td>${b.ville || '-'}</td>
                    <td>${b.capacite_max} casiers max</td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm" onclick="supprimerBox(${b.id})">Supprimer</button>
                    </td>
                </tr>`;
                sel.innerHTML += `<option value="${b.id}">${b.adresse}</option>`;
            });
        }

        document.getElementById('f-box').onsubmit = async (e) => {
            e.preventDefault();
            const res = await fetch(API_BOX, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    adresse: document.getElementById('adresse').value,
                    ville: document.getElementById('ville').value,
                    capacite_max: parseInt(document.getElementById('capacite').value)
                })
            });
            if (res.ok) { e.target.reset(); chargerBox(); }
        };

        async function supprimerBox(id) {
            if (confirm("Supprimer cette box ? Les casiers associés seront aussi supprimés.")) {
                const res = await fetch(`${API_BOX}/${id}`, { method: 'DELETE' });
                if (res.ok) chargerBox();
                else alert("Impossible : des demandes sont liées à cette box.");
            }
        }

        
        async function chargerCasiers() {
            const res = await fetch(API_CASIERS);
            const data = await res.json() || [];
            const tbody = document.getElementById('corps-casiers');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Aucun casier.</td></tr>';
                return;
            }

            data.forEach(c => {
                const badge = c.statut === 'libre'
                    ? '<span class="badge bg-success">Libre</span>'
                    : '<span class="badge bg-danger">Occupé</span>';

                
                const box = toutesLesBox.find(b => b.id === c.id_box);
                const adresseBox = box ? box.adresse : '-';

                tbody.innerHTML += `<tr>
                    <td>#${c.id}</td>
                    <td><strong>${c.numero}</strong></td>
                    <td>${badge}</td>
                    <td>${adresseBox}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-danger btn-sm" onclick="supprimerCasier(${c.id})">Supprimer</button>
                    </td>
                </tr>`;
            });
        }

       document.getElementById('f-casier').onsubmit = async (e) => {
            e.preventDefault();
            const res = await fetch(API_CASIERS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    numero: document.getElementById('num-casier').value,
                    id_box: parseInt(document.getElementById('id-box-casier').value)
                })
            });
            if (res.ok) {
                e.target.reset();
                chargerCasiers();
            } else {
                
                const err = await res.json();
                alert(err.error || 'Erreur lors de l\'ajout du casier.');
            }
        };

        async function supprimerCasier(id) {
            if (confirm("Supprimer ce casier ?")) {
                const res = await fetch(`${API_CASIERS}/${id}`, { method: 'DELETE' });
                if (res.ok) chargerCasiers();
            }
        }

        
        chargerDemandes();
        chargerBox();
        chargerCasiers();
    </script>
</body>
</html>