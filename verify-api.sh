#!/bin/bash

# API V1 Integration Verification Script
# Vérifie que tous les fichiers et configurations sont en place

echo "🔍 Vérification de l'API REST V1..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Files to check
FILES=(
    "config/sanctum.php"
    "app/Http/Api/V1/Middleware/EnsureApiToken.php"
    "app/Http/Api/V1/Middleware/ThrottleApi.php"
    "app/Http/Api/V1/Data/ProductData.php"
    "app/Http/Api/V1/Data/UserData.php"
    "app/Http/Api/V1/Data/CartData.php"
    "app/Http/Api/V1/Data/OrderData.php"
    "app/Http/Api/V1/Requests/FormRequests.php"
    "app/Http/Api/V1/Controllers/AuthController.php"
    "app/Http/Api/V1/Controllers/ProductController.php"
    "app/Http/Api/V1/Controllers/CartController.php"
    "app/Http/Api/V1/Controllers/OrderController.php"
    "app/Http/Api/V1/Controllers/AccountController.php"
    "routes/api.php"
    "tests/Feature/Api/V1/AuthenticationTest.php"
    "tests/Feature/Api/V1/ProductsTest.php"
    "tests/Feature/Api/V1/CartTest.php"
    "tests/Feature/Api/V1/OrdersTest.php"
    "tests/Feature/Api/V1/AccountTest.php"
    "tests/Feature/Api/V1/ApiMiddlewareTest.php"
    "API_V1_DOCUMENTATION.md"
    "API_SETUP_GUIDE.md"
)

echo "📋 Vérification des fichiers..."
echo ""

FILES_OK=0
FILES_MISSING=0

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
        ((FILES_OK++))
    else
        echo -e "${RED}✗${NC} $file (MANQUANT)"
        ((FILES_MISSING++))
    fi
done

echo ""
echo "📊 Résultats:"
echo -e "  ${GREEN}Fichiers trouvés${NC}: $FILES_OK"
echo -e "  ${RED}Fichiers manquants${NC}: $FILES_MISSING"
echo ""

# Check key configurations
echo "⚙️ Vérification des configurations..."
echo ""

# Check if sanctum is configured
if grep -q "SANCTUM_TOKEN_EXPIRATION" config/sanctum.php; then
    echo -e "${GREEN}✓${NC} Sanctum token expiration configuré"
else
    echo -e "${YELLOW}⚠${NC} Configuration Sanctum incomplète"
fi

# Check if routes are defined
if grep -q "/api/v1/" routes/api.php; then
    echo -e "${GREEN}✓${NC} Routes API v1 définies"
else
    echo -e "${RED}✗${NC} Routes API v1 non trouvées"
fi

# Check if tests exist
TEST_FILES=$(find tests/Feature/Api/V1 -name "*Test.php" 2>/dev/null | wc -l)
if [ "$TEST_FILES" -ge 6 ]; then
    echo -e "${GREEN}✓${NC} Tests API ($TEST_FILES fichiers trouvés)"
else
    echo -e "${YELLOW}⚠${NC} Tests API incomplets ($TEST_FILES fichiers)"
fi

echo ""
echo "🚀 Prochaines étapes:"
echo ""
echo "1. Exécuter les migrations:"
echo "   php artisan migrate"
echo ""
echo "2. Exécuter les tests:"
echo "   php artisan test tests/Feature/Api/V1/"
echo ""
echo "3. Vérifier la configuration .env:"
echo "   SANCTUM_TOKEN_EXPIRATION=43200"
echo "   SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1:8000"
echo ""
echo "4. Tester avec Postman ou curl:"
echo "   curl http://localhost:8000/api/v1/health"
echo ""
echo "5. Vérifier les endpoints:"
echo "   GET  /api/v1/products              # Public"
echo "   POST /api/v1/auth/login            # Public"
echo "   GET  /api/v1/auth/me               # Protected"
echo "   POST /api/v1/cart/add              # Protected"
echo ""

if [ "$FILES_MISSING" -eq 0 ]; then
    echo -e "${GREEN}✅ Tous les fichiers API sont en place!${NC}"
else
    echo -e "${RED}⚠️  $FILES_MISSING fichiers manquants${NC}"
fi
