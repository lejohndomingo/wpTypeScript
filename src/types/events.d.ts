/** Maps custom theme event names to their detail payload types */
export interface ThemeCustomEventDetails {
  'theme:menu-toggle': { open: boolean };
  'theme:header-hide': { scrollY: number };
  'theme:header-show': { scrollY: number };
  'theme:scroll-direction': { direction: 'up' | 'down'; scrollY: number };
  'theme:react-mounted': { root: Element };
  'theme:dynamic-css-loaded': { css: string };
}

/** Union of all custom theme event names */
export type ThemeCustomEventName = keyof ThemeCustomEventDetails;

declare global {
  interface WindowEventMap {
    'theme:menu-toggle': CustomEvent<ThemeCustomEventDetails['theme:menu-toggle']>;
    'theme:header-hide': CustomEvent<ThemeCustomEventDetails['theme:header-hide']>;
    'theme:header-show': CustomEvent<ThemeCustomEventDetails['theme:header-show']>;
    'theme:scroll-direction': CustomEvent<ThemeCustomEventDetails['theme:scroll-direction']>;
    'theme:react-mounted': CustomEvent<ThemeCustomEventDetails['theme:react-mounted']>;
    'theme:dynamic-css-loaded': CustomEvent<ThemeCustomEventDetails['theme:dynamic-css-loaded']>;
  }
  interface DocumentEventMap {
    'theme:menu-toggle': CustomEvent<ThemeCustomEventDetails['theme:menu-toggle']>;
    'theme:header-hide': CustomEvent<ThemeCustomEventDetails['theme:header-hide']>;
    'theme:header-show': CustomEvent<ThemeCustomEventDetails['theme:header-show']>;
    'theme:scroll-direction': CustomEvent<ThemeCustomEventDetails['theme:scroll-direction']>;
    'theme:react-mounted': CustomEvent<ThemeCustomEventDetails['theme:react-mounted']>;
    'theme:dynamic-css-loaded': CustomEvent<ThemeCustomEventDetails['theme:dynamic-css-loaded']>;
  }
  interface HTMLElementEventMap {
    'theme:menu-toggle': CustomEvent<ThemeCustomEventDetails['theme:menu-toggle']>;
    'theme:header-hide': CustomEvent<ThemeCustomEventDetails['theme:header-hide']>;
    'theme:header-show': CustomEvent<ThemeCustomEventDetails['theme:header-show']>;
    'theme:scroll-direction': CustomEvent<ThemeCustomEventDetails['theme:scroll-direction']>;
    'theme:react-mounted': CustomEvent<ThemeCustomEventDetails['theme:react-mounted']>;
    'theme:dynamic-css-loaded': CustomEvent<ThemeCustomEventDetails['theme:dynamic-css-loaded']>;
  }
}
