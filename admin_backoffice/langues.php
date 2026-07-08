<?php 
include 'includes/header.php'; 
?>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Langues & Traductions</h2>
            <div class="badge bg-light text-dark p-2">Session Admin : <strong><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></div>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" onclick="afficherOnglet('langues')" href="#">Langues</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" onclick="afficherOnglet('traductions')" href="#">Traductions</a>
            </li>
        </ul>

        <!-- ONGLET LANGUES -->
        <div id="onglet-langues">
            <div class="card mb-4 p-3 shadow-sm border-0">
                <h5 class="mb-3">Ajouter une langue</h5>
                <form id="f-lang">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="small text-muted">Code ISO (2 lettres)</label>
                            <input type="text" id="code" class="form-control" placeholder="ex: en, es..." maxlength="2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Nom</label>
                            <input type="text" id="nom" class="form-control" placeholder="ex: Anglais" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-upcycle w-100">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card p-3 shadow-sm border-0">
                <h5 class="mb-3">Langues disponibles</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="corps-langues"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ONGLET TRADUCTIONS -->
        <div id="onglet-traductions" style="display:none;">
            <div class="card mb-3 p-3 shadow-sm border-0">
                <label class="small text-muted">Choisir la langue à traduire</label>
                <select id="select-langue" class="form-select" onchange="chargerTraductions()">
                    <option value="">-- Choisir une langue --</option>
                </select>
            </div>

            <div id="zone-traductions" style="display:none;">
                <div class="card p-3 shadow-sm border-0">
                    <h5 class="mb-3">Traductions pour <span id="nom-langue-active"></span></h5>
                    <p class="text-muted small">Remplissez chaque texte pour la langue choisie. Cliquez sur "Sauvegarder" après modification.</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 30%;">Clé</th>
                                    <th>Texte traduit</th>
                                    <th class="text-center" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="corps-traductions"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_LANGUES = "/api/langues";
        const API_TRADUCTIONS = "/api/traductions";

        
        const CLES_SITE = [
            // Navigation
            { cle: 'nav_accueil', defaut: 'Accueil' },
            { cle: 'nav_deconnexion', defaut: 'Déconnexion' },
            // Dashboard
            { cle: 'dashboard_titre', defaut: 'Mon tableau de bord' },
            { cle: 'dashboard_action', defaut: 'Que voulez-vous faire ?' },
            { cle: 'btn_annonce', defaut: 'Déposer une annonce' },
            { cle: 'btn_mes_annonces', defaut: 'Mes annonces' },
            { cle: 'btn_box', defaut: 'Demander une box' },
            { cle: 'btn_score', defaut: 'Mon Score' },
            { cle: 'btn_planning', defaut: 'Mon Planning' },
            { cle: 'btn_conseils', defaut: 'Conseils' },
            { cle: 'btn_prestations', defaut: 'Prestations' },
            { cle: 'btn_forum', defaut: 'Forum' },
            { cle: 'btn_profil', defaut: 'Mon Profil' },
            // Communs
            { cle: 'btn_enregistrer', defaut: 'Enregistrer' },
            { cle: 'btn_annuler', defaut: 'Annuler' },
            { cle: 'btn_supprimer', defaut: 'Supprimer' },
            { cle: 'btn_modifier', defaut: 'Modifier' },
            { cle: 'btn_retour', defaut: 'Retour' },
            // Forum
            { cle: 'forum_titre', defaut: 'Forum de la communauté' },
            { cle: 'forum_poster', defaut: 'Poster un message' },
            { cle: 'btn_galerie', defaut: 'Galerie communauté' },
            { cle: 'btn_portefeuille', defaut: 'Mon portefeuille' },
            { cle: 'forum_repondre', defaut: 'Répondre' },
            //index
            { cle: 'nav_galerie', defaut: 'Galerie' },
            { cle: 'nav_connexion', defaut: 'Connexion' },
            { cle: 'nav_inscription', defaut: 'Inscription' },
            { cle: 'hero_titre', defaut: 'Donnez une seconde vie à vos objets' },
            { cle: 'hero_sous_titre', defaut: 'La plateforme qui connecte les particuliers et les artisans...' },
            { cle: 'hero_btn_inscription', defaut: 'Je m\'inscris gratuitement' },
            { cle: 'hero_btn_comment', defaut: 'Comment ça marche' },
            { cle: 'presentation_titre', defaut: 'L\'upcycling, simplement' },
            { cle: 'presentation_texte', defaut: 'Transformez les objets destinés à la poubelle...' },
            { cle: 'presentation_btn', defaut: 'Rejoindre' },
            { cle: 'comment_titre', defaut: 'Comment ça marche ?' },
            { cle: 'etape1_titre', defaut: 'Vous déposez' },
            { cle: 'etape1_texte', defaut: 'Une annonce ou un dépôt en box...' },
            { cle: 'etape2_titre', defaut: 'Un artisan récupère' },
            { cle: 'etape2_texte', defaut: 'Il repère votre objet dans le catalogue...' },
            { cle: 'etape3_titre', defaut: 'L\'objet renaît' },
            { cle: 'etape3_texte', defaut: 'Transformé en création unique...' },
            { cle: 'pourqui_titre', defaut: 'Pour qui ?' },
            { cle: 'pourqui_particuliers_titre', defaut: 'Particuliers' },
            { cle: 'pourqui_particuliers_texte', defaut: 'Donnez ou vendez vos objets...' },
            { cle: 'pourqui_btn', defaut: 'Je m\'inscris' },
            { cle: 'pourqui_artisans_titre', defaut: 'Artisans & Pros' },
            { cle: 'pourqui_artisans_texte', defaut: 'Trouvez votre matière première...' },
            { cle: 'pourqui_btn2', defaut: 'Je m\'inscris' },
            { cle: 'pourqui_animateurs_titre', defaut: 'Nos animateurs' },
            { cle: 'pourqui_animateurs_texte', defaut: 'Notre équipe organise les ateliers...' },
            { cle: 'pourqui_badge', defaut: 'Équipe UpcycleConnect' },
            { cle: 'ateliers_titre', defaut: 'Nos prochains ateliers' },
            { cle: 'stat_points', defaut: 'points par annonce' },
            { cle: 'stat_espaces', defaut: 'espaces ouverts' },
            { cle: 'stat_revalorises', defaut: 'objets revalorisés' },
            { cle: 'stat_gratuit', defaut: 'pour s\'inscrire' },
            { cle: 'cta_titre', defaut: 'Prêt à commencer ?' },
            { cle: 'cta_btn', defaut: 'Créer mon compte' },
            { cle: 'footer_slogan', defaut: 'L\'upcycling intelligent — Paris, depuis 2026' }
        ];

        let toutesLangues = [];
        let toutesTraductions = [];

        function afficherOnglet(nom) {
            document.getElementById('onglet-langues').style.display = nom === 'langues' ? 'block' : 'none';
            document.getElementById('onglet-traductions').style.display = nom === 'traductions' ? 'block' : 'none';
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            event.target.classList.add('active');
        }

        // ===== LANGUES =====
        async function chargerLangues() {
            const res = await fetch(API_LANGUES);
            toutesLangues = await res.json() || [];

            const tbody = document.getElementById('corps-langues');
            tbody.innerHTML = '';

            if (toutesLangues.length === 0) {
                tbody.innerHTML = "<tr><td colspan='4' class='text-center text-muted py-3'>Aucune langue.</td></tr>";
            } else {
                toutesLangues.forEach(l => {
                    tbody.innerHTML += `<tr>
                        <td><span class="badge bg-secondary font-monospace p-2">${l.code.toUpperCase()}</span></td>
                        <td><strong>${l.nom}</strong></td>
                        <td class="text-center"><span class="badge bg-success">● Actif</span></td>
                        <td class="text-center">
                            <button class="btn btn-outline-danger btn-sm" onclick="supprimerLangue(${l.id})">Désactiver</button>
                        </td>
                    </tr>`;
                });
            }

            // Mettre à jour aussi le select pour les traductions
            const select = document.getElementById('select-langue');
            select.innerHTML = '<option value="">-- Choisir une langue --</option>';
            toutesLangues.forEach(l => {
                select.innerHTML += `<option value="${l.id}" data-nom="${l.nom}">${l.nom} (${l.code.toUpperCase()})</option>`;
            });
        }

        document.getElementById("f-lang").onsubmit = async (e) => {
            e.preventDefault();
            const res = await fetch(API_LANGUES, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    code: document.getElementById("code").value.toLowerCase(),
                    nom: document.getElementById("nom").value
                })
            });
            if (res.ok) { e.target.reset(); chargerLangues(); }
            else alert("Erreur : ce code ISO existe déjà.");
        };

        async function supprimerLangue(id) {
            if (confirm("Désactiver cette langue ? Les traductions associées seront perdues.")) {
                const res = await fetch(`${API_LANGUES}/${id}`, { method: 'DELETE' });
                if (res.ok) chargerLangues();
            }
        }

        // ===== TRADUCTIONS =====
        async function chargerTraductions() {
            const idLangue = document.getElementById('select-langue').value;
            const zone = document.getElementById('zone-traductions');

            if (!idLangue) {
                zone.style.display = 'none';
                return;
            }

            zone.style.display = 'block';

            // Récupérer le nom de la langue choisie
            const option = document.querySelector('#select-langue option:checked');
            document.getElementById('nom-langue-active').innerText = option.dataset.nom;

            // Charger les traductions existantes pour cette langue
            const res = await fetch(`${API_TRADUCTIONS}?id_langue=${idLangue}`);
            toutesTraductions = await res.json() || [];

            const tbody = document.getElementById('corps-traductions');
            tbody.innerHTML = '';

            // Pour chaque clé du site, on cherche la traduction existante
            CLES_SITE.forEach(c => {
                const trad = toutesTraductions.find(t => t.cle === c.cle);
                const valeur = trad ? trad.texte : '';

                tbody.innerHTML += `<tr>
                    <td><code class="text-success">${c.cle}</code><br><small class="text-muted">Défaut : ${c.defaut}</small></td>
                    <td><input type="text" class="form-control form-control-sm" id="trad-${c.cle}" value="${valeur}" placeholder="${c.defaut}"></td>
                    <td class="text-center">
                        <button class="btn btn-primary-upcycle btn-sm" onclick="sauvegarder('${c.cle}', ${idLangue})">Sauvegarder</button>
                    </td>
                </tr>`;
            });
        }

        async function sauvegarder(cle, idLangue) {
            const texte = document.getElementById('trad-' + cle).value.trim();
            if (!texte) { alert('Le texte ne peut pas être vide.'); return; }

            const res = await fetch(API_TRADUCTIONS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cle: cle, id_langue: parseInt(idLangue), texte: texte })
            });
            if (res.ok) {
                alert('Traduction enregistrée !');
            } else {
                alert('Erreur lors de la sauvegarde.');
            }
        }

        chargerLangues();
    </script>
</body>
</html>