<?php
// Ce fichier reçoit la photo et la sauvegarde dans le dossier uploads/

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    
    // On crée le dossier uploads s'il existe pas
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // On génère un nom unique pour éviter les doublons
    $nom = time() . '_' . basename($_FILES['photo']['name']);
    $destination = 'uploads/' . $nom;

    // On déplace la photo du dossier temporaire vers uploads/
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        echo json_encode(['chemin' => $destination]);
    } else {
        echo json_encode(['chemin' => '']);
    }
} else {
    echo json_encode(['chemin' => '']);
}
?>