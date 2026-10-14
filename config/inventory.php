<?php

return [

    // Received deliveries that expire within this many days appear in
    // "Expiry watch" and trigger an "approaching expiry" notification.
    // Change it in .env with INVENTORY_EXPIRY_ALERT_DAYS=5, for example.
    'expiry_alert_days' => (int) env('INVENTORY_EXPIRY_ALERT_DAYS', 3),

];
