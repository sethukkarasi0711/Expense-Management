<?php
// lib/helpers.php
require_once __DIR__ . '/../config/db.php';

function json_response($arr, $code = 200) {
    header('Content-Type: application/json', true, $code);
    echo json_encode($arr);
    exit;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Unauthorized'], 401);
    }
}

function current_user() {
    global $mysqli;
    if (!isset($_SESSION['user_id'])) return null;
    $id = intval($_SESSION['user_id']);
    $res = $mysqli->query("SELECT * FROM users WHERE id=$id");
    return $res ? $res->fetch_assoc() : null;
}

function is_admin() {
    $u = current_user();
    return $u && $u['role'] === 'Admin';
}

function is_manager_or_higher() {
    $u = current_user();
    return $u && ($u['role'] === 'Manager' || $u['sub_role'] !== 'None');
}

/**
 * Simple exchangerate fetch (use your own API key if needed)
 * Example: https://api.exchangerate-api.com/v4/latest/{BASE}
 */
function fetch_exchange_rates($base = 'USD') {
    $url = "https://api.exchangerate-api.com/v4/latest/".urlencode($base);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

function convert_currency($amount, $from, $to) {
    if (strtoupper($from) === strtoupper($to)) return round($amount,2);
    $rates = fetch_exchange_rates(strtoupper($from));
    if (!isset($rates['rates'][$to])) {
        // fallback: fetch based on USD
        $rates = fetch_exchange_rates('USD');
        if (!isset($rates['rates'][$to]) || !isset($rates['rates'][$from])) {
            return null;
        }
        $usd_from = $rates['rates'][$from];
        $usd_to = $rates['rates'][$to];
        $converted = ($amount / $usd_from) * $usd_to;
        return round($converted,2);
    }
    $rate = $rates['rates'][$to];
    return round($amount * $rate, 2);
}
?>
