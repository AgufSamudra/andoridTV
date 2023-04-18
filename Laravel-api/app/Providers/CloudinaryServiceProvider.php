<?php

// Contoh Penyedia Layanan Kustom untuk Cloudinary
// app/Providers/CloudinaryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cloudinary\Cloudinary;

class CloudinaryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Cloudinary::class, function ($app) {
            $cloudinaryConfig = new \Cloudinary\Configuration\Configuration();
            $cloudinaryConfig->cloud->cloudName = env('CLOUDINARY_CLOUD_NAME');
            $cloudinaryConfig->cloud->apiKey = env('CLOUDINARY_API_KEY');
            $cloudinaryConfig->cloud->apiSecret = env('CLOUDINARY_API_SECRET');
            return new Cloudinary($cloudinaryConfig);
        });
    }
}
