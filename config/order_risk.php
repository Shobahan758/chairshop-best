<?php

return [
    'fake_threshold' => 60,
    'reason_labels' => [
        'repeated_phone' => 'একই ফোন থেকে আগেও অর্ডার হয়েছে',
        'repeated_ip' => 'একই IP থেকে ৩০ মিনিটের মধ্যে অর্ডার',
        'rapid_repeat' => 'একই ফোন থেকে ২ মিনিটের মধ্যে অর্ডার',
        'changed_details' => 'একই ফোনে নাম বা ঠিকানা পরিবর্তন',
        'previous_fake_phone' => 'এই ফোনের আগের অর্ডার Fake হিসেবে চিহ্নিত',
    ],
];
