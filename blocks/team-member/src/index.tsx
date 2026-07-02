import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';

registerBlockType('wptypescript/team-member', {
  apiVersion: 3,
  title: __('Team Member', 'wptypescript'),
  description: __('A lightweight team member card. Add content with core blocks, then save as a reusable block.', 'wptypescript'),
  icon: 'groups',
  category: 'wptypescript',
  keywords: ['team', 'member', 'profile', 'staff', 'card'],
  supports: {
    align: ['wide', 'full'],
    html: false,
  },
  edit: Edit,
  save: Save,
});
