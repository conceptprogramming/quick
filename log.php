<?php

$paths = [
"/home/u351046149/.logs/error_log",
"/home/u351046149/logs/error_log",
"/home/u351046149/domains/quikchat.pdfconvertor.online/logs/error.log"
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "<h3>$path</h3>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($path));
        echo "</pre>";
    }
}

?>