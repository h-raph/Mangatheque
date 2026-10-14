<?php
function getResumeAvecCache($titreOeuvre) {
    // 1. Nettoyage du titre pour créer un nom de fichier unique et propre
    $titreFormate = str_replace(' ', '_', trim($titreOeuvre));
    
    // Dossier et nom du fichier cache (par exemple : cache_les_miserables.txt)
    $dossierCache = __DIR__ . '/cache/';
    
    // Créer le dossier "cache" s'il n'existe pas
    if (!file_exists($dossierCache)) {
        mkdir($dossierCache, 0777, true);
    }
    
    $fichierCache = $dossierCache . 'resume_' . md5(strtolower($titreFormate)) . '.txt';
    
    // Durée de validité du cache en secondes (ici : 7 jours = 7 * 24 * 60 * 60)
    $dureeCache = 2592000; 

    // 2. Vérifier si le fichier existe et s'il est encore récent
    if (file_exists($fichierCache) && (time() - filemtime($fichierCache) < $dureeCache)) {
        // Le cache est valide, on lit le fichier directement !
        // (Optionnel : tu peux afficher un petit message de debug pour voir que ça vient du cache)
        // echo "<!-- Source : Cache -->";
        return file_get_contents($fichierCache);
    }

    // 3. Sinon, on appelle l'API Wikipédia
    $url = "https://fr.wikipedia.org/api/rest_v1/page/summary/" . urlencode($titreFormate);

    $options = [
        "http" => [
            "header" => "User-Agent: MonProjetSiteWeb/1.0 (contact@monsite.com)"
        ]
    ];
    $context = stream_context_create($options);

    $response = @file_get_contents($url, false, $context);

    if ($response !== FALSE) {
        $data = json_decode($response, true);
        
        if (isset($data['extract'])) {
            $resume = $data['extract'];
            
            // 4. On sauvegarde le résultat dans le fichier cache grâce à file_put_contents
            file_put_contents($fichierCache, $resume);
            
            // echo "<!-- Source : API Wikipédia -->";
            return $resume;
        }
    }
    
    // Si l'API échoue, on regarde si un vieux cache existe quand même (sécurité)
    if (file_exists($fichierCache)) {
        return file_get_contents($fichierCache);
    }
    
    return "Désolé, aucun résumé n'a été trouvé pour cette œuvre.";
}
?>