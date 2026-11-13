// Dynamic category segments arrive percent-encoded in the installed Next runtime.
export function decodedSegment(value: string): string | null {
  try { return decodeURIComponent(value); } catch { return null; }
}
