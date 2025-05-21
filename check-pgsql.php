<?php

// Check if the PostgreSQL PHP driver is installed
if (extension_loaded('pdo_pgsql')) {
    echo "PostgreSQL PDO driver is installed.\n";
} else {
    echo "PostgreSQL PDO driver is NOT installed.\n";
    echo "Please install it using one of the following methods:\n\n";
    
    if (PHP_OS_FAMILY === 'Windows') {
        echo "For Windows:\n";
        echo "1. Open your php.ini file (usually in C:\\php\\php.ini)\n";
        echo "2. Uncomment the line: ;extension=pdo_pgsql\n";
        echo "   by removing the semicolon at the beginning\n";
        echo "3. Restart your web server\n\n";
        
        echo "If the extension is not available, you may need to download it from:\n";
        echo "https://windows.php.net/downloads/pecl/releases/pdo_pgsql/\n\n";
        
        echo "Or install it using the PECL command:\n";
        echo "pecl install pdo_pgsql\n\n";
    } else {
        echo "For Linux/macOS:\n";
        echo "Ubuntu/Debian: sudo apt-get install php-pgsql\n";
        echo "CentOS/RHEL: sudo yum install php-pgsql\n";
        echo "macOS (Homebrew): brew install php@8.1 && brew install php@8.1-pgsql\n\n";
        
        echo "After installation, restart your web server:\n";
        echo "sudo service apache2 restart (for Apache)\n";
        echo "sudo service nginx restart (for Nginx)\n";
        echo "sudo service php-fpm restart (for PHP-FPM)\n\n";
    }
}

// Check other required extensions
$requiredExtensions = ['pdo', 'json', 'mbstring', 'openssl', 'tokenizer'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (empty($missingExtensions)) {
    echo "All required PHP extensions are installed.\n";
} else {
    echo "The following required PHP extensions are missing: " . implode(', ', $missingExtensions) . "\n";
    echo "Please install them before proceeding.\n";
}

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "PHP version is compatible with Laravel 10.\n";
} else {
    echo "PHP version is not compatible with Laravel 10. Please upgrade to PHP 8.1 or higher.\n";
}
