<?php

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function flash()
{
    if (!empty($_SESSION['flash'])) {

        $f = $_SESSION['flash'];

        unset($_SESSION['flash']);

        echo '<div class="alert ' .
             e($f['type']) .
             '">' .
             e($f['message']) .
             '</div>';
    }
}

?>