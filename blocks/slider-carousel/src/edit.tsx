import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';

const ALLOWED_BLOCKS = ['core/image', 'core/cover', 'core/heading', 'core/paragraph', 'core/group', 'core/buttons'];

const TEMPLATE = [
  ['core/image', { placeholder: __('Add slide image…', 'wptypescript') }],
  ['core/image', { placeholder: __('Add slide image…', 'wptypescript') }],
  ['core/image', { placeholder: __('Add slide image…', 'wptypescript') }],
];

export default function Edit({ attributes, setAttributes }) {
  const { autoplay, autoplaySpeed, showArrows, showDots } = attributes;

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Carousel Settings', 'wptypescript')}>
          <ToggleControl
            label={__('Autoplay', 'wptypescript')}
            checked={autoplay}
            onChange={(val) => setAttributes({ autoplay: val })}
          />
          {autoplay && (
            <RangeControl
              label={__('Autoplay Speed (ms)', 'wptypescript')}
              value={autoplaySpeed}
              onChange={(val) => setAttributes({ autoplaySpeed: val })}
              min={1000}
              max={10000}
              step={500}
            />
          )}
          <ToggleControl
            label={__('Show Arrows', 'wptypescript')}
            checked={showArrows}
            onChange={(val) => setAttributes({ showArrows: val })}
          />
          <ToggleControl
            label={__('Show Dots', 'wptypescript')}
            checked={showDots}
            onChange={(val) => setAttributes({ showDots: val })}
          />
        </PanelBody>
      </InspectorControls>

      <div className="wptypescript-slider-wrapper">
        <div className="wptypescript-slider-track">
          <InnerBlocks
            allowedBlocks={ALLOWED_BLOCKS}
            template={TEMPLATE}
            templateLock={false}
          />
        </div>
      </div>
    </div>
  );
}
