<?php
function clean_temp_files() {
    global $config;
    $retentionMinutes = $config['temp_file_retention_minutes'] ?? 60;
    $cutoff = time() - ($retentionMinutes * 60);

    $directories = [ORIGINALS_PATH, PROCESSED_PATH];

    foreach ($directories as $dir) {
        if (is_dir($dir) && $handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file !== '.' && $file !== '..' && $file !== '.htaccess') {
                    $filePath = $dir . $file;
                    if (is_file($filePath) && filemtime($filePath) < $cutoff) {
                        @unlink($filePath);
                    }
                }
            }
            closedir($handle);
        }
    }
}