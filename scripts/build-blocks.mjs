import { rollup } from 'rollup';
import esbuild from 'rollup-plugin-esbuild';
import { resolve, dirname } from 'path';
import { readdirSync, existsSync, mkdirSync, writeFileSync } from 'fs';
import { fileURLToPath } from 'url';
import * as sass from 'sass';

const __dirname = dirname(fileURLToPath(import.meta.url));
const blocksDir = resolve(__dirname, '../blocks');
const blockNames = readdirSync(blocksDir, { withFileTypes: true })
  .filter(d => d.isDirectory())
  .map(d => d.name);

const wpGlobals = {
  '@wordpress/blocks': 'wp.blocks',
  '@wordpress/i18n': 'wp.i18n',
  '@wordpress/element': 'wp.element',
  '@wordpress/components': 'wp.components',
  '@wordpress/block-editor': 'wp.blockEditor',
  '@wordpress/compose': 'wp.compose',
  '@wordpress/data': 'wp.data',
  '@wordpress/hooks': 'wp.hooks',
  '@wordpress/date': 'wp.date',
  '@wordpress/url': 'wp.url',
  '@wordpress/api-fetch': 'wp.apiFetch',
  'react': 'React',
  'react-dom': 'ReactDOM',
};

for (const name of blockNames) {
  const srcDir = resolve(blocksDir, name, 'src');
  const outDir = resolve(blocksDir, name, 'build');
  mkdirSync(outDir, { recursive: true });

  if (!existsSync(resolve(srcDir, 'index.tsx'))) continue;

  // Build JS with Rollup + esbuild plugin → index.js (IIFE)
  const bundle = await rollup({
    input: resolve(srcDir, 'index.tsx'),
    external: Object.keys(wpGlobals),
    plugins: [esbuild({
      include: /\.(tsx?|jsx?)$/,
      tsconfig: resolve(__dirname, '../tsconfig.json'),
      minify: true,
    })],
  });

  await bundle.write({
    file: resolve(outDir, 'index.js'),
    format: 'iife',
    name: `WpTypescriptCta`,
    globals: wpGlobals,
  });

  await bundle.close();

  // Compile style.scss → style-index.css
  const stylePath = resolve(srcDir, 'style.scss');
  if (existsSync(stylePath)) {
    const result = sass.compile(stylePath, { style: 'compressed' });
    writeFileSync(resolve(outDir, 'style-index.css'), result.css);
  }

  // Compile editor.scss → index.css
  const editorPath = resolve(srcDir, 'editor.scss');
  if (existsSync(editorPath)) {
    const result = sass.compile(editorPath, { style: 'compressed' });
    writeFileSync(resolve(outDir, 'index.css'), result.css);
  }
}

console.log('Blocks built successfully');
