import { StrictMode, type ReactNode, useEffect } from 'react';
import { useScrollDirection } from '../hooks';
import { dispatch } from '../utils';

export function App({ children }: { children?: ReactNode }) {
  const direction = useScrollDirection(50);

  useEffect(() => {
    const topHeader = document.querySelector<HTMLElement>(
      '.top-header-bar[data-hide-scroll="true"]',
    );
    if (!topHeader) return;
    topHeader.classList.toggle('top-header-hidden', direction === 'down');
    dispatch(
      direction === 'down' ? 'theme:header-hide' : 'theme:header-show',
      { scrollY: window.scrollY },
    );
  }, [direction]);

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      const btn = (e.target as HTMLElement).closest('.mobile-menu-toggle');
      if (btn) {
        e.preventDefault();
        const open = document.body.classList.toggle('menu-open');
        dispatch('theme:menu-toggle', { open });
      }
    };
    document.addEventListener('click', handler);
    return () => document.removeEventListener('click', handler);
  }, []);

  return <StrictMode>{children}</StrictMode>;
}
