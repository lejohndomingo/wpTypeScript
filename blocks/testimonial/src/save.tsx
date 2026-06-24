import { useBlockProps, RichText } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

interface TestimonialAttributes {
  quote: string;
  authorName: string;
  authorRole: string;
  mediaUrl: string;
  backgroundColor: string;
  [key: string]: unknown;
}

export default function Save({ attributes }: BlockSaveProps<TestimonialAttributes>) {
  const { quote, authorName, authorRole, mediaUrl, backgroundColor } = attributes;

  return (
    <div {...useBlockProps.save()}>
      <div
        className="wptypescript-testimonial"
        style={{ backgroundColor: backgroundColor || undefined, textAlign: 'center' }}
      >
        {mediaUrl && (
          <div className="wptypescript-testimonial-avatar">
            <img src={mediaUrl} alt={authorName || ''} />
          </div>
        )}

        {quote && <RichText.Content tagName="blockquote" value={quote} />}

        {authorName && <RichText.Content tagName="cite" value={authorName} />}

        {authorRole && (
          <span className="wptypescript-testimonial-role">{authorRole}</span>
        )}
      </div>
    </div>
  );
}
