  //----------------------------------------------------------------HEADER----------------------------------------------------------------------------------------------------------
  let lastScrollTop = 0;
  const header = document.querySelector("header");
  
  window.addEventListener("scroll", function () {
      let scrollTop = window.scrollY;
  
      if (scrollTop > lastScrollTop) {
          // Scrolling down
          header.style.transform = "translateY(-100%)";
      } else {
          // Scrolling up
          header.style.transform = "translateY(0)";
      }
      
      lastScrollTop = scrollTop;
  });

  document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menu-btn');
    const nav = document.querySelector('header .nav');
    
    menuBtn.addEventListener('click', function() {
        nav.classList.toggle('active');
        this.querySelector('i').classList.toggle('fa-bars');
        this.querySelector('i').classList.toggle('fa-times');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!nav.contains(event.target) && !menuBtn.contains(event.target) && nav.classList.contains('active')) {
            nav.classList.remove('active');
            menuBtn.querySelector('i').classList.add('fa-bars');
            menuBtn.querySelector('i').classList.remove('fa-times');
        }
    });
});
  
//---------------------------------------------------------------------------------PRODUKCIJAS BILŽU KONTEINERIS---------------------------------------------------------------------
function initializeSplide() {
    const mainSliderElement = document.getElementById('main-slider');
    const thumbnailSliderElement = document.getElementById('thumbnail-slider');
    
    if (mainSliderElement && thumbnailSliderElement) {
        // Check if Splide is loaded
        if (typeof Splide !== 'undefined') {
            var mainSlider = new Splide('#main-slider', {
                type: 'fade',
                rewind: true,
                pagination: false,
                arrows: false,
            });

            var thumbnailSlider = new Splide('#thumbnail-slider', {
                fixedWidth: 100,
                fixedHeight: 60,
                gap: 10,
                rewind: true,
                pagination: false,
                cover: true,
                isNavigation: true,
                focus: 'center',
                breakpoints: {
                    600: {
                        fixedWidth: 60,
                        fixedHeight: 44,
                    },
                },
            });

            mainSlider.sync(thumbnailSlider);
            mainSlider.mount();
            thumbnailSlider.mount();
        } else {
            // If Splide is not loaded yet, try again after a short delay
            setTimeout(initializeSplide, 100);
        }
    }
}

// Try to initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeSplide);

// Also try when window is fully loaded as fallback
window.addEventListener('load', initializeSplide);

  

//----------------------------------------------------------------------------------------------PROFILS--------------------------------------------------------

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Noņem aktīvo klasi no visiem pogas un paneliem
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Pievieno aktīvo klasi noklikšķinātai pogai un atbilstošajam panelim
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });

        // Order expand/collapse functionality
    const expandButtons = document.querySelectorAll('.expand-button');
    
    expandButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = button.getAttribute('data-order');
            const orderDetails = document.getElementById(targetId);
            const chevronIcon = button.querySelector('i');
            const buttonText = button.querySelector('.expand-text') || button;
            
            if (orderDetails.style.display === 'none' || orderDetails.style.display === '') {
                orderDetails.style.display = 'block';
                chevronIcon.className = 'fas fa-chevron-up';
                button.innerHTML = '<i class="fas fa-chevron-up"></i> Slēpt detaļas';
            } else {
                orderDetails.style.display = 'none';
                chevronIcon.className = 'fas fa-chevron-down';
                button.innerHTML = '<i class="fas fa-chevron-down"></i> Skatīt detaļas';
            }
        });
    });
});
  