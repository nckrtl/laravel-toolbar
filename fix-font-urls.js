import { readFileSync, writeFileSync } from 'fs';
import { glob } from 'glob';

const cssFiles = glob.sync('./build/assets/*.css');

cssFiles.forEach((file) => {
    let content = readFileSync(file, 'utf-8');

    console.log(`Processing: ${file}`);

    content = content.replace(
        /url\((["']?)\/build\/assets\/([^)"']+\.(ttf|woff2?|otf))\1\)/gi,
        (match, quote, filename) => {
            console.log(`  Replacing: ${match} -> url(${quote}/_toolbar/${filename}${quote})`);
            return `url(${quote}/_toolbar/${filename}${quote})`;
        },
    );

    writeFileSync(file, content);
    console.log(`✅ Updated: ${file}\n`);
});

console.log('Font URL fixing complete!');
