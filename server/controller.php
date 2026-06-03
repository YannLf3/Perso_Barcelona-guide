<?php
require('model.php');

const ADMIN_PASSWORD = 'barcelona2026';
const SUPPORTED_LOCALES = ['en', 'es', 'fr', 'it'];

// Petit journal de debug attaché à la requête courante.
// Il permet de renvoyer dans la réponse JSON les étapes traversées par updatetour.
function updateTourDebugReset() {
    $GLOBALS['UPDATE_TOUR_DEBUG'] = [];
}

// Ajoute une étape lisible dans le journal de debug.
// On l'utilise uniquement pour diagnostiquer la requête update.
function updateTourDebugAdd($step, $context = []) {
    $payload = $step;
    if (!empty($context)) {
        $payload .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $GLOBALS['UPDATE_TOUR_DEBUG'][] = $payload;
}

// Retourne le journal de debug sous forme de tableau.
// Le contrôleur le renvoie dans la réponse JSON pour l'afficher dans l'admin.
function updateTourDebugGet() {
    return $GLOBALS['UPDATE_TOUR_DEBUG'] ?? [];
}

function validateLocale($locale, $strict = false) {
    // Valide que le locale fait partie des langues supportées.
    // En mode strict, une langue invalide retourne null au lieu d'un fallback silencieux.
    if ($locale === null || $locale === '') {
        return $strict ? null : 'en';
    }
    if (in_array($locale, SUPPORTED_LOCALES, true)) {
        return $locale;
    }
    return $strict ? null : 'en';
}

function getLocalizedMessage($locale, $key) {
    $messages = [
        'en' => [
            'invalidLocale' => 'Invalid locale.',
            'missingFields' => 'Please fill in all fields.',
            'invalidEmail' => 'Please provide a valid email address.',
            'messageSuccess' => 'Your message has been sent successfully. We will contact you soon!',
            'messageError' => 'An error occurred while saving your message.',
        ],
        'es' => [
            'invalidLocale' => 'Idioma no válido.',
            'missingFields' => 'Por favor, rellena todos los campos.',
            'invalidEmail' => 'Por favor, proporciona una dirección de email válida.',
            'messageSuccess' => 'Tu mensaje se ha enviado correctamente. ¡Te contactaremos pronto!',
            'messageError' => 'Ocurrió un error al guardar tu mensaje.',
        ],
        'fr' => [
            'invalidLocale' => 'Langue invalide.',
            'missingFields' => 'Veuillez remplir tous les champs.',
            'invalidEmail' => 'Veuillez fournir une adresse email valide.',
            'messageSuccess' => 'Votre message a été envoyé avec succès. Nous vous contacterons bientôt !',
            'messageError' => 'Une erreur est survenue lors de l\'enregistrement de votre message.',
        ],
        'it' => [
            'invalidLocale' => 'Lingua non valida.',
            'missingFields' => 'Compila tutti i campi.',
            'invalidEmail' => 'Fornisci un indirizzo email valido.',
            'messageSuccess' => 'Il tuo messaggio è stato inviato con successo. Ti contatteremo presto!',
            'messageError' => 'Si è verificato un errore durante il salvataggio del tuo messaggio.',
        ],
    ];

    $fallbackLocale = in_array($locale, SUPPORTED_LOCALES) ? $locale : 'en';
    return $messages[$fallbackLocale][$key] ?? $messages['en'][$key] ?? $key;
}

function requireAdminPassword() {
    $password = $_REQUEST['password'] ?? null;
    if ($password === null || $password === '') {
        return false;
    }
    return hash_equals(ADMIN_PASSWORD, $password); //bonne pratique de crypto et sécurisation hash_equals : évite les attaques par timing car la fct dit si ok ou pas après avoir tout parcouru la chaine contrairement à ===
}

function readToursController() {
    $rawLocale = $_REQUEST['lang'] ?? null;
    $locale = validateLocale($rawLocale, $rawLocale !== null);

    if ($rawLocale !== null && $locale === null) {
        return ['success' => false, 'message' => getLocalizedMessage('en', 'invalidLocale'), 'statusCode' => 400];
    }

    $tours = getActiveTours($locale);
    if ($tours === false || $tours === null) {
        return false;
    }
    return $tours;
}

function readMonumentsController() {
    $monuments = getAllMonuments();
    if ($monuments === false || $monuments === null) {
        return false;
    }
    return $monuments;
}

function addMessageController() {
    $locale = validateLocale($_REQUEST['lang'] ?? null);
    $fullname = $_REQUEST['fullname'] ?? null;
    $email = $_REQUEST['email'] ?? null;
    $message = $_REQUEST['message'] ?? null;

    if ($fullname === null || trim($fullname) === '' || 
        $email === null || trim($email) === '' ||
        $message === null || trim($message) === '') {
        return ['success' => false, 'message' => getLocalizedMessage($locale, 'missingFields')];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => getLocalizedMessage($locale, 'invalidEmail')];
    }

    $ok = addMessage($fullname, $email, $message);
    if ($ok) {
        return ['success' => true, 'message' => getLocalizedMessage($locale, 'messageSuccess')];
    }

    return ['success' => false, 'message' => getLocalizedMessage($locale, 'messageError')];
}

function adminLoginController() {
    if (!requireAdminPassword()) {
        return 'Invalid password.';
    }
    return 'Login success.';
}

function readAdminToursController() {
    if (!requireAdminPassword()) {
        return false;
    }

    // Pour l'admin, on peut passer un paramètre lang pour filtrer par langue
    // Si pas de lang, on retourne toutes les traductions groupées
    $locale = $_REQUEST['lang'] ?? null;
    if ($locale !== null) {
        $validatedLocale = validateLocale($locale, true);
        if ($validatedLocale === null) {
            return ['success' => false, 'message' => getLocalizedMessage('en', 'invalidLocale'), 'statusCode' => 400];
        }
        $locale = $validatedLocale;
    }
    
    $tours = getAllTours($locale);
    if ($tours === false || $tours === null) {
        return false;
    }
    return $tours;
}

function addTourController() {
    if (!requireAdminPassword()) {
        return false;
    }

    $duration = trim((string)($_REQUEST['duration'] ?? ''));
    $price = trim((string)($_REQUEST['price'] ?? ''));
    
    // Récupérer les traductions pour chaque langue
    $translations = [];
    foreach (SUPPORTED_LOCALES as $locale) {
        $title = trim((string)($_REQUEST['title_' . $locale] ?? ''));
        $summary = trim((string)($_REQUEST['summary_' . $locale] ?? ''));
        
        // Au minimum l'anglais est obligatoire
        if ($locale === 'en' && ($title === '' || $summary === '')) {
            return false;
        }
        
        // Si une traduction est fournie, on l'ajoute
        if ($title !== '' && $summary !== '') {
            $translations[$locale] = [
                'title' => $title,
                'summary' => $summary
            ];
        }
    }

    if ($duration === '' ||
        $price === '' ||
        empty($translations)) {
        return false;
    }

    $ok = addTour($duration, $price, $translations);
    if ($ok) {
        return 'Tour added successfully.';
    }

    return 'Failed to add tour.';
}

function updateTourController() {
    updateTourDebugReset();
    updateTourDebugAdd('controller.enter', [
        'hasPassword' => isset($_REQUEST['password']) && $_REQUEST['password'] !== '',
        'requestKeys' => array_keys($_REQUEST),
    ]);

    if (!requireAdminPassword()) {
        updateTourDebugAdd('controller.reject.password');
        return ['success' => false, 'message' => 'Unauthorized.', 'statusCode' => 401, 'debug' => updateTourDebugGet()];
    }
    $idStr = trim((string)($_REQUEST['id'] ?? ''));
    $duration = trim((string)($_REQUEST['duration'] ?? ''));
    $price = trim((string)($_REQUEST['price'] ?? ''));
    $is_active = (int)($_REQUEST['is_active'] ?? 0);
    $locale = validateLocale($_REQUEST['locale'] ?? null, true);
    $title = trim((string)($_REQUEST['title'] ?? ''));
    $summary = trim((string)($_REQUEST['summary'] ?? ''));

    updateTourDebugAdd('controller.payload', [
        'idStr' => $idStr,
        'duration' => $duration,
        'price' => $price,
        'is_active' => $is_active,
        'locale' => $locale,
        'titleLength' => strlen($title),
        'summaryLength' => strlen($summary),
    ]);

    if ($idStr === '' || !ctype_digit($idStr)) {
        updateTourDebugAdd('controller.reject.invalid_id');
        return ['success' => false, 'message' => 'Invalid tour id.', 'statusCode' => 400, 'debug' => updateTourDebugGet()];
    }
    $id = (int)$idStr;

    if ($duration === '' || $price === '') {
        updateTourDebugAdd('controller.reject.missing_common_fields');
        return ['success' => false, 'message' => 'Please fill in all fields.', 'statusCode' => 400, 'debug' => updateTourDebugGet()];
    }

    // Construire tableau de traductions s'il y en a (title_en/summary_en, title_fr/...)
    $translations = [];
    foreach (SUPPORTED_LOCALES as $loc) {
        $t = trim((string)($_REQUEST['title_' . $loc] ?? ''));
        $s = trim((string)($_REQUEST['summary_' . $loc] ?? ''));
        if ($t !== '' && $s !== '') {
            $translations[$loc] = ['title' => $t, 'summary' => $s];
        }
    }

    updateTourDebugAdd('controller.translations.scanned', [
        'count' => count($translations),
        'locales' => array_keys($translations),
    ]);

    // Sinon fallback vers API single-locale pour compatibilité
    if ($locale !== null && $title !== '' && $summary !== '' && !isset($translations[$locale])) {
        $translations[$locale] = ['title' => $title, 'summary' => $summary];
        updateTourDebugAdd('controller.translation.injected_from_single_locale', [
            'locale' => $locale,
        ]);
    }

    if (!empty($translations)) {
        updateTourDebugAdd('controller.call_model', [
            'id' => $id,
            'translationCount' => count($translations),
        ]);
        $ok = updateTour($id, $duration, $price, $is_active, $translations, '', '');
        updateTourDebugAdd('controller.model_return', [
            'ok' => $ok,
        ]);
        if ($ok) {
            updateTourDebugAdd('controller.success');
            return ['success' => true, 'message' => 'Tour updated successfully.', 'debug' => updateTourDebugGet()];
        }
        updateTourDebugAdd('controller.failure');
        return ['success' => false, 'message' => 'Failed to update tour.', 'statusCode' => 500, 'debug' => updateTourDebugGet()];
    }

    updateTourDebugAdd('controller.reject.no_translation_data');
    return ['success' => false, 'message' => 'No translation data provided.', 'statusCode' => 400, 'debug' => updateTourDebugGet()];
}

function readMessagesController() {
    if (!requireAdminPassword()) {
        return false;
    }

    $messages = getAllMessages();
    if ($messages === false || $messages === null) {
        return false;
    }
    return $messages;
}
?>
