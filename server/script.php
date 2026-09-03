<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function ($e) {
    error_log('[script.php] Uncaught exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error.',
    ]);
    exit();
});

set_error_handler(function ($severity, $message, $file, $line) {
    // Convertit les warnings/notices en exceptions pour garder une sortie JSON propre.
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require('controller.php');

if (isset($_REQUEST['todo'])) {
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
        case 'listimages':
            $data = listImagesController();
            break;
        case 'uploadimage':
            $data = uploadImageController();
            break;
        case 'adminmonuments':
            $data = readAdminMonumentsController();
            break;

        case 'updatemonument':
            $data = updateMonumentController();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown todo value']);
            http_response_code(400);
            exit();
    }

    if ($data === false) {
        echo json_encode(['success' => false, 'message' => 'Controller returns false']);
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
echo json_encode(['success' => false, 'message' => 'Not found']);
?>