document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('lampiranContainer');
    const addBtn = document.getElementById('addLampiranBtn');

    // Add New Lampiran Logic
    if (addBtn && container) {
        function addRow() {
            const row = document.createElement('div');
            row.className = 'card mb-2 p-3 border-light bg-light shadow-sm lampiran-row';
            row.innerHTML = `
                <div class="row align-items-center g-2">
                    <div class="col-md-5">
                        <input type="text" name="judul_lampiran[]" class="form-control form-control-sm title-input" placeholder="Judul Lampiran">
                    </div>
                    <div class="col-md-5">
                        <input type="file" name="file_lampiran[]" class="form-control form-control-sm file-input">
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-success upload-lampiran me-1" title="Upload & Simpan">
                            <i class="bi bi-cloud-upload"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger remove-lampiran">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(row);

            // Delete row event
            row.querySelector('.remove-lampiran').addEventListener('click', function () {
                row.remove();
            });

            // Upload event
            const uploadBtn = row.querySelector('.upload-lampiran');
            uploadBtn.addEventListener('click', async function () {
                const titleInput = row.querySelector('.title-input');
                const fileInput = row.querySelector('.file-input');
                const file = fileInput.files[0];

                if (!file) {
                    showToast('Silakan pilih file terlebih dahulu', 'error');
                    return;
                }

                // Show loading
                const originalContent = uploadBtn.innerHTML;
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                try {
                    const formData = new FormData();
                    formData.append('id_notulen', document.querySelector('input[name="id"]').value);
                    formData.append('judul_lampiran', titleInput.value);
                    formData.append('file_lampiran', file);

                    const res = await fetch('../proses/proses_upload_lampiran.php', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();

                    if (json.success) {
                        showToast('Lampiran berhasil diupload!', 'success');

                        // Move to existing list
                        addExistingLampiranRow(json.data);

                        // Remove this input row
                        row.remove();
                    } else {
                        showToast(json.message || 'Gagal upload', 'error');
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = originalContent;
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Terjadi kesalahan sistem', 'error');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalContent;
                }
            });
        }
        addBtn.addEventListener('click', addRow);
    }

    // Helper to add row to existing list (visual only)
    function addExistingLampiranRow(data) {
        const listGroup = document.querySelector('.list-group');
        if (!listGroup) return; // Should exist if checked correctly, or create if not exist

        const div = document.createElement('div');
        div.className = 'list-group-item d-flex justify-content-between align-items-center';
        div.id = 'lampiran-' + data.id;
        div.innerHTML = `
            <div class="d-flex align-items-center">
                    <a href="../uploads/${data.file_lampiran}" target="_blank" class="text-decoration-none text-dark d-flex align-items-center">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                    <span>${data.judul_lampiran}</span>
                    </a>
            </div>
            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteLampiran(${data.id})" title="Hapus Lampiran">
                <i class="bi bi-trash"></i>
            </button>
        `;
        listGroup.appendChild(div);
    }

    // ===== LOCK FORM JIKA STATUS FINAL =====

    // We need to check if status is final. 
    // Since we extracted JS, we need a way to know the status.
    // We will assume window.notulenStatus is set in the PHP file.
    const currentStatus = window.notulenStatus || 'draft';

    if (currentStatus === 'final') {
        // Disable semua input field kecuali status dan tombol kembali
        document.querySelectorAll('input[name="judul"], input[name="tanggal"], #isi, input[name="peserta[]"]').forEach(field => {
            field.disabled = true;
        });

        // Disable dropdown peserta
        document.querySelectorAll('.dropdown-toggle, .form-check-input, input[type="file"]').forEach(field => {
            if (field.name !== 'status') field.disabled = true;
        });

        // Ubah warna tombol simpan ke abu-abu dan disable
        const submitBtn = document.getElementById('simpan_perubahan');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-save');
            submitBtn.classList.add('btn-secondary');
            submitBtn.innerHTML = '⚠️ Notulen Sudah Final (Tidak dapat diedit)';
        }
    }

    /* =======================
      FORM SUBMIT AJAX
    ======================= */
    const editForm = document.getElementById("editForm");
    if (editForm) {
        editForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            // Cegah submit jika Final
            if (currentStatus === 'final') {
                alert('Notulen sudah Final! Tidak dapat diedit.');
                return;
            }

            try {
                // Sync TinyMCE content
                if (typeof tinymce !== 'undefined' && tinymce.get("isi")) {
                    tinymce.triggerSave();
                }

                const fd = new FormData(this);

                // CRITICAL FIX: Manually append participant IDs
                // Get all participant rows from the table
                const participantRows = document.querySelectorAll('#addedContainer .added-item');
                participantRows.forEach(row => {
                    const participantId = row.dataset.id;
                    if (participantId) {
                        fd.append('peserta[]', participantId);
                    }
                });

                // DEBUG: Log FormData contents
                console.log('=== FORM DATA DEBUG ===');
                for (let [key, value] of fd.entries()) {
                    console.log(key, value);
                }
                console.log('=== END DEBUG ===');

                const res = await fetch("../proses/proses_edit_notulen.php", {
                    method: "POST",
                    body: fd
                });

                const text = await res.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (err) {
                    console.error("Invalid JSON:", text);
                    showToast("Terjadi kesalahan server: " + text.substring(0, 50), 'error');
                    return;
                }

                if (json.success) {
                    showToast('Notulen berhasil diperbarui!', 'success');

                    // Disable button
                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.disabled = true;

                    setTimeout(() => {
                        window.location.href = 'dashboard_admin.php';
                    }, 1500);
                } else {
                    showToast(json.message || "Gagal menyimpan data.", 'error');
                }
            } catch (error) {
                console.error(error);
                showToast("Terjadi kesalahan: " + error.message, 'error');
            }
        });
    }

    // Logout handlers
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

    // ===================
    // Fungsi Modal Peserta (Updated)
    // ===================

    // Helper function untuk escape HTML
    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    const searchInput = document.getElementById('searchInput');
    const notulenItems = document.querySelectorAll('.notulen-item');
    const notulenCheckboxes = document.querySelectorAll('.notulen-checkbox');
    const selectAll = document.getElementById('selectAll');
    const btnSimpanPeserta = document.getElementById('btnSimpanPeserta');
    const addedContainer = document.getElementById('addedContainer');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const noResults = document.getElementById('noResults');
    const modalPeserta = document.getElementById('modalPeserta');

    // Fitur Pencarian di Modal
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            let hasVisible = false;

            notulenItems.forEach(item => {
                const label = item.querySelector('label').innerText.toLowerCase();
                if (label.includes(filter)) {
                    item.classList.remove('d-none');
                    hasVisible = true;
                } else {
                    item.classList.add('d-none');
                }
            });

            if (noResults) {
                noResults.classList.toggle('d-none', hasVisible);
            }
        });
    }

    // Checkbox Pilih Semua
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            // Hanya select yang visible jika sedang search
            const visibleCheckboxes = Array.from(notulenCheckboxes).filter(cb => !cb.closest('.notulen-item').classList.contains('d-none'));

            visibleCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // Tombol Bersihkan/Reset
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            notulenCheckboxes.forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('keyup')); // Trigger search reset
            }
        });
    }

    const hiddenPesertaContainer = document.getElementById('hiddenPesertaContainer');

    // Tombol Simpan Pilihan (Dari Modal)
    if (btnSimpanPeserta) {
        btnSimpanPeserta.addEventListener('click', function () {
            const selected = document.querySelectorAll('.notulen-checkbox:checked');

            // Clear containers
            addedContainer.innerHTML = '';
            if (hiddenPesertaContainer) hiddenPesertaContainer.innerHTML = '';

            if (selected.length === 0) {
                addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
            } else {
                selected.forEach((cb, index) => {
                    const id = cb.value;
                    const name = cb.dataset.name;

                    // Visual Row
                    const tr = document.createElement('tr');
                    tr.className = 'added-item align-middle border-bottom';
                    tr.dataset.id = id;
                    tr.innerHTML = `
                        <td class="px-2 px-md-4 text-center text-muted small">${index + 1}</td>
                        <td class="px-2 px-md-4 text-start">
                            ${escapeHtml(name)}
                        </td>
                        <td class="text-center px-2 px-md-4">
                            <button type="button" class="btn btn-sm btn-danger remove-btn text-white" data-id="${id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    addedContainer.appendChild(tr);

                    // Hidden Input
                    if (hiddenPesertaContainer) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'peserta[]';
                        input.value = id;
                        input.id = 'input-peserta-' + id;
                        hiddenPesertaContainer.appendChild(input);
                    }
                });
            }

            // Tutup Modal
            const modalInstance = bootstrap.Modal.getInstance(modalPeserta);
            if (modalInstance) {
                modalInstance.hide();
            }

            showToast('Daftar peserta diperbarui', 'success');
        });
    }

    // Event delegation for remove buttons (Hapus dari tabel)
    if (addedContainer) {
        addedContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) {
                e.preventDefault();
                const btn = e.target.classList.contains('remove-btn') ? e.target : e.target.closest('.remove-btn');
                const id = btn.dataset.id;

                // Uncheck di modal (agar sinkron jika dibuka lagi)
                const modalCb = document.querySelector(`.notulen-checkbox[value="${id}"]`);
                if (modalCb) modalCb.checked = false;

                // Remove Visual Row
                const item = btn.closest('.added-item');
                if (item) item.remove();

                // Remove Hidden Input
                const hiddenInput = document.getElementById('input-peserta-' + id);
                if (hiddenInput) hiddenInput.remove();

                // Re-numbering
                const remainingItems = addedContainer.querySelectorAll('.added-item');
                remainingItems.forEach((row, index) => {
                    row.querySelector('td').innerText = index + 1;
                });

                if (remainingItems.length === 0) {
                    addedContainer.innerHTML = '<tr id="emptyRow" style="border-bottom: 1px solid #dee2e6 !important;"><td colspan="3" class="text-center text-muted py-5"><div class="d-flex flex-column align-items-center"><i class="bi bi-people text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i><small>Belum ada peserta yang ditambahkan</small></div></td></tr>';
                }
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

                    // Add new user to checkbox list in modal peserta
                    const notulenList = document.getElementById('notulenList');
                    if (notulenList && result.data) {
                        const newItem = document.createElement('div');
                        newItem.className = 'form-check notulen-item py-1 border-bottom';
                        newItem.innerHTML = `
                            <input class="form-check-input notulen-checkbox"
                                type="checkbox"
                                value="${result.data.id}"
                                data-name="${escapeHtml(result.data.nama)}"
                                id="u${result.data.id}"
                                checked>
                            <label class="form-check-label w-100" for="u${result.data.id}" style="cursor: pointer;">
                                ${escapeHtml(result.data.nama)}
                                <small class="text-muted d-block" style="text-transform: lowercase !important;">${escapeHtml(result.data.email.toLowerCase())}</small>
                            </label>
                        `;
                        notulenList.prepend(newItem);
                    }

                    // Auto-add to participant table
                    if (addedContainer && result.data) {
                        // Remove empty row if exists
                        const emptyRow = document.getElementById('emptyRow');
                        if (emptyRow) emptyRow.remove();

                        // Get current count
                        const currentItems = addedContainer.querySelectorAll('.added-item');
                        const newIndex = currentItems.length + 1;

                        const tr = document.createElement('tr');
                        tr.className = 'added-item align-middle border-bottom';
                        tr.dataset.id = result.data.id;
                        tr.innerHTML = `
                            <td class="px-2 px-md-4 text-center text-muted small">${newIndex}</td>
                            <td class="px-2 px-md-4 text-start">${escapeHtml(result.data.nama)}</td>
                            <td class="text-center px-2 px-md-4">
                                <button type="button" class="btn btn-sm btn-danger remove-btn text-white" data-id="${result.data.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        addedContainer.appendChild(tr);

                        // Add hidden input
                        if (hiddenPesertaContainer) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'peserta[]';
                            input.value = result.data.id;
                            input.id = 'input-peserta-' + result.data.id;
                            hiddenPesertaContainer.appendChild(input);
                        }
                    }

                    // Show success message
                    showToast('Pengguna berhasil ditambahkan!', 'success');

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

// Delete Existing Lampiran Logic
// Make global to support inline onclick
async function deleteLampiran(id) {
    const confirmed = await showConfirm("Yakin ingin menghapus lampiran ini?");
    if (!confirmed) return;

    try {
        const response = await fetch('../proses/proses_hapus_lampiran.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            // Try both ID formats for compatibility
            let item = document.getElementById('lampiran-row-' + id);
            if (!item) item = document.getElementById('lampiran-' + id);
            if (item) item.remove();
            showToast('Lampiran berhasil dihapus', 'success');
        } else {
            showToast(result.message || 'Gagal menghapus lampiran', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Terjadi kesalahan sistem', 'error');
    }
}

// === EDIT LAMPIRAN LOGIC ===
function editLampiran(id) {
    // Hide display elements
    const title = document.getElementById(`lampiran-title-${id}`);
    const actions = document.getElementById(`lampiran-actions-${id}`);
    const editContainer = document.getElementById(`lampiran-edit-container-${id}`);
    const saveActions = document.getElementById(`lampiran-save-actions-${id}`);

    if (title) title.classList.add('d-none');
    if (actions) actions.classList.add('d-none');

    // Show edit elements
    if (editContainer) editContainer.classList.remove('d-none');
    if (saveActions) saveActions.classList.remove('d-none');

    // Focus input
    const input = document.getElementById(`lampiran-input-${id}`);
    if (input) input.focus();
}

function cancelEditLampiran(id) {
    // Reset input value to original title
    const titleEl = document.getElementById(`lampiran-title-${id}`);
    const inputEl = document.getElementById(`lampiran-input-${id}`);

    if (titleEl && inputEl) {
        inputEl.value = titleEl.innerText;
    }

    // Revert UI
    if (titleEl) titleEl.classList.remove('d-none');
    const actions = document.getElementById(`lampiran-actions-${id}`);
    if (actions) actions.classList.remove('d-none');

    const editContainer = document.getElementById(`lampiran-edit-container-${id}`);
    const saveActions = document.getElementById(`lampiran-save-actions-${id}`);
    if (editContainer) editContainer.classList.add('d-none');
    if (saveActions) saveActions.classList.add('d-none');
}

async function saveLampiran(id) {
    const inputEl = document.getElementById(`lampiran-input-${id}`);
    if (!inputEl) return;

    const newTitle = inputEl.value.trim();

    if (!newTitle) {
        showToast("Judul lampiran tidak boleh kosong", "error");
        return;
    }

    // Show loading state
    const saveBtn = document.querySelector(`#lampiran-save-actions-${id} .btn-success`);
    let originalBtnContent = '';
    if (saveBtn) {
        originalBtnContent = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('judul_lampiran', newTitle);

        const response = await fetch('../proses/proses_edit_lampiran.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Update UI with new title
            const titleEl = document.getElementById(`lampiran-title-${id}`);
            if (titleEl) titleEl.innerText = newTitle;

            // Revert to display mode
            cancelEditLampiran(id);

            // Sync input value
            inputEl.value = newTitle;

            showToast("Judul lampiran berhasil diperbarui", "success");
        } else {
            showToast(result.message || "Gagal memperbarui judul", "error");
        }
    } catch (error) {
        console.error(error);
        showToast("Terjadi kesalahan sistem", "error");
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnContent;
        }
    }
}
