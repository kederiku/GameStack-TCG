import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import type { TestContext } from 'node:test';
import { createServer } from 'vite';

export function createSsrTestServer(context: TestContext) {
    const cacheDir = mkdtempSync(join(tmpdir(), 'gamestackr-vite-'));
    context.after(() => rmSync(cacheDir, { recursive: true, force: true }));

    return createServer({
        configFile: false,
        cacheDir,
        server: { middlewareMode: true, ws: false },
        resolve: { alias: { '@': join(process.cwd(), 'resources/js') } },
    });
}
