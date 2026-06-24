import { useBlockProps, RichText } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

type Alignment = 'left' | 'center' | 'right';

interface CtaAttributes {
  title: string;
  description: string;
  buttonText: string;
  buttonUrl: string;
  alignment: string;
  [key: string]: unknown;
}

export default function Save({ attributes }: BlockSaveProps<CtaAttributes>) {
  const { title, description, buttonText, buttonUrl, alignment } = attributes;
  const textAlign = alignment as Alignment;

  return (
    <div {...useBlockProps.save()}>
      <div className="wptypescript-cta" style={{ textAlign }}>
        {title && <RichText.Content tagName="h2" value={title} />}

        {description && <RichText.Content tagName="p" value={description} />}

        {buttonText && buttonUrl && (
          <a href={buttonUrl} className="wptypescript-cta-button">
            {buttonText}
          </a>
        )}
      </div>
    </div>
  );
}
