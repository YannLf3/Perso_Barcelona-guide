<?php
require('model.php');

const ADMIN_PASSWORD = 'barcelona2026';
const SUPPORTED_LOCALES = ['en', 'es', 'fr', 'it'];

// Petit journal de debug attaché à la requête courante.
// Il permet de renvoyer dans la réponse JSON les étapes traversées par updatetour.
function updateTourDebugReset()
{
    $GLOBALS['UPDATE_TOUR_DEBUG'] = [];
}

// Ajoute une étape lisible dans le journal de debug.
// On l'utilise uniquement pour diagnostiquer la requête update.
function updateTourDebugAdd($step, $context = [])
{
    $payload = $step;
    if (!empty($context)) {
        $payload .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    $GLOBALS['UPDATE_TOUR_DEBUG'][] = $payload;
}

//ajout dans controller.php de la récupération de <?php tagline_en _es _fr _it puis transmettre tagline aux fonctions du modèle. 



// Retourne le journal de debug sous forme de tableau.
// Le contrôleur le renvoie dans la réponse JSON pour l'afficher dans l'admin.
function updateTourDebugGet()
{
    return $GLOBALS['UPDATE_TOUR_DEBUG'] ?? [];
}

function validateLocale($locale, $strict = false)
{
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

function getLocalizedMessage($locale, $key)
{
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

function requireAdminPassword()
{
    $password = $_REQUEST['password'] ?? null;
    if ($password === null || $password === '') {
        return false;
    }
    return hash_equals(ADMIN_PASSWORD, $password); //bonne pratique de crypto et sécurisation hash_equals : évite les attaques par timing car la fct dit si ok ou pas après avoir tout parcouru la chaine contrairement à ===
}

function readToursController()
{
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

function readMonumentsController()
{
    $monuments = getAllMonuments();
    if ($monuments === false || $monuments === null) {
        return false;
    }
    return $monuments;
}

function addMessageController()
{
    $locale = validateLocale($_REQUEST['lang'] ?? null);
    $fullname = $_REQUEST['fullname'] ?? null;
    $email = $_REQUEST['email'] ?? null;
    $message = $_REQUEST['message'] ?? null;

    if (
        $fullname === null || trim($fullname) === '' ||
        $email === null || trim($email) === '' ||
        $message === null || trim($message) === ''
    ) {
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

function adminLoginController()
{
    if (!requireAdminPassword()) {
        return 'Invalid password.';
    }
    return 'Login success.';
}

function readAdminToursController()
{
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

function addTourController()
{
    if (!requireAdminPassword()) {
        return false;
    }

    $duration = trim((string) ($_REQUEST['duration'] ?? ''));
    $capacity = trim((string) ($_REQUEST['capacity'] ?? $_REQUEST['price'] ?? ''));
    $groupType = $_REQUEST['group_type'] ?? 'small';
    $imageUrl = trim((string) ($_REQUEST['image_url'] ?? ''));
    $allowedGroupTypes = ['private', 'small', 'school'];

    if (
        $imageUrl !== '' &&
        (
            basename($imageUrl) !== $imageUrl ||
            !preg_match('/^[a-zA-Z0-9._-]+$/', $imageUrl) ||
            !is_file(__DIR__ . '/../Images/' . $imageUrl)
        )
    ) {
        return [
            'success' => false,
            'message' => 'Invalid image name.',
            'statusCode' => 400
        ];
    }

    if (!in_array($groupType, $allowedGroupTypes, true)) {
        return false;
    }

    // Récupérer les traductions pour chaque langue
    $translations = [];
    foreach (SUPPORTED_LOCALES as $locale) {
        $title = trim((string) ($_REQUEST['title_' . $locale] ?? ''));
        $tagline = trim((string) ($_REQUEST['tagline_' . $locale] ?? ''));

        $summary = trim((string) ($_REQUEST['summary_' . $locale] ?? ''));

        // Au minimum l'anglais est obligatoire
        if ($locale === 'en' && ($title === '' || $tagline === '' || $summary === '')) {
            return false;
        }

        // Si une traduction est fournie, on l'ajoute
        if ($title !== '' && $tagline !== '' && $summary !== '') {
            $translations[$locale] = [
                'title' => $title,
                'tagline' => $tagline,
                'summary' => $summary
            ];
        }
    }

    if (
        $duration === '' ||
        $capacity === '' ||
        $groupType === '' ||
        empty($translations)
    ) {
        return false;
    }

    $ok = addTour(
        $duration,
        $capacity,
        $groupType,
        $translations,
        $imageUrl
    );
    if ($ok) {
        return 'Tour added successfully.';
    }

    return 'Failed to add tour.';
}

function updateTourController()
{
    updateTourDebugReset();
    updateTourDebugAdd('controller.enter', [
        'hasPassword' => isset($_REQUEST['password']) && $_REQUEST['password'] !== '',
        'requestKeys' => array_keys($_REQUEST),
    ]);

    if (!requireAdminPassword()) {
        updateTourDebugAdd('controller.reject.password');
        return ['success' => false, 'message' => 'Unauthorized.', 'statusCode' => 401, 'debug' => updateTourDebugGet()];
    }
    $idStr = trim((string) ($_REQUEST['id'] ?? ''));
    $duration = trim((string) ($_REQUEST['duration'] ?? ''));
    $capacity = trim((string) ($_REQUEST['capacity'] ?? $_REQUEST['price'] ?? ''));
    $groupType = $_REQUEST['group_type'] ?? 'small';
    $allowedGroupTypes = ['private', 'small', 'school'];
    $imageUrl = trim((string) ($_REQUEST['image_url'] ?? ''));

    if (!in_array($groupType, $allowedGroupTypes, true)) {
        return [
            'success' => false,
            'message' => 'Invalid group type.',
            'statusCode' => 400
        ];
    }
    $is_active = (int) ($_REQUEST['is_active'] ?? 0);
    $locale = validateLocale($_REQUEST['locale'] ?? null, true);
    $title = trim((string) ($_REQUEST['title'] ?? ''));
    $tagline = trim((string) ($_REQUEST['tagline'] ?? ''));
    $summary = trim((string) ($_REQUEST['summary'] ?? ''));

    updateTourDebugAdd('controller.payload', [
        'idStr' => $idStr,
        'duration' => $duration,
        'capacity' => $capacity,
        'groupType' => $groupType,
        'is_active' => $is_active,
        'locale' => $locale,
        'taglineLength' => strlen($tagline),
        'titleLength' => strlen($title),
        'summaryLength' => strlen($summary),
    ]);

    if ($idStr === '' || !ctype_digit($idStr)) {
        updateTourDebugAdd('controller.reject.invalid_id');
        return ['success' => false, 'message' => 'Invalid tour id.', 'statusCode' => 400, 'debug' => updateTourDebugGet()];
    }
    $id = (int) $idStr;

    if ($duration === '' || $capacity === '') {
        updateTourDebugAdd('controller.reject.missing_common_fields');
        return ['success' => false, 'message' => 'Please fill in all fields.', 'statusCode' => 400, 'debug' => updateTourDebugGet()];
    }

    // Construire tableau de traductions s'il y en a (title_en/summary_en, title_fr/...)
    $translations = [];
    foreach (SUPPORTED_LOCALES as $loc) {
        $t = trim((string) ($_REQUEST['title_' . $loc] ?? ''));
        $s = trim((string) ($_REQUEST['summary_' . $loc] ?? ''));
        $g = trim((string) ($_REQUEST['tagline_' . $loc] ?? ''));
        if ($t !== '' && $s !== '' && $g !== '') {
            $translations[$loc] = ['title' => $t, 'summary' => $s, 'tagline' => $g];
        }
    }

    updateTourDebugAdd('controller.translations.scanned', [
        'count' => count($translations),
        'locales' => array_keys($translations),
    ]);

    // Sinon fallback vers API single-locale pour compatibilité
    if ($locale !== null && $title !== '' && $tagline !== '' && $summary !== '' && !isset($translations[$locale])) {
        $translations[$locale] = ['title' => $title, 'summary' => $summary, 'tagline' => $tagline];
        updateTourDebugAdd('controller.translation.injected_from_single_locale', [
            'locale' => $locale,
        ]);
    }


    if (!empty($translations)) {
        updateTourDebugAdd('controller.call_model', [
            'id' => $id,
            'translationCount' => count($translations),
        ]);

        if (
            $imageUrl !== '' &&
            (
                basename($imageUrl) !== $imageUrl ||
                !preg_match('/^[a-zA-Z0-9._-]+$/', $imageUrl) ||
                !is_file(__DIR__ . '/../Images/' . $imageUrl)
            )
        ) {
            return [
                'success' => false,
                'message' => 'Invalid image name.',
                'statusCode' => 400
            ];
        }

        $ok = updateTour(
            $id,
            $duration,
            $capacity,
            $groupType,
            $is_active,
            $translations,
            '',
            '',
            '',
            $imageUrl
        );

        updateTourDebugAdd('controller.model_return', [
            'ok' => $ok,
        ]);



        if ($ok) {
            updateTourDebugAdd('controller.success');
            return [
                'success' => true,
                'message' => 'Tour updated successfully.',
                'debug' => updateTourDebugGet()
            ];
        }

        updateTourDebugAdd('controller.failure');

        return [
            'success' => false,
            'message' => 'Failed to update tour.',
            'statusCode' => 500,
            'debug' => updateTourDebugGet()
        ];
    }
}

function readAdminMonumentsController()
{
    if (!requireAdminPassword()) {
        return [
            'success' => false,
            'message' => 'Unauthorized.',
            'statusCode' => 401
        ];
    }

    $monuments = getAllMonumentsAdmin();

    if ($monuments === false || $monuments === null) {
        return [
            'success' => false,
            'message' => 'Unable to load monuments.',
            'statusCode' => 500
        ];
    }

    return $monuments;
}

function updateMonumentController()
{
    if (!requireAdminPassword()) {
        return [
            'success' => false,
            'message' => 'Unauthorized.',
            'statusCode' => 401
        ];
    }

    $idString = trim((string) ($_REQUEST['id'] ?? ''));

    $name = trim((string) ($_REQUEST['name'] ?? ''));
    $district = trim((string) ($_REQUEST['district'] ?? ''));
    $description = trim((string) ($_REQUEST['description'] ?? ''));
    $imageUrl = trim((string) ($_REQUEST['image_url'] ?? ''));

    if ($idString === '' || !ctype_digit($idString)) {
        return [
            'success' => false,
            'message' => 'Invalid monument id.',
            'statusCode' => 400
        ];
    }

    if ($name === '' || $district === '' || $description === '') {
        return [
            'success' => false,
            'message' => 'Please fill in all fields.',
            'statusCode' => 400
        ];
    }

    /*
     * L'image est facultative :
     * - une chaîne vide signifie qu'aucune image n'est associée ;
     * - sinon, on accepte uniquement un nom de fichier simple ;
     * - le fichier doit réellement exister dans /Images.
     */
    if ($imageUrl !== '') {
        $isSafeFileName = preg_match(
            '/^[a-zA-Z0-9._-]+$/', // signifie que le nom de fichier ne doit contenir que des lettres, des chiffres, des points, des tirets et des underscores
            $imageUrl
        );

        $imagePath = __DIR__ . '/../Images/' . $imageUrl;

        if (
            $isSafeFileName !== 1 ||
            !is_file($imagePath)
        ) {
            return [
                'success' => false,
                'message' => 'Invalid image.',
                'statusCode' => 400
            ];
        }
    }

    $id = (int) $idString;

    $ok = updateMonument(
        $id,
        $name,
        $district,
        $description,
        $imageUrl
    );

    if (!$ok) {
        return [
            'success' => false,
            'message' => 'Failed to update monument.',
            'statusCode' => 500
        ];
    }

    return [
        'success' => true,
        'message' => 'Monument updated successfully.'
    ];
}

function readMessagesController()
{
    if (!requireAdminPassword()) {
        return false;
    }

    $messages = getAllMessages();
    if ($messages === false || $messages === null) {
        return false;
    }
    return $messages;
}


function listImagesController()
{
    if (!requireAdminPassword()) {
        return [
            'success' => false,
            'message' => 'Unauthorized.',
            'statusCode' => 401
        ];
    }

    $directory = __DIR__ . '/../Images/';
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $images = [];

    foreach (scandir($directory) as $fileName) {
        $filePath = $directory . $fileName;

        if (!is_file($filePath)) {
            continue;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($extension, $allowedExtensions, true)) {
            $images[] = $fileName;
        }
    }

    sort($images);

    return $images;
}


function uploadImageController()
{
    if (!requireAdminPassword()) {
        return [
            'success' => false,
            'message' => 'Unauthorized.',
            'statusCode' => 401
        ];
    }

    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return [
            'success' => false,
            'message' => 'No image selected.',
            'statusCode' => 400
        ];
    }

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Upload error.',
            'statusCode' => 400
        ];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // limite de 5Mo car $file['size'] est en octets donc >5*1024*1024 permet de limiter la taille du fichier à 5Mo
        return [
            'success' => false,
            'message' => 'Image is too large.',
            'statusCode' => 400
        ];
    }

    $mimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file['tmp_name']);

    if (!isset($mimeTypes[$mimeType])) {
        return [
            'success' => false,
            'message' => 'Invalid image type.',
            'statusCode' => 400
        ];
    }

    $fileName = bin2hex(random_bytes(16))
        . '.'
        . $mimeTypes[$mimeType];

    $destination = __DIR__ . '/../Images/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => false,
            'message' => 'Unable to save image.',
            'statusCode' => 500
        ];
    }

    return [
        'success' => true,
        'fileName' => $fileName
    ];
}
