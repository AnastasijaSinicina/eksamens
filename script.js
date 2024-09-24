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























const snowContainer = document.getElementById("snow-container");
const snowContent = ['&#10052', '&#10053', '&#10054']

const random = (num) => {
  return Math.floor(Math.random() * num);
}

const getRandomStyles = () => {
  const top = random(100);
  const left = random(100);
  const dur = random(10) + 10;
  const size = random(25) + 25;
  return `
    top: -${top}%;
    left: ${left}%;
    font-size: ${size}px;
    animation-duration: ${dur}s;
  `;
}

const createSnow = (num) => {
  for (var i = num; i > 0; i--) {
    var snow = document.createElement("div");
    snow.className = "snow";
    snow.style.cssText = getRandomStyles();
    snow.innerHTML = snowContent[random(3)]
    snowContainer.append(snow);
  }
}

const removeSnow = () => {
  snowContainer.style.opacity = "0";
  setTimeout(() => {
    snowContainer.remove()
  }, 500)
}

window.addEventListener("load", () => {
  createSnow(30)
  setTimeout(removeSnow, (1000 * 60))
});

window.addEventListener("click", () => {
  removeSnow()
});

