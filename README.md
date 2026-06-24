# wpTypeScript Theme

A modern WordPress theme built with TypeScript and Vite for a clean, development-friendly workflow.

## Features

- **TypeScript**: Type-safe JavaScript development
- **Vite**: Fast build tool with HMR (Hot Module Replacement)
- **SCSS**: Modern CSS preprocessing with variables and nesting
- **Modern Build Pipeline**: Optimized production builds with asset hashing
- **WordPress Standards**: Follows WordPress theme development best practices

## Project Structure

```
wpTypeScript/
├── assets/              # Compiled assets (generated)
│   ├── js/              # Compiled JavaScript
│   ├── css/             # Compiled CSS
│   └── assets/          # Other compiled assets
├── src/
│   ├── ts/              # TypeScript source files
│   │   └── main.ts      # Main entry point
│   └── scss/            # SCSS source files
│       └── main.scss    # Main stylesheet
├── functions.php        # WordPress theme functions
├── style.css            # WordPress theme header
├── index.php            # Main template
├── header.php           # Header template
├── footer.php           # Footer template
├── package.json         # Node dependencies
├── tsconfig.json        # TypeScript configuration
├── vite.config.ts       # Vite build configuration
└── README.md            # This file
```

## Installation

1. **Install Dependencies**

   ```bash
   npm install
   ```

2. **Development Mode**

   ```bash
   npm run dev
   ```

   This starts Vite's development server with HMR enabled.

3. **Build for Production**

   ```bash
   npm run build
   ```

   This compiles TypeScript and SCSS to the `assets/` directory.

4. **Type Checking**
   ```bash
   npm run type-check
   ```

## Development Workflow

1. Edit TypeScript files in `src/ts/`
2. Edit SCSS files in `src/scss/`
3. Run `npm run dev` for development with hot reload
4. Run `npm run build` before committing/deploying

## WordPress Integration

The theme automatically enqueues compiled assets:

- JavaScript: `assets/js/main.js`
- CSS: `assets/css/styles.css`

Compiled assets are versioned with the theme version for cache busting.

## Adding New TypeScript Modules

Create new `.ts` files in `src/ts/` and import them in `main.ts`:

```typescript
import { myFunction } from './my-module';
```

## Adding New SCSS Files

Create new `.scss` files in `src/scss/` and import them in `main.scss`:

```scss
@import './my-styles';
```

## Production Deployment

Before deploying:

1. Run `npm run build` to compile assets
2. Commit the compiled `assets/` directory
3. Deploy the theme to your WordPress installation

## Requirements

- Node.js 18+
- PHP 7.4+
- WordPress 5.0+

## License

GPL-2.0-or-later
