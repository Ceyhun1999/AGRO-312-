AOS.init();
// Находим все элементы <a> с классом 'no-click'
const noClickLinks = document.querySelectorAll('a.no-click');

// Проходимся по каждому элементу и отключаем клик
noClickLinks.forEach(link => {
    link.style.pointerEvents = 'none';
});

/*header-slider script start*/
let swiper = new Swiper(".mySwiper", {
    slidesPerView: 1,
    direction: "vertical",
    grabCursor: true,
    autoplay: {
        delay: 3000, // Задержка между сменой слайдов в миллисекундах (3 секунды)
        disableOnInteraction: false, // Продолжать автопроигрывание после взаимодействия
    },
    pagination: {
        el: ".swiper-pagination",
        type: "fraction",
        clickable: true,
    },
    breakpoints: {
        0: {
            grabCursor: true,
            direction: "horizontal",
        },
        992: {
            direction: "vertical",
            grabCursor: true,
        },
    },
});

function updatePagination() {
    // Generate slide numbers dynamically
    const slideNumbers = document.querySelector(".slider__numbers");
    slideNumbers.innerHTML = "";
    for (let i = 0; i < swiper.slides.length; i++) {
        const slideNumber = (i + 1).toString().padStart(2, "0");
        const span = document.createElement("span");
        span.setAttribute("data-slide-index", i);
        span.textContent = slideNumber;
        if (i === swiper.realIndex) {
            span.classList.add("active");
        }
        slideNumbers.appendChild(span);
    }
}

swiper.on("init", updatePagination);
swiper.on("slideChange", updatePagination);

// Delay the initial formatting to ensure Swiper has fully initialized
setTimeout(updatePagination, 0);

// Handle click events on slide numbers
document.querySelector(".slide-numbers")?.addEventListener("click", function (event) {
    if (event.target.tagName === "SPAN") {
        const slideIndex = parseInt(event.target.getAttribute("data-slide-index"), 10);
        swiper.slideTo(slideIndex);
    }
});
/*header-slider script end*/

/*header animation script start*/
window.addEventListener("scroll", function () {
    const scrollTop = window.scrollY || window.pageYOffset;
    const header = document.querySelector("header");

    // Set the threshold for the sticky behavior
    const stickyThreshold = 30;

    // Toggle the sticky class based on the scroll position
    if (scrollTop >= stickyThreshold) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
});
/*header animation script end*/

 const items = document.querySelectorAll(".header__languages-item");

  // Определяем соответствие языка и значения order
  const orderMap = {
    ru: 1,
    kg: 2,
    en: 3,
    zh: 4
  };

  // Присваиваем каждому элементу соответствующий order
  items.forEach(item => {
    const lang = item.getAttribute("data-lang");
    if (orderMap[lang] !== undefined) {
      item.style.order = orderMap[lang];
    }
  });