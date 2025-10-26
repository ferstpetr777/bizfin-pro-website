#!/usr/bin/env bash

# Exit if any command fails.
set -e

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}


status "💃 Time to build Astra Pro Sites plugin duplicate folder 🕺"

if [ ! -d "artifact" ]; then
  mkdir "artifact"
fi

cd artifact

if [ ! -d "phpstan" ]; then
  mkdir "phpstan"
fi

cd ..

# Copy files for zip.
rsync -rc --exclude-from ".distignore" --exclude-from "bin/.excludePathPHPStan" "./" "artifact/phpstan/astra-pro-sites"

success "Done. Your Astra Pro Sites Folder is copied for creating stubs..! 🎉"
