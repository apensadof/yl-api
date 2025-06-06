#!/bin/bash

echo "Installing required Symfony dependencies..."

# Install JWT library
composer require firebase/php-jwt

# Install security bundle
composer require symfony/security-bundle

# Install validator
composer require symfony/validator

# Install maker bundle for development
composer require symfony/maker-bundle --dev

# Install additional helpful packages
composer require symfony/serializer

echo "All dependencies installed successfully!"
echo ""
echo "Next steps:"
echo "1. Configure your database in .env file"
echo "2. Run: php bin/console doctrine:database:create"
echo "3. Run: php bin/console doctrine:schema:create"
echo "4. Test your API endpoints" 