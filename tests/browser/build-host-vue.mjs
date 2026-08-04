import { build } from 'vite';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(fileURLToPath(import.meta.url));
const configFile = resolve(root, 'fixtures/host-vue-bundled/vite.config.ts');

await build({ configFile });
console.log('Built host-vue-bundled fixture');
