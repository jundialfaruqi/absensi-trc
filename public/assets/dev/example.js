/**
 * Role & Permission Management Scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    const config = window.RolePermissionConfig || {};

    // --- Modal Management ---
    const addPermHandler = function() {
        document.getElementById('permission-form').action = config.routes.permissionsStore;
        document.getElementById('permission-method').value = 'POST';
        document.getElementById('permission-name').value = '';
        document.getElementById('permission-group').value = '';
        document.getElementById('permission-guard').value = 'web';
        document.getElementById('permission-modal').showModal();
    };

    const addRoleHandler = function() {
        document.getElementById('role-form').action = config.routes.rolesStore;
        document.getElementById('role-method').value = 'POST';
        document.getElementById('role-name').value = '';
        document.getElementById('role-guard').value = 'web';
        const color = '#64748b';
        document.getElementById('role-color').value = color;
        document.getElementById('role-color-text').value = color;
        document.querySelectorAll('#role-form input[type=checkbox]').forEach(cb => cb.checked = false);
        document.getElementById('role-modal').showModal();
    };

    document.getElementById('btn-add-permission')?.addEventListener('click', addPermHandler);
    document.getElementById('fab-add-permission')?.addEventListener('click', addPermHandler);

    document.getElementById('btn-add-role')?.addEventListener('click', addRoleHandler);
    document.getElementById('fab-add-role')?.addEventListener('click', addRoleHandler);

    // --- Color Picker Sync ---
    const roleColor = document.getElementById('role-color');
    const roleColorText = document.getElementById('role-color-text');

    roleColor?.addEventListener('input', function() {
        roleColorText.value = this.value;
    });

    roleColorText?.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            roleColor.value = this.value;
        }
    });

    document.querySelectorAll('button[data-edit-permission]')?.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.editPermission;
            document.getElementById('permission-form').action = config.routes.permissionsBaseUrl + "/" + id;
            document.getElementById('permission-method').value = 'PUT';
            document.getElementById('permission-name').value = this.dataset.name || '';
            document.getElementById('permission-group').value = this.dataset.group || '';
            document.getElementById('permission-guard').value = this.dataset.guard || 'web';
            document.getElementById('permission-modal').showModal();
        });
    });

    document.querySelectorAll('button[data-edit-role]')?.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.editRole;
            document.getElementById('role-form').action = config.routes.rolesBaseUrl + "/" + id;
            document.getElementById('role-method').value = 'PUT';
            document.getElementById('role-name').value = this.dataset.name || '';
            document.getElementById('role-guard').value = this.dataset.guard || 'web';
            const color = this.dataset.color || '#64748b';
            document.getElementById('role-color').value = color;
            document.getElementById('role-color-text').value = color;
            document.querySelectorAll('#role-form input[type=checkbox]').forEach(cb => cb.checked = false);
            const ids = (this.dataset.permissionIds || '').split(',').map(s => s.trim()).filter(Boolean);
            if (ids.length) {
                const set = new Set(ids);
                document.querySelectorAll('#role-form input[type=checkbox][name="permission_ids[]"]').forEach(cb => {
                    if (set.has(cb.value)) cb.checked = true;
                });
            }
            document.getElementById('role-modal').showModal();
        });
    });

    document.querySelectorAll('button[data-close]')?.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-close');
            const modal = document.getElementById(id);
            if (modal) {
                modal.querySelectorAll('.text-red-500').forEach(el => el.remove());
                modal.close();
            }
        });
    });

    document.querySelectorAll('.rp-delete-btn')?.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            const id = this.dataset.id;
            const name = this.dataset.name || '';
            const form = document.getElementById('rp-delete-form');
            const modal = document.getElementById('rp-delete-modal');
            document.getElementById('rp-delete-name').textContent = name;
            if (type === 'permission') {
                form.action = config.routes.permissionsBaseUrl + "/" + id;
            } else {
                form.action = config.routes.rolesBaseUrl + "/" + id;
            }
            modal.showModal();
        });
    });

    // --- Search & Suggestion ---
    const searchInput = document.getElementById('rp-search-input');
    const searchSuggestions = document.getElementById('rp-search-suggestions');
    const searchClearBtn = document.getElementById('rp-search-clear');
    let searchTimer = null;

    function hideSuggestions() {
        searchSuggestions.classList.add('hidden');
        searchSuggestions.innerHTML = '';
    }

    function updateSearchClearBtn() {
        const hasValue = searchInput.value.trim().length > 0;
        if (hasValue) {
            searchClearBtn.classList.remove('hidden');
            searchClearBtn.classList.add('flex', 'items-center');
        } else {
            searchClearBtn.classList.add('hidden');
            searchClearBtn.classList.remove('flex', 'items-center');
        }
    }

    function showSuggestions(data) {
        let html = '';
        html += '<div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-base-content/40 bg-base-200/50">Roles</div>';
        if (!data.roles || !data.roles.length) {
            html += '<div class="px-3 py-2 text-sm text-base-content/60 italic">Tidak ada data</div>';
        } else {
            html += '<ul class="menu menu-sm w-full p-0">' + data.roles.map(r =>
                '<li><button type="button" data-q="' + encodeURIComponent(r.name) + '">' +
                '<div class="flex items-center gap-2 text-left">' +
                '<div class="w-2 h-2 rounded-full" style="background-color: ' + (r.color || '#64748b') + '"></div>' +
                '<div class="flex flex-col">' +
                '<span class="font-medium">' + (r.name ?? '') + '</span>' +
                '<span class="text-xs opacity-60">' + (r.guard ?? '') + '</span>' +
                '</div>' +
                '</div></button></li>'
            ).join('') + '</ul>';
        }

        html += '<div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-base-content/40 bg-base-200/50 border-t border-base-200">Permissions</div>';
        if (!data.permissions || !data.permissions.length) {
            html += '<div class="px-3 py-2 text-sm text-base-content/60 italic">Tidak ada data</div>';
        } else {
            html += '<ul class="menu menu-sm w-full p-0">' + data.permissions.map(p =>
                '<li><button type="button" data-q="' + encodeURIComponent(p.name) + '">' +
                '<div class="flex flex-col text-left">' +
                '<span class="font-medium">' + (p.name ?? '') + '</span>' +
                '<span class="text-xs opacity-60">' + [p.group, p.guard].filter(Boolean).join(' • ') + '</span>' +
                '</div></button></li>'
            ).join('') + '</ul>';
        }
        searchSuggestions.innerHTML = html;
        searchSuggestions.classList.remove('hidden');
    }

    function performSearch(q) {
        if (!q) {
            hideSuggestions();
            updateSearchClearBtn();
            return;
        }
        fetch(config.routes.permissionsSuggest + `?q=` + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(d => showSuggestions(d))
        .catch(() => showSuggestions({ roles: [], permissions: [] }));
    }

    searchInput?.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            const params = new URLSearchParams(window.location.search);
            params.set('q', this.value);
            window.location.href = config.routes.index + (params.toString() ? '?' + params.toString() : '');
        }
    });

    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        searchTimer = setTimeout(() => performSearch(q), 200);
        updateSearchClearBtn();
    });

    searchClearBtn?.addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        params.delete('q');
        window.location.href = config.routes.index + (params.toString() ? '?' + params.toString() : '');
    });

    searchSuggestions?.addEventListener('mousedown', function(e) {
        const btn = e.target.closest('button[data-q]');
        if (!btn) return;
        const q = decodeURIComponent(btn.getAttribute('data-q') || '');
        const url = new URL(window.location.href);
        url.searchParams.set('q', q);
        window.location = url.toString();
    });

    updateSearchClearBtn();

    // --- Toast Handler ---
    const successToast = document.getElementById('success-toast');
    const errorToast = document.getElementById('error-toast');
    [successToast, errorToast].forEach(function(el) {
        if (!el) return;
        setTimeout(function() {
            el.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => el.remove(), 500);
        }, 8000);
    });

    // --- Validation Errors & Modal Auto-open ---
    const modalType = config.old.modalType;
    if (modalType === 'permission') {
        const permModal = document.getElementById('permission-modal');
        if (permModal && permModal.querySelector('.text-red-500')) permModal.showModal();
    } else if (modalType === 'role') {
        const roleModal = document.getElementById('role-modal');
        if (roleModal && roleModal.querySelector('.text-red-500')) roleModal.showModal();
    }

});
