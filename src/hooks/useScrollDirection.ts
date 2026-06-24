import { useState, useEffect, useRef } from 'react';

/** Vertical scroll direction */
export type ScrollDirection = 'up' | 'down';

export function useScrollDirection(threshold = 50): ScrollDirection {
  const [direction, setDirection] = useState<ScrollDirection>('up');
  const lastScrollY = useRef(0);

  useEffect(() => {
    const update = () => {
      const current = window.scrollY;
      if (current > lastScrollY.current && current > threshold) {
        setDirection('down');
      } else {
        setDirection('up');
      }
      lastScrollY.current = current;
    };

    let ticking = false;
    const onScroll = () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          update();
          ticking = false;
        });
        ticking = true;
      }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, [threshold]);

  return direction;
}
