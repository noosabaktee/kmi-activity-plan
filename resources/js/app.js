import './exposure';

window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    const isClosed = sidebar.classList.contains('-translate-x-full');
    if (isClosed) {
        window.openSidebar();
    } else {
        window.closeSidebar();
    }
};

window.openSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('-translate-x-full');
    if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

window.closeSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.add('-translate-x-full');
    if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

window.openModal = function (modalId, titleText) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    if (titleText) {
        const modalTitle = modal.querySelector('.modal-header h3');
        if (modalTitle) modalTitle.innerText = titleText;
    }
    modal.classList.remove('hidden');
    modal.classList.add('active');
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('active');
};
