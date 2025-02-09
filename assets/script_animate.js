
  
  let currentIndex = 0;
  
  function updateButtons() {
      const prevButton = document.querySelector('.prev');
      const nextButton = document.querySelector('.next');
      const totalBoxes = document.querySelectorAll('#parMums .box').length;
  
      // Hide prev button on first box
      if (currentIndex === 0) {
          prevButton.style.display = 'none';
      } else {
          prevButton.style.display = 'block';
      }
  
      // Hide next button on last box
      if (currentIndex === totalBoxes - 1) {
          nextButton.style.display = 'none';
      } else {
          nextButton.style.display = 'block';
      }
  }
  
  function nextSlide() {
      const boxContainer = document.querySelector('#parMums .box-container');
      const totalBoxes = document.querySelectorAll('#parMums .box').length;
      if (currentIndex < totalBoxes - 1) {
          currentIndex++;
          boxContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      }
      updateButtons();
  }
  
  function prevSlide() {
      const boxContainer = document.querySelector('#parMums .box-container');
      if (currentIndex > 0) {
          currentIndex--;
          boxContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      }
      updateButtons();
  }
  
  // Call updateButtons after the DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
      updateButtons();
  });
  
  
  
  
  
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
  

  
  