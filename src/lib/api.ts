/**
 * Klient API panelu CMS.
 *
 * Wszystkie funkcje są wywoływane WYŁĄCZNIE w czasie buildu (frontmatter .astro),
 * dzięki czemu strona pozostaje w pełni statyczna. Żaden z tych fetchy nie trafia
 * do przeglądarki.
 *
 * Gdy panel jest niedostępny (offline, deploy w toku), zwracamy pustą kolekcję
 * zamiast wywracać build — sekcja bez danych po prostu się nie renderuje.
 */

/** Domena panelu — nadpisywalna zmienną środowiskową na czas buildu. */
export const API_BASE = (
  import.meta.env.PUBLIC_API_BASE ?? 'https://panimatcha.pl'
).replace(/\/$/, '');

export interface Category {
  id: number;
  name: string;
  sort_order: number;
}

export interface Product {
  id: number;
  category_id: number | null;
  name: string;
  description: string;
  price: string;
  photo_url: string;
  is_available: number;
  sort_order: number;
  category_name: string | null;
}

export interface GalleryPhoto {
  id: number;
  photo_url: string;
  caption: string;
  sort_order: number;
}

export interface EventItem {
  id: number;
  title: string;
  description: string;
  event_date: string;
  photo_url: string;
}

export interface Opinion {
  id: number;
  author_name: string;
  content: string;
  rating: number;
  created_at: string;
}

export interface Settings {
  hero_text: string;
  contact_phone: string;
  contact_email: string;
  opening_hours: string;
  instagram_url: string;
}

const EMPTY_SETTINGS: Settings = {
  hero_text: '',
  contact_phone: '',
  contact_email: '',
  opening_hours: '',
  instagram_url: '',
};

/**
 * Panel trzyma sesję PHP, więc równoległe żądania ustawiają się w kolejce po
 * blokadę pliku sesji i potrafią przekroczyć timeout. Dlatego pobieramy
 * sekwencyjnie (patrz `fetchSiteData`) i dokładamy jedną próbę ponowienia.
 */
async function getJSON<T>(endpoint: string, fallback: T, attempts = 2): Promise<T> {
  const url = `${API_BASE}/panel/api/${endpoint}`;

  for (let attempt = 1; attempt <= attempts; attempt++) {
    try {
      const res = await fetch(url, {
        headers: { Accept: 'application/json' },
        signal: AbortSignal.timeout(20_000),
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return (await res.json()) as T;
    } catch (err) {
      const message = (err as Error).message;
      if (attempt < attempts) {
        console.warn(`[api] ${endpoint} — próba ${attempt} nieudana (${message}), ponawiam`);
        continue;
      }
      console.warn(`[api] ${endpoint} niedostępny (${message}), używam danych zastępczych`);
    }
  }

  return fallback;
}

export const getCategories = () => getJSON<Category[]>('categories.php', []);
export const getProducts = () => getJSON<Product[]>('products.php', []);
export const getGallery = () => getJSON<GalleryPhoto[]>('gallery.php', []);
export const getEvents = () => getJSON<EventItem[]>('events.php', []);
export const getOpinions = () => getJSON<Opinion[]>('opinions.php', []);
export const getSettings = () => getJSON<Settings>('settings.php', EMPTY_SETTINGS);

/**
 * Pobiera komplet treści strony — sekwencyjnie, jedno żądanie po drugim.
 * Wywoływane raz, w czasie buildu.
 */
export async function fetchSiteData() {
  return {
    settings: await getSettings(),
    categories: await getCategories(),
    products: await getProducts(),
    gallery: await getGallery(),
    events: await getEvents(),
    opinions: await getOpinions(),
  };
}

/** Ścieżki z panelu są względne (/panel/uploads/...) — sklejamy je z domeną. */
export function mediaUrl(path: string | null | undefined): string | null {
  if (!path) return null;
  if (/^https?:\/\//i.test(path)) return path;
  return `${API_BASE}/${path.replace(/^\//, '')}`;
}

/** "12.00" → "12,00 zł" */
export function formatPrice(price: string | number): string {
  const value = typeof price === 'number' ? price : parseFloat(price);
  if (!Number.isFinite(value)) return '';
  return `${value.toFixed(2).replace('.', ',')} zł`;
}

/** "2026-08-22" → "22 sierpnia 2026" */
export function formatDate(date: string): string {
  const parsed = new Date(date);
  if (Number.isNaN(parsed.getTime())) return date;
  return new Intl.DateTimeFormat('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(parsed);
}

/** Krótka forma na "kafelek" daty: { day: "22", month: "sie" } */
export function dateBadge(date: string): { day: string; month: string } | null {
  const parsed = new Date(date);
  if (Number.isNaN(parsed.getTime())) return null;
  return {
    day: new Intl.DateTimeFormat('pl-PL', { day: 'numeric' }).format(parsed),
    month: new Intl.DateTimeFormat('pl-PL', { month: 'short' }).format(parsed).replace('.', ''),
  };
}

/** Normalizuje adres z panelu (może być bez protokołu) do klikalnego linku. */
export function externalUrl(url: string | null | undefined): string | null {
  if (!url) return null;
  const trimmed = url.trim();
  if (!trimmed) return null;
  return /^https?:\/\//i.test(trimmed) ? trimmed : `https://${trimmed}`;
}

/** Grupuje produkty wg kategorii, zachowując kolejność z panelu. */
export function groupByCategory(categories: Category[], products: Product[]) {
  const sortedCategories = [...categories].sort(
    (a, b) => a.sort_order - b.sort_order || a.id - b.id
  );

  const groups = sortedCategories.map((category) => ({
    category,
    products: products.filter((p) => p.category_id === category.id),
  }));

  const orphans = products.filter(
    (p) => p.category_id === null || !categories.some((c) => c.id === p.category_id)
  );
  if (orphans.length > 0) {
    groups.push({
      category: { id: 0, name: 'Pozostałe', sort_order: 9999 },
      products: orphans,
    });
  }

  return groups.filter((group) => group.products.length > 0);
}
