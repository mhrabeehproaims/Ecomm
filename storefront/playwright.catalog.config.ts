import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/catalog', workers: 1, fullyParallel: false, retries: 0,
  timeout: 90_000, expect: { timeout: 15_000 },
  reporter: [['list'], ['json', { outputFile: 'artifacts/catalog-sync/results.json' }]],
  outputDir: 'artifacts/catalog-sync/test-results',
  use: { baseURL: 'http://127.0.0.1:3150', browserName: 'chromium', screenshot: 'only-on-failure', trace: 'retain-on-failure' },
});
