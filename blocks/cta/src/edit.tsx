import { __ } from '@wordpress/i18n';
import { BlockControls, useBlockProps, RichText } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, TextControl } from '@wordpress/components';

type Alignment = 'left' | 'center' | 'right';

interface CtaAttributes {
  title: string;
  description: string;
  buttonText: string;
  buttonUrl: string;
  alignment: string;
  [key: string]: unknown;
}

export default function Edit({ attributes, setAttributes }: BlockEditProps<CtaAttributes>) {
  const { title, description, buttonText, buttonUrl, alignment } = attributes;

  return (
    <div {...useBlockProps()}>
      <BlockControls>
        <ToolbarGroup>
          <ToolbarButton
            icon="editor-alignleft"
            label={__('Align left', 'wptypescript')}
            isActive={alignment === 'left'}
            onClick={() => setAttributes({ alignment: 'left' })}
          />
          <ToolbarButton
            icon="editor-aligncenter"
            label={__('Align center', 'wptypescript')}
            isActive={alignment === 'center'}
            onClick={() => setAttributes({ alignment: 'center' })}
          />
          <ToolbarButton
            icon="editor-alignright"
            label={__('Align right', 'wptypescript')}
            isActive={alignment === 'right'}
            onClick={() => setAttributes({ alignment: 'right' })}
          />
        </ToolbarGroup>
      </BlockControls>

      <div className="wptypescript-cta" style={{ textAlign: alignment }}>
        <RichText
          tagName="h2"
          value={title}
          onChange={(val: string) => setAttributes({ title: val })}
          placeholder={__('Enter title…', 'wptypescript')}
        />

        <RichText
          tagName="p"
          value={description}
          onChange={(val: string) => setAttributes({ description: val })}
          placeholder={__('Enter description…', 'wptypescript')}
        />

        <div className="wptypescript-cta-button-row">
          <TextControl
            value={buttonText}
            onChange={(val: string) => setAttributes({ buttonText: val })}
            placeholder={__('Button text', 'wptypescript')}
          />
          <TextControl
            value={buttonUrl}
            onChange={(val: string) => setAttributes({ buttonUrl: val })}
            placeholder={__('Button URL', 'wptypescript')}
          />
        </div>
      </div>
    </div>
  );
}
