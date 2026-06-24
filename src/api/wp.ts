/** Value type for REST API query parameters */
export type QueryParamValue = string | number | boolean;
/** Map of query parameter names to their single or array values */
export type QueryParams = Record<string, QueryParamValue | QueryParamValue[]>;

/** Generic CRUD API client for a WordPress REST resource */
export interface ResourceApi<T extends { id?: number }> {
  list(params?: QueryParams): Promise<T[]>;
  get(id: number): Promise<T>;
  bySlug(slug: string): Promise<T | null>;
  create(data: Partial<T>): Promise<T>;
  update(id: number, data: Partial<T>): Promise<T>;
  remove(id: number): Promise<void>;
}

const BASE =
  window.wpTypeScriptData?.ajaxUrl?.replace(/\/admin-ajax\.php$/, '') || '/wp-json/wp/v2';

async function request<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${BASE}${endpoint}`, {
    headers: { 'Content-Type': 'application/json', ...options?.headers },
    ...options,
  });
  if (!res.ok) throw new Error(`WP API error: ${res.status} ${res.statusText}`);
  return res.json() as Promise<T>;
}

function buildQuery(params?: QueryParams): string {
  if (!params) return '';
  const entries = Object.entries(params).flatMap(([key, value]) =>
    Array.isArray(value) ? value.map((v) => [key, String(v)]) : [[key, String(value)]],
  );
  if (entries.length === 0) return '';
  return '?' + new URLSearchParams(entries).toString();
}

export function createResourceApi<T extends { id?: number }>(
  resource: string,
): ResourceApi<T> {
  return {
    list: (params) => request<T[]>(`/${resource}${buildQuery(params)}`),
    get: (id) => request<T>(`/${resource}/${id}`),
    bySlug: (slug) =>
      request<T[]>(`/${resource}?slug=${slug}`).then((rows) => rows[0] ?? null),
    create: (data) =>
      request<T>(`/${resource}`, {
        method: 'POST',
        body: JSON.stringify(data),
      }),
    update: (id, data) =>
      request<T>(`/${resource}/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data),
      }),
    remove: (id) =>
      request<void>(`/${resource}/${id}`, { method: 'DELETE' }),
  };
}

export const posts = createResourceApi<WPRestPost>('posts');
export const pages = createResourceApi<WPRestPage>('pages');
export const categories = createResourceApi<WPRestCategory>('categories');
export const media = createResourceApi<WPRestMedia>('media');
export const menus = createResourceApi<WPRestMenu>('menus');

export const getPosts = posts.list;
export const getPost = posts.bySlug;
export const getPages = pages.list;
export const getCategories = categories.list;
export const getMedia = media.get;
export const getMenus = menus.list;

export async function submitFormData<T = unknown>(
  url: string,
  data: Record<string, string>,
): Promise<T> {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data),
  });
  if (!res.ok) throw new Error(`Submit failed: ${res.status} ${res.statusText}`);
  return res.json() as Promise<T>;
}
