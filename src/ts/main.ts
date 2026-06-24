import { qs, on, dispatch, ready } from '../utils';

ready(() => {
  document.body.classList.add('js-loaded');

  const toggle = qs<HTMLElement>('.mobile-menu-toggle');
  if (toggle) {
    on(toggle, 'click', (e) => {
      e.preventDefault();
      const open = document.body.classList.toggle('menu-open');
      dispatch('theme:menu-toggle', { open });
    });
  }

  const topHeader = qs<HTMLElement>('.top-header-bar[data-hide-scroll="true"]');
  if (topHeader) {
    let lastScrollY = window.scrollY;
    let ticking = false;

    const updateHeader = () => {
      const currentScrollY = window.scrollY;
      const scrollingDown = currentScrollY > lastScrollY && currentScrollY > 50;
      topHeader.classList.toggle('top-header-hidden', scrollingDown);
      dispatch(
        scrollingDown ? 'theme:header-hide' : 'theme:header-show',
        { scrollY: currentScrollY },
      );
      lastScrollY = currentScrollY;
      ticking = false;
    };

    on(
      window,
      'scroll',
      () => {
        if (!ticking) {
          requestAnimationFrame(updateHeader);
          ticking = true;
        }
      },
      { passive: true },
    );
  }
});

function getElementById<T extends HTMLElement>(id: string): T | null {
  return document.getElementById(id) as T | null;
}

export { getElementById };
