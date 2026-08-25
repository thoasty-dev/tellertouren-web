{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('articles.index.get') }}</loc>
    </url>
    @foreach($articles as $article)
        <url>
            <loc>{{ $catalog->canonicalUrl($article) }}</loc>
            <lastmod>{{ \Illuminate\Support\Carbon::parse($article->attribute('updated_at'))->toAtomString() }}</lastmod>
        </url>
    @endforeach
    <url>
        <loc>{{ route('pages.data-privacy.get') }}</loc>
    </url>
    <url>
        <loc>{{ route('pages.site-notice.get') }}</loc>
    </url>
</urlset>
