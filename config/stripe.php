<?php
return [
    'secret_key' => getenv('STRIPE_SECRET'),
    'publishable_key' => getenv('STRIPE_PUBLIC'),
];