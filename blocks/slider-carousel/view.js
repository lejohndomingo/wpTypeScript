(function () {
  'use strict';

  function initSlider(slider) {
    var track = slider.querySelector('.wptypescript-slider-track');
    if (!track) return;

    var slides = track.children;
    var slideCount = slides.length;
    if (slideCount < 2) return;

    var autoplay = slider.getAttribute('data-autoplay') === 'true';
    var autoplaySpeed = parseInt(slider.getAttribute('data-autoplay-speed')) || 5000;
    var showArrows = slider.getAttribute('data-show-arrows') !== 'false';
    var showDots = slider.getAttribute('data-show-dots') !== 'false';
    var currentIndex = 0;
    var autoplayTimer = null;

    function goTo(index) {
      if (index < 0) index = slideCount - 1;
      if (index >= slideCount) index = 0;
      currentIndex = index;
      track.style.transform = 'translateX(-' + (index * 100) + '%)';

      if (showDots) {
        var dots = slider.querySelectorAll('.wptypescript-slider-dot');
        for (var d = 0; d < dots.length; d++) {
          dots[d].classList.toggle('active', d === index);
        }
      }

      if (showArrows) {
        var prev = slider.querySelector('.wptypescript-slider-prev');
        var next = slider.querySelector('.wptypescript-slider-next');
        if (prev) prev.setAttribute('aria-label', 'Go to slide ' + (index === 0 ? slideCount : index));
        if (next) next.setAttribute('aria-label', 'Go to slide ' + (index + 2 > slideCount ? 1 : index + 2));
      }
    }

    if (showArrows) {
      var prevBtn = document.createElement('button');
      prevBtn.className = 'wptypescript-slider-arrow wptypescript-slider-prev';
      prevBtn.setAttribute('aria-label', 'Go to slide ' + slideCount);
      prevBtn.setAttribute('type', 'button');
      prevBtn.innerHTML = '&#8249;';
      prevBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        goTo(currentIndex - 1);
      });
      slider.appendChild(prevBtn);

      var nextBtn = document.createElement('button');
      nextBtn.className = 'wptypescript-slider-arrow wptypescript-slider-next';
      nextBtn.setAttribute('aria-label', 'Go to slide 2');
      nextBtn.setAttribute('type', 'button');
      nextBtn.innerHTML = '&#8250;';
      nextBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        goTo(currentIndex + 1);
      });
      slider.appendChild(nextBtn);
    }

    if (showDots) {
      var dotsContainer = document.createElement('div');
      dotsContainer.className = 'wptypescript-slider-dots';
      dotsContainer.setAttribute('role', 'tablist');
      for (var i = 0; i < slideCount; i++) {
        (function (index) {
          var dot = document.createElement('button');
          dot.className = 'wptypescript-slider-dot' + (i === 0 ? ' active' : '');
          dot.setAttribute('role', 'tab');
          dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
          dot.setAttribute('type', 'button');
          dot.addEventListener('click', function (e) {
            e.stopPropagation();
            goTo(index);
          });
          dotsContainer.appendChild(dot);
        })(i);
      }
      slider.appendChild(dotsContainer);
    }

    var startX = 0;
    var isDragging = false;

    slider.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
      isDragging = true;
    }, { passive: true });

    slider.addEventListener('touchend', function (e) {
      if (!isDragging) return;
      isDragging = false;
      var endX = e.changedTouches[0].clientX;
      var diff = startX - endX;
      if (Math.abs(diff) > 50) {
        goTo(currentIndex + (diff > 0 ? 1 : -1));
      }
    }, { passive: true });

    if (autoplay) {
      function startAutoplay() {
        stopAutoplay();
        autoplayTimer = setInterval(function () {
          goTo(currentIndex + 1);
        }, autoplaySpeed);
      }

      function stopAutoplay() {
        if (autoplayTimer) {
          clearInterval(autoplayTimer);
          autoplayTimer = null;
        }
      }

      startAutoplay();
      slider.addEventListener('mouseenter', stopAutoplay, { passive: true });
      slider.addEventListener('mouseleave', startAutoplay, { passive: true });
      slider.addEventListener('touchstart', stopAutoplay, { passive: true });
      slider.addEventListener('touchend', startAutoplay, { passive: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      var sliders = document.querySelectorAll('.wp-block-wptypescript-slider-carousel');
      for (var s = 0; s < sliders.length; s++) {
        initSlider(sliders[s]);
      }
    });
  } else {
    var sliders = document.querySelectorAll('.wp-block-wptypescript-slider-carousel');
    for (var s = 0; s < sliders.length; s++) {
      initSlider(sliders[s]);
    }
  }
})();
