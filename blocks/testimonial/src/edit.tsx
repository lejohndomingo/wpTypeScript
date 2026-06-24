import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps, RichText, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, PanelBody, ColorPalette } from '@wordpress/components';

interface TestimonialAttributes {
  quote: string;
  authorName: string;
  authorRole: string;
  mediaId: number;
  mediaUrl: string;
  backgroundColor: string;
  [key: string]: unknown;
}

const ALLOWED_MEDIA_TYPES = ['image'];

export default function Edit({ attributes, setAttributes }: BlockEditProps<TestimonialAttributes>) {
  const { quote, authorName, authorRole, mediaUrl, backgroundColor } = attributes;

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__('Background Color', 'wptypescript')}>
          <ColorPalette
            value={backgroundColor}
            onChange={(color: string | undefined) =>
              setAttributes({ backgroundColor: color || '' })
            }
          />
        </PanelBody>
      </InspectorControls>

      <div
        className="wptypescript-testimonial"
        style={{ backgroundColor: backgroundColor || undefined, textAlign: 'center' }}
      >
        <div className="wptypescript-testimonial-avatar">
          <MediaUploadCheck>
            <MediaUpload
              onSelect={(media: { id: number; url: string }) =>
                setAttributes({ mediaId: media.id, mediaUrl: media.url })
              }
              allowedTypes={ALLOWED_MEDIA_TYPES}
              value={attributes.mediaId}
              render={({ open }) =>
                mediaUrl ? (
                  <button type="button" className="wptypescript-testimonial-image-btn" onClick={open}>
                    <img src={mediaUrl} alt={__('Author avatar', 'wptypescript')} />
                  </button>
                ) : (
                  <Button onClick={open} variant="secondary">
                    {__('Add Image', 'wptypescript')}
                  </Button>
                )
              }
            />
          </MediaUploadCheck>
        </div>

        <RichText
          tagName="blockquote"
          value={quote}
          onChange={(val: string) => setAttributes({ quote: val })}
          placeholder={__('Enter testimonial quote…', 'wptypescript')}
        />

        <RichText
          tagName="cite"
          value={authorName}
          onChange={(val: string) => setAttributes({ authorName: val })}
          placeholder={__('Author name', 'wptypescript')}
        />

        <RichText
          tagName="span"
          className="wptypescript-testimonial-role"
          value={authorRole}
          onChange={(val: string) => setAttributes({ authorRole: val })}
          placeholder={__('Author role', 'wptypescript')}
        />
      </div>
    </div>
  );
}
