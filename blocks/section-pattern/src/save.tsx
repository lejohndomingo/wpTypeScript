import { useBlockProps } from '@wordpress/block-editor';
import patterns from './patterns';

type SaveProps = {
  attributes: { patternName: string };
};

const Save = ({ attributes }: SaveProps) => {
  const blockProps = useBlockProps.save();
  const pattern = patterns[attributes.patternName];

  if (!pattern) {
    return null;
  }

  return (
    <div {...blockProps} dangerouslySetInnerHTML={{ __html: pattern.content }} />
  );
};

export default Save;
