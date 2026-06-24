import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const TEMPLATE: ([string, Record<string, unknown>] | [string, Record<string, unknown>, unknown[]])[] = [
  ['core/image', {
    align: 'center',
    width: 120,
    height: 120,
    className: 'is-style-rounded',
    sizeSlug: 'thumbnail',
  }],
  ['core/heading', {
    level: 3,
    placeholder: __('Name', 'wptypescript'),
    textAlign: 'center',
  }],
  ['core/paragraph', {
    placeholder: __('Role', 'wptypescript'),
    textAlign: 'center',
    fontSize: 'small',
    className: 'is-style-display',
  }],
  ['core/paragraph', {
    placeholder: __('Bio…', 'wptypescript'),
    textAlign: 'center',
  }],
  ['core/social-links', {
    align: 'center',
  }],
];

export default function Edit() {
  return (
    <div {...useBlockProps()}>
      <InnerBlocks template={TEMPLATE} templateLock={false} />
    </div>
  );
}
