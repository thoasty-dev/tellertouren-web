import confetti from 'canvas-confetti';
import PhotoSwipeDynamicCaption from 'photoswipe-dynamic-caption-plugin';
import PhotoSwipeLightbox from 'photoswipe/lightbox';

let lightbox;
let photoSwipeRefreshQueued = false;

const initializePhotoSwipe = () => {
    lightbox?.destroy();
    lightbox = new PhotoSwipeLightbox({
        gallery: '.photoswipe',
        children: 'a',
        pswpModule: () => import('photoswipe'),
    });
    new PhotoSwipeDynamicCaption(lightbox, { type: 'auto' });
    lightbox.init();
};

const schedulePhotoSwipeRefresh = () => {
    if (photoSwipeRefreshQueued) {
        return;
    }

    photoSwipeRefreshQueued = true;
    queueMicrotask(() => {
        photoSwipeRefreshQueued = false;
        initializePhotoSwipe();
    });
};

const fork = confetti.shapeFromPath({
    path: 'M724.81 217.124C724.81 215.144 724.968 213.415 725.28 211.713L734.21 108.862C734.262 108.267 734.882 107.805 735.629 107.805C736.377 107.805 736.997 108.267 737.048 108.862L745.104 201.643H747.793L755.85 108.844C755.902 108.25 756.521 107.788 757.268 107.788C758.015 107.788 758.634 108.25 758.685 108.844L766.743 201.643H769.431L777.431 109.516C777.484 108.897 778.129 108.416 778.907 108.416C779.684 108.416 780.329 108.897 780.382 109.516L789.256 211.713C793 229 783 242 766.925 245.99C768.4 294 768.087 370 768.087 415.384C768.087 461.19 763.239 498.379 757.268 498.379C751.297 498.379 746.449 461.19 746.449 415.384C746.449 370 746 294 747.611 245.99C731 242 721 229 724.81 217.124Z',
});

const celebrate = (originElement = null) => {
    const bounds = originElement?.getBoundingClientRect();

    confetti({
        shapes: [fork],
        angle: 90,
        spread: 65,
        particleCount: 70,
        scalar: 3,
        disableForReducedMotion: true,
        origin: bounds
            ? { x: (bounds.left + bounds.width / 2) / window.innerWidth, y: (bounds.top + bounds.height / 2) / window.innerHeight }
            : { y: 0.65 },
    });
};

document.addEventListener('click', (event) => {
    const target = event.target.closest('.confetti');

    if (target) {
        celebrate(target);
    }

    const copyButton = event.target.closest('.js-copy-link');

    if (copyButton) {
        const value = copyButton.dataset.copyValue;

        navigator.clipboard.writeText(value).then(() => {
            copyButton.classList.add('btn-success');
            copyButton.setAttribute('aria-label', 'Link kopiert');
            copyButton.querySelector('i')?.classList.replace('fa-copy', 'fa-check');
        });
    }
});

document.addEventListener('DOMContentLoaded', initializePhotoSwipe);
document.addEventListener('livewire:navigated', initializePhotoSwipe);
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', schedulePhotoSwipeRefresh);
});
document.addEventListener('minigame-completed', () => celebrate());
