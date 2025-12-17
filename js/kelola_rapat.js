document.addEventListener("DOMContentLoaded", function () {
    // DATA SEKARANG HANYA BERISI PESERTA KARENA SUDAH DIFILTER OLEH PHP
    let users = window.kelolaRapatUsers || [];

    // ID Admin (untuk perbandingan dalam fungsi hapus)
    const CURRENT_ADMIN_ID = window.currentAdminId || 0;

    const tbody = document.getElementById("userTableBody");
    const pagination = document.getElementById("pagination");
    const dataInfo = document.getElementById("dataInfo");
    const searchInput = document.getElementById("searchInput");
    const rowsPerPageSelect = document.getElementById("rowsPerPage"); // New Element

    let currentPage = 1;
    let itemsPerPage = parseInt(rowsPerPageSelect.value); // Dynamic Init
    let filteredUsers = Array.isArray(users) ? [...users] : [];

    // Update itemsPerPage dynamically
    rowsPerPageSelect.addEventListener('change', function () {
        if (this.value === 'all') {
            itemsPerPage = 1000000; // Show all data
        } else {
            itemsPerPage = parseInt(this.value);
        }
        currentPage = 1; // Reset to page 1
        renderTable(filteredUsers);
    });

    // Fungsi render tabel
    window.renderTable = function (data) {
        tbody.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data pengguna ditemukan.</td></tr>`;
            dataInfo.textContent = "";
            pagination.innerHTML = "";
            return;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedData = data.slice(start, end);

        paginatedData.forEach((u, index) => {
            const nama = escapeHtml(u.nama || '');
            const nik = escapeHtml(u.nik || '-');
            const email = escapeHtml(u.email || '-');
            const role = escapeHtml(u.role || '');

            // Logic Foto vs Default Icon
            let photoHtml = '';
            if (u.foto && u.foto !== '') {
                // Jika ada foto
                const photoPath = `../file/${encodeURIComponent(u.foto)}`;
                photoHtml = `<img src="${photoPath}" alt="${nama}" 
                                class="rounded-circle shadow-sm" 
                                style="width: 45px; height: 45px; object-fit: cover;"
                                onerror="handleImageError(this)">`;
            } else {
                // Default Icon
                photoHtml = `<i class="bi bi-person-circle text-secondary" style="font-size: 45px;"></i>`;
            }

            const row = `
                <tr>
                    <td class="align-middle text-center text-dark fw-bold">${start + index + 1}</td>
                    <td class="align-middle">
                        ${photoHtml}
                    </td>
                    <td class="align-middle fw-medium mb-1">${nama}</td>
                    <td class="align-middle">${nik}</td>
                    <td class="align-middle">${email}</td>
                    <td class="align-middle text-center"><span class="badge-role-custom">${role}</span></td>
                    <td class="align-middle text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button onclick="openEditModal(${u.id})" class="btn btn-sm btn-soft-success" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-soft-danger btn-delete text-center" onclick="deleteUser(${Number(u.id)}, this)" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", row);
        });

        updatePagination(data);
    }

    // Fungsi pembaruan paginasi
    function updatePagination(data) {
        pagination.innerHTML = "";
        const totalPages = Math.max(1, Math.ceil(data.length / itemsPerPage));

        const start = data.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(start + itemsPerPage - 1, data.length);
        dataInfo.textContent = data.length === 0 ? '' : `Menampilkan ${start} -${end} dari ${data.length} pengguna`;

        pagination.insertAdjacentHTML(
            "beforeend",
            `<li class="page-item ${currentPage === 1 ? "disabled" : ""}" >
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1});return false;">«</a>
        </li> `
        );

        for (let i = 1; i <= totalPages; i++) {
            const active = i === currentPage ? "active" : "";
            pagination.insertAdjacentHTML(
                "beforeend",
                `<li class="page-item ${active}" >
                <a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a>
            </li> `
            );
        }

        pagination.insertAdjacentHTML(
            "beforeend",
            `<li class="page-item ${currentPage === totalPages ? "disabled" : ""}" >
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1});return false;">»</a>
        </li>`
        );
    }

    window.changePage = function (page) {
        const totalPages = Math.ceil(filteredUsers.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable(filteredUsers);
    }

    // Hapus pengguna via AJAX (tanpa muat ulang)
    window.deleteUser = async function (id, btn) {
        if (id === CURRENT_ADMIN_ID) {
            showToast('Anda tidak dapat menghapus akun Anda sendiri.', 'warning');
            return;
        }

        const confirmed = await showConfirm("Yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.");
        if (!confirmed) {
            return;
        }

        let originalHTML = null;
        if (btn) {
            originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        }

        try {
            const response = await fetch('../proses/proses_hapus_peserta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                users = users.filter(u => u.id != id);
                filteredUsers = filteredUsers.filter(u => u.id != id);

                const totalPages = Math.max(1, Math.ceil(filteredUsers.length / itemsPerPage));
                if (currentPage > totalPages) currentPage = totalPages;

                renderTable(filteredUsers);
                showToast(result.message || 'Pengguna berhasil dihapus.', 'success');
            } else {
                showToast(result.message || 'Gagal menghapus pengguna.', 'error');
            }
        } catch (err) {
            console.error('Error:', err);
            showToast('Terjadi kesalahan saat menghubungi server.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    }

    // Fungsi tampilkan notifikasi
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('successToast');
        if (!toastEl) return;

        toastEl.className = `toast align-items-center text-bg-${type} border-0`;

        toastEl.querySelector('.toast-body').innerHTML = `
    <i class="bi bi-check-circle-fill me-2"></i> ${message}
`;

        const toast = new bootstrap.Toast(toastEl, {
            delay: 3000
        });
        toast.show();
    }


    // Global function to handle image errors
    window.handleImageError = function (img) {
        img.onerror = null; // Prevent infinite loop
        // Replace the image with the default icon
        const icon = document.createElement('i');
        icon.className = 'bi bi-person-circle text-secondary';
        icon.style.fontSize = '45px';
        img.parentNode.replaceChild(icon, img);
    }

    // Fitur pencarian
    // bantuan kecil untuk escape HTML saat menyuntikkan dari JSON
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            const keyword = this.value.toLowerCase();
            filteredUsers = users.filter(
                (u) =>
                    (u.nama && String(u.nama).toLowerCase().includes(keyword)) ||
                    (u.email && String(u.email).toLowerCase().includes(keyword)) ||
                    (u.nik && String(u.nik).toLowerCase().includes(keyword)) ||
                    (u.role && String(u.role).toLowerCase().includes(keyword))
            );
            currentPage = 1;
            renderTable(filteredUsers);
        });
    }

    // Render awal
    renderTable(filteredUsers);

    // Session success message
    if (window.sessionSuccessMessage) {
        showToast(window.sessionSuccessMessage, 'success');
    }

    // Buka WhatsApp otomatis jika tautan ada
    if (window.waData && window.waData.link) {
        const waLink = window.waData.link;
        const waNomor = window.waData.nomor;
        const waMessage = window.waData.message;

        showConfirm('Peserta berhasil ditambahkan! Buka WhatsApp untuk mengirim informasi login ke ' + waNomor + '?')
            .then(function (confirmed) {
                if (confirmed) {
                    window.open(waLink, '_blank');
                    if (waMessage) {
                        showToast(waMessage, 'success');
                    }
                }
            });
    }

    // Handler Logout
    async function confirmLogout(e) {
        e.preventDefault();
        const confirmed = await showConfirm("Yakin mau keluar?");
        if (confirmed) {
            window.location.href = "../proses/proses_logout.php";
        }
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", confirmLogout);
    }
    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) {
        logoutBtnMobile.addEventListener("click", confirmLogout);
    }
});

// Edit User Modal Logic (Outside DOMContentLoaded to be accessible if needed, or inside with window assign)
// Defined globally or attached to window
window.openEditModal = function (id) {
    // Find user data
    const user = window.kelolaRapatUsers.find(u => u.id == id);
    if (!user) return;

    // Populate form
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nama').value = user.nama || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_nik').value = user.nik || '';
    document.getElementById('edit_whatsapp').value = user.nomor_whatsapp || '';
    document.getElementById('reset_password').checked = false;

    // Show Modal
    const modalEl = document.getElementById('editUserModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
};

// Handle Edit Form Submission
document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('editUserForm');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btnSave = document.getElementById('btnSaveEdit');
            const spinner = btnSave.querySelector('.spinner-border');

            // Lock UI
            btnSave.disabled = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(this);

            try {
                const response = await fetch('../proses/proses_edit_peserta.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Close modal
                    const modalEl = document.getElementById('editUserModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    showToast(result.message, 'success');

                    // Reload page to reflect changes (simplest way to sync data)
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            } finally {
                btnSave.disabled = false;
                spinner.classList.add('d-none');
            }
        });
    }

    /* =======================
       MODAL TAMBAH PENGGUNA BARU
    ======================= */
    const btnSimpanPengguna = document.getElementById('btnSimpanPengguna');
    const formTambahPengguna = document.getElementById('formTambahPengguna');
    const modalTambahPengguna = document.getElementById('modalTambahPengguna');

    // Email Suggestion Logic for Modal
    const newNamaInput = document.getElementById('newNama');
    const newEmailInput = document.getElementById('newEmail');
    const emailSuggestionModal = document.getElementById('emailSuggestionModal');

    if (newNamaInput && newEmailInput && emailSuggestionModal) {
        newNamaInput.addEventListener('input', function () {
            const name = this.value;
            // Basic sanitization: lowercase, remove special chars, replace spaces with nothing
            const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');

            if (cleanName.length > 0) {
                const candidateEmail = cleanName + '@gmail.com';
                // Show clickable badge
                emailSuggestionModal.style.display = 'block';
                emailSuggestionModal.innerHTML = `
                    <small class="text-muted d-block mb-1">Rekomendasi:</small>
                    <span class="badge bg-success-subtle text-success border border-success" 
                          style="cursor: pointer; font-size: 0.9rem;"
                          onclick="fillModalEmail('${candidateEmail}')">
                        <i class="bi bi-magic me-1"></i> ${candidateEmail}
                    </span>
                `;
            } else {
                emailSuggestionModal.style.display = 'none';
                emailSuggestionModal.innerHTML = '';
            }
        });

        // Function to fill email (exposed globally for onclick)
        window.fillModalEmail = function (email) {
            newEmailInput.value = email;
            // Visual feedback
            newEmailInput.classList.add('is-valid');
            setTimeout(() => newEmailInput.classList.remove('is-valid'), 1000);
        };
    }

    if (btnSimpanPengguna && formTambahPengguna) {
        btnSimpanPengguna.addEventListener('click', async function () {
            // Get form values
            const nama = document.getElementById('newNama').value.trim();
            const email = document.getElementById('newEmail').value.trim();
            const nik = document.getElementById('newNik').value.trim();
            const whatsapp = document.getElementById('newWhatsapp').value.trim();

            // Basic validation
            if (!nama || !email || !nik) {
                showToast('Nama, Email, dan NIK wajib diisi', 'error');
                return;
            }

            // Disable button and show loading
            const originalText = btnSimpanPengguna.innerHTML;
            btnSimpanPengguna.disabled = true;
            btnSimpanPengguna.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

            try {
                const formData = new FormData();
                formData.append('nama', nama);
                formData.append('email', email);
                formData.append('nik', nik);
                formData.append('nomor_whatsapp', whatsapp);

                const response = await fetch('../proses/proses_tambah_peserta_ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(modalTambahPengguna);
                    if (modal) modal.hide();

                    // Reset form
                    formTambahPengguna.reset();
                    if (emailSuggestionModal) {
                        emailSuggestionModal.style.display = 'none';
                        emailSuggestionModal.innerHTML = '';
                    }

                    // Show success message
                    showToast('Pengguna berhasil ditambahkan!', 'success');

                    // Reload page to show new user in table
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    showToast(result.message || 'Gagal menambahkan pengguna', 'error');
                }

            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                // Restore button
                btnSimpanPengguna.disabled = false;
                btnSimpanPengguna.innerHTML = originalText;
            }
        });
    }
});
