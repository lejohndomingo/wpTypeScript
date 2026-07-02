import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

const blocks = [
  {
    name: 'wptypescript/hero-section-pattern',
    title: __('Hero Section Pattern', 'wptypescript'),
    description: __('A clean, modern hero section with title, subtitle, featured image, content text, and CTA button.', 'wptypescript'),
    patternName: 'hero-section-pattern',
  },
  {
    name: 'wptypescript/services-section',
    title: __('Services Section', 'wptypescript'),
    description: __('A 3-column services section with icons, headings, descriptions, and CTA links.', 'wptypescript'),
    patternName: 'services-section',
  },
  {
    name: 'wptypescript/landing-page',
    title: __('Landing Page', 'wptypescript'),
    description: __('A full landing page with hero, features, testimonial, and footer CTA.', 'wptypescript'),
    patternName: 'landing-page',
  },
  {
    name: 'wptypescript/blog-post-layout',
    title: __('Blog Post Layout', 'wptypescript'),
    description: __('A clean blog post template with featured image, meta, content, and sidebar with author card, TOC, and newsletter CTA.', 'wptypescript'),
    patternName: 'blog-post-layout',
  },
];

blocks.forEach((block) => {
  registerBlockType(block.name, {
    apiVersion: 3,
    title: block.title,
    description: block.description,
    icon: 'layout',
    category: 'wptypescript',
    keywords: ['pattern', 'section', 'layout'],
    attributes: {
      patternName: { type: 'string', default: block.patternName },
    },
    supports: { html: false },
    edit: Edit,
    save: Save,
  });
});
