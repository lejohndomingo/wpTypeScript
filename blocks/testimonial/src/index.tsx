import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

registerBlockType('wptypescript/testimonial', {
  apiVersion: 3,
  title: __('Testimonial', 'wptypescript'),
  description: __('A customer testimonial with quote, author, and optional image.', 'wptypescript'),
  icon: 'format-quote',
  category: 'wptypescript',
  keywords: ['testimonial', 'quote', 'review'],
  attributes: {
    quote: { type: 'string', default: '' },
    authorName: { type: 'string', default: '' },
    authorRole: { type: 'string', default: '' },
    mediaId: { type: 'number', default: 0 },
    mediaUrl: { type: 'string', default: '' },
    backgroundColor: { type: 'string', default: '' },
  },
  supports: {
    align: ['wide', 'full'],
    html: false,
  },
  edit: Edit,
  save: Save,
});
