export function qs<T extends HTMLElement = HTMLElement>(
  selector: string,
  parent?: ParentNode,
): T | null {
  return (parent ?? document).querySelector<T>(selector);
}

export function qsa<T extends HTMLElement = HTMLElement>(
  selector: string,
  parent?: ParentNode,
): T[] {
  return Array.from((parent ?? document).querySelectorAll<T>(selector));
}

export function on<K extends keyof WindowEventMap>(
  target: Window,
  event: K,
  handler: (this: Window, ev: WindowEventMap[K]) => void,
  options?: AddEventListenerOptions | boolean,
): () => void;
export function on<K extends keyof DocumentEventMap>(
  target: Document,
  event: K,
  handler: (this: Document, ev: DocumentEventMap[K]) => void,
  options?: AddEventListenerOptions | boolean,
): () => void;
export function on<K extends keyof HTMLElementEventMap>(
  target: HTMLElement,
  event: K,
  handler: (this: HTMLElement, ev: HTMLElementEventMap[K]) => void,
  options?: AddEventListenerOptions | boolean,
): () => void;
export function on(
  target: EventTarget,
  event: string,
  handler: EventListener,
  options?: AddEventListenerOptions | boolean,
): () => void {
  target.addEventListener(event, handler, options);
  return () => target.removeEventListener(event, handler, options);
}

export function dispatch<T>(
  event: string,
  detail?: T,
  target: EventTarget = document,
): void {
  target.dispatchEvent(
    new CustomEvent(event, { detail, bubbles: true, cancelable: true }),
  );
}

export function ready(fn: () => void) {
  if (document.readyState !== 'loading') {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
}
