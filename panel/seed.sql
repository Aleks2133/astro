-- ============================================================================
--  panimatcha.pl — dane startowe (placeholder)
-- ============================================================================
--
--  Treści zastępcze dla kawiarni / matcha baru. Są napisane tak, żeby strona
--  wyglądała sensownie od pierwszego buildu, ale WSZYSTKO tutaj jest do
--  podmiany przez panel: nazwy, opisy, ceny, godziny, kontakt.
--
--  photo_url zostawiamy puste — zdjęcia dodaje się przez panel (upload.php),
--  który zapisuje pliki do /panel/uploads/ i sam uzupełnia ścieżkę.
--
--  Uruchomienie (phpMyAdmin → import, albo z konsoli):
--      mysql -u UZYTKOWNIK -p NAZWA_BAZY < seed.sql
--
--  UWAGA: skrypt kasuje zawartość tabel products, categories i events.
--
--  Kodowanie: baza działa na utf8mb4 (patrz config.php), więc polskie znaki
--  przechodzą bez zmian. Przy imporcie upewnij się, że plik jest wczytywany
--  jako UTF-8.
-- ============================================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
--  1. Czyszczenie danych testowych
-- ----------------------------------------------------------------------------
--  Kolejność ma znaczenie: products odwołuje się do categories przez
--  category_id, więc produkty lecą pierwsze.

DELETE FROM products;
DELETE FROM categories;

ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;

--  Wydarzenia czyścimy — w bazie siedzi testowa "Promocja".
DELETE FROM events;

--  Galerii i opinii nie ruszamy: te tabele są puste, nie ma czego kasować.
--  Gdyby kiedyś trafiły tam wpisy testowe, odkomentuj poniższe linie:
--
-- DELETE FROM gallery;
-- DELETE FROM opinions;
-- ALTER TABLE gallery AUTO_INCREMENT = 1;
-- ALTER TABLE events AUTO_INCREMENT = 1;
-- ALTER TABLE opinions AUTO_INCREMENT = 1;

-- ----------------------------------------------------------------------------
--  2. Kategorie menu
-- ----------------------------------------------------------------------------
--  sort_order steruje kolejnością sekcji w menu na stronie.

INSERT INTO categories (name, sort_order) VALUES
    ('Matcha',        1),
    ('Kawa',          2),
    ('Napoje zimne',  3),
    ('Wypieki',       4);

-- ----------------------------------------------------------------------------
--  3. Produkty
-- ----------------------------------------------------------------------------
--  category_id wskazuje na kategorie wstawione wyżej (1-4, po resecie
--  AUTO_INCREMENT). is_available = 1 oznacza pozycję dostępną; ustawienie 0
--  zostawia produkt w menu, ale oznaczony jako chwilowo niedostępny.

INSERT INTO products (category_id, name, description, price, photo_url, is_available, sort_order) VALUES
    -- Matcha
    (1, 'Matcha latte',          'Ceremonialna matcha ubijana na gorąco, z mlekiem do wyboru.',            18.00, '', 1, 1),
    (1, 'Iced matcha latte',     'Matcha na zimno, z lodem i mlekiem owsianym.',                           20.00, '', 1, 2),
    (1, 'Matcha usucha',         'Czysta matcha parzona tradycyjnie, bez mleka i cukru.',                  16.00, '', 1, 3),
    (1, 'Matcha z wanilią',      'Matcha latte z domowym syropem waniliowym.',                             21.00, '', 1, 4),

    -- Kawa
    (2, 'Espresso',              'Pojedyncze espresso z ziaren palonych lokalnie.',                         9.00, '', 1, 1),
    (2, 'Flat white',            'Podwójne espresso i aksamitnie spienione mleko.',                        16.00, '', 1, 2),
    (2, 'Cappuccino',            'Klasyka w równych proporcjach kawy, mleka i pianki.',                    15.00, '', 1, 3),
    (2, 'Filter V60',            'Kawa przelewowa parzona ręcznie, zmienna oferta ziaren.',                14.00, '', 1, 4),

    -- Napoje zimne
    (3, 'Cold brew',             'Kawa parzona na zimno przez 16 godzin, łagodna i orzeźwiająca.',         17.00, '', 1, 1),
    (3, 'Lemoniada imbirowa',    'Świeża cytryna, imbir i woda gazowana.',                                14.00, '', 1, 2),
    (3, 'Iced chai latte',       'Korzenna herbata chai z mlekiem, podawana z lodem.',                     19.00, '', 1, 3),

    -- Wypieki
    (4, 'Cynamonka',             'Drożdżowa bułka z cynamonem, wypiekana codziennie rano.',                13.00, '', 1, 1),
    (4, 'Sernik z matchą',       'Kremowy sernik na zimno z warstwą matchy.',                              16.00, '', 1, 2),
    (4, 'Croissant maślany',     'Francuski croissant z masłem, chrupiący na zewnątrz.',                   11.00, '', 1, 3),
    (4, 'Ciasto marchewkowe',    'Wilgotne ciasto z orzechami i kremem z serka.',                          15.00, '', 1, 4);

-- ----------------------------------------------------------------------------
--  4. Galeria
-- ----------------------------------------------------------------------------
--  Wpisy z pustym photo_url nie pokażą się na stronie, dopóki nie wgrasz
--  zdjęcia przez panel — podpisy są gotowe, wystarczy dodać pliki.

INSERT INTO gallery (photo_url, caption, sort_order) VALUES
    ('', 'Wnętrze kawiarni',   1),
    ('', 'Poranna atmosfera',  2),
    ('', 'Nasze wypieki',      3);

-- ----------------------------------------------------------------------------
--  5. Wydarzenia
-- ----------------------------------------------------------------------------
--  Daty są ustawione na najbliższe tygodnie względem sierpnia 2026.
--  Po ich minięciu wydarzenia nadal będą widoczne na stronie — podmień daty
--  albo usuń wpisy przez panel.

INSERT INTO events (title, description, event_date, photo_url) VALUES
    ('Warsztaty parzenia matchy',
     'Godzinne spotkanie przy chasenie: uczymy ubijać matchę i dobierać temperaturę wody. Liczba miejsc ograniczona.',
     '2026-09-05', ''),
    ('Wieczór akustyczny',
     'Kameralny koncert na żywo przy świecach. Kuchnia i bar czynne do późna.',
     '2026-09-19', '');

-- ----------------------------------------------------------------------------
--  6. Opinie
-- ----------------------------------------------------------------------------
--  is_published = 1 oznacza opinię widoczną publicznie. Opinie z 0 widzi
--  wyłącznie zalogowany admin w panelu.

INSERT INTO opinions (author_name, content, rating, is_published, created_at) VALUES
    ('Anna K.',
     'Najlepsza matcha latte w mieście, a obsługa zawsze doradzi, co wybrać. Wracam co tydzień.',
     5, 1, '2026-07-14 10:20:00'),
    ('Marek Wiśniewski',
     'Spokojne miejsce do pracy, dobre wifi i mocne espresso. Bywa tłoczno w weekendy.',
     4, 1, '2026-07-28 16:45:00'),
    ('Julia',
     'Sernik z matchą to majstersztyk. Wnętrze klimatyczne, chętnie wpadam na dłużej.',
     5, 1, '2026-08-09 12:05:00');

-- ----------------------------------------------------------------------------
--  7. Ustawienia strony
-- ----------------------------------------------------------------------------
--  Wszystkie wartości to placeholdery do podmiany w panelu (zakładka
--  Ustawienia). ON DUPLICATE KEY UPDATE nadpisuje istniejące klucze, więc
--  skrypt można puścić ponownie bez błędu o duplikacie.
--
--  hero_text trafia na sam środek strony głównej jako główny nagłówek.

INSERT INTO site_settings (setting_key, setting_value) VALUES
    ('hero_text',     'Zaparzymy Ci chwilę spokoju — matcha, kawa i domowe wypieki każdego dnia.'),
    ('contact_phone', '+48 512 340 678'),
    ('contact_email', 'kontakt@panimatcha.pl'),
    ('opening_hours', 'pon.–pt. 8:00–18:00, sob.–niedz. 9:00–17:00'),
    ('instagram_url', 'https://www.instagram.com/panimatcha')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
