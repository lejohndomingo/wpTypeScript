import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, MediaUpload, MediaUploadCheck, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Button, RangeControl, ColorPicker, TextControl, SelectControl } from '@wordpress/components';

interface HeroAttributes {
  title: string;
  subtitle: string;
  titleColor: string;
  subtitleColor: string;
  buttonText: string;
  buttonUrl: string;
  buttonColor: string;
  buttonTextColor: string;
  buttonBorderRadius: number;
  buttonBorderColor: string;
  buttonGradient: string;
  buttonHoverColor: string;
  buttonHoverTextColor: string;
  buttonTextDecoration: string;
  bgType: string;
  bgColor: string;
  videoUrl: string;
  videoId: number;
  mediaId: number;
  mediaUrl: string;
  overlayColor: string;
  overlayOpacity: number;
  minHeight: number;
  [key: string]: unknown;
}

function colorToString(c: unknown): string {
  if (!c) return '';
  if (typeof c === 'string') return c;
  if (typeof c === 'object' && c !== null && 'hex' in c) return (c as { hex: string }).hex;
  return '';
}

export default function Edit({ attributes, setAttributes }: BlockEditProps<HeroAttributes>) {
  const { title, subtitle, titleColor, subtitleColor, buttonText, buttonUrl, mediaUrl, overlayColor, overlayOpacity, minHeight, buttonColor, buttonTextColor, buttonBorderRadius, buttonBorderColor, buttonGradient, buttonHoverColor, buttonHoverTextColor, buttonTextDecoration, bgType, bgColor, videoUrl } = attributes;

  const buttonStyle: React.CSSProperties = {
    background: buttonGradient || buttonColor || undefined,
    color: buttonTextColor || undefined,
    borderRadius: buttonBorderRadius,
    border: buttonBorderColor ? `2px solid ${buttonBorderColor}` : undefined,
    textDecoration: buttonTextDecoration === 'underline' ? 'underline' : 'none',
  } as React.CSSProperties;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Background', 'wptypescript')} initialOpen={true}>
          <SelectControl
            label={__('Background Type', 'wptypescript')}
            value={bgType || 'image'}
            options={[
              { label: 'Image', value: 'image' },
              { label: 'Solid Color', value: 'color' },
              { label: 'Video', value: 'video' },
            ]}
            onChange={(val: string) => setAttributes({ bgType: val })}
          />
          {(bgType === 'image' || !bgType) && (
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media: { id: number; url: string }) =>
                  setAttributes({ mediaId: media.id, mediaUrl: media.url })
                }
                allowedTypes={['image']}
                value={attributes.mediaId}
                render={({ open }: { open: () => void }) =>
                  mediaUrl ? (
                    <div>
                      <img src={mediaUrl} alt={__('Background', 'wptypescript')} style={{ maxWidth: '100%', marginBottom: '8px', borderRadius: '4px' }} />
                      <Button onClick={open} variant="secondary">{__('Replace Image', 'wptypescript')}</Button>
                      <Button onClick={() => setAttributes({ mediaId: 0, mediaUrl: '' })} variant="link" isDestructive>{__('Remove', 'wptypescript')}</Button>
                    </div>
                  ) : (
                    <Button onClick={open} variant="secondary">{__('Select Image', 'wptypescript')}</Button>
                  )
                }
              />
            </MediaUploadCheck>
          )}
          {bgType === 'color' && (
            <>
              <ColorPicker
                color={bgColor || '#1e293b'}
                onChange={(c: unknown) => setAttributes({ bgColor: colorToString(c) || '' })}
                enableAlpha
              />
              <Button onClick={() => setAttributes({ bgColor: '' })} variant="link" isDestructive>{__('Clear', 'wptypescript')}</Button>
            </>
          )}
          {bgType === 'video' && (
            <MediaUploadCheck>
              <MediaUpload
                onSelect={(media: { id: number; url: string }) =>
                  setAttributes({ videoId: media.id, videoUrl: media.url })
                }
                allowedTypes={['video']}
                value={attributes.videoId}
                render={({ open }: { open: () => void }) =>
                  videoUrl ? (
                    <div>
                      <video src={videoUrl} style={{ maxWidth: '100%', marginBottom: '8px', borderRadius: '4px' }} muted autoPlay loop />
                      <Button onClick={open} variant="secondary">{__('Replace Video', 'wptypescript')}</Button>
                      <Button onClick={() => setAttributes({ videoId: 0, videoUrl: '' })} variant="link" isDestructive>{__('Remove', 'wptypescript')}</Button>
                    </div>
                  ) : (
                    <Button onClick={open} variant="secondary">{__('Select Video', 'wptypescript')}</Button>
                  )
                }
              />
            </MediaUploadCheck>
          )}
        </PanelBody>
        <PanelBody title={__('Typography', 'wptypescript')} initialOpen={true}>
          <label>{__('Title Color', 'wptypescript')}</label>
          <ColorPicker
            color={titleColor || '#ffffff'}
            onChange={(c: unknown) => setAttributes({ titleColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ titleColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <label>{__('Subtitle Color', 'wptypescript')}</label>
          <ColorPicker
            color={subtitleColor || '#ffffff'}
            onChange={(c: unknown) => setAttributes({ subtitleColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ subtitleColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
        </PanelBody>
        <PanelBody title={__('Overlay', 'wptypescript')} initialOpen={false}>
          <RangeControl
            label={__('Opacity', 'wptypescript')}
            value={overlayOpacity}
            onChange={(val: number) => setAttributes({ overlayOpacity: val })}
            min={0}
            max={100}
          />
        </PanelBody>
        <PanelBody title={__('Button Settings', 'wptypescript')} initialOpen={true}>
          <TextControl
            label={__('Button URL', 'wptypescript')}
            value={buttonUrl}
            onChange={(val: string) => setAttributes({ buttonUrl: val })}
            placeholder={__('https://example.com', 'wptypescript')}
          />
          <label>{__('Button Color', 'wptypescript')}</label>
          <ColorPicker
            color={buttonColor || '#ffffff'}
            onChange={(c: unknown) => setAttributes({ buttonColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ buttonColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <label>{__('Text Color', 'wptypescript')}</label>
          <ColorPicker
            color={buttonTextColor || '#1e293b'}
            onChange={(c: unknown) => setAttributes({ buttonTextColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ buttonTextColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <label>{__('Border Color', 'wptypescript')}</label>
          <ColorPicker
            color={buttonBorderColor || '#000000'}
            onChange={(c: unknown) => setAttributes({ buttonBorderColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ buttonBorderColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <RangeControl
            label={__('Border Radius (px)', 'wptypescript')}
            value={buttonBorderRadius}
            onChange={(val: number) => setAttributes({ buttonBorderRadius: val })}
            min={0}
            max={50}
          />
          <TextControl
            label={__('Gradient (overrides color)', 'wptypescript')}
            value={buttonGradient}
            onChange={(val: string) => setAttributes({ buttonGradient: val })}
            placeholder={__('e.g. linear-gradient(...)', 'wptypescript')}
            help={__('Leave empty to use solid color above.', 'wptypescript')}
          />
          <label>{__('Hover Background', 'wptypescript')}</label>
          <ColorPicker
            color={buttonHoverColor || '#ffffff'}
            onChange={(c: unknown) => setAttributes({ buttonHoverColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ buttonHoverColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <label>{__('Hover Text Color', 'wptypescript')}</label>
          <ColorPicker
            color={buttonHoverTextColor || '#ffffff'}
            onChange={(c: unknown) => setAttributes({ buttonHoverTextColor: colorToString(c) || '' })}
            enableAlpha
          />
          <Button onClick={() => setAttributes({ buttonHoverTextColor: '' })} variant="link" isDestructive style={{ marginBottom: '12px' }}>{__('Clear', 'wptypescript')}</Button>
          <SelectControl
            label={__('Text Decoration', 'wptypescript')}
            value={buttonTextDecoration}
            options={[
              { label: 'None', value: 'none' },
              { label: 'Underline', value: 'underline' },
            ]}
            onChange={(val: string) => setAttributes({ buttonTextDecoration: val })}
          />
        </PanelBody>
        <PanelBody title={__('Height', 'wptypescript')} initialOpen={false}>
          <RangeControl
            label={__('Min Height (vh)', 'wptypescript')}
            value={minHeight}
            onChange={(val: number) => setAttributes({ minHeight: val })}
            min={30}
            max={100}
            step={5}
          />
        </PanelBody>
      </InspectorControls>

      <div {...useBlockProps()}>
        <div
          className="wptypescript-hero-background"
          style={{
            backgroundImage: bgType === 'video' || bgType === 'color' || !mediaUrl ? undefined : `url(${mediaUrl})`,
            backgroundColor: bgType === 'color' ? bgColor || '#1e293b' : (mediaUrl ? undefined : '#1e293b'),
            minHeight: `${minHeight}vh`,
          }}
        >
          {bgType === 'video' && videoUrl && (
            <video className="wptypescript-hero-video" src={videoUrl} muted autoPlay loop playsInline />
          )}
          <div className="wptypescript-hero-overlay" style={{ backgroundColor: overlayColor, opacity: overlayOpacity / 100 }} />
          <div className="wptypescript-hero-content" style={{ '--hero-title-color': titleColor || undefined, '--hero-subtitle-color': subtitleColor || undefined } as React.CSSProperties}>
            <RichText
              tagName="h1"
              className="wptypescript-hero-title"
              value={title}
              onChange={(val: string) => setAttributes({ title: val })}
              placeholder={__('Enter hero title…', 'wptypescript')}
            />
            <RichText
              tagName="p"
              className="wptypescript-hero-subtitle"
              value={subtitle}
              onChange={(val: string) => setAttributes({ subtitle: val })}
              placeholder={__('Enter subtitle…', 'wptypescript')}
            />
            <div className="wptypescript-hero-button" style={buttonStyle}>
              <RichText
                tagName="span"
                value={buttonText}
                onChange={(val: string) => setAttributes({ buttonText: val })}
                placeholder={__('Button text', 'wptypescript')}
              />
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
