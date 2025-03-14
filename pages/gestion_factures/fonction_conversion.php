<?php
// Convertir un nombre en lettres
function convertirEnLettres($nombre) {
    $unites = ["", "un", "deux", "trois", "quatre", "cinq", "six", "sept", "huit", "neuf"];
    $dizaines = ["", "dix", "vingt", "trente", "quarante", "cinquante", "soixante", "soixante-dix", "quatre-vingt", "quatre-vingt-dix"];
    $exceptions = [
        11 => "onze", 12 => "douze", 13 => "treize", 14 => "quatorze", 15 => "quinze",
        16 => "seize", 17 => "dix-sept", 18 => "dix-huit", 19 => "dix-neuf"
    ];
    
    if ($nombre == 0) {
        return "zéro";
    }

    $texte = "";
    
    // Gérer les millions
    if ($nombre >= 1000000) {
        $millions = floor($nombre / 1000000);
        $texte .= convertirEnLettres($millions) . " million" . ($millions > 1 ? "s" : "") . " ";
        $nombre %= 1000000;
    }

    // Gérer les milliers
    if ($nombre >= 1000) {
        $milliers = floor($nombre / 1000);
        if ($milliers > 1) {
            $texte .= convertirEnLettres($milliers) . " mille ";
        } else {
            $texte .= "mille ";
        }
        $nombre %= 1000;
    }

    // Gérer les centaines
    if ($nombre >= 100) {
        $centaines = floor($nombre / 100);
        if ($centaines > 1) {
            $texte .= $unites[$centaines] . " cent";
        } else {
            $texte .= "cent";
        }
        $nombre %= 100;
        if ($nombre > 0) {
            $texte .= " ";
        }
    }

    // Gérer les dizaines et unités
    if ($nombre > 0) {
        if (isset($exceptions[$nombre])) {
            $texte .= $exceptions[$nombre];
        } else {
            $dizaine = floor($nombre / 10);
            $unite = $nombre % 10;

            if ($dizaine > 0) {
                $texte .= $dizaines[$dizaine];
                if ($unite > 0) {
                    $texte .= "-";
                }
            }
            if ($unite > 0) {
                $texte .= $unites[$unite];
            }
        }
    }

    return trim($texte);
}
?>