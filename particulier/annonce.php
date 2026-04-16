<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déposer une annonce | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<nav class="navbar" style="background-color: var(--primary-green);">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="dashboard.php"> UpcycleConnect</a>
        <a href="../connexion.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <a href="dashboard.php" style="color:var(--primary-green);">← Retour</a>

    <div class="card mx-auto mt-3" style="max-width:550px;">
        <div class="card-body">
            <h4 style="color:var(--primary-green);"> Déposer une annonce</h4>
            <p class="text-muted small">Votre annonce sera vérifiée avant publication.</p>

            <div id="msg"></div>

            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" class="form-control" id="titre" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Catégorie</label>
                <select class="form-select" id="categorie">
                    <option value="bois">Bois</option>
                    <option value="textile">Textile</option>
                    <option value="metal">Métal</option>
                    <option value="electronique">Électronique</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label><input type="radio" name="type" value="don" checked> Don gratuit</label>
                <label class="ms-3"><input type="radio" name="type" value="vente"> Vente</label>
            </div>

            <div class="mb-3">
                <label class="form-label">Prix (€) - si vente</label>
                <input type="number" class="form-control" id="prix" value="0">
            </div>

            <button class="btn btn-primary-upcycle w-100" onclick="envoyer()">ENVOYER</button>
        </div>
    </div>
</div>

<script>
function envoyer() {
    fetch('http://localhost:8080/api/annonces', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            titre: document.getElementById('titre').value,
            categorie: document.getElementById('categorie').value,
            description: document.getElementById('description').value,
            type_offre: document.querySelector('input[name="type"]:checked').value,
            prix: parseFloat(document.getElementById('prix').value) || 0,
            id_user: <?php echo $_SESSION['user_id']; ?>
        })
    }).then(function(res) {
        if (res.ok) {
            document.getElementById('msg').innerHTML = '<div class="alert alert-success">Annonce envoyée !</div>';
        } else {
            document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Erreur.</div>';
        }
    });
}
</script>

</body>
</html>