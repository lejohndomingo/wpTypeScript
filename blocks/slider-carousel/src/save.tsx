import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function Save({ attributes }) {
  const { autoplay, autoplaySpeed, showArrows, showDots } = attributes;

  const blockProps = useBlockProps.save({
    'data-autoplay': autoplay ? 'true' : 'false',
    'data-autoplay-speed': String(autoplaySpeed),
    'data-show-arrows': showArrows ? 'true' : 'false',
    'data-show-dots': showDots ? 'true' : 'false',
  });

  return (
    <div {...blockProps}>
      <div className="wptypescript-slider-wrapper">
        <div className="wptypescript-slider-track">
          <InnerBlocks.Content />
        </div>
      </div>
    </div>
  );
}
