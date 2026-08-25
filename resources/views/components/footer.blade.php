<footer class="mx-auto mt-12 grid w-full max-w-5xl gap-8 border-t border-base-300 px-6 py-12 sm:grid-cols-2">
    <div class="flex flex-col items-start gap-2">
        <span class="font-title text-2xl font-black uppercase">Tellertouren</span>
        <a href="{{ route('pages.site-notice.get') }}" class="link link-hover">Impressum</a>
        <a href="{{ route('pages.data-privacy.get') }}" class="link link-hover">Datenschutz</a>
        <a href="{{ route('feed') }}" class="link link-hover">Atom-Feed</a>
    </div>
    <a href="https://www.instagram.com/tellertouren" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 sm:justify-self-end">
        <i class="fab fa-instagram-square text-4xl"></i>
        <span>Instagram</span>
    </a>
</footer>
