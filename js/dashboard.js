document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.getElementById("tableBody");
    const searchInput = document.getElementById("searchInput");
    const filterPembuat = document.getElementById("filterPembuat");
    const pagination = document.getElementById("pagination");
    const dataInfo = document.getElementById("dataInfo");
    const rowsPerPageSelect = document.getElementById("rowsPerPage");
    const logoutBtn = document.getElementById("logoutBtn");
    const logoutBtnMobile = document.getElementById("logoutBtnMobile");

    // Ambil data dari variabel global yang diset di PHP
    const notulenData = window.notulenDataFromPHP || [];

    let currentPage = 1;
    let rowsPerPage = 10;

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function renderTable(data, startIndex = 0) {
        const notulenList = document.getElementById("notulenList");
        notulenList.innerHTML = "";

        if (data.length === 0) {
            notulenList.innerHTML = `<div class="text-center text-muted py-4">Belum ada data notulen.</div>`;
            return;
        }

        data.forEach((item, index) => {
            const nomorUrut = startIndex + index + 1;
            const judul = escapeHtml(item.judul || '');
            const tanggal = escapeHtml(item.tanggal || '');
            const pembuat = escapeHtml(item.tempat || 'Admin');
            const pesertaCount = item.peserta ? item.peserta.split(',').length : 0;
            const status = escapeHtml(item.status || 'draft');

            // Format tanggal dengan jam
            let tanggalDenganJam = tanggal;
            if (item.created_at) {
                const dateObj = new Date(item.created_at);
                const jam = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                tanggalDenganJam = `${tanggal} • ${jam}`;
            }

            // Map status ke badge color
            const statusBadge = status === 'final'
                ? '<span class="badge d-flex align-items-center gap-1" style="background-color: #198754 !important; color: white;"><i class="bi bi-check-circle"></i> Final</span>'
                : '<span class="badge bg-secondary d-flex align-items-center gap-1"><i class="bi bi-pencil-square"></i> Draft</span>';

            const card = document.createElement('div');
            card.className = 'col'; // Grid column

            card.innerHTML = `
                <div class="highlight-card interact-card h-100 p-3 rounded-3 position-relative shadow-sm d-flex flex-column justify-content-between bg-white text-dark" style="background: #fff; cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='detail_rapat_admin.php?id=${encodeURIComponent(item.id)}'">
                    
                    <!-- Header: Actions & Status Badge -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        ${statusBadge}
                        <div class="d-flex gap-2">
                            <a href="edit_rapat_admin.php?id=${encodeURIComponent(item.id)}" class="btn btn-sm text-dark p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                            <button class="btn btn-sm text-secondary p-0 btn-delete" data-id="${encodeURIComponent(item.id)}" title="Hapus"><i class="bi bi-trash fs-5"></i></button>
                        </div>
                    </div>

                    <!-- Body: Title & Metadata -->
                    <div>
                        <h5 class="fw-bold text-dark mb-3 text-truncate" title="${judul}">${judul}</h5>
                        
                        <div class="d-flex flex-column gap-2 text-secondary small">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event"></i>
                                <span>${tanggalDenganJam}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person"></i>
                                <span class="text-truncate" style="max-width: 200px;">PIC: ${pembuat}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people"></i>
                                <span>${pesertaCount} Peserta</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            notulenList.appendChild(card);
        });
    }

    function populateFilterPembuat() {
        // Handle case where notulenData is empty/undefined
        if (!notulenData || !Array.isArray(notulenData)) return;

        const pembuatUnik = [...new Set(notulenData.map(d => d.tempat || 'Admin'))];
        pembuatUnik.forEach(nama => {
            const opt = document.createElement("option");
            opt.value = nama;
            opt.textContent = nama;
            filterPembuat.appendChild(opt);
        });
    }

    function getFilteredData() {
        const keyword = (searchInput.value || "").toLowerCase();
        const selectedPembuat = filterPembuat.value;

        if (!notulenData) return [];

        return notulenData.filter(item => {
            const judul = (item.judul || '').toLowerCase();
            const tanggal = (item.tanggal || '').toLowerCase();
            const pembuat = (item.tempat || 'Admin').toLowerCase();

            const cocokKeyword = judul.includes(keyword) || tanggal.includes(keyword) || pembuat.includes(keyword);
            const cocokPembuat = selectedPembuat === "" || (item.tempat || 'Admin') === selectedPembuat;
            return cocokKeyword && cocokPembuat;
        });
    }

    function paginate(data) {
        if (rowsPerPage === "all") return data;
        const start = (currentPage - 1) * rowsPerPage;
        return data.slice(start, start + rowsPerPage);
    }

    function renderPagination(totalRows) {
        pagination.innerHTML = "";
        if (rowsPerPage === "all") return;

        const totalPages = Math.ceil(totalRows / rowsPerPage);

        // Helper untuk scroll
        const doScroll = () => {
            const notulenList = document.getElementById("notulenList");
            window.scrollTo({
                top: notulenList.getBoundingClientRect().top + window.scrollY - 100,
                behavior: "smooth"
            });
        };

        // Tombol Sebelumnya
        const prevLi = document.createElement("li");
        prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
        prevLi.innerHTML = `<a class="page-link border-success text-success" href="#">«</a>`;
        prevLi.querySelector("a").addEventListener("click", (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                updateTable();
                doScroll();
            }
        });
        pagination.appendChild(prevLi);

        // Angka Halaman
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            li.className = `page-item ${i === currentPage ? "active" : ""}`;

            // Note: Active state styling needs to override text-success usually, but bootstrap handles .page-item.active .page-link
            li.innerHTML = `<a class="page-link border-success text-success" href="#">${i}</a>`;

            li.querySelector("a").addEventListener("click", (e) => {
                e.preventDefault();
                currentPage = i;
                updateTable();
                doScroll();
            });
            pagination.appendChild(li);
        }

        // Tombol Selanjutnya
        const nextLi = document.createElement("li");
        nextLi.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
        nextLi.innerHTML = `<a class="page-link border-success text-success" href="#">»</a>`;
        nextLi.querySelector("a").addEventListener("click", (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                updateTable();
                doScroll();
            }
        });
        pagination.appendChild(nextLi);
    }

    function updateTable() {
        const filteredData = getFilteredData();
        const totalRows = filteredData.length;
        const startIndex = (rowsPerPage === "all" || totalRows === 0) ? 0 : (currentPage - 1) * rowsPerPage;
        const paginatedData = paginate(filteredData);

        renderTable(paginatedData, startIndex);
        renderPagination(totalRows);

        const start = totalRows === 0 ? 0 : startIndex + 1;
        const end = start + paginatedData.length - 1;
        dataInfo.textContent = `Menampilkan ${start}-${end} dari ${totalRows} data`;
    }

    searchInput.addEventListener("input", () => {
        currentPage = 1;
        updateTable();
    });
    filterPembuat.addEventListener("change", () => {
        currentPage = 1;
        updateTable();
    });
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener("change", () => {
            rowsPerPage = rowsPerPageSelect.value === "all" ? "all" : parseInt(rowsPerPageSelect.value, 10);
            currentPage = 1;
            updateTable();
        });
    }

    document.addEventListener("click", async function (e) {
        const btn = e.target.closest(".btn-delete");
        if (!btn) return;
        const id = btn.dataset.id;
        if (!id) return;

        const confirmed = await showConfirm("Yakin mau menghapus data ini? Tindakan ini tidak dapat dibatalkan.");
        if (!confirmed) return;

        try {
            const res = await fetch('../proses/proses_hapus_notulen.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const json = await res.json();
            if (json.success) {
                // Reload page to update counters and table
                window.location.reload();
            } else {
                showToast(json.message || 'Gagal menghapus notulen.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan saat menghapus.', 'error');
        }
    });

    function setupLogoutButtons() {
        if (logoutBtn) {
            logoutBtn.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
        if (logoutBtnMobile) {
            logoutBtnMobile.addEventListener("click", async function (e) {
                e.preventDefault();
                const confirmed = await showConfirm("Yakin mau keluar?");
                if (confirmed) {
                    localStorage.removeItem("adminData");
                    window.location.href = "../proses/proses_logout.php";
                }
            });
        }
    }

    populateFilterPembuat();
    updateTable();

    // Re-render table when viewport changes (debounced)
    window.addEventListener('resize', function () {
        if (window._dashResizeTimer) clearTimeout(window._dashResizeTimer);
        window._dashResizeTimer = setTimeout(() => {
            updateTable();
        }, 120);
    });

    setupLogoutButtons();

    // Handle highlight card clicks to mark as viewed
    document.querySelectorAll('.highlight-card').forEach(card => {
        const link = card.closest('a');
        if (link) {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                const urlParams = new URLSearchParams(new URL(href, window.location.origin).search);
                const id = urlParams.get('id');

                if (id) {
                    fetch('../proses/proses_mark_viewed.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    }).catch(err => console.error('Error marking as viewed:', err));
                }
            });
        }
    });
});
