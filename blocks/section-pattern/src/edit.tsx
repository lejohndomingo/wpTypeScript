import { useBlockProps } from '@wordpress/block-editor';
import patterns from './patterns';

type EditProps = {
  attributes: { patternName: string };
  setAttributes: (attrs: Record<string, unknown>) => void;
};

const Edit = ({ attributes }: EditProps) => {
  const blockProps = useBlockProps();
  const pattern = patterns[attributes.patternName];

  if (!pattern) {
    return <div {...blockProps}><p>Select a pattern variation.</p></div>;
  }

  return (
    <div {...blockProps}>
      <div style={{ padding: '12px', background: '#f0f0f1', borderRadius: '4px', marginBottom: '8px' }}>
        <strong>{pattern.title}</strong>
      </div>
      <div dangerouslySetInnerHTML={{ __html: pattern.content }} />
    </div>
  );
};

export default Edit;
