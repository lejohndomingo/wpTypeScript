import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

registerBlockType('wptypescript/slider-carousel', {
  apiVersion: 3,
  title: __('Slider Carousel', 'wptypescript'),
  description: __('A responsive image and content carousel with navigation and autoplay.', 'wptypescript'),
  icon: 'slides',
  category: 'wptypescript',
  keywords: ['slider', 'carousel', 'slideshow', 'gallery'],
  attributes: {
    autoplay: { type: 'boolean', default: false },
    autoplaySpeed: { type: 'number', default: 5000 },
    showArrows: { type: 'boolean', default: true },
    showDots: { type: 'boolean', default: true },
  },
  supports: {
    align: ['wide', 'full'],
    html: false,
    color: { background: true, text: true },
  },
  edit: Edit,
  save: Save,
});
