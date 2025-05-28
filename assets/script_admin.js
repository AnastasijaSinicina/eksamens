const sidebar = document.querySelector(".sidebar");
const sidebarToggler = document.querySelector(".sidebar-toggler");
const menuToggler = document.querySelector(".menu-toggler");
const mainContent = document.querySelector("main");

const collapsedSidebarHeight = "56px";

sidebarToggler.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    // Force DOM reflow to ensure styles are applied correctly
    void mainContent.offsetWidth;
});

const toggleMenu = (isMenuActive) => {
    sidebar.style.height = isMenuActive ? `${sidebar.scrollHeight}px` : collapsedSidebarHeight;
}

menuToggler.addEventListener("click", () => {
    const isActive = sidebar.classList.toggle("menu-active");
    toggleMenu(isActive);
    // Force DOM reflow for mobile view as well
    void mainContent.offsetWidth;
});

 function showConfirmModal(message, onConfirm, onCancel = null) {
        const modal = document.getElementById('confirmModal');
        const messageElement = document.getElementById('confirmMessage');
        const yesButton = document.getElementById('confirmYes');
        const noButton = document.getElementById('confirmNo');
        
        messageElement.textContent = message;
        modal.style.display = 'block';
        
        // Remove any existing event listeners
        const newYesButton = yesButton.cloneNode(true);
        const newNoButton = noButton.cloneNode(true);
        yesButton.parentNode.replaceChild(newYesButton, yesButton);
        noButton.parentNode.replaceChild(newNoButton, noButton);
        
        // Add new event listeners
        document.getElementById('confirmYes').addEventListener('click', function() {
            hideConfirmModal();
            if (onConfirm) onConfirm();
        });
        
        document.getElementById('confirmNo').addEventListener('click', function() {
            hideConfirmModal();
            if (onCancel) onCancel();
        });
    }

    function hideConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    // Close confirmation modal when clicking outside
    window.addEventListener('click', function(event) {
        const confirmModal = document.getElementById('confirmModal');
        if (event.target === confirmModal) {
            hideConfirmModal();
        }
    });