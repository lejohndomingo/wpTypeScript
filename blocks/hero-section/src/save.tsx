import { useBlockProps, RichText } from '@wordpress/block-editor';
import type { BlockSaveProps } from '@wordpress/blocks';

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
  mediaUrl: string;
  overlayColor: string;
  overlayOpacity: number;
  minHeight: number;
  [key: string]: unknown;
}

export default function Save({ attributes }: BlockSaveProps<HeroAttributes>) {
  const { title, subtitle, titleColor, subtitleColor, buttonText, buttonUrl, buttonColor, buttonTextColor, buttonBorderRadius, buttonBorderColor, buttonGradient, buttonHoverColor, buttonHoverTextColor, buttonTextDecoration, mediaUrl, overlayColor, overlayOpacity, minHeight, bgType, bgColor, videoUrl } = attributes;

  const buttonStyle: React.CSSProperties = {
    background: buttonGradient || buttonColor || undefined,
    color: buttonTextColor || undefined,
    borderRadius: buttonBorderRadius,
    border: buttonBorderColor ? `2px solid ${buttonBorderColor}` : undefined,
    textDecoration: buttonTextDecoration === 'underline' ? 'underline' : 'none',
    '--hero-btn-hover-bg': buttonHoverColor || undefined,
    '--hero-btn-hover-color': buttonHoverTextColor || undefined,
  } as React.CSSProperties;

  return (
    <div {...useBlockProps.save()}>
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
          {title && <RichText.Content tagName="h1" className="wptypescript-hero-title" value={title} />}
          {subtitle && <RichText.Content tagName="p" className="wptypescript-hero-subtitle" value={subtitle} />}
          {buttonText && buttonUrl && (
            <a href={buttonUrl} className="wptypescript-hero-button" style={buttonStyle}>{buttonText}</a>
          )}
        </div>
      </div>
    </div>
  );
}
