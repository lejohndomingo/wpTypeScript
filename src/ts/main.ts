// Main TypeScript entry point for WordPress theme
// This file will be compiled to assets/js/main.js

console.log('WordPress TypeScript theme loaded');

// Example: DOM manipulation with TypeScript
document.addEventListener('DOMContentLoaded', (): void => {
  const body = document.body;
  
  // Add a class when DOM is ready
  body.classList.add('js-loaded');
  
  // Example: Type-safe event handling
  const mobileMenuToggle = document.querySelector('.mobile-menu-toggle') as HTMLElement;
  
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', (e: Event): void => {
      e.preventDefault();
      body.classList.toggle('menu-open');
    });
  }
});

// Example: Type-safe function
function getElementById<T extends HTMLElement>(id: string): T | null {
  return document.getElementById(id) as T | null;
}

// Export for use in other modules
export { getElementById };
