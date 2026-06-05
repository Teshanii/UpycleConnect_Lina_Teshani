<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
$id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une annonce | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php"> UpcycleConnect</a>
        <a href="../connexion.php?logout=1" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="mes_annonces.php" style="color:var(--primary-green);">← Retour</a>

    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);"> Modifier mon annonce</h4>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" class="form-control" id="titre">
            </div>

            <div class="mb-3">
                <label class="form-label">Catégorie</label>
                <select class="form-select" id="categorie">
                    
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="description" rows="3"></textarea>
            </div>

            <!-- Champ pour changer la photo si besoin -->
            <div class="mb-3">
                <label class="form-label">Nouvelle photo (optionnel)</label>
                <input type="file" class="form-control" id="photo" accept="image/*">
            </div>

            <div class="mb-3">
                <label><input type="radio" name="type" value="don" id="don"> Don gratuit</label>
                <label class="ms-3"><input type="radio" name="type" value="vente" id="vente"> Vente</label>
            </div>

            <div class="mb-3">
                <label class="form-label">Prix (€)</label>
                <input type="number" class="form-control" id="prix" value="0">
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="modifier()">ENREGISTRER</button>
        </div>
    </div>
</div>

<script>
var id = <?php echo $id; ?>;

// D'abord les catégories, puis les données de l'annonce
fetch('http://localhost:8080/api/categories')
    .then(function(res) { return res.json(); })
    .then(function(cats) {
        var select = document.getElementById('categorie');
        select.innerHTML = '';
        cats.forEach(function(c) {
            select.innerHTML += '<option value="' + c.nom + '">' + c.nom + '</option>';
        });
        // Une fois les catégories chargées on charge l'annonce
        return fetch('http://localhost:8080/api/annonces?id_user=<?php echo $_SESSION['user_id']; ?>');
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var annonce = data.find(function(a) { return a.id === id; });
        if (annonce) {
            document.getElementById('titre').value = annonce.titre;
            document.getElementById('description').value = annonce.description || '';
            document.getElementById('categorie').value = annonce.categorie || '';
            document.getElementById('prix').value = annonce.prix || 0;
            if (annonce.type_offre === 'don') {
                document.getElementById('don').checked = true;
            } else {
                document.getElementById('vente').checked = true;
            }
        }
    });

function modifier() {
    var photoFile = document.getElementById('photo').files[0];
    if (photoFile) {
        var formData = new FormData();
        formData.append('photo', photoFile);
        fetch('../upload_photo.php', { method: 'POST', body: formData })
            .then(function(res) { return res.json(); })
            .then(function(data) { envoyerModif(data.chemin); });
    } else {
        envoyerModif('');
    }
}

function envoyerModif(cheminPhoto) {
    var body = {
        titre: document.getElementById('titre').value,
        description: document.getElementById('description').value,
        categorie: document.getElementById('categorie').value,
        type_offre: document.querySelector('input[name="type"]:checked').value,
        prix: parseFloat(document.getElementById('prix').value) || 0
    };
    if (cheminPhoto) body.photo = cheminPhoto;

    fetch('http://localhost:8080/api/annonces/' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Annonce modifiée !</div>';
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}
</script>

</body>
</html>