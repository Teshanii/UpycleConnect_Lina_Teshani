<?php
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '128M');

header('Content-Type: application/json');

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

   
    $extensionsOK = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionsOK)) {
        echo json_encode(['chemin' => '', 'error' => 'Format non autorisé. Images uniquement (jpg, png, webp).']);
        exit;
    }


    $infosImage = getimagesize($_FILES['photo']['tmp_name']);
    if ($infosImage === false) {
        echo json_encode(['chemin' => '', 'error' => 'Le fichier n\'est pas une image valide.']);
        exit;
    }

    // 3. On vérifie la taille 
    if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
        echo json_encode(['chemin' => '', 'error' => 'Image trop lourde (max 10 Mo).']);
        exit;
    }

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }


    $nom = time() . '_' . uniqid() . '.' . $extension;
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