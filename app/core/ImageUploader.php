<?php

declare(strict_types=1);

/**
 * Gestion des images : liste, résolution d'URL, dimensions et envoi sécurisé.
 */
final class ImageUploader
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_DIMENSION = 8000;
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const MIME_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private static function imagesDir(): string
    {
        return BASE_PATH . '/public/assets/images/';
    }

    private static function uploadsDir(): string
    {
        return BASE_PATH . '/public/assets/uploads/';
    }

    /**
     * Vérifie qu'un nom est un simple nom de fichier image (pas de chemin, pas de caractère exotique).
     */
    public static function isSafeName(string $name): bool
    {
        return preg_match('/^[a-zA-Z0-9._-]+$/', $name) === 1
            && basename($name) === $name
            && in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::EXTENSIONS, true);
    }

    /**
     * Vérifie qu'une image existe dans le dossier statique ou dans celui des envois.
     */
    public static function exists(string $name): bool
    {
        return self::isSafeName($name) && self::locate($name) !== null;
    }

    /**
     * Liste triée de toutes les images disponibles (statiques et envoyées).
     *
     * @return array<int, string>
     */
    public static function listImages(): array
    {
        $names = array_merge(self::scan(self::imagesDir()), self::scan(self::uploadsDir()));
        sort($names);

        return array_values(array_unique($names));
    }

    /**
     * URL publique d'une image, chaîne vide si le nom est vide ou invalide.
     */
    public static function publicUrl(?string $name): string
    {
        if ($name === null || $name === '' || !self::isSafeName($name)) {
            return '';
        }

        $path = self::locate($name);

        return $path === null ? '' : '/assets/' . $path . '/' . rawurlencode($name);
    }

    /**
     * Largeur et hauteur d'une image, pour renseigner les attributs width et height (anti-CLS).
     *
     * @return array{width: int, height: int}|null
     */
    public static function dimensions(?string $name): ?array
    {
        if ($name === null || $name === '' || !self::isSafeName($name)) {
            return null;
        }

        $path = self::locate($name);
        $size = $path === null ? false : @getimagesize(BASE_PATH . '/public/assets/' . $path . '/' . $name);

        return $size === false ? null : ['width' => (int) $size[0], 'height' => (int) $size[1]];
    }

    /**
     * Valide puis enregistre une image envoyée depuis l'administration.
     *
     * @param array<string, mixed> $file Entrée du tableau $_FILES
     * @return array{success: bool, message: string, status: int, fileName?: string}
     */
    public static function upload(array $file): array
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return self::failure(t('admin.api.image_none'), 400);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return self::failure(t('admin.api.image_error'), 400);
        }

        if ($file['size'] > self::MAX_BYTES) {
            return self::failure(t('admin.api.image_too_large'), 400);
        }

        // Le type MIME réel est lu dans le contenu du fichier, jamais dans son nom
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!isset(self::MIME_TYPES[$mime])) {
            return self::failure(t('admin.api.image_type'), 400);
        }

        $size = @getimagesize($file['tmp_name']);

        if ($size === false || $size[0] < 1 || $size[1] < 1 || $size[0] > self::MAX_DIMENSION || $size[1] > self::MAX_DIMENSION) {
            return self::failure(t('admin.api.image_dimensions'), 400);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return self::failure(t('admin.api.image_error'), 400);
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . self::MIME_TYPES[$mime];
        $destination = self::uploadsDir() . $fileName;

        // Les JPEG de téléphone contiennent souvent la position GPS : ils sont réencodés sans métadonnées
        $error = $mime === 'image/jpeg'
            ? self::saveJpegWithoutMetadata($file['tmp_name'], $destination, (int) $size[0], (int) $size[1])
            : (move_uploaded_file($file['tmp_name'], $destination) ? null : 'image_save');

        if ($error !== null) {
            return self::failure(t('admin.api.' . $error), $error === 'image_save' ? 500 : 400);
        }

        chmod($destination, 0644);

        return ['success' => true, 'message' => t('admin.api.image_uploaded'), 'status' => 200, 'fileName' => $fileName];
    }

    /**
     * Réencode un JPEG avec GD : les métadonnées EXIF (position GPS, modèle de l'appareil, date) disparaissent.
     * L'orientation EXIF est appliquée aux pixels avant l'enregistrement, sinon la photo s'afficherait couchée.
     * Sans l'extension GD, le fichier est enregistré tel quel et l'incident est journalisé.
     *
     * @param string $source      Fichier temporaire envoyé
     * @param string $destination Chemin final dans uploads/
     * @return string|null Clé du message d'erreur (admin.api.*), null si tout s'est bien passé
     */
    private static function saveJpegWithoutMetadata(string $source, string $destination, int $width, int $height): ?string
    {
        if (!function_exists('imagecreatefromjpeg')) {
            error_log('[ImageUploader] Extension GD absente : métadonnées EXIF conservées pour ' . basename($destination));

            return move_uploaded_file($source, $destination) ? null : 'image_save';
        }

        $exif = function_exists('exif_read_data') ? @exif_read_data($source) : false;
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        // Angle de rotation (sens inverse des aiguilles d'une montre) selon la valeur EXIF ; les orientations miroir, très rares, sont ignorées
        $angle = [3 => 180, 6 => 270, 8 => 90][$orientation] ?? 0;

        // GD décompresse l'image en mémoire : environ 5 octets par pixel, le double si la rotation crée une seconde image
        $needed = memory_get_usage() + $width * $height * 5 * ($angle === 0 ? 1 : 2) + 16 * 1024 * 1024;

        if (!self::ensureMemory($needed)) {
            return 'image_pixels';
        }

        $image = @imagecreatefromjpeg($source);

        if ($image === false) {
            return 'image_type';
        }

        $oriented = $angle === 0 ? $image : imagerotate($image, $angle, 0);

        return $oriented !== false && imagejpeg($oriented, $destination, 85) ? null : 'image_save';
    }

    /**
     * Relève la limite de mémoire de PHP si nécessaire (et si l'hébergeur l'autorise).
     *
     * @param int $bytes Mémoire nécessaire en octets
     * @return bool true si la limite permet de traiter l'image
     */
    private static function ensureMemory(int $bytes): bool
    {
        $limit = self::toBytes((string) ini_get('memory_limit'));

        if ($limit < 0 || $limit >= $bytes) {
            return true;
        }

        @ini_set('memory_limit', (string) $bytes);

        $raised = self::toBytes((string) ini_get('memory_limit'));

        return $raised < 0 || $raised >= $bytes;
    }

    /**
     * Convertit une valeur de php.ini ("128M", "1G", "-1") en octets (-1 signifie illimité).
     */
    private static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $units = ['k' => 1024, 'm' => 1024 ** 2, 'g' => 1024 ** 3];

        return (int) $value * ($units[strtolower(substr($value, -1))] ?? 1);
    }

    /**
     * @return array{success: bool, message: string, status: int}
     */
    private static function failure(string $message, int $status): array
    {
        return ['success' => false, 'message' => $message, 'status' => $status];
    }

    /**
     * Dossier ("images" ou "uploads") qui contient le fichier, null s'il n'existe nulle part.
     */
    private static function locate(string $name): ?string
    {
        return match (true) {
            is_file(self::uploadsDir() . $name) => 'uploads',
            is_file(self::imagesDir() . $name) => 'images',
            default => null,
        };
    }

    /**
     * Noms des images d'un dossier.
     *
     * @return array<int, string>
     */
    private static function scan(string $directory): array
    {
        $files = is_dir($directory) ? (scandir($directory) ?: []) : [];

        return array_values(array_filter($files, fn (string $file): bool => is_file($directory . $file) && self::isSafeName($file)));
    }
}
