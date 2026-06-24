import { dispatch } from '../utils';

export function MobileMenu() {
  return (
    <button
      className="mobile-menu-toggle"
      aria-label="Toggle menu"
      onClick={(e) => {
        e.preventDefault();
        const open = document.body.classList.toggle('menu-open');
        dispatch('theme:menu-toggle', { open });
      }}
    >
      <span />
      <span />
      <span />
    </button>
  );
}
