{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="de">
    <title>Tellertouren</title>
    <subtitle>Ehrliche Restaurant- und Hotelberichte</subtitle>
    <link href="{{ route('feed') }}" rel="self" />
    <link href="{{ route('articles.index.get') }}" />
    <id>{{ route('articles.index.get') }}</id>
    <updated>{{ \Illuminate\Support\Carbon::parse($articles->max(fn ($article) => $article->attribute('updated_at')))->toAtomString() }}</updated>
    @foreach($articles as $article)
        <entry>
            <title>{{ $article->title }}</title>
            <link href="{{ $catalog->canonicalUrl($article) }}" />
            <id>{{ $catalog->canonicalUrl($article) }}</id>
            <published>{{ \Illuminate\Support\Carbon::parse($article->attribute('published_at'))->toAtomString() }}</published>
            <updated>{{ \Illuminate\Support\Carbon::parse($article->attribute('updated_at'))->toAtomString() }}</updated>
            <summary>{{ $article->attribute('description') }}</summary>
        </entry>
    @endforeach
</feed>
