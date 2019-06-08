#!/usr/bin/env bash

# we don't have a git repository here, but a bunch of modified files - we need to recreate it from scratch
echo "Removing existing library code..."
rm -rf lib/auryn

git clone https://github.com/rdlowrey/auryn.git lib/auryn

cd lib/auryn

# add namespace to all classes
echo "Adding Toolset namespace prefix..."
find lib -name '*.php' | xargs sed -i 's/namespace Auryn;/namespace OTGS\\Toolset\\Common\\Auryn;/g'
find lib -name '*.php' | xargs sed -i 's/\\Auryn\\/\\OTGS\\Toolset\\Common\\Auryn\\/g'

# cleanup
echo "Removing unnecessary files..."
rm -rf examples
rm -rf test
find -path './.*' -delete
rm phpunit.xml

echo "Done. Please test the library works as expected after the update."