// Ambil CSRF token dari <meta> di header.php
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Kirim data JSON ke server dengan method POST
async function postJson(url, data) {
    var response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify(data)
    });
    return response.json();
}

// Tampilkan pesan singkat di bawah layar
var toastTimer = null;
function showToast(message, type) {
    var toast = document.getElementById('toast');
    if (!toast) { return; }
    toast.textContent = message;
    toast.className = 'toast show ' + (type || '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toast.className = 'toast'; }, 2800);
}

// Buat elemen HTML dengan class dan teks (textContent aman dari XSS)
function createElement(tag, className, text) {
    var element = document.createElement(tag);
    if (className) { element.className = className; }
    if (text !== undefined) { element.textContent = text; }
    return element;
}

// Buat ikon SVG sederhana (hati, pesan, hapus)
function createIcon(name) {
    var paths = {
        heart: '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        message: '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        trash: '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>'
    };
    var wrapper = document.createElement('span');
    // Isi SVG berasal dari konstanta di atas, bukan dari input pengguna
    wrapper.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
        'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + paths[name] + '</svg>';
    return wrapper.firstChild;
}

// Buat badge "Tamu"
function createGuestBadge() {
    return createElement('span', 'badge', 'Tamu');
}

// Buat avatar bulat berisi huruf pertama username
function createAvatar(username, color, size) {
    var avatar = createElement('span', 'avatar avatar-' + size, username.charAt(0).toUpperCase());
    if (/^#[0-9a-fA-F]{6}$/.test(color)) { avatar.style.background = color; }
    return avatar;
}

document.addEventListener('click', function (event) {
    var toggle = event.target.closest('.js-theme-toggle');
    if (!toggle) { return; }
    var current = document.documentElement.getAttribute('data-theme');
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('rh-theme', next); } catch (error) {}
});

// konfirmasi sebelum hapus
document.addEventListener('submit', function (event) {
    var form = event.target.closest('form.js-confirm');
    if (form && !window.confirm(form.getAttribute('data-confirm') || 'Yakin?')) {
        event.preventDefault();
    }
});

// Like / unlike (AJAX)
document.addEventListener('click', async function (event) {
    var button = event.target.closest('.like-btn');
    if (!button || button.disabled) { return; }

    button.disabled = true;
    try {
        var result = await postJson('api/like.php', { post_id: Number(button.getAttribute('data-post-id')) });
        if (result.success) {
            button.classList.toggle('liked', result.liked);
            button.setAttribute('aria-pressed', result.liked ? 'true' : 'false');
            button.querySelector('.like-count').textContent = result.like_count;
        } else {
            showToast(result.message || 'Gagal memproses like.', 'error');
        }
    } catch (error) {
        showToast('Koneksi bermasalah. Coba lagi.', 'error');
    } finally {
        button.disabled = false;
    }
});

// Hitung karakter 

function setupCharCounter(textarea, counter, maxLength) {
    if (!textarea || !counter) { return; }
    function update() { counter.textContent = textarea.value.length + ' / ' + maxLength; }
    textarea.addEventListener('input', update);
    update();
}

//  Form postingan (validasi frontend) 

(function setupPostForm() {
    var form = document.getElementById('post-form');
    if (!form) { return; }

    var content = document.getElementById('post-content');
    var category = document.getElementById('post-category');
    var error = document.getElementById('post-error');

    setupCharCounter(content, document.getElementById('post-counter'), 1000);

    form.addEventListener('submit', function (event) {
        var text = content.value.trim();
        var message = '';
        if (text.length < 10) { message = 'Isi postingan minimal 10 karakter.'; }
        else if (text.length > 1000) { message = 'Isi postingan maksimal 1000 karakter.'; }
        else if (!category.value) { message = 'Pilih kategori terlebih dahulu.'; }

        if (message) {
            event.preventDefault();
            error.textContent = message;
            error.hidden = false;
        }
    });
})();

// Form login dan register (validasi frontend) 

function showFormError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.hidden = false;
}

(function setupLoginForm() {
    var form = document.getElementById('login-form');
    if (!form) { return; }
    form.addEventListener('submit', function (event) {
        var identity = document.getElementById('login-identity').value.trim();
        var password = document.getElementById('login-password').value;
        if (!identity || !password) {
            event.preventDefault();
            showFormError(document.getElementById('login-error'), 'Username/email dan password wajib diisi.');
        }
    });
})();

(function setupRegisterForm() {
    var form = document.getElementById('register-form');
    if (!form) { return; }
    form.addEventListener('submit', function (event) {
        var username = document.getElementById('register-username').value.trim();
        var email = document.getElementById('register-email').value.trim();
        var password = document.getElementById('register-password').value;
        var confirm = document.getElementById('register-confirm').value;
        var message = '';

        if (!/^[A-Za-z0-9_]{3,20}$/.test(username)) { message = 'Username 3-20 karakter: huruf, angka, atau underscore.'; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { message = 'Format email tidak valid.'; }
        else if (password.length < 8) { message = 'Password minimal 8 karakter.'; }
        else if (password !== confirm) { message = 'Konfirmasi password tidak sama.'; }

        if (message) {
            event.preventDefault();
            showFormError(document.getElementById('register-error'), message);
        }
    });
})();

// Jawaban (AJAX)
// Susun elemen satu jawaban dari data JSON
function buildReplyElement(reply) {
    var item = createElement('div', 'reply-item');
    item.id = 'reply-' + reply.id;

    item.appendChild(createAvatar(reply.username, reply.avatar_color, 'sm'));

    var body = createElement('div', 'reply-body');
    var meta = createElement('div', 'reply-meta');

    var userLink = createElement('a', 'post-user', reply.username);
    userLink.href = 'profile.php?id=' + reply.user_id;
    meta.appendChild(userLink);
    if (reply.is_guest) { meta.appendChild(createGuestBadge()); }
    meta.appendChild(createElement('span', 'post-time', '\u00B7 ' + reply.time_text));

    body.appendChild(meta);

    var text = createElement('p', 'reply-text', reply.content);
    text.style.whiteSpace = 'pre-line'; // pertahankan baris baru
    body.appendChild(text);

    // Form hapus (hanya pemilik, dan jawaban baru pasti milik sendiri)
    var form = createElement('form', 'js-confirm');
    form.method = 'post';
    form.action = 'process/delete_reply.php';
    form.setAttribute('data-confirm', 'Hapus jawaban ini?');
    [['csrf_token', getCsrfToken()], ['reply_id', reply.id], ['from', 'detail']].forEach(function (pair) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair[0];
        input.value = pair[1];
        form.appendChild(input);
    });
    var deleteButton = createElement('button', 'action-button danger small');
    deleteButton.type = 'submit';
    deleteButton.appendChild(createIcon('trash'));
    deleteButton.appendChild(createElement('span', '', ' Hapus'));
    form.appendChild(deleteButton);
    body.appendChild(form);

    item.appendChild(body);
    return item;
}

(function setupReplyForm() {
    var form = document.getElementById('reply-form');
    if (!form) { return; }

    var content = document.getElementById('reply-content');
    var error = document.getElementById('reply-error');
    var list = document.getElementById('reply-list');
    var emptyText = document.getElementById('reply-empty');
    var replyCount = document.getElementById('reply-count');
    var submitButton = form.querySelector('button[type="submit"]');

    setupCharCounter(content, document.getElementById('reply-counter'), 1000);

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        var text = content.value.trim();

        // Validasi frontend
        if (text.length < 3) { showFormError(error, 'Jawaban minimal 3 karakter.'); return; }
        if (text.length > 1000) { showFormError(error, 'Jawaban maksimal 1000 karakter.'); return; }
        error.hidden = true;

        submitButton.disabled = true;
        try {
            var result = await postJson('api/reply.php', {
                post_id: Number(form.getAttribute('data-post-id')),
                content: text
            });

            if (!result.success) {
                showFormError(error, result.message || 'Gagal mengirim jawaban.');
                return;
            }

            list.appendChild(buildReplyElement(result.reply));
            replyCount.textContent = result.reply_count;
            if (emptyText) { emptyText.hidden = true; }

            // Perbarui angka jawaban di kartu postingan (tanpa reload)
            var cardCount = document.querySelector('.post-card .post-actions a[aria-label="Jawaban"] span');
            if (cardCount) { cardCount.textContent = result.reply_count; }

            content.value = '';
            content.dispatchEvent(new Event('input'));
            showToast('Jawaban terkirim.');
        } catch (err) {
            showFormError(error, 'Koneksi bermasalah. Coba lagi.');
        } finally {
            submitButton.disabled = false;
        }
    });
})();

// Pencarian (AJAX) 

// Susun kartu postingan dari data JSON (versi JavaScript dari post_card.php)
function buildPostCard(post) {
    var card = createElement('article', 'post-card');
    card.id = 'post-' + post.id;

    // Bagian kepala: avatar, username, waktu, kategori
    var head = createElement('div', 'post-head');
    head.appendChild(createAvatar(post.username, post.avatar_color, 'md'));

    var meta = createElement('div', 'post-meta');
    var userLink = createElement('a', 'post-user', post.username);
    userLink.href = 'profile.php?id=' + post.user_id;
    meta.appendChild(userLink);
    if (post.is_guest) { meta.appendChild(createGuestBadge()); }
    meta.appendChild(createElement('span', 'post-time', '\u00B7 ' + post.time_text + (post.is_edited ? ' (diedit)' : '')));
    head.appendChild(meta);

    var tag = createElement('a', 'category-tag', post.category_name);
    tag.href = 'index.php?kategori=' + encodeURIComponent(post.category_slug);
    head.appendChild(tag);
    card.appendChild(head);

    // Isi postingan (klik untuk buka detail)
    var link = createElement('a', 'post-link');
    link.href = 'detail-post.php?id=' + post.id;
    var content = createElement('p', 'post-content clamp', post.content);
    content.style.whiteSpace = 'pre-line';
    link.appendChild(content);
    card.appendChild(link);

    // Tombol like dan jawaban
    var actions = createElement('div', 'post-actions');

    var likeButton = createElement('button', 'action-button like-btn' + (post.liked_by_me ? ' liked' : ''));
    likeButton.type = 'button';
    likeButton.setAttribute('data-post-id', post.id);
    likeButton.setAttribute('aria-pressed', post.liked_by_me ? 'true' : 'false');
    likeButton.setAttribute('aria-label', 'Suka');
    likeButton.appendChild(createIcon('heart'));
    likeButton.appendChild(createElement('span', 'like-count', String(post.like_count)));
    actions.appendChild(likeButton);

    var replyLink = createElement('a', 'action-button');
    replyLink.href = 'detail-post.php?id=' + post.id + '#jawaban';
    replyLink.setAttribute('aria-label', 'Jawaban');
    replyLink.appendChild(createIcon('message'));
    replyLink.appendChild(createElement('span', '', String(post.reply_count)));
    actions.appendChild(replyLink);

    card.appendChild(actions);
    return card;
}

(function setupSearch() {
    var form = document.getElementById('search-form');
    if (!form) { return; }

    var input = document.getElementById('search-input');
    var categorySelect = document.getElementById('search-category');
    var sortSelect = document.getElementById('search-sort');
    var results = document.getElementById('search-results');
    var countText = document.getElementById('search-count');

    var debounceTimer = null;
    var requestNumber = 0; // untuk mengabaikan respon lama

    async function runSearch() {
        requestNumber++;
        var thisRequest = requestNumber;

        var params = new URLSearchParams({
            q: input.value.trim(),
            category: categorySelect.value,
            sort: sortSelect.value
        });

        // Perbarui URL agar hasil pencarian bisa dibagikan
        history.replaceState(null, '', 'search.php?' + params.toString());
        countText.textContent = 'Mencari...';

        try {
            var response = await fetch('api/search.php?' + params.toString());
            var data = await response.json();
            if (thisRequest !== requestNumber) { return; } // ada pencarian yang lebih baru

            results.innerHTML = '';
            if (!data.success) {
                countText.textContent = data.message || 'Pencarian gagal.';
                return;
            }

            countText.textContent = data.total + ' hasil ditemukan';
            if (data.total === 0) {
                results.appendChild(createElement('div', 'empty-state', 'Tidak ada diskusi yang cocok. Coba kata kunci lain.'));
            }
            data.posts.forEach(function (post) { results.appendChild(buildPostCard(post)); });
        } catch (error) {
            if (thisRequest === requestNumber) { countText.textContent = 'Koneksi bermasalah. Coba lagi.'; }
        }
    }

    // Cari otomatis 300ms setelah pengguna berhenti mengetik
    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runSearch, 300);
    });
    categorySelect.addEventListener('change', runSearch);
    sortSelect.addEventListener('change', runSearch);
    form.addEventListener('submit', function (event) { event.preventDefault(); runSearch(); });

    runSearch(); // tampilkan hasil awal saat halaman dibuka
})();
