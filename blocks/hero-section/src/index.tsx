import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

registerBlockType('wptypescript/hero-section', {
  apiVersion: 3,
  title: __('Hero Section', 'wptypescript'),
  description: __('A hero section with title, subtitle, background image, and CTA button.', 'wptypescript'),
  icon: 'cover-image',
  category: 'wptypescript',
  keywords: ['hero', 'header', 'banner', 'cta'],
  attributes: {
    title: { type: 'string', default: '' },
    subtitle: { type: 'string', default: '' },
    titleColor: { type: 'string', default: '' },
    subtitleColor: { type: 'string', default: '' },
    buttonText: { type: 'string', default: 'Get Started' },
    buttonUrl: { type: 'string', default: '' },
    buttonColor: { type: 'string', default: '' },
    buttonTextColor: { type: 'string', default: '' },
    buttonBorderRadius: { type: 'number', default: 6 },
    buttonBorderColor: { type: 'string', default: '' },
    buttonGradient: { type: 'string', default: '' },
    buttonHoverColor: { type: 'string', default: '' },
    buttonHoverTextColor: { type: 'string', default: '' },
    buttonTextDecoration: { type: 'string', default: 'none' },
    bgType: { type: 'string', default: 'image' },
    bgColor: { type: 'string', default: '' },
    videoUrl: { type: 'string', default: '' },
    videoId: { type: 'number', default: 0 },
    mediaId: { type: 'number', default: 0 },
    mediaUrl: { type: 'string', default: '' },
    overlayColor: { type: 'string', default: '#000000' },
    overlayOpacity: { type: 'number', default: 40 },
    minHeight: { type: 'number', default: 70 },
  },
  supports: {
    align: ['wide', 'full'],
    html: false,
    color: { text: true },
  },
  edit: Edit,
  save: Save,
});
