<?php

namespace App\Http\Controllers;

use App\Support\PageMetadata;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageMetadata $metadata) {}

    public function dataPrivacy(): View
    {
        return view('pages.data-privacy', [
            'metadata' => $this->metadata->set('Datenschutz', canonical: route('pages.data-privacy.get')),
        ]);
    }

    public function siteNotice(): View
    {
        return view('pages.site-notice', [
            'metadata' => $this->metadata->set('Impressum', canonical: route('pages.site-notice.get')),
        ]);
    }
}
