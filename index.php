<?php
/**
 * FINZO - Root Redirect
 * Redirects all root traffic to the public/ directory.
 */
header("Location: public/index.html");
exit;