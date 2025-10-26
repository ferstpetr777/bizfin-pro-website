#!/bin/bash

# Run release script
#bash bin/i18n.sh

# Run textdomain updates and generate the POT file
echo "Running textdomain update and POT file generation..."
npm run i18n

# Update PO files
echo "Updating PO files..."
npm run i18n:po

# Translate using GPT-PO for Dutch, French, and more
echo "Translating PO files using GPT-PO..."
npm run i18n:gptpo:nl
npm run i18n:gptpo:fr
npm run i18n:gptpo:de
npm run i18n:gptpo:es
npm run i18n:gptpo:pt
npm run i18n:gptpo:pl
npm run i18n:gptpo:it

# Update PO files again after translation
echo "Updating PO files again..."
npm run i18n:po

# Generate MO files
echo "Generating MO files..."
npm run i18n:mo

# Generate JSON translation files
echo "Generating JSON translation files..."
npm run i18n:json

echo "All commands executed successfully."
