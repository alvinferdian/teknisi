<?php
function renderView($view, $data = []) {
    extract($data);
    $viewPath = __DIR__ . '/../View/templates/' . $view . '.php';
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new Exception("View not found: " . $view);
    }
}

function partial($partial, $data = []) {
    extract($data);
    $partialPath = __DIR__ . '/../View/templates/' . $partial . '.php';
    
    if (file_exists($partialPath)) {
        include $partialPath;
    } else {
        throw new Exception("Partial not found: " . $partial);
    }
}
?>