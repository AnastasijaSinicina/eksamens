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