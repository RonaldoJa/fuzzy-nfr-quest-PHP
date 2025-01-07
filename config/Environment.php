<?php
class Environment {
    public static function loadEnv($file) {

        if (!file_exists($file)) {
            throw new Exception("Archivo .env no encontrado.");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            putenv("$name=$value");
        }
    }
}
?>