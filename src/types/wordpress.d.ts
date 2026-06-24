/** A single REST API link (HATEOAS) */
interface WPRestLink {
  href: string;
  embeddable?: boolean;
  target?: string;
  [key: string]: unknown;
}

/** Collection of REST API links keyed by relation type */
interface WPRestLinks {
  self: WPRestLink[];
  collection: WPRestLink[];
  about?: WPRestLink[];
  author?: WPRestLink[];
  replies?: WPRestLink[];
  'wp:featuredmedia'?: WPRestLink[];
  'wp:term'?: WPRestLink[];
  'wp:attachment'?: WPRestLink[];
  [key: string]: unknown;
}

/** WordPress REST API post object */
interface WPRestPost {
  id: number;
  date: string;
  date_gmt: string;
  guid: { rendered: string };
  modified: string;
  modified_gmt: string;
  slug: string;
  status: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  type: string;
  link: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  author: number;
  featured_media: number;
  comment_status: 'open' | 'closed';
  ping_status: 'open' | 'closed';
  sticky: boolean;
  template: string;
  format: string;
  meta: Record<string, unknown>;
  categories: number[];
  tags: number[];
  _links: WPRestLinks;
  _embedded?: Record<string, unknown[]>;
}

/** WordPress REST API page object (extends post without taxonomy fields) */
interface WPRestPage extends Omit<WPRestPost, 'categories' | 'tags' | 'sticky' | 'format'> {
  parent: number;
  menu_order: number;
}

/** WordPress REST API taxonomy term */
interface WPRestTerm {
  id: number;
  count: number;
  description: string;
  link: string;
  name: string;
  slug: string;
  taxonomy: string;
  parent: number;
  meta: Record<string, unknown>;
  _links: WPRestLinks;
}

/** WordPress REST API category term */
interface WPRestCategory extends WPRestTerm {
  taxonomy: 'category';
}

/** WordPress REST API tag term */
interface WPRestTag extends WPRestTerm {
  taxonomy: 'post_tag';
}

/** WordPress REST API media attachment */
interface WPRestMedia {
  id: number;
  date: string;
  slug: string;
  type: string;
  link: string;
  title: { rendered: string };
  author: number;
  caption: { rendered: string };
  alt_text: string;
  media_type: 'image' | 'video' | 'file';
  mime_type: string;
  media_details: {
    width: number;
    height: number;
    file: string;
    sizes?: Record<
      string,
      {
        file: string;
        width: number;
        height: number;
        mime_type: string;
        source_url: string;
      }
    >;
  };
  source_url: string;
  _links: WPRestLinks;
}

/** WordPress REST API user */
interface WPRestUser {
  id: number;
  name: string;
  url: string;
  description: string;
  link: string;
  slug: string;
  avatar_urls: Record<string, string>;
  meta: Record<string, unknown>;
  _links: WPRestLinks;
}

/** WordPress REST API comment */
interface WPRestComment {
  id: number;
  post: number;
  parent: number;
  author: number;
  author_name: string;
  author_url: string;
  date: string;
  content: { rendered: string };
  link: string;
  status: string;
  type: string;
  author_avatar_urls: Record<string, string>;
  meta: Record<string, unknown>;
  _links: WPRestLinks;
}

/** WordPress REST API navigation menu */
interface WPRestMenu {
  id: number;
  name: string;
  slug: string;
  description: string;
  locations: string[];
  items: WPRestMenuItem[];
  meta: Record<string, unknown>;
  _links: WPRestLinks;
}

/** WordPress REST API navigation menu item */
interface WPRestMenuItem {
  id: number;
  title: { rendered: string };
  url: string;
  attr_title: string;
  description: string;
  type: string;
  type_label: string;
  object: string;
  object_id: string;
  parent: number;
  menu_order: number;
  target: string;
  classes: string[];
  xfn: string[];
  meta: Record<string, unknown>;
  children?: WPRestMenuItem[];
  _links: WPRestLinks;
}

/** WordPress REST API widget */
interface WPRestWidget {
  id: string;
  widget_type: string;
  widget_id: string;
  sidebar: string;
  instance: Record<string, unknown>;
  _links: WPRestLinks;
}

/** WordPress block (parsed from block markup) */
interface WPBlock {
  blockName: string | null;
  attrs: Record<string, unknown>;
  innerBlocks: WPBlock[];
  innerHTML: string;
  innerContent: (string | WPBlock)[];
}

/** WordPress theme support declaration values */
interface WPThemeSupports {
  'align-wide'?: boolean;
  'custom-logo'?:
    | boolean
    | { width?: number; height?: number; flexWidth?: boolean; flexHeight?: boolean };
  'post-thumbnails'?: boolean | string[];
  'responsive-embeds'?: boolean;
  'title-tag'?: boolean;
  html5?: string[];
  widgets?: boolean;
  'block-template-parts'?: boolean;
  [key: string]: unknown;
}

/** WordPress theme modification values stored in options table */
interface WPThemeMods {
  custom_logo?: number;
  header_image?: string;
  background_image?: string;
  background_color?: string;
  nav_menu_locations?: Record<string, number>;
  [key: string]: unknown;
}

/** WordPress option row from the options table */
interface WPOption {
  option_name: string;
  option_value: string;
  autoload: 'yes' | 'no';
}
