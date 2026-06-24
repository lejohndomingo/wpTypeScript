import type * as WPBlocks from '@wordpress/blocks';
import type * as WPI18n from '@wordpress/i18n';
import type * as WPEElement from '@wordpress/element';
import type * as WPComponents from '@wordpress/components';
import type * as WPBlockEditor from '@wordpress/block-editor';
import type * as WPCompose from '@wordpress/compose';
import type * as WPData from '@wordpress/data';
import type * as WPHooks from '@wordpress/hooks';
import type * as WPDate from '@wordpress/date';
import type * as WPUrl from '@wordpress/url';
import type * as WPApiFetch from '@wordpress/api-fetch';

/** Augments the global Window with theme-specific and WP global objects */
declare global {
  interface Window {
  wpTypeScriptData: ThemeLocalizedData;
  wptypescriptAdminData?: AdminLocalizedData;
  wp: {
    blocks: typeof WPBlocks;
    i18n: typeof WPI18n;
    element: typeof WPEElement;
    components: typeof WPComponents;
    blockEditor: typeof WPBlockEditor;
    compose: typeof WPCompose;
    data: typeof WPData;
    hooks: typeof WPHooks;
    date: typeof WPDate;
    url: typeof WPUrl;
    apiFetch: typeof WPApiFetch;
    media: WpMedia;
    [key: string]: unknown;
  };
};
}

/** Global variable set by wp_localize_script in PHP */
declare const wpTypeScriptData: ThemeLocalizedData;

declare module '@wordpress/blocks' {
  export * from '@wordpress/blocks';
}

declare module '@wordpress/i18n' {
  export * from '@wordpress/i18n';
}

declare module '@wordpress/element' {
  export * from '@wordpress/element';
}

declare module '@wordpress/components' {
  export * from '@wordpress/components';
}

declare module '@wordpress/block-editor' {
  export * from '@wordpress/block-editor';
}
