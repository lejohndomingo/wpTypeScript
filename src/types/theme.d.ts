/** Theme color palette configuration */
interface ThemeColors {
  primary: string;
  secondary: string;
  text: string;
  background: string;
  accent: string;
}

/** Theme font stack configuration */
interface ThemeFonts {
  body: string;
  heading: string;
}

/** Theme layout structure configuration */
interface ThemeLayout {
  containerWidth: number;
  layoutType: 'full-width' | 'boxed';
  headerStyle: 'standard' | 'centered' | 'minimal';
  footerColumns: 1 | 2 | 3 | 4;
}

/** Full theme options object as stored in the customizer database */
interface ThemeOptions {
  colors: ThemeColors;
  fonts: ThemeFonts;
  layout: ThemeLayout;
  analytics: {
    enabled: boolean;
    code: string;
  };
  topHeader: {
    enabled: boolean;
    hideOnScroll: boolean;
    content: string;
  };
  sidebar: {
    defaultLayout: 'full-width' | 'left-sidebar' | 'right-sidebar';
    width: number;
  };
}

/** Injectable scripts/styles for header/body/footer regions */
interface ThemeScripts {
  headerCSS: string;
  headerJS: string;
  bodyCSS: string;
  bodyJS: string;
  footerCSS: string;
  footerJS: string;
}

/** Data localized from PHP into the JavaScript global scope */
interface ThemeLocalizedData {
  ajaxUrl: string;
  nonce: string;
  themeUri: string;
  assetsUri: string;
  options?: Partial<ThemeOptions>;
  [key: string]: unknown;
}
