#!/usr/bin/env php
<?php

/**
 * Quick Setup Script for Homework Helper
 * Run: php setup.php
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     📚 Homework Helper - Quick Setup                     ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";
echo "\n";

$steps = [
    [
        'name' => '1. Installing Composer Dependencies',
        'command' => 'composer require smalot/pdfparser phpoffice/phpword --no-interaction',
        'required' => true
    ],
    [
        'name' => '2. Creating Storage Link',
        'command' => 'php artisan storage:link',
        'required' => true
    ],
    [
        'name' => '3. Clearing Config Cache',
        'command' => 'php artisan config:clear',
        'required' => false
    ],
    [
        'name' => '4. Installing NPM Dependencies',
        'command' => 'npm install',
        'required' => false
    ],
    [
        'name' => '5. Building Frontend Assets',
        'command' => 'npm run build',
        'required' => false
    ],
];

$failed = [];

foreach ($steps as $step) {
    echo "┌─ {$step['name']}\n";
    echo "│  Running: {$step['command']}\n";
    echo "│\n";
    
    $output = [];
    $returnCode = 0;
    
    exec($step['command'] . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "│  ✓ Success\n";
    } else {
        echo "│  ✗ Failed (exit code: {$returnCode})\n";
        if ($step['required']) {
            $failed[] = $step['name'];
        } else {
            echo "│  (This step is optional, continuing...)\n";
        }
    }
    echo "└─\n\n";
    
    // Small delay for readability
    usleep(500000);
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";

if (empty($failed)) {
    echo "║     ✓ Setup Complete!                                    ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "Next steps:\n";
    echo "\n";
    echo "1. Make sure your .env file has MISTRAL_API_KEY set\n";
    echo "2. Run: php artisan serve\n";
    echo "3. Visit: http://localhost:8000\n";
    echo "\n";
    echo "📖 Read README_HOMEWORK_HELPER.md for full documentation\n";
    echo "\n";
} else {
    echo "║     ⚠ Setup Incomplete                                   ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "The following required steps failed:\n";
    foreach ($failed as $step) {
        echo "  ✗ {$step}\n";
    }
    echo "\n";
    echo "Please resolve these issues and run the script again.\n";
    echo "\n";
}
