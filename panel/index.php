<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel administracyjny</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f5f5f7;
            color: #222;
        }
        h1 { margin-top: 0; }
        h2 { margin-bottom: 12px; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .panel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }
        form.inline-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        form.inline-form label {
            display: flex;
            flex-direction: column;
            font-size: 13px;
            gap: 4px;
        }
        input, select, textarea {
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea { min-height: 60px; resize: vertical; }
        button {
            padding: 7px 14px;
            border: none;
            border-radius: 4px;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        button.secondary { background: #6b7280; }
        button.danger { background: #dc2626; }
        button:hover { opacity: 0.9; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        th { font-size: 13px; color: #666; }
        img.thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            background: #eee;
        }
        .actions button { margin-right: 6px; }
        .category-group { margin-top: 20px; }
        .category-group h3 {
            margin-bottom: 4px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 4px;
        }
        .status-msg { font-size: 13px; margin-top: 8px; }
        .status-msg.error { color: #dc2626; }
        .status-msg.success { color: #16a34a; }
        .unavailable { opacity: 0.5; }
        .badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            background: #eee;
        }
        .badge.available { background: #dcfce7; color: #166534; }
        .badge.unavailable { background: #fee2e2; color: #991b1b; }
        .gallery-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 12px;
        }
        .gallery-item {
            width: 160px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }
        .gallery-item img.thumb-lg {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            background: #eee;
        }
        .gallery-item .caption {
            font-size: 13px;
            margin: 6px 0;
            word-break: break-word;
        }
        .gallery-item .actions {
            display: flex;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h1>Panel administracyjny — menu</h1>
        <p><a href="logout.php">Wyloguj</a></p>
    </div>

    <div class="panel">
        <h2 id="category-form-title">Dodaj kategorię</h2>
        <form id="category-form" class="inline-form">
            <input type="hidden" id="category-id" value="">
            <label>
                Nazwa
                <input type="text" id="category-name" required>
            </label>
            <label>
                Kolejność
                <input type="number" id="category-sort-order" value="0">
            </label>
            <button type="submit">Zapisz</button>
            <button type="button" class="secondary" id="category-form-cancel" style="display:none;">Anuluj edycję</button>
        </form>
        <div id="category-status" class="status-msg"></div>

        <table>
            <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Kolejność</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody id="categories-table-body"></tbody>
        </table>
    </div>

    <div class="panel">
        <h2 id="product-form-title">Dodaj produkt</h2>
        <form id="product-form" class="inline-form" enctype="multipart/form-data">
            <input type="hidden" id="product-id" value="">
            <label>
                Nazwa
                <input type="text" id="product-name" required>
            </label>
            <label>
                Kategoria
                <select id="product-category-id" required></select>
            </label>
            <label>
                Cena
                <input type="number" id="product-price" step="0.01" min="0" required>
            </label>
            <label>
                Kolejność
                <input type="number" id="product-sort-order" value="0">
            </label>
            <label>
                Zdjęcie
                <input type="file" id="product-photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </label>
            <label>
                <input type="checkbox" id="product-is-available" checked>
                Dostępny
            </label>
            <label style="flex-basis: 100%;">
                Opis
                <textarea id="product-description"></textarea>
            </label>
            <button type="submit">Zapisz</button>
            <button type="button" class="secondary" id="product-form-cancel" style="display:none;">Anuluj edycję</button>
        </form>
        <div id="product-status" class="status-msg"></div>
    </div>

    <div class="panel">
        <h2>Produkty</h2>
        <div id="products-container"></div>
    </div>

    <div class="panel">
        <h2 id="gallery-form-title">Dodaj zdjęcie do galerii</h2>
        <form id="gallery-form" class="inline-form" enctype="multipart/form-data">
            <input type="hidden" id="gallery-id" value="">
            <label>
                Zdjęcie
                <input type="file" id="gallery-photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </label>
            <label>
                Podpis
                <input type="text" id="gallery-caption">
            </label>
            <label>
                Kolejność
                <input type="number" id="gallery-sort-order" value="0">
            </label>
            <button type="submit">Zapisz</button>
            <button type="button" class="secondary" id="gallery-form-cancel" style="display:none;">Anuluj edycję</button>
        </form>
        <div id="gallery-status" class="status-msg"></div>

        <div class="gallery-grid" id="gallery-container"></div>
    </div>

    <script>
    (function () {
        'use strict';

        var categoriesCache = [];
        var productsCache = [];

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        function setStatus(elId, message, isError) {
            var el = document.getElementById(elId);
            el.textContent = message;
            el.className = 'status-msg ' + (isError ? 'error' : 'success');
            if (message) {
                setTimeout(function () {
                    if (el.textContent === message) {
                        el.textContent = '';
                        el.className = 'status-msg';
                    }
                }, 4000);
            }
        }

        function apiRequest(url, options) {
            options = options || {};
            var opts = Object.assign({ headers: {} }, options);
            if (opts.body && !(opts.body instanceof FormData)) {
                opts.headers['Content-Type'] = 'application/json';
            }
            return fetch(url, opts).then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        throw new Error(data.error || 'Wystąpił błąd (' + res.status + ')');
                    }
                    return data;
                });
            });
        }

        // ---------- Categories ----------

        function loadCategories() {
            return apiRequest('api/categories.php').then(function (data) {
                categoriesCache = data;
                renderCategoriesTable();
                renderCategoryDropdown();
            }).catch(function (err) {
                setStatus('category-status', err.message, true);
            });
        }

        function renderCategoriesTable() {
            var tbody = document.getElementById('categories-table-body');
            tbody.innerHTML = '';
            categoriesCache.forEach(function (cat) {
                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td>' + escapeHtml(cat.name) + '</td>' +
                    '<td>' + escapeHtml(cat.sort_order) + '</td>' +
                    '<td class="actions">' +
                        '<button type="button" data-action="edit-category" data-id="' + cat.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-category" data-id="' + cat.id + '">Usuń</button>' +
                    '</td>';
                tbody.appendChild(tr);
            });
        }

        function renderCategoryDropdown() {
            var select = document.getElementById('product-category-id');
            var current = select.value;
            select.innerHTML = '';
            categoriesCache.forEach(function (cat) {
                var opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                select.appendChild(opt);
            });
            if (current) select.value = current;
        }

        document.getElementById('category-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('category-id').value;
            var payload = {
                name: document.getElementById('category-name').value.trim(),
                sort_order: parseInt(document.getElementById('category-sort-order').value, 10) || 0
            };

            var url = 'api/categories.php' + (id ? '?id=' + encodeURIComponent(id) : '');
            var method = id ? 'PUT' : 'POST';

            apiRequest(url, { method: method, body: JSON.stringify(payload) }).then(function () {
                setStatus('category-status', 'Zapisano kategorię.', false);
                resetCategoryForm();
                loadCategories().then(loadProducts);
            }).catch(function (err) {
                setStatus('category-status', err.message, true);
            });
        });

        document.getElementById('category-form-cancel').addEventListener('click', resetCategoryForm);

        function resetCategoryForm() {
            document.getElementById('category-id').value = '';
            document.getElementById('category-name').value = '';
            document.getElementById('category-sort-order').value = '0';
            document.getElementById('category-form-title').textContent = 'Dodaj kategorię';
            document.getElementById('category-form-cancel').style.display = 'none';
        }

        document.getElementById('categories-table-body').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-category') {
                var cat = categoriesCache.find(function (c) { return String(c.id) === String(id); });
                if (!cat) return;
                document.getElementById('category-id').value = cat.id;
                document.getElementById('category-name').value = cat.name;
                document.getElementById('category-sort-order').value = cat.sort_order;
                document.getElementById('category-form-title').textContent = 'Edytuj kategorię';
                document.getElementById('category-form-cancel').style.display = 'inline-block';
            } else if (action === 'delete-category') {
                if (!confirm('Na pewno usunąć tę kategorię? Produkty w niej pozostaną bez kategorii.')) return;
                apiRequest('api/categories.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('category-status', 'Usunięto kategorię.', false);
                    loadCategories().then(loadProducts);
                }).catch(function (err) {
                    setStatus('category-status', err.message, true);
                });
            }
        });

        // ---------- Products ----------

        function loadProducts() {
            return apiRequest('api/products.php').then(function (data) {
                productsCache = data;
                renderProducts();
            }).catch(function (err) {
                setStatus('product-status', err.message, true);
            });
        }

        function renderProducts() {
            var container = document.getElementById('products-container');
            container.innerHTML = '';

            var byCategory = {};
            productsCache.forEach(function (p) {
                var key = p.category_id == null ? '' : String(p.category_id);
                if (!byCategory[key]) byCategory[key] = [];
                byCategory[key].push(p);
            });

            var groups = categoriesCache.map(function (c) { return { id: c.id, name: c.name }; });
            groups.push({ id: '', name: 'Bez kategorii' });

            groups.forEach(function (group) {
                var items = byCategory[String(group.id)];
                if (!items || items.length === 0) return;

                var section = document.createElement('div');
                section.className = 'category-group';

                var table = document.createElement('table');
                table.innerHTML =
                    '<thead><tr>' +
                        '<th>Zdjęcie</th>' +
                        '<th>Nazwa</th>' +
                        '<th>Cena</th>' +
                        '<th>Status</th>' +
                        '<th>Akcje</th>' +
                    '</tr></thead>';

                var tbody = document.createElement('tbody');
                items.forEach(function (p) {
                    var tr = document.createElement('tr');
                    if (!Number(p.is_available)) tr.classList.add('unavailable');

                    var photoHtml = p.photo_url
                        ? '<img class="thumb" src="' + escapeHtml(p.photo_url) + '" alt="">'
                        : '<div class="thumb"></div>';

                    tr.innerHTML =
                        '<td>' + photoHtml + '</td>' +
                        '<td>' + escapeHtml(p.name) + '<br><small>' + escapeHtml(p.description || '') + '</small></td>' +
                        '<td>' + Number(p.price).toFixed(2) + ' zł</td>' +
                        '<td><span class="badge ' + (Number(p.is_available) ? 'available' : 'unavailable') + '">' +
                            (Number(p.is_available) ? 'Dostępny' : 'Niedostępny') + '</span></td>' +
                        '<td class="actions">' +
                            '<button type="button" data-action="edit-product" data-id="' + p.id + '">Edytuj</button>' +
                            '<button type="button" class="danger" data-action="delete-product" data-id="' + p.id + '">Usuń</button>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);

                var heading = document.createElement('h3');
                heading.textContent = group.name;

                section.appendChild(heading);
                section.appendChild(table);
                container.appendChild(section);
            });

            if (container.innerHTML === '') {
                container.innerHTML = '<p>Brak produktów.</p>';
            }
        }

        document.getElementById('product-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('product-id').value;
            var fileInput = document.getElementById('product-photo');
            var file = fileInput.files[0];

            function savePayload(photoUrl) {
                var payload = {
                    name: document.getElementById('product-name').value.trim(),
                    category_id: document.getElementById('product-category-id').value,
                    price: parseFloat(document.getElementById('product-price').value),
                    sort_order: parseInt(document.getElementById('product-sort-order').value, 10) || 0,
                    description: document.getElementById('product-description').value.trim(),
                    is_available: document.getElementById('product-is-available').checked ? 1 : 0,
                    photo_url: photoUrl
                };

                var url = 'api/products.php' + (id ? '?id=' + encodeURIComponent(id) : '');
                var method = id ? 'PUT' : 'POST';

                return apiRequest(url, { method: method, body: JSON.stringify(payload) });
            }

            var uploadPromise;
            if (file) {
                var formData = new FormData();
                formData.append('photo', file);
                uploadPromise = apiRequest('api/upload.php', { method: 'POST', body: formData }).then(function (res) {
                    return res.url;
                });
            } else {
                var existing = id ? productsCache.find(function (p) { return String(p.id) === String(id); }) : null;
                uploadPromise = Promise.resolve(existing ? existing.photo_url : '');
            }

            uploadPromise.then(savePayload).then(function () {
                setStatus('product-status', 'Zapisano produkt.', false);
                resetProductForm();
                loadProducts();
            }).catch(function (err) {
                setStatus('product-status', err.message, true);
            });
        });

        document.getElementById('product-form-cancel').addEventListener('click', resetProductForm);

        function resetProductForm() {
            document.getElementById('product-id').value = '';
            document.getElementById('product-name').value = '';
            document.getElementById('product-price').value = '';
            document.getElementById('product-sort-order').value = '0';
            document.getElementById('product-description').value = '';
            document.getElementById('product-is-available').checked = true;
            document.getElementById('product-photo').value = '';
            document.getElementById('product-form-title').textContent = 'Dodaj produkt';
            document.getElementById('product-form-cancel').style.display = 'none';
        }

        document.getElementById('products-container').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-product') {
                var p = productsCache.find(function (item) { return String(item.id) === String(id); });
                if (!p) return;
                document.getElementById('product-id').value = p.id;
                document.getElementById('product-name').value = p.name;
                document.getElementById('product-category-id').value = p.category_id || '';
                document.getElementById('product-price').value = p.price;
                document.getElementById('product-sort-order').value = p.sort_order;
                document.getElementById('product-description').value = p.description || '';
                document.getElementById('product-is-available').checked = !!Number(p.is_available);
                document.getElementById('product-photo').value = '';
                document.getElementById('product-form-title').textContent = 'Edytuj produkt';
                document.getElementById('product-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('product-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'delete-product') {
                if (!confirm('Na pewno usunąć ten produkt?')) return;
                apiRequest('api/products.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('product-status', 'Usunięto produkt.', false);
                    loadProducts();
                }).catch(function (err) {
                    setStatus('product-status', err.message, true);
                });
            }
        });

        // ---------- Gallery ----------

        var galleryCache = [];

        function loadGallery() {
            return apiRequest('api/gallery.php').then(function (data) {
                galleryCache = data;
                renderGallery();
            }).catch(function (err) {
                setStatus('gallery-status', err.message, true);
            });
        }

        function renderGallery() {
            var container = document.getElementById('gallery-container');
            container.innerHTML = '';

            if (galleryCache.length === 0) {
                container.innerHTML = '<p>Brak zdjęć w galerii.</p>';
                return;
            }

            galleryCache.forEach(function (item) {
                var div = document.createElement('div');
                div.className = 'gallery-item';

                var photoHtml = item.photo_url
                    ? '<img class="thumb-lg" src="' + escapeHtml(item.photo_url) + '" alt="">'
                    : '<div class="thumb-lg"></div>';

                div.innerHTML =
                    photoHtml +
                    '<div class="caption">' + escapeHtml(item.caption || '') + '</div>' +
                    '<div class="actions">' +
                        '<button type="button" data-action="edit-gallery" data-id="' + item.id + '">Edytuj</button>' +
                        '<button type="button" class="danger" data-action="delete-gallery" data-id="' + item.id + '">Usuń</button>' +
                    '</div>';

                container.appendChild(div);
            });
        }

        document.getElementById('gallery-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var id = document.getElementById('gallery-id').value;
            var fileInput = document.getElementById('gallery-photo');
            var file = fileInput.files[0];

            function savePayload(photoUrl) {
                if (!photoUrl) {
                    setStatus('gallery-status', 'Zdjęcie jest wymagane.', true);
                    return Promise.reject(new Error('Zdjęcie jest wymagane.'));
                }

                var payload = {
                    photo_url: photoUrl,
                    caption: document.getElementById('gallery-caption').value.trim(),
                    sort_order: parseInt(document.getElementById('gallery-sort-order').value, 10) || 0
                };

                var url = 'api/gallery.php' + (id ? '?id=' + encodeURIComponent(id) : '');
                var method = id ? 'PUT' : 'POST';

                return apiRequest(url, { method: method, body: JSON.stringify(payload) });
            }

            var uploadPromise;
            if (file) {
                var formData = new FormData();
                formData.append('photo', file);
                uploadPromise = apiRequest('api/upload.php', { method: 'POST', body: formData }).then(function (res) {
                    return res.url;
                });
            } else {
                var existing = id ? galleryCache.find(function (g) { return String(g.id) === String(id); }) : null;
                uploadPromise = Promise.resolve(existing ? existing.photo_url : '');
            }

            uploadPromise.then(savePayload).then(function () {
                setStatus('gallery-status', 'Zapisano zdjęcie.', false);
                resetGalleryForm();
                loadGallery();
            }).catch(function (err) {
                if (err) setStatus('gallery-status', err.message, true);
            });
        });

        document.getElementById('gallery-form-cancel').addEventListener('click', resetGalleryForm);

        function resetGalleryForm() {
            document.getElementById('gallery-id').value = '';
            document.getElementById('gallery-caption').value = '';
            document.getElementById('gallery-sort-order').value = '0';
            document.getElementById('gallery-photo').value = '';
            document.getElementById('gallery-form-title').textContent = 'Dodaj zdjęcie do galerii';
            document.getElementById('gallery-form-cancel').style.display = 'none';
        }

        document.getElementById('gallery-container').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var action = btn.getAttribute('data-action');

            if (action === 'edit-gallery') {
                var item = galleryCache.find(function (g) { return String(g.id) === String(id); });
                if (!item) return;
                document.getElementById('gallery-id').value = item.id;
                document.getElementById('gallery-caption').value = item.caption || '';
                document.getElementById('gallery-sort-order').value = item.sort_order;
                document.getElementById('gallery-photo').value = '';
                document.getElementById('gallery-form-title').textContent = 'Edytuj zdjęcie';
                document.getElementById('gallery-form-cancel').style.display = 'inline-block';
                window.scrollTo({ top: document.getElementById('gallery-form').offsetTop - 20, behavior: 'smooth' });
            } else if (action === 'delete-gallery') {
                if (!confirm('Na pewno usunąć to zdjęcie z galerii?')) return;
                apiRequest('api/gallery.php?id=' + encodeURIComponent(id), { method: 'DELETE' }).then(function () {
                    setStatus('gallery-status', 'Usunięto zdjęcie.', false);
                    loadGallery();
                }).catch(function (err) {
                    setStatus('gallery-status', err.message, true);
                });
            }
        });

        // ---------- Init ----------

        loadCategories().then(loadProducts);
        loadGallery();
    })();
    </script>
</body>
</html>
