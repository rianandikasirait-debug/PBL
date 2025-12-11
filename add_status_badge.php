<?php
// Script to add status badge and remove "Baru" badge

$file = 'c:/laragon/www/smartnote/peserta/dashboard_peserta.php';
$content = file_get_contents($file);

// Replace the JavaScript section to add status badge
$old = '                data.forEach((item, index) => {
                    const judul = escapeHtml(item.judul_rapat || \'\');
                    const tanggal = escapeHtml(item.tanggal_rapat || \'\');
                    const pembuat = escapeHtml(item.created_by || \'Admin\');
                    const pesertaCount = item.peserta ? item.peserta.split(\',\').length : 0;

                    const card = document.createElement(\'div\');
                    card.className = \'col\'; // Grid column
                    
                    // Badge Baru (New) if not viewed
                     const badge = !item.is_viewed ? 
                        `<span class="position-absolute top-0 start-0 m-2 badge rounded-pill bg-danger border border-light shadow-sm" style="z-index: 10;">
                            Baru
                            <span class="visually-hidden">unread messages</span>
                        </span>` : \'\';

                    // Match Admin Style (Grid Layout)
                    card.innerHTML = `
                        <div class="mobile-card h-100 p-3 rounded-3 position-relative shadow-sm" style="cursor: pointer;" onclick="if(!event.target.closest(\'a\') && !event.target.closest(\'button\')) window.location.href=\'detail_rapat_peserta.php?id=${encodeURIComponent(item.id)}\'">
                            ${badge}
                            <!-- Header: Actions (Badge removed) -->
                            <div class="d-flex justify-content-end align-items-center mb-2">
                                <div class="d-flex gap-2">
                                    ${item.Lampiran ? `<a href="../file/${encodeURIComponent(item.Lampiran)}" class="btn btn-sm text-secondary p-0" title="Download" download><i class="bi bi-download fs-5"></i></a>` : \'\'}
                                </div>
                            </div>

                            <!-- Body: Title & Metadata -->
                            <div>
                                <h5 class="fw-bold text-dark mb-3 text-truncate" title="${judul}">${judul}</h5>
                                
                                <div class="d-flex flex-column gap-2 text-secondary small">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>${tanggal}</span>
                                    </div>';

$new = '                data.forEach((item, index) => {
                    const judul = escapeHtml(item.judul_rapat || \'\');
                    const tanggal = escapeHtml(item.tanggal_rapat || \'\');
                    const pembuat = escapeHtml(item.created_by || \'Admin\');
                    const pesertaCount = item.peserta ? item.peserta.split(\',\').length : 0;
                    const status = escapeHtml(item.status || \'draft\');
                    
                    // Format tanggal dengan jam
                    let tanggalDenganJam = tanggal;
                    if (item.created_at) {
                        const dateObj = new Date(item.created_at);
                        const jam = dateObj.toLocaleTimeString(\'id-ID\', { hour: \'2-digit\', minute: \'2-digit\' });
                        tanggalDenganJam = `${tanggal} • ${jam}`;
                    }
                    
                    // Status badge - FINAL WARNA HIJAU!
                    const statusBadge = status === \'final\' 
                        ? \'<span class="badge d-flex align-items-center gap-1" style="background-color: #198754 !important; color: white;"><i class="bi bi-check-circle"></i> Final</span>\'
                        : \'<span class="badge bg-secondary d-flex align-items-center gap-1"><i class="bi bi-pencil-square"></i> Draft</span>\';

                    const card = document.createElement(\'div\');
                    card.className = \'col\'; // Grid column

                    // Match Admin Style (Grid Layout)
                    card.innerHTML = `
                        <div class="mobile-card h-100 p-3 rounded-3 position-relative shadow-sm" style="cursor: pointer;" onclick="if(!event.target.closest(\'a\') && !event.target.closest(\'button\')) window.location.href=\'detail_rapat_peserta.php?id=${encodeURIComponent(item.id)}\'">
                            <!-- Header: Status Badge & Actions -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                ${statusBadge}
                                <div class="d-flex gap-2">
                                    ${item.Lampiran ? `<a href="../file/${encodeURIComponent(item.Lampiran)}" class="btn btn-sm text-secondary p-0" title="Download" download><i class="bi bi-download fs-5"></i></a>` : \'\'}
                                </div>
                            </div>

                            <!-- Body: Title & Metadata -->
                            <div>
                                <h5 class="fw-bold text-dark mb-3 text-truncate" title="${judul}">${judul}</h5>
                                
                                <div class="d-flex flex-column gap-2 text-secondary small">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>${tanggalDenganJam}</span>
                                    </div>';

$content = str_replace($old, $new, $content);

file_put_contents($file, $content);
echo "Badge status added successfully!\n";
echo "- Removed 'Baru' badge\n";
echo "- Added Draft/Final badge with GREEN color for Final\n";
echo "- Added time to date\n";
