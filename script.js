document.addEventListener('DOMContentLoaded', function() {
    const changes = document.querySelectorAll('.change');
    let currentIndex = 0;

    function changeContent() {
        // Remove 'active' class from the current element
        changes[currentIndex].classList.remove('active');

        // Increment the index, looping back if necessary
        currentIndex = (currentIndex + 1) % changes.length;

        // Add 'active' class to the next element
        changes[currentIndex].classList.add('active');
    }

    // Initially show the first element
    changes[currentIndex].classList.add('active');

    // Change content every 3 seconds
    setInterval(changeContent, 3000);
  });
  function openModal(title, image1, image2, price, description) {
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalImage1").src = image1; // First image
    document.getElementById("modalImage2").src = image2; // Second image
    document.getElementById("modalPrice").innerText = price;
    document.getElementById("modalDescription").innerText = description;
  
    // Show the modal and overlay
    document.getElementById("productModal").style.display = "block";
    document.getElementById("overlay").style.display = "block"; // Show overlay
  
    // Prevent background scrolling
    document.body.classList.add("no-scroll");
  }
  
  function closeModal() {
    document.getElementById("productModal").style.display = "none"; // Hide modal
    document.getElementById("overlay").style.display = "none"; // Hide overlay
  
    // Enable background scrolling
    document.body.classList.remove("no-scroll");
  }
  
  
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
  
  
  
  // let menu = document.querySelector('#menu-btn')
  // let navbar = document.querySelector('nav')
  
  // menu.onclick = () => {
  //     navbar.classList.toggle('active')
  //     menu.classList.toggle('fa-times') //pieliek ikonu - krustinu
  // }
  // window.onscroll = () =>{
  //     navbar.classList.remove('active')
  //     menu.classList.remove('fa-times')
  // }
  
  // if ( window.history.replaceState ) {
  //     window.history.replaceState( null, null, window.location.href );
  // }
  
  // x = () => {
  //     let alert = document.getElementById("pazinojums")
  //     alert.style.display = "none"
  // }
  
  /*-----------------------------------------dropdown--------------------------------------------------- */
  // function showDropdowns() {
  //     // Get selected radio value
  //     const selectedValue = document.querySelector('input[name="color1"]:checked').value;
  
  //     // Dropdown divs
  //     const allDropdowns = document.querySelectorAll('.all');
  //     const bigDropdown = document.querySelector('.big');
  
  //     // Hide all dropdowns initially
  //     allDropdowns.forEach(dropdown => dropdown.style.display = 'none');
  //     bigDropdown.style.display = 'none';
  
  //     // Show relevant dropdowns based on the selected value
  //     if (['apala1', 'apala2', 'medaljons'].includes(selectedValue)) {
  //         // Show all dropdowns (including the small and big)
  //         allDropdowns.forEach(dropdown => dropdown.style.display = 'block');
  //         bigDropdown.style.display = 'block'; // Ensuring big dropdown is shown
  //     } else if (['lasteka', 'ramis', 'lietussargs'].includes(selectedValue)) {
  //         // Show only the small dropdowns, hide the big one
  //         allDropdowns.forEach(dropdown => {
  //             if (dropdown.classList.contains('all')) {
  //                 dropdown.style.display = 'block';
  //             }
  //         });
  //         // The big dropdown remains hidden
  //     }
  // }
  
  
  