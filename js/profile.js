document.addEventListener('DOMContentLoaded', function () {
    // Session success message
    if (window.sessionSuccessMessage) {
        showToast(window.sessionSuccessMessage, 'success');
    }

    // Logout Logic
    async function confirmLogout(e) {
        e.preventDefault();
        const confirmed = await showConfirm("Yakin mau keluar?");
        if (confirmed) window.location.href = "../proses/proses_logout.php";
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) logoutBtn.addEventListener("click", confirmLogout);

    const logoutBtnMobile = document.getElementById("logoutBtnMobile");
    if (logoutBtnMobile) logoutBtnMobile.addEventListener("click", confirmLogout);
});

// Toast Logic (using Bootstrap Toast if available, or fallback)
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        // Create container if not exists
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
    }

    const toastId = 'toast' + Date.now();
    const bgClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';

    const html = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    document.querySelector('.toast-container').insertAdjacentHTML('beforeend', html);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Edit Profile Logic
const editProfileForm = document.getElementById('editProfileForm');
const modalFotoInput = document.getElementById('modalFotoInput');
const modalProfilePreview = document.getElementById('modalProfilePreview');
const btnDeletePhoto = document.getElementById('btnDeletePhoto');
const deletePhotoContainer = document.getElementById('deletePhotoContainer');
const deletePhotoInput = document.getElementById('deletePhotoInput');
const btnSaveProfile = document.getElementById('btnSaveProfile');

if (editProfileForm) {
    // Handle Image Preview
    if (modalFotoInput) {
        modalFotoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Ukuran file terlalu besar (maks 2MB)', 'danger');
                    this.value = ''; // Reset input
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    modalProfilePreview.src = e.target.result;
                    if (deletePhotoContainer) deletePhotoContainer.style.display = 'block';
                    if (deletePhotoInput) deletePhotoInput.value = '0';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle Delete Photo
    if (btnDeletePhoto) {
        btnDeletePhoto.addEventListener('click', function () {
            modalProfilePreview.src = '../file/user.jpg'; // Reset to default
            modalFotoInput.value = ''; // Clear file input
            if (deletePhotoInput) deletePhotoInput.value = '1'; // Set erase flag
            if (deletePhotoContainer) deletePhotoContainer.style.display = 'none';
        });
    }

    // Handle Form Submission
    editProfileForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Show Spinner
        const originalBtnText = btnSaveProfile.innerHTML;
        btnSaveProfile.disabled = true;
        btnSaveProfile.querySelector('.spinner-border').classList.remove('d-none');

        const formData = new FormData(this);

        try {
            const response = await fetch('../proses/proses_edit_profile_ajax.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showToast(result.message || 'Terjadi kesalahan', 'danger');
                btnSaveProfile.disabled = false;
                btnSaveProfile.querySelector('.spinner-border').classList.add('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Gagal menghubungi server', 'danger');
            btnSaveProfile.disabled = false;
            btnSaveProfile.querySelector('.spinner-border').classList.add('d-none');
        }
    });
}
