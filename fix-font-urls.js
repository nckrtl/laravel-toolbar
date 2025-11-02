// scripts/fix-font-urls.js
import { readFileSync, writeFileSync } from 'fs'
import { glob } from 'glob'

const cssFiles = glob.sync('./build/assets/*.css')

cssFiles.forEach(file => {
  let content = readFileSync(file, 'utf-8')

  console.log(`Processing: ${file}`)

  // Match the actual Vite output format
  // Matches: url(/build/assets/FontName-hash.ttf)
  content = content.replace(
    /url\((["']?)\/build\/assets\/([^)"']+\.(ttf|woff2?|otf))\1\)/gi,
    (match, quote, filename) => {
      console.log(`  Replacing: ${match} -> url(${quote}/toolbar/assets/${filename}${quote})`)
      return `url(${quote}/_toolbar/${filename}${quote})`
    }
  )

  writeFileSync(file, content)
  console.log(`✅ Updated: ${file}\n`)
})

console.log('Font URL fixing complete!')