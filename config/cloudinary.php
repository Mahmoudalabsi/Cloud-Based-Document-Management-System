<?php

return [
    'cloud_url' => env('CLOUDINARY_URL', sprintf(
        'cloudinary://%s:%s@%s',
        env('CLOUDINARY_API_KEY'),
        env('CLOUDINARY_API_SECRET'),
        env('CLOUDINARY_CLOUD_NAME')
    )),
];