<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('controller.php');

if (isset($_REQUEST['todo'])) {
    header('Content-Type: application/json');
    $todo = $_REQUEST['todo'];

    switch ($todo) {
        case 'readtours':
            $data = readToursController();
            break;
        case 'readmonuments':
            $data = readMonumentsController();
            break;
        case 'addmessage':
            $data = addMessageController();
            break;
        case 'adminlogin':
            $data = adminLoginController();
            break;
        case 'admintours':
            $data = readAdminToursController();
            break;
        case 'addtour':
            $data = addTourController();
            break;
        case 'updatetour':
            $data = updateTourController();
            break;
        case 'readmessages':
            $data = readMessagesController();
            break;
        default:
            echo json_encode('[error] Unknown todo value');
            http_response_code(400);
            exit();
    }

    if ($data === false) {
        echo json_encode('[error] Controller returns false');
        http_response_code(500);
        exit();
    }

    $statusCode = 200;
    if (is_array($data) && array_key_exists('statusCode', $data)) {
        $statusCode = (int) $data['statusCode'];
        unset($data['statusCode']);
    }

    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

http_response_code(404);
?>
