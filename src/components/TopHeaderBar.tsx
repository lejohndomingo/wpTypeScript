import { useScrollDirection } from '../hooks';

/** Props for the top header bar component */
interface TopHeaderBarProps {
  content: string;
  hideOnScroll?: boolean;
}

export function TopHeaderBar({ content, hideOnScroll }: TopHeaderBarProps) {
  const direction = useScrollDirection(50);
  const hidden = hideOnScroll && direction === 'down';

  return (
    <div
      className={`top-header-bar${hidden ? ' top-header-hidden' : ''}`}
      data-hide-scroll={hideOnScroll ? 'true' : 'false'}
    >
      <div className="container">
        <div className="top-header-content" dangerouslySetInnerHTML={{ __html: content }} />
      </div>
    </div>
  );
}
