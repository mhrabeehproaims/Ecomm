// A fixed, configured origin prevents API-supplied URLs becoming arbitrary image hosts.
export function mediaUrl(origin: string, file?: string | null, base?: string | null, folder = ''): string | null {
  if (!file) return null;
  try {
    const root = new URL(origin);
    let path: string;
    if (/^https?:\/\//i.test(file)) path = new URL(file).pathname;
    else {
      const basePath = base ? new URL(base, root).pathname : `/storage/${folder}`;
      path = file.startsWith('/storage/') || file.startsWith('storage/') ? `/${file.replace(/^\//, '')}` : `${basePath.replace(/\/$/, '')}/${file.replace(/^\//, '')}`;
    }
    const result = new URL(path, root);
    if (result.origin !== root.origin || !result.pathname.startsWith('/storage/') || result.pathname.includes('\\')) return null;
    return result.href;
  } catch { return null; }
}
