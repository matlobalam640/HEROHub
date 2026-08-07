<?php

$key = getenv('USA_PAYMENTS_SECURITY_KEY') ?: '4dCan4zeEgcJ59pdf3K3KHSmDa6x9Hwr';
$base = 'https://usapayments.transactiongateway.com/api/query.php';

function query(array $params): string
{
    global $base, $key;
    $params['security_key'] = $key;
    $ch = curl_init($base);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);

    return (string) $body;
}

$xml = simplexml_load_string(query(['report_type' => 'recurring']));
$subs = $xml->subscription ?? [];
echo 'subscriptions: '.count($subs)."\n";

foreach ($subs as $sub) {
    $subId = (string) $sub->subscription_id;
    $email = (string) $sub->email;
    $next = (string) $sub->next_charge_date;
    $freq = (string) ($sub->plan->day_frequency ?? '');
    $planId = (string) ($sub->plan->plan_id ?? '');
    $completed = (string) ($sub->completed_payments ?? '0');

    $txXml = simplexml_load_string(query(['subscription_id' => $subId]));
    $firstSuccess = null;
    $lastSuccess = null;
    foreach ($txXml->transaction ?? [] as $tx) {
        foreach ($tx->action ?? [] as $action) {
            if ((string) $action->success !== '1') {
                continue;
            }
            $date = (string) $action->date;
            if ($date === '') {
                continue;
            }
            $parsed = DateTime::createFromFormat('YmdHis', $date) ?: DateTime::createFromFormat('Ymd', substr($date, 0, 8));
            if (! $parsed) {
                continue;
            }
            $iso = $parsed->format('Y-m-d');
            if ($firstSuccess === null || $iso < $firstSuccess) {
                $firstSuccess = $iso;
            }
            if ($lastSuccess === null || $iso > $lastSuccess) {
                $lastSuccess = $iso;
            }
        }
    }

    echo "{$subId}\t{$email}\t{$planId}\tfreq={$freq}\tnext={$next}\tcompleted={$completed}\tfirst={$firstSuccess}\tlast={$lastSuccess}\n";
}
