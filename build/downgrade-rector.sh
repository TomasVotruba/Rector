#!/bin/sh -l

# show errors
set -e

# script fails if trying to access to an undefined variable
set -u


# configure - 1st argument, use like
# sh build/downgrade-rector.sh <directory-with-code-to-downgrade>
BUILD_DIRECTORY=$1


#---------------------------------------------


# 1. downgrade it
echo "[NOTE] Running downgrade in '$BUILD_DIRECTORY' directory\n";

# 2. provide directories to downgrade; includes the rector dirs
directories=$(php -d memory_limit=-1 bin/rector downgrade-paths 7.1 --config build/config/config-downgrade.php --working-dir $BUILD_DIRECTORY)

# 4. downgrade the directories
for directory in $directories; do
    echo "[NOTE] Downgrading '$directory' directory\n"

    # --working-dir is needed, so "SKIP" parameter is applied in absolute path of nested directory
    php -d memory_limit=-1 bin/rector process $directory --working-dir $BUILD_DIRECTORY --config build/config/config-downgrade.php
done
