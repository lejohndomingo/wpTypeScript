/** WordPress media library attachment returned from wp.media */
interface WpMediaAttachment {
  url: string;
  id: number;
  [key: string]: unknown;
}

/** WordPress media frame (Backbone-based) */
interface WpMediaFrame {
  open(): void;
  on(event: 'select', handler: () => void): void;
  state(): {
    get(prop: 'selection'): {
      first(): { toJSON(): WpMediaAttachment };
    };
  };
}

/** WordPress media API (`wp.media`) */
interface WpMedia {
  (options: {
    title: string;
    button: { text: string };
    multiple: boolean;
    library: { type: string };
  }): WpMediaFrame;
  view: {
    l10n: {
      addMedia: string;
      select: string;
    };
  };
}

/** Data localized for the admin area */
interface AdminLocalizedData {
  restUrl: string;
  restNonce: string;
}
