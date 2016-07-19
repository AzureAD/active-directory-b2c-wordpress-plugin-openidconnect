@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phpunit/phpunit/composer/bin/phpunit
php "%BIN_TARGET%" %*
