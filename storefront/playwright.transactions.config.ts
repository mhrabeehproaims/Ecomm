import { defineConfig } from '@playwright/test';
export default defineConfig({
  testDir: './tests/transactions', workers: 1, fullyParallel: false, timeout: 180_000, retries: 0,
  expect: { timeout: 15_000 },
  reporter: [['list'], ['html', { outputFolder: 'artifacts/transactions/report', open: 'never' }], ['json', { outputFile: 'artifacts/transactions/results.json' }]],
  outputDir: 'artifacts/transactions/test-results',
  use: { baseURL: 'http://127.0.0.1:3140', browserName: 'chromium', viewport: { width: 393, height: 852 }, reducedMotion: 'reduce', screenshot: 'only-on-failure' },
});
