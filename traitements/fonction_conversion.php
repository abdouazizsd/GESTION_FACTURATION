<?php
    include '../../includes/config.php';
    include '../../includes/verifier_acces.php';
    
    function nombreEnLettres($nombre) {
    $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($f->format($nombre));
}
?>