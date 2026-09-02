<?php
// model.php
require_once __DIR__ . '/../config.php';

function getConnection()
{
    // Singleton : on crée la connexion une seule fois par requête HTTP
    // et on la réutilise pour tous les appels suivants.
    // Sans ça, chaque fonction model() ouvre une nouvelle PDO — ce qui
    // multiplie les handshakes TCP/socket et provoque des délais de 30-50s
    // si la résolution localhost → socket Unix est lente (cas fréquent sous Docker).
    static $cnx = null;

    if ($cnx !== null) {
        return $cnx;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $cnx = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        return $cnx;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

/**
 * Récupère les traductions pour plusieurs tours et les indexe par id.
 *
 * @param PDO $cnx     Objet de connexion PDO
 * @param array $tourIds  Tableau d'identifiants de tours (entiers)
 * @return array         Tableau associatif [tour_id => [locale => ['title'=>..., 'summary'=>...], ...], ...]
 */
function buildTourTranslationsIndex($cnx, $tourIds)
{
    // modifications rajoutées depuis l'ajout dans la BDD de `tagline`pour le sélectionner et le retourner
    // Si aucun id fourni, on renvoie un tableau vide immédiatement
    if (empty($tourIds)) {
        return [];
    }

    // Construire autant de placeholders ? que d'identifiants pour la requête IN(...)
    $placeholders = implode(',', array_fill(0, count($tourIds), '?')); //implode(separator,array) : permet de transformer éléments d'un tableau en string
//array_fill(start_index, num, value) : remplit un tableau avec une valeur répétée un nombre de fois défini
    // Sélectionner les colonnes utiles depuis la table de traduction
    $sql = 'SELECT tour_id, locale, title, tagline, summary
            FROM BT_Tour_Translation
            WHERE tour_id IN (' . $placeholders . ')
            ORDER BY tour_id DESC, locale ASC';

    $stmt = $cnx->prepare($sql);

    // Lier chaque id au placeholder correspondant de façon sécurisée
    foreach ($tourIds as $index => $tourId) { //pour chaque id tours on associe l'id du tour à l'index du placeholder dans la requete préparée
        $stmt->bindValue($index + 1, (int) $tourId, PDO::PARAM_INT);
    }

    // Exécuter la requête
    $stmt->execute();

    // Parcourir les résultats et construire un index { tour_id => { locale => {title, summary} } }
    $translationsByTour = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { //ici foreach permet de parcourir les résultats de la requete et de construire un tableau associatif avec comme clé l'id du tour et comme valeur un tableau associatif des traductions par langue
        $tourId = (int) $row['tour_id'];
        $locale = $row['locale'];

        if (!isset($translationsByTour[$tourId])) {//si le tour n'existe pas encore dans le tableau on l'initialise
            $translationsByTour[$tourId] = [];
        }

        $translationsByTour[$tourId][$locale] = [
            'title' => $row['title'],
            'summary' => $row['summary'],
            'tagline' => $row['tagline'] ?? null, // Utiliser null si tagline n'est pas défini
        ];
    }

    return $translationsByTour;
}

function getActiveTours($locale = 'en')
{
    $cnx = getConnection();
    if ($cnx === null) {
        return false;
    }
    //COALESCE : permet de retourner la première valeur non nulle parmi les arguments fournis. Ici, on essaye d'abord de récupérer la traduction demandée (tr_requested), si elle n'existe pas on tombe sur la traduction anglaise (tr_english), et si elle n'existe pas non plus on utilise les champs historiques de BT_Tour (t.title, t.summary) comme dernier recours.
    $sql = 'SELECT
                t.id,
                t.duration,
                t.price,
                t.group_type,
                t.image_url,
                COALESCE(tr_requested.tagline, tr_english.tagline, t.tagline) AS tagline,
                COALESCE(tr_requested.title, tr_english.title, t.title) AS title, 
                COALESCE(tr_requested.summary, tr_english.summary, t.summary) AS summary
            FROM BT_Tour t
            LEFT JOIN BT_Tour_Translation tr_requested
                ON tr_requested.tour_id = t.id AND tr_requested.locale = :locale
            LEFT JOIN BT_Tour_Translation tr_english
                ON tr_english.tour_id = t.id AND tr_english.locale = \'en\'
            WHERE t.is_active = 1
            ORDER BY t.id DESC';
    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':locale', $locale);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

function getAllTours($locale = null)
{
    $cnx = getConnection();
    if ($cnx === null) {
        return false;
    }

    // Si une langue est fournie, on retourne les tours avec fallback anglais / historique.
    if ($locale !== null) {
        $sql = 'SELECT
                    t.id,
                    t.duration,
                    t.price,
                    t.group_type,
                    t.is_active,
                    t.image_url,
                    COALESCE(tr_requested.tagline, tr_english.tagline, t.tagline) AS tagline,
                    COALESCE(tr_requested.title, tr_english.title, t.title) AS title,
                    COALESCE(tr_requested.summary, tr_english.summary, t.summary) AS summary
                FROM BT_Tour t
                LEFT JOIN BT_Tour_Translation tr_requested
                    ON tr_requested.tour_id = t.id AND tr_requested.locale = :locale
                LEFT JOIN BT_Tour_Translation tr_english
                    ON tr_english.tour_id = t.id AND tr_english.locale = \'en\'
                ORDER BY t.id DESC';
        $stmt = $cnx->prepare($sql);
        $stmt->bindParam(':locale', $locale);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    $sql = 'SELECT t.id, t.title, t.tagline, t.summary, t.duration, t.price, t.is_active, t.image_url, t.group_type
            FROM BT_Tour t
            ORDER BY t.id DESC';
    $stmt = $cnx->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    $tourIds = [];
    foreach ($results as $tour) {
        $tourIds[] = (int) $tour->id;
    }

    $translationsByTour = buildTourTranslationsIndex($cnx, $tourIds);

    // Construire une structure exploitable par l'admin.
    foreach ($results as &$tour) {
        $tourId = (int) $tour->id;
        $tour->translations = $translationsByTour[$tourId] ?? [];

        if (!empty($tour->translations['en'])) {
            $tour->title = $tour->translations['en']['title'];
            $tour->summary = $tour->translations['en']['summary'];
            $tour->tagline = $tour->translations['en']['tagline'];
            continue;
        }

        if (!empty($tour->translations)) {
            $firstLocale = array_key_first($tour->translations);
            $tour->title = $tour->translations[$firstLocale]['title'];
            $tour->summary = $tour->translations[$firstLocale]['summary'];
            $tour->tagline = $tour->translations[$firstLocale]['tagline'];
        }
    }

    return $results;
}

function addTour($duration, $price, $groupType, $translations)
{
    $cnx = getConnection();
    if ($cnx === null) {
        return false;
    }

    if (empty($translations['en'])) {
        return false;
    }

    try {
        $cnx->beginTransaction();

        // On conserve les colonnes historiques de BT_Tour comme fallback anglais.
        $englishTitle = $translations['en']['title'];
        $englishSummary = $translations['en']['summary'];
        $englishTagline = $translations['en']['tagline'] ?? null;
        $sql = 'INSERT INTO BT_Tour (title, duration, price, group_type, summary, tagline, is_active)
                VALUES (:title, :duration, :price, :group_type, :summary, :tagline, 1)';
        $stmt = $cnx->prepare($sql);
        $stmt->bindValue(':title', $englishTitle);
        $stmt->bindValue(':duration', $duration);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':group_type', $groupType);
        $stmt->bindValue(':summary', $englishSummary);
        $stmt->bindValue(':tagline', $englishTagline);

        if (!$stmt->execute()) {
            $cnx->rollBack();
            return false;
        }

        $tourId = $cnx->lastInsertId();

        foreach ($translations as $locale => $data) {
            if (empty($data['title']) || empty($data['summary'])) {
                continue;
            }
            if (empty($data['tagline'])) {
                $data['tagline'] = null;
            }

            $sqlTrans = 'INSERT INTO BT_Tour_Translation (tour_id, locale, title, tagline, summary)
                         VALUES (:tour_id, :locale, :title, :tagline, :summary)';
            $stmtTrans = $cnx->prepare($sqlTrans);
            $stmtTrans->bindValue(':tour_id', $tourId, PDO::PARAM_INT);
            $stmtTrans->bindValue(':locale', $locale);
            $stmtTrans->bindValue(':title', $data['title']);
            $stmtTrans->bindValue(':tagline', $data['tagline']);
            $stmtTrans->bindValue(':summary', $data['summary']);

            if (!$stmtTrans->execute()) {
                $cnx->rollBack();
                return false;
            }
        }

        $cnx->commit();
        return true;
    } catch (Exception $e) {
        if ($cnx->inTransaction()) {
            $cnx->rollBack(); // rollBack permet d'annuler toutes les opérations effectuées depuis le début de la transaction en cas d'erreur, assurant ainsi l'intégrité des données et évitant les états partiellement mis à jour.
            // fichier que j'avias lu sur mdn et php sur les PDO et les transactions : https://www.php.net/manual/fr/pdo.transactions.php
        }
        error_log($e->getMessage());
        return false;
    }
}
//ajout de tagline dans la fonction updateTour pour permettre la mise à jour de cette colonne
function updateTour(
    $id,
    $duration,
    $price,
    $groupType,
    $is_active,
    $locale,
    $title,
    $summary,
    $tagline
) {
    $cnx = getConnection();
    if ($cnx === null) {
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.connection_failed');
        }
        return false;
    }

    try {
        // Ces traces servent uniquement à comprendre à quel endroit la mise à jour échoue.
        // Elles ne modifient pas la logique métier, seulement le diagnostic visible dans l'admin.
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.enter', [
                'id' => $id,
                'duration' => $duration,
                'price' => $price,
                'is_active' => $is_active,
                'localeType' => is_array($locale) ? 'array' : gettype($locale),
                'translationCount' => is_array($locale) ? count($locale) : 0,
            ]);
        }

        $cnx->beginTransaction();
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.transaction_started');
        }

        // Support dual-mode :
        // - $locale string + $title/$summary => simple update d'une langue
        // - $locale array (map locale => ['title', 'summary']) => mise à jour multi-locales
        if (is_array($locale)) {
            $translations = $locale;
            if (function_exists('updateTourDebugAdd')) {
                updateTourDebugAdd('model.translations_from_array', ['locales' => array_keys($translations)]);
            }
        } else {
            $translations = [];
            if (is_string($locale) && $locale !== '' && $title !== '' && $summary !== '' && $tagline !== '') {
                $translations[$locale] = ['title' => $title, 'summary' => $summary, 'tagline' => $tagline];
                if (function_exists('updateTourDebugAdd')) {
                    updateTourDebugAdd('model.translation_from_single_locale', ['locale' => $locale]);
                }
            }
        }

        // Récupérer les valeurs existantes pour BT_Tour
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.fetch_existing.start');
        }
        $stmtFetch = $cnx->prepare('SELECT title, tagline, summary FROM BT_Tour WHERE id = :id');
        $stmtFetch->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtFetch->execute();
        $existing = $stmtFetch->fetch(PDO::FETCH_ASSOC);
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.fetch_existing.done', ['found' => $existing !== false]);
        }

        // Si une traduction anglaise est fournie, l'utiliser comme legacy, sinon conserver l'existant
        $titleForReplace = $translations['en']['title'] ?? ($existing['title'] ?? '');
        $taglineForReplace = $translations['en']['tagline'] ?? ($existing['tagline'] ?? '');
        $summaryForReplace = $translations['en']['summary'] ?? ($existing['summary'] ?? '');

        // REPLACE INTO pour mettre à jour la ligne BT_Tour atomiquement
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.update_main.start');
        }
        $sqlUpdate = 'UPDATE BT_Tour
              SET title = :title,
                  duration = :duration,
                  price = :price,
                  group_type = :group_type,
                  tagline = :tagline,
                  summary = :summary,
                  is_active = :is_active
              WHERE id = :id';
        $stmtUpdate = $cnx->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':title', $titleForReplace);
        $stmtUpdate->bindValue(':duration', $duration);
        $stmtUpdate->bindValue(':price', $price);
        $stmtUpdate->bindValue(':group_type', $groupType);
        $stmtUpdate->bindValue(':tagline', $taglineForReplace);
        $stmtUpdate->bindValue(':summary', $summaryForReplace);
        $stmtUpdate->bindValue(':is_active', $is_active, PDO::PARAM_INT);

        if (!$stmtUpdate->execute()) {
            if (function_exists('updateTourDebugAdd')) {
                updateTourDebugAdd('model.update_main.failed');
            }
            $cnx->rollBack();
            return false;
        }
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.update_main.done');
        }

        // Upsert des traductions (pour chaque locale fournie)
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.translations_upsert.start', ['count' => count($translations)]);
        }
        $sqlTrans = 'INSERT INTO BT_Tour_Translation (tour_id, locale, title, tagline, summary)
                 VALUES (:tour_id, :locale, :title, :tagline, :summary)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), tagline = VALUES(tagline), summary = VALUES(summary)';
        $stmtTrans = $cnx->prepare($sqlTrans);

        foreach ($translations as $loc => $data) {
            if (!is_array($data) || empty($data['title']) || empty($data['summary'])) {
                if (function_exists('updateTourDebugAdd')) {
                    updateTourDebugAdd('model.translation_skipped', ['locale' => $loc]);
                }
                continue;
            }
            if (function_exists('updateTourDebugAdd')) {
                updateTourDebugAdd('model.translation_upsert.start', ['locale' => $loc]);
            }
            $stmtTrans->bindValue(':tour_id', $id, PDO::PARAM_INT);
            $stmtTrans->bindValue(':locale', $loc);
            $stmtTrans->bindValue(':title', $data['title']);
            $stmtTrans->bindValue(':tagline', $data['tagline']);
            $stmtTrans->bindValue(':summary', $data['summary']);

            if (!$stmtTrans->execute()) {
                if (function_exists('updateTourDebugAdd')) {
                    updateTourDebugAdd('model.translation_upsert.failed', ['locale' => $loc]);
                }
                $cnx->rollBack();
                return false;
            }
            if (function_exists('updateTourDebugAdd')) {
                updateTourDebugAdd('model.translation_upsert.done', ['locale' => $loc]);
            }
        }

        $cnx->commit();
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.commit.done');
        }
        return true;
    } catch (\Throwable $e) {
        if ($cnx->inTransaction()) {
            $cnx->rollBack();
        }
        if (function_exists('updateTourDebugAdd')) {
            updateTourDebugAdd('model.exception', ['message' => $e->getMessage()]);
        }
        return false;
    }
}

function getAllMonuments()
{
    $cnx = getConnection();
    $sql = 'SELECT id, name, district, description, image_url FROM BT_Monument ORDER BY name ASC';
    $stmt = $cnx->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

function addMessage($fullname, $email, $message)
{
    $cnx = getConnection();
    $sql = 'INSERT INTO BT_Message (fullname, email, message) VALUES (:fullname, :email, :message)';
    $stmt = $cnx->prepare($sql);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':message', $message);
    return $stmt->execute();
}

function getAllMessages()
{
    $cnx = getConnection();
    $sql = 'SELECT id, fullname, email, message, created_at FROM BT_Message ORDER BY created_at DESC';
    $stmt = $cnx->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>