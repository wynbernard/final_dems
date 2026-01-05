<?php
/**
 * Dependency Security Audit Script
 * 
 * This script runs composer audit to check for known vulnerabilities in dependencies
 * 
 * Usage: php scripts/security_audit.php
 */

echo "🔍 Running Dependency Security Audit...\n\n";

// Check if composer is available
$composer_path = null;
$possible_paths = [
    'composer',
    'composer.phar',
    __DIR__ . '/../vendor/bin/composer',
    'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat'
];

foreach ($possible_paths as $path) {
    $output = [];
    $return_var = 0;
    exec("$path --version 2>&1", $output, $return_var);
    if ($return_var === 0) {
        $composer_path = $path;
        break;
    }
}

if (!$composer_path) {
    echo "❌ ERROR: Composer not found. Please install Composer first.\n";
    echo "   Visit: https://getcomposer.org/download/\n";
    exit(1);
}

echo "✅ Found Composer: $composer_path\n\n";

// Change to project root directory
$project_root = dirname(__DIR__);
chdir($project_root);

// Run composer audit
echo "📦 Checking dependencies for security vulnerabilities...\n\n";

$command = "$composer_path audit --format=table";
$output = [];
$return_var = 0;

exec($command . " 2>&1", $output, $return_var);

// Display results
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\n";

// Check exit code
if ($return_var === 0) {
    echo "✅ Security audit completed. No known vulnerabilities found.\n";
    exit(0);
} else {
    echo "⚠️  Security audit found vulnerabilities. Please review the output above.\n";
    echo "   Run 'composer update' to update vulnerable packages if available.\n";
    exit(1);
}

