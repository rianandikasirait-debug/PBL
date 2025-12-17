document.addEventListener("DOMContentLoaded", function () {
    // Handler Logout
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", async function (e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                window.location.href = "../proses/proses_logout.php";
            }
        });
    }
    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) {
        logoutBtnMobile.addEventListener("click", async function (e) {
            e.preventDefault();
            const confirmed = await showConfirm("Yakin mau keluar?");
            if (confirmed) {
                window.location.href = "../proses/proses_logout.php";
            }
        });
    }

    // Data Peserta dari PHP (global variable)
    const participants = window.detailRapatParticipants || [];
    const participantList = document.getElementById('participantList');
    const searchInput = document.getElementById('searchPeserta');

    // Escape HTML helper
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Image Error Handler (needs to be global or attached to window if used in inline onclick/onerror, 
    // but here we are generating HTML strings. Better to attach listener or use event delegation.
    // However, the original code used inline onerror="handleImageError(this)".
    // We will attach it to window to support the inline handler in the generated HTML.
    window.handleImageError = function (img) {
        img.onerror = null;
        const fallback = document.createElement('div');
        fallback.className = 'bg-light rounded-circle d-flex align-items-center justify-content-center me-3 border';
        fallback.style.width = '38px';
        fallback.style.height = '38px';
        fallback.style.flexShrink = '0';
        fallback.innerHTML = '<i class="bi bi-person-fill text-secondary fs-5"></i>';
        img.parentNode.replaceChild(fallback, img);
    };

    // Render Function
    function renderPeserta(data) {
        if (!participantList) return;
        participantList.innerHTML = '';

        if (data.length === 0) {
            participantList.innerHTML = '<div class="p-4 text-center text-muted small">Peserta tidak ditemukan.</div>';
            return;
        }

        // Render loop
        data.forEach((pd, index) => {
            const nama = escapeHtml(pd.nama || '');
            const email = escapeHtml(pd.email || '');
            // NIK tidak ditampilkan di UI tapi bisa dicari

            // Foto Logic
            let photoHtml = '';
            if (pd.foto && pd.foto !== '') {
                const photoPath = `../uploads/${encodeURIComponent(pd.foto)}`;
                photoHtml = `<img src="${photoPath}" class="rounded-circle me-3 border" style="width: 38px; height: 38px; object-fit: cover; flex-shrink: 0;" onerror="handleImageError(this)">`;
            } else {
                photoHtml = `<div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3 border" style="width: 38px; height: 38px; flex-shrink: 0;"><i class="bi bi-person-fill text-secondary fs-5"></i></div>`;
            }

            const item = document.createElement('div');
            item.className = 'list-group-item d-flex align-items-center py-3 px-3 border-bottom-0 border-top-0 border-end-0 border-start-0';
            item.innerHTML = `
                <span class="me-3 fw-bold text-secondary small" style="min-width: 25px;">${index + 1}.</span>
                ${photoHtml}
                <div class="flex-grow-1">
                    <div class="fw-medium text-dark name-text">${nama}</div>
                    ${email ? `<div class="text-muted small" style="font-size: 0.75rem;">${email}</div>` : ''}
                </div>
            `;
            participantList.appendChild(item);
        });
    }

    // Filter Function
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const keyword = this.value.toLowerCase();
            const filtered = participants.filter(pd => {
                const nama = (pd.nama || '').toLowerCase();
                const email = (pd.email || '').toLowerCase();
                const nik = (pd.nik || '').toLowerCase();

                return nama.includes(keyword) || email.includes(keyword) || nik.includes(keyword);
            });
            renderPeserta(filtered);
        });
    }

    // Initial Render
    renderPeserta(participants);
});
