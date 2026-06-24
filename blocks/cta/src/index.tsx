import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

registerBlockType('wptypescript/cta', {
  title: __('Call to Action', 'wptypescript'),
  description: __('A customizable call-to-action block.', 'wptypescript'),
  icon: 'megaphone',
  category: 'wptypescript',
  keywords: ['cta', 'button', 'call to action'],
  attributes: {
    title: { type: 'string', default: '' },
    description: { type: 'string', default: '' },
    buttonText: { type: 'string', default: 'Learn More' },
    buttonUrl: { type: 'string', default: '' },
    alignment: { type: 'string', default: 'center' },
  },
  supports: {
    align: ['wide', 'full'],
    html: false,
    color: { background: true, text: true },
  },
  edit: Edit,
  save: Save,
});
