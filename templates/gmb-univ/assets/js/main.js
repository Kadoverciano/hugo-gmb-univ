'user strict';

// Preloader
$(window).on('load', function () {
  $('.preloader').fadeOut(1000);
});

//Menu Dropdown
$('ul>li>.sub-menu').parent('li').addClass('has-sub-menu');

$('.menu li a').on('click', function () {
  var element = $(this).parent('li');
  if (element.hasClass('open')) {
    element.removeClass('open');
    element.find('li').removeClass('open');
    element.find('ul').slideUp(300, 'swing');
  } else {
    element.addClass('open');
    element.children('ul').slideDown(300, 'swing');
    element.siblings('li').children('ul').slideUp(300, 'swing');
    element.siblings('li').removeClass('open');
    element.siblings('li').find('li').removeClass('open');
    element.siblings('li').find('ul').slideUp(300, 'swing');
  }
});

// Responsive Menu
var headerTrigger = $('.header-trigger');
headerTrigger.on('click', function () {
  $('.menu').toggleClass('active');
  $('.overlay').toggleClass('active');
});

var headerTrigger2 = $('.top-bar-trigger');
headerTrigger2.on('click', function () {
  $('.header-top').toggleClass('active');
  $('.overlay').addClass('active');
  $('.overlay').removeClass('active');
});

// Overlay Event
var over = $('.overlay');
over.on('click', function () {
  $('.overlay').removeClass('overlay-color');
  $('.overlay').removeClass('active');
  $('.menu, .header-trigger').removeClass('active');
  $('.header-top').removeClass('active');
});

// Sticky Menu
window.addEventListener('scroll', function () {
  var header = document.querySelector('.header');
  header.classList.toggle('sticky', window.scrollY > 0);
});

// Scroll To Top
var scrollTop = $('.scrollToTop');
$(window).on('scroll', function () {
  if ($(this).scrollTop() < 500) {
    scrollTop.removeClass('active');
  } else {
    scrollTop.addClass('active');
  }
});

//Click event to scroll to top
$('.scrollToTop').on('click', function () {
  $('html, body').animate(
    {
      scrollTop: 0,
    },
    300
  );
  return false;
});

$('.feature__slider').slick({
  fade: false,
  slidesToShow: 4,
  slidesToScroll: 1,
  infinite: true,
  autoplay: true,
  pauseOnHover: true,
  centerMode: false,
  dots: false,
  arrows: true,
  nextArrow: '<i class="las la-arrow-right arrow-right"></i>',
  prevArrow: '<i class="las la-arrow-left arrow-left"></i> ',
  responsive: [
    {
      breakpoint: 1199,
      settings: {
        slidesToShow: 3,
      },
    },
    {
      breakpoint: 991,
      settings: {
        slidesToShow: 2,
      },
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 2,
      },
    },
    {
      breakpoint: 575,
      settings: {
        slidesToShow: 1,
      },
    },
  ],
});

$('.testimonial-slider').slick({
  fade: false,
  slidesToShow: 3,
  slidesToScroll: 1,
  infinite: true,
  autoplay: true,
  pauseOnHover: true,
  centerMode: false,
  dots: true,
  arrows: false,
  nextArrow: '<i class="las la-arrow-right arrow-right"></i>',
  prevArrow: '<i class="las la-arrow-left arrow-left"></i> ',
  responsive: [
    {
      breakpoint: 1199,
      settings: {
        slidesToShow: 3,
      },
    },
    {
      breakpoint: 992,
      settings: {
        slidesToShow: 2,
      },
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 1,
      },
    },
    {
      breakpoint: 575,
      settings: {
        slidesToShow: 1,
      },
    },
  ],
});

$('.top-investor-slider').slick({
  fade: false,
  slidesToShow: 3,
  slidesToScroll: 1,
  infinite: true,
  autoplay: true,
  pauseOnHover: true,
  centerMode: false,
  dots: false,
  arrows: false,
  responsive: [
    {
      breakpoint: 1199,
      settings: {
        slidesToShow: 3,
      },
    },
    {
      breakpoint: 991,
      settings: {
        slidesToShow: 4,
      },
    },
    {
      breakpoint: 767,
      settings: {
        slidesToShow: 3,
      },
    },
    {
      breakpoint: 400,
      settings: {
        slidesToShow: 2,
      },
    },
  ],
});

// Odometer Counter
$('.counter__item, .dashboard__card__item').each(function () {
  $(this).isInViewport(function (status) {
    if (status === 'entered') {
      for (var i = 0; i < document.querySelectorAll('.odometer').length; i++) {
        var el = document.querySelectorAll('.odometer')[i];
        el.innerHTML = el.getAttribute('data-odometer-final');
      }
    }
  });
});

//Faq
$('.faq-item__title').on('click', function (e) {
  var element = $(this).parent('.faq-item');
  if (element.hasClass('open')) {
    element.removeClass('open');
    element.find('.faq-item__content').removeClass('open');
    element.find('.faq-item__content').slideUp(300, 'swing');
  } else {
    element.addClass('open');
    element.children('.faq-item__content').slideDown(300, 'swing');
    element.siblings('.faq-item').children('.faq-item__content').slideUp(300, 'swing');
    element.siblings('.faq-item').removeClass('open');
    element.siblings('.faq-item').find('.faq-item__content').slideUp(300, 'swing');
  }
});

$('.user-thumb').on('click', function () {
  $('.dashboard__sidebar').addClass('active');
  $('.overlay').addClass('active');
});

$('.single-select').on('click', function () {
  $('.single-select').removeClass('active');
  $(this).addClass('active');
});

$('.btn-close, .overlay').on('click', function () {
  $('.overlay').removeClass('active');
  $('.menu').removeClass('active');
});

$('.how-item').on('mouseover', function () {
  $('.how-item').removeClass('active');
  $(this).addClass('active');
});

// Dashboard Collapse Expand
$('.user-toggler').on('click', function () {
  $('.dashboard-sidebar').toggleClass('active');
  $('.overlay').toggleClass('overlay-color');
});
$('.close-dashboard, .overlay').on('click', function () {
  $('.dashboard-sidebar').removeClass('active');
  $('.overlay').removeClass('overlay-color');
});

document.addEventListener('DOMContentLoaded', function () {
  const menu = document.getElementById('mobile-menu');
  const trigger = document.getElementById('mobile-trigger');
  const closeBtn = document.getElementById('mobile-close') || (menu ? menu.querySelector('.nbtn-close') : null);

  // Функция закрытия
  function closeMenu() {
    if (!menu) return;
    menu.classList.remove('active');
    if (trigger) trigger.classList.remove('active');
    document.body.classList.remove('overflow-hidden');

    menu.querySelectorAll('.expanded').forEach((item) => item.classList.remove('expanded'));
  }

  function toggleMenu() {
    if (!menu) return;
    if (menu.classList.contains('active')) {
      closeMenu();
    } else {
      menu.classList.add('active');
      if (trigger) trigger.classList.add('active');
      document.body.classList.add('overflow-hidden');
    }
  }

  if (trigger) {
    trigger.onclick = function (e) {
      e.preventDefault();
      toggleMenu();
    };
  }
  if (closeBtn) {
    closeBtn.onclick = function (e) {
      e.preventDefault();
      closeMenu();
    };
  }

  // --- АККОРДЕОН ---
  const accordionLinks = document.querySelectorAll('li.has-sub-menu > a');
  accordionLinks.forEach(function (link) {
    link.onclick = function (e) {
      if (window.innerWidth < 992) {
        e.preventDefault();
        e.stopPropagation();

        const parentLi = this.closest('li');
        if (parentLi) {
          parentLi.classList.toggle('expanded');
        }
      }
    };
  });
});

document.addEventListener('DOMContentLoaded', function () {
  let popup = document.getElementById('popup');
  let closePopup = document.getElementById('closePopup');

  setTimeout(() => {
    popup.classList.add('show');
  }, 1000);

  closePopup.addEventListener('click', function () {
    popup.classList.remove('show');
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const header = document.querySelector('.header-inner');
  const scrollThreshold = 125;

  window.addEventListener('scroll', () => {
    if (window.scrollY > scrollThreshold) {
      header.classList.add('fixed');
    } else {
      header.classList.remove('fixed');
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const dragonItems = document.querySelectorAll('.dragon-item');

  if (dragonItems.length) {
    dragonItems.forEach((item) => {
      const numBlock = item.querySelector('.dragon-num span');
      if (numBlock) {
        const randomNum = Math.floor(Math.random() * (999 - 150 + 1)) + 150;
        numBlock.textContent = randomNum;
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const liveItems = document.querySelectorAll('.live-span_text span');

  if (liveItems.length) {
    liveItems.forEach((span) => {
      const randomNum = Math.floor(Math.random() * (7999 - 3100 + 1)) + 3100;
      span.textContent = randomNum;
    });
  }
});
