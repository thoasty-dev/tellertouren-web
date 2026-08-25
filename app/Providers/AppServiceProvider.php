<?php

namespace App\Providers;

use App\Markdown\GalleryMasonryTag;
use App\Markdown\HotelRatingTag;
use App\Markdown\RestaurantRatingTag;
use App\Support\PageMetadata;
use Develate\Blogframe\Facades\Blogframe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(PageMetadata::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Blogframe::customtag($this->app->make(GalleryMasonryTag::class));
        Blogframe::customtag($this->app->make(RestaurantRatingTag::class));
        Blogframe::customtag($this->app->make(HotelRatingTag::class));
    }
}
