<?php
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '128M');

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    $nom = time() . '_' . basename($_FILES['photo']['name']);
    $destination = 'uploads/' . $nom;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        echo json_encode(['chemin' => $destination]);
    } else {
        echo json_encode(['chemin' => '', 'error' => 'Erreur lors du déplacement du fichier']);
    }
} else {
    $erreur = isset($_FILES['photo']) ? $_FILES['photo']['error'] : 'Aucun fichier';
    echo json_encode(['chemin' => '', 'error' => 'Code erreur: ' . $erreur]);
}
?>