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



  document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.animate');

    function onScroll() {
        animateElements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                element.classList.add('visible');
            } else {
                element.classList.remove('visible');
            }
        });
    }

    window.addEventListener('scroll', onScroll);
    onScroll(); // Initial check on load
});



let menu = document.querySelector('#menu-btn')
let navbar = document.querySelector('nav')

menu.onclick = () => {
    navbar.classList.toggle('active')
    menu.classList.toggle('fa-times') //pieliek ikonu - krustinu
}
window.onscroll = () =>{
    navbar.classList.remove('active')
    menu.classList.remove('fa-times')
}

if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
}

x = () => {
    let alert = document.getElementById("pazinojums")
    alert.style.display = "none"
}

/*-----------------------------------------dropdown--------------------------------------------------- */
const krasaContainer = document.querySelector('.krasa-container');
const krasaItems = document.querySelectorAll('.krasa');
let currentSlide = 0;
const itemsPerSlide = 4;

document.getElementById('nextBtn').addEventListener('click', function() {
    currentSlide++;
    const totalSlides = Math.ceil(krasaItems.length / itemsPerSlide);

    if (currentSlide >= totalSlides) {
        currentSlide = 0; // Reset to the first set
    }

    const offset = -currentSlide * 100;
    krasaContainer.style.transform = `translateX(${offset}%)`;
});