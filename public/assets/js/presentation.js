document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-presentation-viewer]').forEach((viewer) => {
    const stageImage = viewer.querySelector('[data-stage-image]');
    const counter = viewer.querySelector('[data-stage-counter]');
    const thumbs = Array.from(viewer.querySelectorAll('[data-slide-thumb]'));
    const first = viewer.querySelector('[data-slide-first]');
    const prev = viewer.querySelector('[data-slide-prev]');
    const next = viewer.querySelector('[data-slide-next]');
    const fullscreen = viewer.querySelector('[data-slide-fullscreen]');
    const exitFullscreen = viewer.querySelector('[data-slide-exit]');
    const stage = viewer.querySelector('[data-stage]');

    if (!stageImage || thumbs.length === 0 || !stage) {
      return;
    }

    let index = 0;
    let startX = 0;

    const isFullscreen = () =>
      document.fullscreenElement === stage || document.webkitFullscreenElement === stage;

    const updateFullscreenState = () => {
      stage.classList.toggle('is-fullscreen', isFullscreen());
    };

    const activate = (nextIndex) => {
      index = (nextIndex + thumbs.length) % thumbs.length;
      const active = thumbs[index];

      stageImage.src = active.dataset.url || '';
      stageImage.alt = active.dataset.alt || '';

      if (counter) {
        counter.textContent = `${index + 1} / ${thumbs.length}`;
      }

      thumbs.forEach((thumb, thumbIndex) => {
        thumb.classList.toggle('is-active', thumbIndex === index);
      });

      active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    };

    thumbs.forEach((thumb, thumbIndex) => {
      thumb.addEventListener('click', () => activate(thumbIndex));
    });

    first?.addEventListener('click', () => activate(0));
    prev?.addEventListener('click', () => activate(index - 1));
    next?.addEventListener('click', () => activate(index + 1));

    stageImage.addEventListener('pointerdown', (event) => {
      startX = event.clientX;
    });

    stageImage.addEventListener('pointerup', (event) => {
      const delta = event.clientX - startX;

      if (Math.abs(delta) < 40) {
        return;
      }

      activate(delta < 0 ? index + 1 : index - 1);
    });

    fullscreen?.addEventListener('click', () => {
      if (document.fullscreenElement || document.webkitFullscreenElement) {
        return;
      }

      if (stage.requestFullscreen) {
        stage.requestFullscreen().catch(() => {});
        return;
      }

      stage.webkitRequestFullscreen?.();
    });

    exitFullscreen?.addEventListener('click', () => {
      if (document.fullscreenElement && document.exitFullscreen) {
        document.exitFullscreen().catch(() => {});
        return;
      }

      document.webkitExitFullscreen?.();
    });

    document.addEventListener('fullscreenchange', updateFullscreenState);
    document.addEventListener('webkitfullscreenchange', updateFullscreenState);

    updateFullscreenState();
    activate(0);
  });
});
