<?php
require_once __DIR__ . '/../config/bootstrap.php';

try {
    clean_temp_files();
    write_log('Scheduled temporary files cleanup executed successfully.');
    echo "Cleanup completed successfully.\n";
} catch (Exception $e) {
    write_log('Scheduled cleanup failed: ' . $e->getMessage(), 'ERROR');
    echo "Cleanup failed: " . $e->getMessage() . "\n";
}