<?php

$callback_url = "http://rss-collect.phi1010.com/tumblr-return.php";

$settings = 'settings/settings.ini';
$defaultsettings = 'settings/settings-default.ini';

function CheckSettings() {
    // The PHP copy function blindly copies over existing files.  We don't wish
    // this to happen, so we have to perform the copy a bit differently.  The
    // only safe way to ensure we don't overwrite an existing file is to call
    // fopen in create-only mode (mode 'x').  If it succeeds, the file did not
    // exist before, and we've successfully created it, meaning we own the
    // file.  After that, we can safely copy over our own file.
    global $defaultsettings, $settings;
    if ($file = @fopen($settings, 'x')) {
        // We've successfully created a file, so it's ours.  We'll close
        // our handle.
        if (!@fclose($file)) {
            // There was some problem with our file handle.
            return;
        }

        // Now we copy over the file we created.
        if (!@copy($defaultsettings, $settings)) {
            // The copy failed, even though we own the file, so we'll clean
            // up by itrying to remove the file and report failure.
            unlink($settings);
            return;
        }
    }
}

function GetSettings() {
    global $settings;
    CheckSettings();
    return parse_ini_file($settings, TRUE);
}
