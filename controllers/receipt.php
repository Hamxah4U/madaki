<?php
require_once 'model/Database.php';
require_once 'model/receipt_helper.php';

$id  = isset($_GET['id'])  ? (int)$_GET['id']  : 0;
$tid = isset($_GET['tid']) ? (int)$_GET['tid'] : 0;

if ($id <= 0 || $tid <= 0) { require 'controllers/404.php'; exit(); }

// Public visitors need the signed key from their WhatsApp link; logged-in staff don't.
if (empty($_SESSION['userID']) && !receipt_token_valid($id, $tid, $_GET['k'] ?? null)) {
    http_response_code(403);
    echo 'This receipt link is invalid or incomplete. Please ask Bashir Madaki Transport to resend it.';
    exit();
}

$r = get_receipt_data($db->conn, $id, $tid);
if (!$r) { http_response_code(404); echo 'Receipt not found.'; exit(); }

if (($_GET['format'] ?? '') === 'pdf') {
    $file = 'Receipt-' . $r['receipt_no'] . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $file . '"');
    echo render_receipt_pdf($r);
    exit();
}

echo render_receipt_html($r, false);
