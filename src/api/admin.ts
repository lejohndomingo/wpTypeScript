/** Payload for saving a single theme option */
export interface SaveOptionPayload {
  option_name: string;
  option_value: string;
}

/** Payload for saving multiple theme options at once */
export interface SaveOptionsPayload {
  options: Record<string, string>;
}

interface SaveResponse {
  success: boolean;
  message?: string;
}

function getHeaders(): HeadersInit {
  const data = window.wptypescriptAdminData;
  return {
    'Content-Type': 'application/json',
    'X-WP-Nonce': data?.restNonce || '',
  };
}

function getBaseUrl(): string {
  const data = window.wptypescriptAdminData;
  return data?.restUrl || '/wp-json/wptypescript/v1';
}

export async function saveOption(name: string, value: string): Promise<SaveResponse> {
  const res = await fetch(`${getBaseUrl()}/save-option`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ option_name: name, option_value: value } satisfies SaveOptionPayload),
  });
  if (!res.ok) {
    throw new Error(`Save failed: ${res.status} ${res.statusText}`);
  }
  return res.json();
}

export async function saveOptions(options: Record<string, string>): Promise<SaveResponse> {
  const res = await fetch(`${getBaseUrl()}/save-options`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ options } satisfies SaveOptionsPayload),
  });
  if (!res.ok) {
    throw new Error(`Save failed: ${res.status} ${res.statusText}`);
  }
  return res.json();
}
