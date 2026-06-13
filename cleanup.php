<?php

$dir = __DIR__ . '/database/migrations/';

$filesToDelete = [
    '2026_05_23_000001_create_document_templates_table.php',
    '2026_05_23_000002_create_generated_letters_table.php',
    '2026_05_23_000003_create_activity_logs_table.php',
    '2026_05_24_000010_create_stasis_table.php',
    '2026_05_24_000020_create_lingkungan_parokis_table.php',
    '2026_05_24_000030_create_lingkungan_stasis_table.php',
    '2026_05_24_000040_create_bansos_periods_table.php',
    '2026_05_24_000050_create_calon_penerimas_table.php',
    '2026_05_24_000100_modify_users_table_add_fields.php',
    '2026_05_25_000010_create_saw_criteria_table.php',
    '2026_05_25_000020_create_saw_weights_table.php',
    '2026_05_25_000030_create_saw_results_table.php',
    '2026_05_25_000040_add_lock_columns_to_bansos_periods.php',
    '2026_05_27_150000_add_phase4_columns_to_generated_letters_table.php',
    '2019_12_14_000001_create_personal_access_tokens_table.php',
];

foreach ($filesToDelete as $file) {
    if (file_exists($dir . $file)) {
        unlink($dir . $file);
        echo "Deleted: $file\n";
    }
}

echo "Cleanup complete! Now run 'php artisan migrate:fresh --seed'\n";
