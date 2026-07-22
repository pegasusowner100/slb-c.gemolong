<?php
if (defined('TRACK_VISITOR_STUB_LOADED')) {
    return;
}
define('TRACK_VISITOR_STUB_LOADED', true);

/**
 * Stub implementation of trackVisitor().
 *
 * This file is intentionally a no-op so pages that still call
 * `trackVisitor()` won't trigger fatal errors after visitor tracking
 * was removed. If you later want to re-enable tracking, replace this
 * file with the real implementation or remove this stub.
 */
function trackVisitor($page_path = null) {
    // no-op: return false to indicate no logging performed
    return false;
}
