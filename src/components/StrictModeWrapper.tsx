import { StrictMode, type ReactNode } from 'react';

export function StrictModeWrapper({ children }: { children: ReactNode }) {
  return <StrictMode>{children}</StrictMode>;
}
