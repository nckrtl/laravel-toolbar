// vite-plugin-prefix-tailwind.js
import fs from 'fs';
import path from 'path';
import { globSync } from 'glob';

export function prefixTailwindClasses(options = {}) {
  const { prefix = 'tb', sourceGlob = 'resources/js/**/*.vue' } = options;

  let virtualSourceContent = '';

  return {
    name: 'prefix-tailwind-classes',

    buildStart() {
      // Scan all Vue files and extract classes
      const root = process.cwd();
      const vueFiles = globSync(sourceGlob, { cwd: root, absolute: true });

      console.log(`[prefix-tailwind] Found ${vueFiles.length} Vue files`);

      const allClasses = new Set();

      vueFiles.forEach(file => {
        try {
          const content = fs.readFileSync(file, 'utf-8');

          // Extract all classes from class="..."
          const staticClassMatches = content.matchAll(/class="([^"]*)"/g);
          for (const match of staticClassMatches) {
            match[1].split(/\s+/).forEach(cls => {
              if (cls.trim()) allClasses.add(cls.trim());
            });
          }

          // Extract all classes from :class="..."
          const dynamicClassMatches = content.matchAll(/:class="([^"]*)"/g);
          for (const match of dynamicClassMatches) {
            // Extract strings from within quotes
            const stringMatches = match[1].matchAll(/'([^']*)'/g);
            for (const strMatch of stringMatches) {
              strMatch[1].split(/\s+/).forEach(cls => {
                if (cls.trim()) allClasses.add(cls.trim());
              });
            }
          }
        } catch (err) {
          console.error(`[prefix-tailwind] Error reading ${file}:`, err);
        }
      });

      console.log(`[prefix-tailwind] Extracted ${allClasses.size} unique classes`);

      // Create virtual content with prefixed classes for Tailwind to scan
      virtualSourceContent = Array.from(allClasses)
        .map(cls => `<div class="${prefix}:${cls}"></div>`)
        .join('\n');

      // Write to a temp file that Tailwind can scan
      const tempFile = path.join(root, 'node_modules/.laravel-toolbar-classes.html');
      fs.mkdirSync(path.dirname(tempFile), { recursive: true });
      fs.writeFileSync(tempFile, virtualSourceContent);

      console.log(`[prefix-tailwind] Written prefixed classes to ${tempFile}`);
    },

    transform(code, id) {
      // Only process Vue files
      if (!id.endsWith('.vue')) {
        return null;
      }

      const templateMatch = code.match(/(<template[^>]*>)([\s\S]*?)(<\/template>)/);

      if (!templateMatch) {
        return null;
      }

      const [fullMatch, openTag, templateContent, closeTag] = templateMatch;
      const beforeTemplate = code.substring(0, code.indexOf(fullMatch));
      const afterTemplate = code.substring(code.indexOf(fullMatch) + fullMatch.length);

      let transformedTemplate = templateContent;

      // Match class=" only when NOT preceded by a colon or letter
      // This avoids matching :class=" or substrings like "textShadowClass="
      transformedTemplate = transformedTemplate.replace(
        /(?<![:a-zA-Z])class="([^"]*)"/g,
        (match, classContent) => {
          const prefixedClasses = prefixClass(classContent, prefix);
          return `class="${prefixedClasses}"`;
        }
      );

      // Match :class=" - must have the colon
      transformedTemplate = transformedTemplate.replace(
        /(?<![a-zA-Z]):class="([^"]*)"/g,
        (match, bindingContent) => {
          const processed = bindingContent.replace(/'([^']*)'/g, (m, className) => {
            const trimmed = className.trim();
            if (!trimmed) return m;

            const prefixedClasses = prefixClass(className, prefix);
            return `'${prefixedClasses}'`;
          });
          return `:class="${processed}"`;
        }
      );

      const transformedCode = beforeTemplate + openTag + transformedTemplate + closeTag + afterTemplate;

      return {
        code: transformedCode,
        map: null
      };
    }
  };
}

function prefixClass(classContent, prefix) {
  return classContent
    .split(/\s+/)
    .filter(cls => cls.trim())
    .map(cls => {
      const trimmed = cls.trim();

      // Skip empty
      if (!trimmed) {
        return trimmed;
      }

      // Handle !important modifier - prefix goes after the !
      if (trimmed.startsWith('!')) {
        const withoutBang = trimmed.substring(1);
        // Check if already prefixed after the !
        if (withoutBang.startsWith(`${prefix}:`)) {
          return trimmed;
        }
        return `!${prefix}:${withoutBang}`;
      }

      // Check if already prefixed (for regular classes)
      if (trimmed.startsWith(`${prefix}:`)) {
        return trimmed;
      }

      // Add prefix to all other classes
      return `${prefix}:${trimmed}`;
    })
    .join(' ');
}