#!/bin/bash

set -e

reldir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null && pwd )/..";

cd "$reldir";

directory=$(pwd)

echo "Working DIR"

echo "$directory";

cd "$directory";

# remove output

rm -rf ./output;

product_name="$(basename $PWD)";

echo "Installing NPM on Plugin.";

#rm -rf node_modules;

# Note : Need to use below `command` so it will use package-lock.json

if ! npm install; then
    echo "Error: npm install failed!"
    exit 1
fi

echo "Installing Composer for Production Environment";

if ! composer install --no-dev --ignore-platform-req=php --ignore-platform-req=ext-curl; then
    echo "Error: Composer install failed!"
    exit 1
fi

# do class name dump.
# Suppress PSR-4 warnings - classes are loaded manually via require_once, not Composer autoload
set +e
composer dumpautoload --optimize 2>&1 | grep -v "does not comply with psr-4" || true
composer_exit=${PIPESTATUS[0]}
set -e
if [ $composer_exit -ne 0 ]; then
    exit $composer_exit
fi

echo "Generating Translations...";

if ! npx grunt; then
    echo "Error: Grunt task failed!"
    exit 1
fi

echo "SCSS Task Production Version..";

if ! npm run scss:build:prod; then
    echo "Error: SCSS build failed!"
    exit 1
fi

echo "JS Task Production Version..";

if ! npm run js:build:prod; then
    echo "Error: JS build failed! Check if all required dependencies are installed."
    echo "Missing dependencies might include: css-minimizer-webpack-plugin, terser-webpack-plugin, webpack-merge"
    exit 1
fi

echo "Update Since Version..";

if ! npm run replace-version; then
    echo "Error: Version replacement failed!"
    exit 1
fi

echo "Product Name $product_name";

echo "Creating Zip";

rm -f "../$product_name.zip";

# Include readme.txt (WordPress.org format with changelog) but exclude README.md (GitHub format)
# Note: readme.txt is included for WordPress.org compatibility and changelog display
zip "../$product_name.zip" ./ -r -x "Gruntfile.js" "webpack.config.js" "webpack.dev.js" "webpack.prod.js" "workspace.code-workspace" "./node_modules/*" "./src/*" "./bin/*" "./npm*" "*.git*" "*.idea*" "*wpcs*" "*DS_Store*" "./composer.*" "./package*" "./phpcs.*" "./.*" "./README.md" "./api-docs/*" "./docs/*" "./demo/*" "./tests/*" "./assets/fonts/**" "assets/css/*.map" "assets/js/*.map" "*claude.md" "*CLAUDE.md" "*RELEASE.md" "*phpunit.xml*"

mkdir -p "$directory/output/";

mv "../$product_name.zip" "$directory/output/";

echo "Restoring SCSS Dev Version..";

npm run scss:build

echo "Restoring JS Dev Version..";

npm run js:build

echo ""
echo ""
echo ""

echo "Release Archive is Generated Saved without modules at -"
echo "$directory/output/$product_name.zip";

